<?php

namespace App\Command;

use App\Entity\SshAttempt;
use App\Repository\VpsServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:parse-ssh-logs',
    description: 'Parse les logs SSH pour détecter les tentatives de connexion',
)]
class ParseSshLogsCommand extends Command
{
    private const LOG_PATHS = [
        '/var/log/auth.log',      // Debian/Ubuntu
        '/var/log/secure',         // CentOS/RHEL
        'C:\ProgramData\ssh\logs\sshd.log', // Windows (pour test)
    ];

    public function __construct(
        private VpsServerRepository $vpsServerRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('server-id', null, InputOption::VALUE_OPTIONAL, 'ID du serveur')
            ->addOption('lines', null, InputOption::VALUE_OPTIONAL, 'Nombre de lignes à parser', 1000)
            ->addOption('log-file', null, InputOption::VALUE_OPTIONAL, 'Chemin du fichier de log');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $serverId = $input->getOption('server-id');
        $lines = (int) $input->getOption('lines');
        $customLogFile = $input->getOption('log-file');

        // Récupérer le serveur
        if ($serverId) {
            $server = $this->vpsServerRepository->find($serverId);
            if (!$server) {
                $io->error("Serveur avec l'ID $serverId non trouvé");
                return Command::FAILURE;
            }
        } else {
            // Utiliser le premier serveur local
            $servers = $this->vpsServerRepository->findAll();
            $server = $servers[0] ?? null;
            
            if (!$server) {
                $io->error('Aucun serveur trouvé');
                return Command::FAILURE;
            }
        }

        $io->title('Analyse des logs SSH');
        $io->text(sprintf('Serveur: %s', $server->getName()));

        // Trouver le fichier de log
        $logFile = $customLogFile;
        
        if (!$logFile) {
            foreach (self::LOG_PATHS as $path) {
                if (file_exists($path)) {
                    $logFile = $path;
                    break;
                }
            }
        }

        if (!$logFile || !file_exists($logFile)) {
            $io->warning('Fichier de log SSH non trouvé. Chemins testés:');
            foreach (self::LOG_PATHS as $path) {
                $io->text("  - $path");
            }
            $io->note('Utilisez --log-file pour spécifier un chemin personnalisé');
            return Command::FAILURE;
        }

        $io->text(sprintf('Fichier de log: %s', $logFile));

        // Lire les dernières lignes du fichier
        $logLines = $this->readLastLines($logFile, $lines);
        
        if (empty($logLines)) {
            $io->warning('Aucune ligne trouvée dans le fichier de log');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Analyse de %d lignes...', count($logLines)));
        $io->progressStart(count($logLines));

        $parsedCount = 0;
        $newAttempts = 0;

        foreach ($logLines as $line) {
            $attempt = $this->parseSshLogLine($line, $server);
            
            if ($attempt) {
                // Vérifier si cette tentative existe déjà
                $exists = $this->entityManager->getRepository(SshAttempt::class)
                    ->findOneBy([
                        'ipAddress' => $attempt->getIpAddress(),
                        'attemptedAt' => $attempt->getAttemptedAt(),
                        'username' => $attempt->getUsername(),
                    ]);

                if (!$exists) {
                    $this->entityManager->persist($attempt);
                    $newAttempts++;
                }
                
                $parsedCount++;
            }
            
            $io->progressAdvance();
        }

        $io->progressFinish();

        if ($newAttempts > 0) {
            $this->entityManager->flush();
        }

        $io->newLine();
        $io->success(sprintf(
            'Analyse terminée: %d tentatives détectées, %d nouvelles enregistrées',
            $parsedCount,
            $newAttempts
        ));

        return Command::SUCCESS;
    }

    /**
     * Lit les dernières lignes d'un fichier
     */
    private function readLastLines(string $file, int $lines): array
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return [];
        }

        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];

        while ($linecounter > 0) {
            $t = ' ';
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) {
                break;
            }
        }

        fclose($handle);
        return array_reverse($text);
    }

    /**
     * Parse une ligne de log SSH
     */
    private function parseSshLogLine(string $line, $server): ?SshAttempt
    {
        // Pattern pour les connexions réussies
        // Ex: Oct  5 20:15:23 server sshd[12345]: Accepted password for user from 192.168.1.1 port 54321 ssh2
        if (preg_match('/(\w+\s+\d+\s+\d+:\d+:\d+).*sshd\[\d+\]: Accepted (\w+) for (\w+) from ([\d\.]+) port (\d+)/', $line, $matches)) {
            $attempt = new SshAttempt();
            $attempt->setServer($server);
            $attempt->setAttemptedAt($this->parseDate($matches[1]));
            $attempt->setMethod($matches[2]);
            $attempt->setUsername($matches[3]);
            $attempt->setIpAddress($matches[4]);
            $attempt->setPort($matches[5]);
            $attempt->setSuccess(true);
            $attempt->setRawLog($line);
            
            return $attempt;
        }

        // Pattern pour les échecs de connexion
        // Ex: Oct  5 20:15:23 server sshd[12345]: Failed password for invalid user admin from 192.168.1.1 port 54321 ssh2
        if (preg_match('/(\w+\s+\d+\s+\d+:\d+:\d+).*sshd\[\d+\]: Failed password for (?:invalid user )?(\w+) from ([\d\.]+) port (\d+)/', $line, $matches)) {
            $attempt = new SshAttempt();
            $attempt->setServer($server);
            $attempt->setAttemptedAt($this->parseDate($matches[1]));
            $attempt->setUsername($matches[2]);
            $attempt->setIpAddress($matches[3]);
            $attempt->setPort($matches[4]);
            $attempt->setSuccess(false);
            $attempt->setMethod('password');
            $attempt->setRawLog($line);
            
            return $attempt;
        }

        // Pattern pour les tentatives invalides
        // Ex: Oct  5 20:15:23 server sshd[12345]: Invalid user admin from 192.168.1.1
        if (preg_match('/(\w+\s+\d+\s+\d+:\d+:\d+).*sshd\[\d+\]: Invalid user (\w+) from ([\d\.]+)/', $line, $matches)) {
            $attempt = new SshAttempt();
            $attempt->setServer($server);
            $attempt->setAttemptedAt($this->parseDate($matches[1]));
            $attempt->setUsername($matches[2]);
            $attempt->setIpAddress($matches[3]);
            $attempt->setSuccess(false);
            $attempt->setRawLog($line);
            
            return $attempt;
        }

        return null;
    }

    /**
     * Parse une date de log système
     */
    private function parseDate(string $dateStr): \DateTimeImmutable
    {
        // Format: Oct  5 20:15:23
        $currentYear = date('Y');
        $date = \DateTimeImmutable::createFromFormat('M j H:i:s', $dateStr);
        
        if ($date) {
            // Ajouter l'année courante
            $date = $date->setDate((int)$currentYear, (int)$date->format('m'), (int)$date->format('d'));
            return $date;
        }
        
        return new \DateTimeImmutable();
    }
}
