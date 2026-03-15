<?php

namespace App\Repository;

use App\Entity\Player;
use App\Enum\ContentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * @return Player[]
     */
    public function findPublishedOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', ContentStatus::Published)
            ->orderBy('p.displayOrder', 'ASC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedBySlug(string $slug): ?Player
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', ContentStatus::Published)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAnyBySlug(string $slug): ?Player
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
