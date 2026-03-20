<?php

namespace App\Repository;

use App\Entity\Page;
use App\Enum\ContentStatus;
use App\Enum\PagePlacement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /** @return Page[] */
    public function findForPlacement(PagePlacement $placement): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.placement IN (:placements)')
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('placements', match ($placement) {
                PagePlacement::Header => [PagePlacement::Header, PagePlacement::Both],
                PagePlacement::Footer => [PagePlacement::Footer, PagePlacement::Both],
                default => [$placement],
            })
            ->orderBy('p.menuOrder', 'ASC')
            ->addOrderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedBySlug(string $slug): ?Page
    {
        return $this->findOneBy([
            'slug' => $slug,
            'status' => ContentStatus::Published,
        ]);
    }

    public function findAnyBySlug(string $slug): ?Page
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findPublishedBySystemKey(string $systemKey): ?Page
    {
        return $this->findOneBy([
            'systemKey' => $systemKey,
            'status' => ContentStatus::Published,
        ]);
    }

    public function findAnyBySystemKey(string $systemKey): ?Page
    {
        return $this->findOneBy(['systemKey' => $systemKey]);
    }
}
