<?php

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Season>
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function findCurrentSeason(): ?Season
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isCurrent = :current')
            ->setParameter('current', true)
            ->orderBy('s.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Season[] */
    public function findArchives(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isCurrent = :current')
            ->setParameter('current', false)
            ->orderBy('s.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
