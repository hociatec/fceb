<?php

namespace App\Service\Tournify;

use App\Entity\Season;
use App\Entity\TournifySyncRun;
use App\Enum\TournifySyncStatus;
use Doctrine\ORM\EntityManagerInterface;

final class TournifySyncRunLogger
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param array{
     *     source_matches?: int,
     *     created?: int,
     *     updated?: int,
     *     removed?: int,
     *     synced?: array<mixed>,
     *     removed_matches?: array<mixed>
     * } $result
     */
    public function logResult(
        ?Season $season,
        string $liveLink,
        string $divisionId,
        string $teamName,
        ?string $competition,
        bool $dryRun,
        array $result,
        ?string $message = null,
    ): void {
        $run = (new TournifySyncRun())
            ->setSeason($season)
            ->setLiveLink($liveLink)
            ->setDivisionId($divisionId)
            ->setTeamName($teamName)
            ->setCompetition($competition)
            ->setStatus($dryRun ? TournifySyncStatus::Preview : TournifySyncStatus::Success)
            ->setIsDryRun($dryRun)
            ->setSourceMatches((int) ($result['source_matches'] ?? 0))
            ->setCreatedCount((int) ($result['created'] ?? 0))
            ->setUpdatedCount((int) ($result['updated'] ?? 0))
            ->setRemovedCount((int) ($result['removed'] ?? 0))
            ->setMessage($message)
            ->setDetails([
                'synced' => array_values($result['synced'] ?? []),
                'removed_matches' => array_values($result['removed_matches'] ?? []),
            ]);

        $this->entityManager->persist($run);
        $this->entityManager->flush();
    }

    public function logFailure(
        ?Season $season,
        string $liveLink,
        string $divisionId,
        string $teamName,
        ?string $competition,
        bool $dryRun,
        \Throwable $exception,
    ): void {
        $run = (new TournifySyncRun())
            ->setSeason($season)
            ->setLiveLink($liveLink)
            ->setDivisionId($divisionId)
            ->setTeamName($teamName)
            ->setCompetition($competition)
            ->setStatus(TournifySyncStatus::Failure)
            ->setIsDryRun($dryRun)
            ->setMessage($exception->getMessage())
            ->setDetails([
                'exception_class' => $exception::class,
            ]);

        $this->entityManager->persist($run);
        $this->entityManager->flush();
    }
}
