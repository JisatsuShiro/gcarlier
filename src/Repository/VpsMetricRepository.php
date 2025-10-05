<?php

namespace App\Repository;

use App\Entity\VpsMetric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpsMetric>
 */
class VpsMetricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpsMetric::class);
    }

    public function findLatestByServer($server, int $limit = 24): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.server = :server')
            ->setParameter('server', $server)
            ->orderBy('m.recordedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
