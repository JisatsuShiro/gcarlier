<?php

namespace App\Repository;

use App\Entity\VpsServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VpsServer>
 */
class VpsServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VpsServer::class);
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
