<?php

namespace App\Repository;

use App\Entity\RankingEntry;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RankingEntry>
 */
class RankingEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RankingEntry::class);
    }

    /** @return RankingEntry[] */
    public function findBySeasonOrdered(Season $season): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.season = :season')
            ->setParameter('season', $season)
            ->orderBy('r.displayOrder', 'ASC')
            ->addOrderBy('r.points', 'DESC')
            ->addOrderBy('r.goalDifference', 'DESC')
            ->addOrderBy('r.wins', 'DESC')
            ->addOrderBy('r.teamName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestSeasonWithEntries(): ?Season
    {
        $result = $this->createQueryBuilder('r')
            ->join('r.season', 's')
            ->orderBy('s.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof RankingEntry ? $result->getSeason() : null;
    }
}
