<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Season;
use App\Enum\ArticlePlacement;
use App\Enum\ContentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findLatestHomepageArticle(): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.placement IN (:placements)')
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('placements', [ArticlePlacement::Homepage, ArticlePlacement::CurrentSeason])
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        return $this->findOneBy([
            'slug' => $slug,
            'status' => ContentStatus::Published,
        ]);
    }

    public function findAnyBySlug(string $slug): ?Article
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /** @return Article[] */
    public function findPublishedBySeason(Season $season): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.season = :season')
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('season', $season)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Article[] */
    public function findLatestPublished(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->setParameter('status', ContentStatus::Published)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
