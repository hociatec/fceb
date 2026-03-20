<?php

namespace App\Repository;

use App\Entity\NavigationItem;
use App\Enum\NavigationItemLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NavigationItem>
 */
class NavigationItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NavigationItem::class);
    }

    /**
     * @return NavigationItem[]
     */
    public function findEnabledByLocationOrdered(NavigationItemLocation $location): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.location = :location')
            ->andWhere('n.isEnabled = :enabled')
            ->setParameter('location', $location)
            ->setParameter('enabled', true)
            ->orderBy('n.displayOrder', 'ASC')
            ->addOrderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
