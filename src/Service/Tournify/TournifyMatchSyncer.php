<?php

namespace App\Service\Tournify;

use App\Entity\MatchGame;
use App\Entity\Season;
use App\Enum\MatchStatus;
use App\Repository\MatchGameRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TournifyMatchSyncer
{
    public const DEFAULT_LIVE_LINK = 'championnatfrancecifoot25-26';
    public const DEFAULT_DIVISION_ID = '1740072125';
    public const DEFAULT_TEAM_NAME = 'FC. Cécifoot 59 La bassée';

    public function __construct(
        private readonly TournifyClient $tournifyClient,
        private readonly MatchGameRepository $matchGameRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{
     *     source_matches: int,
     *     created: int,
     *     updated: int,
     *     removed: int,
     *     synced: list<array{opponent: string, match_date: string, status: string}>,
     *     removed_matches: list<array{opponent: string, match_date: string}>
     * }
     */
    public function syncSeason(
        Season $season,
        string $liveLink,
        string $divisionId,
        string $teamName,
        string $competition = MatchGame::COMPETITION_CHAMPIONNAT,
        bool $pruneMissing = true,
        bool $dryRun = false,
    ): array {
        $sourceMatches = $this->loadClubMatches($liveLink, $divisionId, $teamName, $competition);
        $sourceOpponentNames = array_values(array_unique(array_map(
            static fn (array $match): string => $match['opponent'],
            $sourceMatches
        )));

        $existingMatches = array_filter(
            $this->matchGameRepository->findBySeasonOrdered($season),
            static fn (MatchGame $match): bool => $match->getCompetition() === $competition
                && in_array($match->getOpponent(), $sourceOpponentNames, true)
        );

        $indexedExisting = [];
        foreach ($existingMatches as $match) {
            $indexedExisting[$this->buildExistingKey($match)] = $match;
        }

        $created = 0;
        $updated = 0;
        $syncedKeys = [];
        $synced = [];

        foreach ($sourceMatches as $sourceMatch) {
            $existingKey = $this->buildSourceKey($sourceMatch);
            $match = $indexedExisting[$existingKey] ?? null;

            if (!$match instanceof MatchGame) {
                $match = new MatchGame();
                $match->setSeason($season);
                ++$created;
            } else {
                ++$updated;
            }

            $match
                ->setOpponent($sourceMatch['opponent'])
                ->setCompetition($sourceMatch['competition'])
                ->setLocation($sourceMatch['location'])
                ->setMatchDate($sourceMatch['matchDate'])
                ->setSide($sourceMatch['side'])
                ->setStatus($sourceMatch['status'])
                ->setOurScore($sourceMatch['ourScore'])
                ->setOpponentScore($sourceMatch['opponentScore']);

            if (!$dryRun) {
                $this->entityManager->persist($match);
            }

            $finalKey = $this->buildExistingKey($match);
            $syncedKeys[$finalKey] = true;
            $synced[] = [
                'opponent' => (string) $match->getOpponent(),
                'match_date' => $match->getMatchDate()?->format('Y-m-d H:i:s') ?? '',
                'status' => $match->getStatus()->value,
            ];
        }

        $removed = 0;
        $removedMatches = [];
        if ($pruneMissing) {
            foreach ($existingMatches as $match) {
                $key = $this->buildExistingKey($match);
                if (isset($syncedKeys[$key])) {
                    continue;
                }

                ++$removed;
                $removedMatches[] = [
                    'opponent' => (string) $match->getOpponent(),
                    'match_date' => $match->getMatchDate()?->format('Y-m-d H:i:s') ?? '',
                ];

                if (!$dryRun) {
                    $this->entityManager->remove($match);
                }
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        return [
            'source_matches' => count($sourceMatches),
            'created' => $created,
            'updated' => max(0, $updated - $created),
            'removed' => $removed,
            'synced' => $synced,
            'removed_matches' => $removedMatches,
        ];
    }

    /**
     * @return list<array{
     *     opponent: string,
     *     competition: string,
     *     location: string,
     *     matchDate: \DateTimeImmutable,
     *     side: string,
     *     status: MatchStatus,
     *     ourScore: ?int,
     *     opponentScore: ?int
     * }>
     */
    private function loadClubMatches(string $liveLink, string $divisionId, string $teamName, string $competition): array
    {
        $tournament = $this->tournifyClient->findTournamentByLiveLink($liveLink);
        $teams = $this->tournifyClient->listDocuments($tournament['path'], 'teams');
        $days = $this->tournifyClient->listDocuments($tournament['path'], 'days');
        $matches = $this->tournifyClient->listDocuments($tournament['path'], 'matches');
        $fields = array_values(array_filter(
            $tournament['fields']['fields'] ?? [],
            static fn (mixed $field): bool => is_array($field)
        ));

        $clubTeam = $this->findClubTeam($teams, $divisionId, $teamName);
        $clubPouleId = (string) ($clubTeam['poule0'] ?? '');
        $clubTeamSlot = (int) ($clubTeam['numInPoule0'] ?? -1);

        if ('' === $clubPouleId || $clubTeamSlot < 0) {
            throw new \RuntimeException(sprintf('Équipe Tournify "%s" incomplète: poule ou position manquante.', $teamName));
        }

        $opponentsBySlot = [];
        foreach ($teams as $team) {
            if (($team['division'] ?? null) !== $divisionId || ($team['poule0'] ?? null) !== $clubPouleId) {
                continue;
            }

            if (!isset($team['numInPoule0'], $team['name'])) {
                continue;
            }

            $opponentsBySlot[(int) $team['numInPoule0']] = (string) $team['name'];
        }

        $daysById = [];
        foreach ($days as $day) {
            if (isset($day['id'], $day['date'])) {
                $daysById[(string) $day['id']] = (int) $day['date'];
            }
        }

        $fieldsById = [];
        foreach ($fields as $field) {
            if (isset($field['id'], $field['name'])) {
                $fieldsById[(string) $field['id']] = (string) $field['name'];
            }
        }

        $clubMatches = [];
        foreach ($matches as $match) {
            if (($match['poule'] ?? null) !== $clubPouleId) {
                continue;
            }

            if (!isset($match['team1'], $match['team2'], $match['day'], $match['st'])) {
                continue;
            }

            $team1 = (int) $match['team1'];
            $team2 = (int) $match['team2'];
            if ($team1 !== $clubTeamSlot && $team2 !== $clubTeamSlot) {
                continue;
            }

            $dayTimestamp = $daysById[(string) $match['day']] ?? null;
            if (null === $dayTimestamp) {
                continue;
            }

            $date = gmdate('Y-m-d', $dayTimestamp);
            $matchDate = \DateTimeImmutable::createFromFormat('!Y-m-d H:i', sprintf('%s %s', $date, (string) $match['st']), new \DateTimeZone('UTC'));
            if (!$matchDate instanceof \DateTimeImmutable) {
                throw new \RuntimeException(sprintf('Date Tournify invalide pour le match %s.', (string) ($match['id'] ?? 'inconnu')));
            }

            $clubIsFirst = $team1 === $clubTeamSlot;
            $opponentSlot = $clubIsFirst ? $team2 : $team1;
            $opponentName = $opponentsBySlot[$opponentSlot] ?? sprintf('Adversaire %d', $opponentSlot);
            $isCompleted = is_int($match['score1'] ?? null) && is_int($match['score2'] ?? null);

            $clubMatches[] = [
                'opponent' => $opponentName,
                'competition' => $competition,
                'location' => $fieldsById[(string) ($match['field'] ?? '')] ?? '',
                'matchDate' => $matchDate,
                'side' => $clubIsFirst ? 'home' : 'away',
                'status' => $isCompleted ? MatchStatus::Completed : MatchStatus::Scheduled,
                'ourScore' => $isCompleted ? (int) ($clubIsFirst ? $match['score1'] : $match['score2']) : null,
                'opponentScore' => $isCompleted ? (int) ($clubIsFirst ? $match['score2'] : $match['score1']) : null,
            ];
        }

        usort(
            $clubMatches,
            static fn (array $left, array $right): int => $left['matchDate'] <=> $right['matchDate']
        );

        return $clubMatches;
    }

    /**
     * @param list<array<string, mixed>> $teams
     *
     * @return array<string, mixed>
     */
    private function findClubTeam(array $teams, string $divisionId, string $teamName): array
    {
        $normalizedTeamName = $this->normalize($teamName);

        foreach ($teams as $team) {
            if (($team['division'] ?? null) !== $divisionId) {
                continue;
            }

            if ($this->normalize((string) ($team['name'] ?? '')) === $normalizedTeamName) {
                return $team;
            }
        }

        throw new \RuntimeException(sprintf('Équipe Tournify "%s" introuvable dans la division %s.', $teamName, $divisionId));
    }

    private function buildExistingKey(MatchGame $match): string
    {
        return $this->normalize((string) $match->getOpponent()).'|'.$match->getMatchDate()?->format('Y-m-d');
    }

    /**
     * @param array{opponent: string, matchDate: \DateTimeImmutable} $sourceMatch
     */
    private function buildSourceKey(array $sourceMatch): string
    {
        return $this->normalize($sourceMatch['opponent']).'|'.$sourceMatch['matchDate']->format('Y-m-d');
    }

    private function normalize(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (false !== $transliterated) {
            $value = $transliterated;
        }

        $value = str_replace(['’', '\'', '.'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\\s-]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\\s+/', ' ', $value) ?? '');
    }
}
