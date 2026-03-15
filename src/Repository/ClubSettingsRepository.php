<?php

namespace App\Repository;

use App\Entity\ClubSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClubSettings>
 */
class ClubSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubSettings::class);
    }

    public function getSettings(): ?ClubSettings
    {
        return $this->findOneBy([], ['id' => 'ASC']);
    }
}

