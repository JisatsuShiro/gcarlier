<?php

namespace App\Command;

use App\Entity\VpsMetric;
use App\Repository\VpsServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:collect-metrics',
    description: 'Collecte les métriques des serveurs VPS via SSH',
)]
class CollectMetricsCommand extends Command
{
    public function __construct(
        private VpsServerRepository $vpsServerRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('server-id', null, InputOption::VALUE_OPTIONAL, 'ID du serveur spécifique à monitorer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $serverId = $input->getOption('server-id');

        // Récupérer les serveurs à monitorer
        if ($serverId) {
            $servers = [$this->vpsServerRepository->find($serverId)];
            if (!$servers[0]) {
                $io->error("Serveur avec l'ID $serverId non trouvé");
                return Command::FAILURE;
            }
        } else {
            $servers = $this->vpsServerRepository->findBy(['status' => 'active']);
        }

        if (empty($servers)) {
            $io->warning('Aucun serveur actif à monitorer');
            return Command::SUCCESS;
        }

        $io->title('Collecte des métriques VPS');
        $io->progressStart(count($servers));

        $successCount = 0;
        $errorCount = 0;

        foreach ($servers as $server) {
            try {
                $isLocal = $this->isLocalServer($server);
                $io->writeln(sprintf(
                    '  Serveur: %s (IP: %s) - %s',
                    $server->getName(),
                    $server->getIpAddress(),
                    $isLocal ? 'LOCAL' : 'DISTANT'
                ));
                
                $metrics = $this->collectServerMetrics($server);
                
                if ($metrics) {
                    $metric = new VpsMetric();
                    $metric->setServer($server);
                    $metric->setCpuUsage($metrics['cpu']);
                    $metric->setMemoryUsage($metrics['memory']);
                    $metric->setDiskUsage($metrics['disk']);
                    $metric->setUptime($metrics['uptime']);
                    
                    $this->entityManager->persist($metric);
                    $successCount++;
                    
                    $io->writeln(sprintf(
                        '  ✓ %s - CPU: %s%%, RAM: %s%%, Disque: %s%%',
                        $server->getName(),
                        $metrics['cpu'],
                        $metrics['memory'],
                        $metrics['disk']
                    ));
                } else {
                    $errorCount++;
                    $io->writeln(sprintf('  ✗ %s - Échec de la collecte (métriques nulles)', $server->getName()));
                }
            } catch (\Exception $e) {
                $errorCount++;
                $io->writeln(sprintf('  ✗ %s - Erreur: %s', $server->getName(), $e->getMessage()));
            }
            
            $io->progressAdvance();
        }

        $io->progressFinish();
        $this->entityManager->flush();

        $io->newLine();
        $io->success(sprintf(
            'Collecte terminée: %d succès, %d erreurs',
            $successCount,
            $errorCount
        ));

        return Command::SUCCESS;
    }

    /**
     * Collecte les métriques du serveur local (si l'app est hébergée sur le VPS)
     * ou via SSH pour les serveurs distants
     */
    private function collectServerMetrics($server): ?array
    {
        // Déterminer si c'est le serveur local
        $isLocal = $this->isLocalServer($server);

        try {
            if ($isLocal) {
                // Collecte locale (l'application tourne sur ce serveur)
                return $this->collectLocalMetrics();
            } else {
                // Collecte distante via SSH
                return $this->collectRemoteMetrics($server);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Vérifie si le serveur est le serveur local
     */
    private function isLocalServer($server): bool
    {
        $serverIp = $server->getIpAddress();
        
        // IPs locales
        $localIps = ['127.0.0.1', 'localhost', '::1'];
        
        if (in_array($serverIp, $localIps)) {
            return true;
        }

        // Comparer avec l'IP du serveur actuel
        $currentIp = gethostbyname(gethostname());
        return $serverIp === $currentIp;
    }

    /**
     * Collecte les métriques localement (Linux)
     */
    private function collectLocalMetrics(): ?array
    {
        $metrics = [];

        // Détection de l'OS
        $os = PHP_OS_FAMILY;

        if ($os === 'Linux') {
            // CPU: pourcentage d'utilisation
            $cpuCmd = "top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1";
            $metrics['cpu'] = trim(shell_exec($cpuCmd) ?? '0');

            // RAM: pourcentage utilisé
            $memCmd = "free | grep Mem | awk '{printf \"%.2f\", ($3/$2) * 100.0}'";
            $metrics['memory'] = trim(shell_exec($memCmd) ?? '0');

            // Disque: pourcentage utilisé sur /
            $diskCmd = "df -h / | awk 'NR==2 {print $5}' | cut -d'%' -f1";
            $metrics['disk'] = trim(shell_exec($diskCmd) ?? '0');

            // Uptime en secondes
            $uptimeCmd = "cat /proc/uptime | awk '{print int($1)}'";
            $metrics['uptime'] = trim(shell_exec($uptimeCmd) ?? '0');

        } elseif ($os === 'Windows') {
            // CPU sur Windows
            $cpuCmd = 'wmic cpu get loadpercentage /value';
            $cpuOutput = shell_exec($cpuCmd);
            preg_match('/LoadPercentage=(\d+)/', $cpuOutput ?? '', $matches);
            $metrics['cpu'] = $matches[1] ?? '0';

            // RAM sur Windows
            $memCmd = 'wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value';
            $memOutput = shell_exec($memCmd);
            preg_match('/FreePhysicalMemory=(\d+)/', $memOutput ?? '', $free);
            preg_match('/TotalVisibleMemorySize=(\d+)/', $memOutput ?? '', $total);
            if (isset($free[1], $total[1]) && $total[1] > 0) {
                $metrics['memory'] = number_format((($total[1] - $free[1]) / $total[1]) * 100, 2);
            } else {
                $metrics['memory'] = '0';
            }

            // Disque sur Windows (disque C:)
            $diskCmd = 'wmic logicaldisk where "DeviceID=\'C:\'" get FreeSpace,Size /value';
            $diskOutput = shell_exec($diskCmd);
            preg_match('/FreeSpace=(\d+)/', $diskOutput ?? '', $free);
            preg_match('/Size=(\d+)/', $diskOutput ?? '', $size);
            if (isset($free[1], $size[1]) && $size[1] > 0) {
                $metrics['disk'] = number_format((($size[1] - $free[1]) / $size[1]) * 100, 2);
            } else {
                $metrics['disk'] = '0';
            }

            // Uptime sur Windows (en secondes)
            $uptimeCmd = 'wmic os get lastbootuptime /value';
            $uptimeOutput = shell_exec($uptimeCmd);
            preg_match('/LastBootUpTime=(\d{14})/', $uptimeOutput ?? '', $matches);
            if (isset($matches[1])) {
                $bootTime = \DateTime::createFromFormat('YmdHis', substr($matches[1], 0, 14));
                $now = new \DateTime();
                $metrics['uptime'] = (string) ($now->getTimestamp() - $bootTime->getTimestamp());
            } else {
                $metrics['uptime'] = '0';
            }
        } else {
            // OS non supporté
            return null;
        }

        // Validation des données
        if (!is_numeric($metrics['cpu']) || !is_numeric($metrics['memory']) || 
            !is_numeric($metrics['disk']) || !is_numeric($metrics['uptime'])) {
            return null;
        }

        return $metrics;
    }

    /**
     * Collecte les métriques via SSH (serveur distant)
     */
    private function collectRemoteMetrics($server): ?array
    {
        // Vérifier que les informations SSH sont disponibles
        if (!$server->getIpAddress() || !$server->getSshUser()) {
            return null;
        }

        $host = $server->getIpAddress();
        $port = $server->getSshPort();
        $user = $server->getSshUser();

        // Commandes pour récupérer les métriques
        $commands = [
            'cpu' => "top -bn1 | grep 'Cpu(s)' | awk '{print \$2}' | cut -d'%' -f1",
            'memory' => "free | grep Mem | awk '{printf \"%.2f\", (\$3/\$2) * 100.0}'",
            'disk' => "df -h / | awk 'NR==2 {print \$5}' | cut -d'%' -f1",
            'uptime' => "cat /proc/uptime | awk '{print int(\$1)}'",
        ];

        $metrics = [];

        foreach ($commands as $key => $command) {
            // Construction de la commande SSH
            $sshCommand = sprintf(
                'ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -p %d %s@%s "%s" 2>&1',
                $port,
                escapeshellarg($user),
                escapeshellarg($host),
                $command
            );

            // Exécution de la commande
            $output = shell_exec($sshCommand);
            
            if ($output === null) {
                return null;
            }

            $metrics[$key] = trim($output);
        }

        // Validation des données
        if (!is_numeric($metrics['cpu']) || !is_numeric($metrics['memory']) || 
            !is_numeric($metrics['disk']) || !is_numeric($metrics['uptime'])) {
            return null;
        }

        return $metrics;
    }
}
