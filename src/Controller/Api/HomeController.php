<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\MatchGame;
use App\Repository\ArticleRepository;
use App\Repository\MatchGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/home', name: 'api_home_', format: 'json')]
class HomeController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, MatchGameRepository $matchRepository): JsonResponse
    {
        return $this->json([
            'club' => 'Cécifoot La Bassée',
            'sections' => [
                'latest_news' => $this->normalizeArticle($articleRepository->findLatestHomepageArticle()),
                'next_match' => $this->normalizeMatch($matchRepository->findNextMatch()),
                'last_match' => $this->normalizeMatch($matchRepository->findLastMatch()),
            ],
        ]);
    }

    private function normalizeArticle(?Article $article): ?array
    {
        if (!$article) {
            return null;
        }

        return [
            'title' => $article->getTitle(),
            'slug' => $article->getSlug(),
            'excerpt' => $article->getExcerpt(),
            'content' => $article->getContent(),
            'published_at' => $article->getPublishedAt()?->format(\DateTimeInterface::ATOM),
            'season' => $article->getSeason()?->getName(),
        ];
    }

    private function normalizeMatch(?MatchGame $match): ?array
    {
        if (!$match) {
            return null;
        }

        return [
            'id' => $match->getId(),
            'opponent' => $match->getOpponent(),
            'competition' => $match->getCompetition(),
            'location' => $match->getLocation(),
            'date' => $match->getMatchDate()?->format(\DateTimeInterface::ATOM),
            'side' => $match->getSide(),
            'status' => $match->getStatus()->value,
            'score' => [
                'club' => $match->getOurScore(),
                'opponent' => $match->getOpponentScore(),
            ],
            'season' => $match->getSeason()?->getName(),
        ];
    }
}
