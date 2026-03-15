<?php

namespace App\Repository;

use App\Entity\SocialLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SocialLink>
 */
class SocialLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialLink::class);
    }

    /** @return SocialLink[] */
    public function findVisibleOrdered(): array
    {
        return $this->findBy(['isVisible' => true], ['displayOrder' => 'ASC', 'label' => 'ASC']);
    }
}
