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
    name: 'app:generate-test-ssh-logs',
    description: 'Génère des logs SSH de test',
)]
class GenerateTestSshLogsCommand extends Command
{
    private const COMMON_USERNAMES = [
        'root', 'admin', 'user', 'test', 'ubuntu', 'pi', 'oracle', 'postgres',
        'mysql', 'git', 'jenkins', 'deploy', 'www-data', 'apache', 'nginx'
    ];

    private const ATTACK_IPS = [
        '185.220.101.1', '45.142.212.61', '103.253.145.28', '194.169.175.22',
        '91.240.118.168', '159.65.142.76', '167.99.164.160', '134.209.24.42',
        '178.128.83.165', '206.189.156.102'
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
            ->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Nombre de logs à générer', 100)
            ->addOption('server-id', null, InputOption::VALUE_OPTIONAL, 'ID du serveur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = (int) $input->getOption('count');
        $serverId = $input->getOption('server-id');

        // Récupérer le serveur
        if ($serverId) {
            $server = $this->vpsServerRepository->find($serverId);
            if (!$server) {
                $io->error("Serveur avec l'ID $serverId non trouvé");
                return Command::FAILURE;
            }
        } else {
            $servers = $this->vpsServerRepository->findAll();
            $server = $servers[0] ?? null;
            
            if (!$server) {
                $io->error('Aucun serveur trouvé');
                return Command::FAILURE;
            }
        }

        $io->title('Génération de logs SSH de test');
        $io->text(sprintf('Serveur: %s', $server->getName()));
        $io->text(sprintf('Nombre de logs: %d', $count));

        $io->progressStart($count);

        $successCount = 0;
        $failedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $attempt = new SshAttempt();
            $attempt->setServer($server);

            // 80% d'échecs, 20% de succès (réaliste pour un serveur exposé)
            $isSuccess = rand(1, 100) <= 20;
            $attempt->setSuccess($isSuccess);

            if ($isSuccess) {
                // Connexion réussie - utilisateur légitime
                $attempt->setIpAddress($this->generateLegitimateIp());
                $attempt->setUsername($server->getSshUser() ?? 'root');
                $attempt->setMethod('publickey');
                $successCount++;
            } else {
                // Tentative d'attaque
                $attempt->setIpAddress($this->getRandomAttackIp());
                $attempt->setUsername($this->getRandomUsername());
                $attempt->setMethod('password');
                $failedCount++;
            }

            $attempt->setPort((string) $server->getSshPort());
            
            // Date aléatoire dans les dernières 24h
            $hoursAgo = rand(0, 24);
            $minutesAgo = rand(0, 59);
            $attemptedAt = new \DateTimeImmutable(sprintf('-%d hours -%d minutes', $hoursAgo, $minutesAgo));
            $attempt->setAttemptedAt($attemptedAt);

            // Log brut simulé
            $logLine = sprintf(
                '%s sshd[%d]: %s for %s from %s port %s ssh2',
                $attemptedAt->format('M d H:i:s'),
                rand(1000, 9999),
                $isSuccess ? 'Accepted publickey' : 'Failed password',
                $attempt->getUsername(),
                $attempt->getIpAddress(),
                $attempt->getPort()
            );
            $attempt->setRawLog($logLine);

            $this->entityManager->persist($attempt);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->newLine();
        $io->success(sprintf(
            'Génération terminée: %d logs créés (%d réussies, %d échouées)',
            $count,
            $successCount,
            $failedCount
        ));

        $io->note('Accédez à la page "Logs SSH" pour voir les résultats');

        return Command::SUCCESS;
    }

    private function getRandomUsername(): string
    {
        return self::COMMON_USERNAMES[array_rand(self::COMMON_USERNAMES)];
    }

    private function getRandomAttackIp(): string
    {
        return self::ATTACK_IPS[array_rand(self::ATTACK_IPS)];
    }

    private function generateLegitimateIp(): string
    {
        // IP locale ou IP de confiance
        $legitimateIps = [
            '192.168.1.' . rand(1, 254),
            '10.0.0.' . rand(1, 254),
            '172.16.0.' . rand(1, 254),
        ];
        
        return $legitimateIps[array_rand($legitimateIps)];
    }
}
