<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\MatchGame;

final class MatchArticleResolver
{
    /**
     * @param Article[] $articles
     */
    public function resolve(?MatchGame $match, array $articles): ?Article
    {
        if (!$match instanceof MatchGame) {
            return null;
        }

        $linkedArticle = $match->getLinkedArticle();
        if ($linkedArticle instanceof Article && $linkedArticle->isVisibleOnSite()) {
            return $linkedArticle;
        }

        $opponent = $this->normalizeMatchReference($match->getOpponent());
        $opponentWithoutSport = trim(preg_replace('/\bcecifoot\b/u', ' ', $opponent) ?? '');
        $matchDay = $match->getMatchDate()?->format('Y-m-d');

        foreach ($articles as $article) {
            if (!$article->getPublishedAt() || $article->getPublishedAt()->format('Y-m-d') !== $matchDay) {
                continue;
            }

            $title = $this->normalizeMatchReference($article->getTitle());
            if (
                ('' !== $opponent && str_contains($title, $opponent))
                || ('' !== $opponentWithoutSport && str_contains($title, $opponentWithoutSport))
            ) {
                return $article;
            }
        }

        return null;
    }

    /**
     * @param MatchGame[] $matches
     * @param Article[]   $articles
     *
     * @return array{automatic:int, unresolved:int}
     */
    public function summarize(array $matches, array $articles): array
    {
        $automatic = 0;
        $unresolved = 0;

        foreach ($matches as $match) {
            $resolvedArticle = $this->resolve($match, $articles);
            if (!$resolvedArticle instanceof Article) {
                ++$unresolved;
                continue;
            }

            if (null === $match->getLinkedArticle()) {
                ++$automatic;
            }
        }

        return [
            'automatic' => $automatic,
            'unresolved' => $unresolved,
        ];
    }

    private function normalizeMatchReference(?string $value): string
    {
        $value = trim(mb_strtolower((string) $value));

        if ('' === $value) {
            return '';
        }

        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (false !== $transliterated) {
            $value = $transliterated;
        }

        $value = str_replace(['’', '\'', '.'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\\s-]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\\s+/', ' ', $value) ?? '');
    }
}
