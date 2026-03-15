<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\MatchGame;
use App\Entity\Season;
use App\Repository\ArticleRepository;
use App\Repository\MatchGameRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/seasons', name: 'api_season_', format: 'json')]
class SeasonController extends AbstractController
{
    #[Route('/current', name: 'current', methods: ['GET'])]
    public function current(SeasonRepository $seasonRepository, ArticleRepository $articleRepository, MatchGameRepository $matchRepository): JsonResponse
    {
        $season = $seasonRepository->findCurrentSeason();
        if (!$season) {
            throw new NotFoundHttpException('Aucune saison en cours.');
        }

        return $this->json($this->normalizeSeason($season, $articleRepository, $matchRepository));
    }

    #[Route('/archives', name: 'archives', methods: ['GET'])]
    public function archives(SeasonRepository $seasonRepository): JsonResponse
    {
        return $this->json([
            'items' => array_map(fn (Season $season) => [
                'name' => $season->getName(),
                'slug' => $season->getSlug(),
                'start_date' => $season->getStartDate()?->format('Y-m-d'),
                'end_date' => $season->getEndDate()?->format('Y-m-d'),
            ], $seasonRepository->findArchives()),
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(
        string $slug,
        SeasonRepository $seasonRepository,
        ArticleRepository $articleRepository,
        MatchGameRepository $matchRepository,
    ): JsonResponse {
        $season = $seasonRepository->findOneBy(['slug' => $slug]);
        if (!$season) {
            throw new NotFoundHttpException('Saison introuvable.');
        }

        return $this->json($this->normalizeSeason($season, $articleRepository, $matchRepository));
    }

    private function normalizeSeason(Season $season, ArticleRepository $articleRepository, MatchGameRepository $matchRepository): array
    {
        return [
            'name' => $season->getName(),
            'slug' => $season->getSlug(),
            'is_current' => $season->isCurrent(),
            'start_date' => $season->getStartDate()?->format('Y-m-d'),
            'end_date' => $season->getEndDate()?->format('Y-m-d'),
            'articles' => array_map(fn (Article $article) => [
                'title' => $article->getTitle(),
                'slug' => $article->getSlug(),
                'excerpt' => $article->getExcerpt(),
                'published_at' => $article->getPublishedAt()?->format(\DateTimeInterface::ATOM),
                'placement' => $article->getPlacement()->value,
            ], $articleRepository->findPublishedBySeason($season)),
            'matches' => array_map(fn (MatchGame $match) => [
                'opponent' => $match->getOpponent(),
                'competition' => $match->getCompetition(),
                'location' => $match->getLocation(),
                'date' => $match->getMatchDate()?->format(\DateTimeInterface::ATOM),
                'status' => $match->getStatus()->value,
                'score' => [
                    'club' => $match->getOurScore(),
                    'opponent' => $match->getOpponentScore(),
                ],
            ], $matchRepository->findBySeasonOrdered($season)),
        ];
    }
}
