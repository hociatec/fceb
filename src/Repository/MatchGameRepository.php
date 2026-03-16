<?php

namespace App\Repository;

use App\Entity\MatchGame;
use App\Entity\Season;
use App\Enum\MatchStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MatchGame>
 */
class MatchGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchGame::class);
    }

    public function findNextMatch(): ?MatchGame
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->andWhere('m.matchDate >= :now')
            ->setParameter('status', MatchStatus::Scheduled)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.matchDate', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return MatchGame[] */
    public function findUpcomingMatches(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->andWhere('m.matchDate >= :now')
            ->setParameter('status', MatchStatus::Scheduled)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.matchDate', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLastMatch(): ?MatchGame
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->andWhere('m.matchDate <= :now')
            ->setParameter('status', MatchStatus::Completed)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.matchDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return MatchGame[] */
    public function findBySeasonOrdered(Season $season): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.season = :season')
            ->setParameter('season', $season)
            ->orderBy('m.matchDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return MatchGame[] */
    public function findUpcomingBySeason(Season $season): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.season = :season')
            ->andWhere('m.matchDate >= :now')
            ->setParameter('season', $season)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.matchDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return MatchGame[] */
    public function findPastBySeason(Season $season): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.season = :season')
            ->andWhere('m.matchDate < :now')
            ->setParameter('season', $season)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('m.matchDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countCompletedWithoutLinkedArticle(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.status = :status')
            ->andWhere('m.linkedArticle IS NULL')
            ->setParameter('status', MatchStatus::Completed)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return MatchGame[] */
    public function findLatestCompletedWithoutLinkedArticle(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->andWhere('m.linkedArticle IS NULL')
            ->setParameter('status', MatchStatus::Completed)
            ->orderBy('m.matchDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
