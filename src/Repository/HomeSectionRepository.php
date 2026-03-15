<?php

namespace App\Repository;

use App\Entity\HomeSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeSection>
 */
class HomeSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeSection::class);
    }

    /** @return HomeSection[] */
    public function findEnabledOrdered(): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('h.displayOrder', 'ASC')
            ->addOrderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return HomeSection[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('h')
            ->orderBy('h.displayOrder', 'ASC')
            ->addOrderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
