<?php

namespace App\Controller;

use App\Repository\SshAttemptRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ssh-logs')]
#[IsGranted('ROLE_USER')]
class SshLogsController extends AbstractController
{
    #[Route('/', name: 'app_ssh_logs')]
    public function index(Request $request, SshAttemptRepository $sshAttemptRepository): Response
    {
        $filter = $request->query->get('filter', 'all');
        $limit = (int) $request->query->get('limit', 100);

        // Récupérer les tentatives selon le filtre
        switch ($filter) {
            case 'failed':
                $attempts = $sshAttemptRepository->findFailedAttempts($limit);
                break;
            case 'success':
                $attempts = $sshAttemptRepository->createQueryBuilder('s')
                    ->andWhere('s.success = :success')
                    ->setParameter('success', true)
                    ->orderBy('s.attemptedAt', 'DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
                break;
            default:
                $attempts = $sshAttemptRepository->findRecent($limit);
        }

        // Statistiques
        $totalAttempts = $sshAttemptRepository->count([]);
        $failedAttempts = $sshAttemptRepository->countBySuccess(false);
        $successfulAttempts = $sshAttemptRepository->countBySuccess(true);
        $topAttackers = $sshAttemptRepository->getTopAttackingIps(10);

        return $this->render('ssh_logs/index.html.twig', [
            'attempts' => $attempts,
            'filter' => $filter,
            'limit' => $limit,
            'stats' => [
                'total' => $totalAttempts,
                'failed' => $failedAttempts,
                'successful' => $successfulAttempts,
                'failureRate' => $totalAttempts > 0 ? round(($failedAttempts / $totalAttempts) * 100, 1) : 0,
            ],
            'topAttackers' => $topAttackers,
        ]);
    }
}
