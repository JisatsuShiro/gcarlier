<?php

namespace App\Controller;

use App\Repository\VpsServerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(VpsServerRepository $vpsServerRepository): Response
    {
        $user = $this->getUser();
        $servers = $vpsServerRepository->findByUser($user);

        // Calculer les statistiques
        $totalServers = count($servers);
        $activeServers = count(array_filter($servers, fn($s) => $s->getStatus() === 'active'));
        $inactiveServers = $totalServers - $activeServers;

        // Calculer les moyennes des métriques
        $avgCpu = 0;
        $avgMemory = 0;
        $avgDisk = 0;
        $serversWithMetrics = 0;

        foreach ($servers as $server) {
            $latestMetric = $server->getLatestMetric();
            if ($latestMetric) {
                $avgCpu += (float) $latestMetric->getCpuUsage();
                $avgMemory += (float) $latestMetric->getMemoryUsage();
                $avgDisk += (float) $latestMetric->getDiskUsage();
                $serversWithMetrics++;
            }
        }

        if ($serversWithMetrics > 0) {
            $avgCpu /= $serversWithMetrics;
            $avgMemory /= $serversWithMetrics;
            $avgDisk /= $serversWithMetrics;
        }

        return $this->render('dashboard/index.html.twig', [
            'servers' => $servers,
            'stats' => [
                'total' => $totalServers,
                'active' => $activeServers,
                'inactive' => $inactiveServers,
                'avgCpu' => round($avgCpu, 1),
                'avgMemory' => round($avgMemory, 1),
                'avgDisk' => round($avgDisk, 1),
            ],
        ]);
    }
}
