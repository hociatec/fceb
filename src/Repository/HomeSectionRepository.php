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

    /** @return string[] */
    public function configuredSectionKeys(): array
    {
        return array_values(array_filter(array_map(
            static fn (HomeSection $section): ?string => $section->getSectionKey(),
            $this->findAll()
        )));
    }

    /** @return array<string, string> */
    public function missingSectionChoices(): array
    {
        $configuredKeys = $this->configuredSectionKeys();
        $choices = [];

        foreach (HomeSection::AVAILABLE_KEYS as $key => $label) {
            if (!in_array($key, $configuredKeys, true)) {
                $choices[$label] = $key;
            }
        }

        return $choices;
    }

    public function findConfiguredByKey(string $sectionKey): ?HomeSection
    {
        return $this->findOneBy(['sectionKey' => $sectionKey]);
    }
}
