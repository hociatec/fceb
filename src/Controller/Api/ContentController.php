<?php

namespace App\Controller\Api;

use App\Repository\ArticleRepository;
use App\Repository\PageRepository;
use App\Repository\PartnerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_content_', format: 'json')]
class ContentController extends AbstractController
{
    #[Route('/articles', name: 'articles', methods: ['GET'])]
    public function articles(ArticleRepository $articleRepository): JsonResponse
    {
        return $this->json([
            'items' => array_map(static fn ($article) => [
                'title' => $article->getTitle(),
                'slug' => $article->getSlug(),
                'excerpt' => $article->getExcerpt(),
                'published_at' => $article->getPublishedAt()?->format(\DateTimeInterface::ATOM),
                'season' => $article->getSeason()?->getName(),
            ], $articleRepository->findLatestPublished()),
        ]);
    }

    #[Route('/pages/{slug}', name: 'page', methods: ['GET'])]
    public function page(string $slug, PageRepository $pageRepository): JsonResponse
    {
        $page = $pageRepository->findPublishedBySlug($slug);
        if (!$page) {
            throw new NotFoundHttpException('Page introuvable.');
        }

        return $this->json([
            'title' => $page->getTitle(),
            'slug' => $page->getSlug(),
            'content' => $page->getContent(),
            'placement' => $page->getPlacement()->value,
        ]);
    }

    #[Route('/partners', name: 'partners', methods: ['GET'])]
    public function partners(PartnerRepository $partnerRepository): JsonResponse
    {
        return $this->json([
            'items' => array_map(static fn ($partner) => [
                'name' => $partner->getName(),
                'website_url' => $partner->getWebsiteUrl(),
                'logo_url' => $partner->getLogoUrl(),
            ], $partnerRepository->findVisibleOrdered()),
        ]);
    }
}
