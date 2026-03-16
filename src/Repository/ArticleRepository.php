<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Season;
use App\Enum\ArticleHomepageSlot;
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
            ->andWhere($this->publishedNowExpression('a'))
            ->andWhere('a.homepageSlot = :homepageSlot')
            ->setParameter('homepageSlot', ArticleHomepageSlot::Featured)
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return Article[] */
    public function findHomepageSecondaryArticles(int $limit = 3, array $excludeIds = []): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->andWhere($this->publishedNowExpression('a'))
            ->andWhere('a.homepageSlot = :homepageSlot')
            ->setParameter('homepageSlot', ArticleHomepageSlot::Secondary)
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit);

        $excludeIds = array_values(array_unique(array_filter($excludeIds, static fn (mixed $id): bool => null !== $id)));
        if ([] !== $excludeIds) {
            $queryBuilder
                ->andWhere('a.id NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPublishedBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->andWhere($this->publishedNowExpression('a'))
            ->setParameter('slug', $slug)
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAnyBySlug(string $slug): ?Article
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /** @return Article[] */
    public function findPublishedBySeason(Season $season): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere($this->publishedNowExpression('a'))
            ->andWhere('a.season = :season')
            ->setParameter('season', $season)
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return Article[] */
    public function findLatestPublished(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere($this->publishedNowExpression('a'))
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPreviousPublished(Article $article): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere($this->publishedNowExpression('a'))
            ->andWhere('a.publishedAt < :publishedAt OR (a.publishedAt = :publishedAt AND a.id < :id)')
            ->setParameter('publishedAt', $article->getPublishedAt())
            ->setParameter('id', $article->getId())
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextPublished(Article $article): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere($this->publishedNowExpression('a'))
            ->andWhere('a.publishedAt > :publishedAt OR (a.publishedAt = :publishedAt AND a.id > :id)')
            ->setParameter('publishedAt', $article->getPublishedAt())
            ->setParameter('id', $article->getId())
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'ASC')
            ->addOrderBy('a.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countCurrentlyVisibleByHomepageSlot(ArticleHomepageSlot $slot): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere($this->publishedNowExpression('a'))
            ->andWhere('a.homepageSlot = :homepageSlot')
            ->setParameter('homepageSlot', $slot)
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countScheduledPublications(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.status = :status')
            ->andWhere('a.publishedAt > :now')
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Article[] */
    public function findScheduledPublications(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.status = :status')
            ->andWhere('a.publishedAt > :now')
            ->setParameter('status', ContentStatus::Published)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    private function publishedNowExpression(string $alias): string
    {
        return sprintf('%s.status = :status AND %s.publishedAt <= :now', $alias, $alias);
    }
}
