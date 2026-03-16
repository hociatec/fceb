<?php

namespace App\Repository;

use App\Entity\TournifySyncRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournifySyncRun>
 */
class TournifySyncRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournifySyncRun::class);
    }

    public function findLatestRun(): ?TournifySyncRun
    {
        return $this->findOneBy([], ['createdAt' => 'DESC', 'id' => 'DESC']);
    }

    /** @return TournifySyncRun[] */
    public function findLatestRuns(int $limit = 5): array
    {
        return $this->findBy([], ['createdAt' => 'DESC', 'id' => 'DESC'], $limit);
    }
}
