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
    name: 'app:generate-test-metrics',
    description: 'Génère des métriques de test pour les serveurs VPS',
)]
class GenerateTestMetricsCommand extends Command
{
    public function __construct(
        private VpsServerRepository $vpsServerRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('server-id', null, InputOption::VALUE_OPTIONAL, 'ID du serveur spécifique')
            ->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Nombre de métriques à générer', 24);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $serverId = $input->getOption('server-id');
        $count = (int) $input->getOption('count');

        // Récupérer les serveurs
        if ($serverId) {
            $servers = [$this->vpsServerRepository->find($serverId)];
            if (!$servers[0]) {
                $io->error("Serveur avec l'ID $serverId non trouvé");
                return Command::FAILURE;
            }
        } else {
            $servers = $this->vpsServerRepository->findAll();
        }

        if (empty($servers)) {
            $io->warning('Aucun serveur trouvé');
            return Command::SUCCESS;
        }

        $io->title('Génération de métriques de test');
        $io->text(sprintf('Génération de %d métriques par serveur', $count));

        $totalMetrics = 0;

        foreach ($servers as $server) {
            $io->section(sprintf('Serveur: %s', $server->getName()));

            // Générer des métriques avec des valeurs réalistes
            $baseCpu = rand(20, 50);
            $baseMemory = rand(40, 70);
            $baseDisk = rand(30, 60);

            for ($i = 0; $i < $count; $i++) {
                $metric = new VpsMetric();
                $metric->setServer($server);
                
                // Variation aléatoire autour des valeurs de base
                $metric->setCpuUsage((string) max(0, min(100, $baseCpu + rand(-15, 15))));
                $metric->setMemoryUsage((string) max(0, min(100, $baseMemory + rand(-10, 10))));
                $metric->setDiskUsage((string) max(0, min(100, $baseDisk + rand(-5, 5))));
                
                // Trafic réseau aléatoire (en octets)
                $metric->setNetworkIn((string) rand(1000000, 10000000));
                $metric->setNetworkOut((string) rand(500000, 5000000));
                
                // Uptime croissant (en secondes)
                $metric->setUptime(rand(86400, 2592000)); // Entre 1 jour et 30 jours
                
                // Date dans le passé (dernières 24h)
                $hoursAgo = $count - $i;
                $recordedAt = new \DateTimeImmutable(sprintf('-%d hours', $hoursAgo));
                $metric->setRecordedAt($recordedAt);
                
                $this->entityManager->persist($metric);
                $totalMetrics++;
            }

            $io->text(sprintf('  ✓ %d métriques générées', $count));
        }

        $this->entityManager->flush();

        $io->newLine();
        $io->success(sprintf(
            'Total: %d métriques générées pour %d serveur(s)',
            $totalMetrics,
            count($servers)
        ));

        $io->note('Rafraîchissez la page du serveur pour voir les graphiques mis à jour');

        return Command::SUCCESS;
    }
}
