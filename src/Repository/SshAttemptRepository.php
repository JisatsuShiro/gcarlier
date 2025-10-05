<?php

namespace App\Repository;

use App\Entity\SshAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SshAttempt>
 */
class SshAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SshAttempt::class);
    }

    public function findRecent(int $limit = 100): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.attemptedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByServer($server, int $limit = 100): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.server = :server')
            ->setParameter('server', $server)
            ->orderBy('s.attemptedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countBySuccess(bool $success): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.success = :success')
            ->setParameter('success', $success)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findFailedAttempts(int $limit = 100): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.success = :success')
            ->setParameter('success', false)
            ->orderBy('s.attemptedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getTopAttackingIps(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.ipAddress, COUNT(s.id) as attemptCount')
            ->andWhere('s.success = :success')
            ->setParameter('success', false)
            ->groupBy('s.ipAddress')
            ->orderBy('attemptCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
