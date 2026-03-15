<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partner>
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }

    /** @return Partner[] */
    public function findVisibleOrdered(): array
    {
        return $this->findBy(['isVisible' => true], ['displayOrder' => 'ASC', 'name' => 'ASC']);
    }
}
