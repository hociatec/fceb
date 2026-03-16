<?php

namespace App\Command;

use App\Entity\MatchGame;
use App\Repository\SeasonRepository;
use App\Service\Tournify\TournifyMatchSyncer;
use App\Service\Tournify\TournifySyncRunLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync:tournify-matches',
    description: 'Synchronise les matchs du club depuis Tournify.',
)]
final class SyncTournifyMatchesCommand extends Command
{
    public function __construct(
        private readonly TournifyMatchSyncer $tournifyMatchSyncer,
        private readonly SeasonRepository $seasonRepository,
        private readonly TournifySyncRunLogger $tournifySyncRunLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('live-link', null, InputOption::VALUE_REQUIRED, 'Live link Tournify à synchroniser.', TournifyMatchSyncer::DEFAULT_LIVE_LINK)
            ->addOption('division-id', null, InputOption::VALUE_REQUIRED, 'Identifiant Tournify de la division.', TournifyMatchSyncer::DEFAULT_DIVISION_ID)
            ->addOption('team-name', null, InputOption::VALUE_REQUIRED, 'Nom exact de l’équipe dans Tournify.', TournifyMatchSyncer::DEFAULT_TEAM_NAME)
            ->addOption('season-slug', null, InputOption::VALUE_REQUIRED, 'Slug de la saison locale. Utilise la saison en cours si omis.')
            ->addOption('competition', null, InputOption::VALUE_REQUIRED, 'Libellé de compétition à enregistrer.', MatchGame::COMPETITION_CHAMPIONNAT)
            ->addOption('prune-missing', null, InputOption::VALUE_NEGATABLE, 'Supprime les matchs locaux absents de Tournify dans ce périmètre.', true)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Prévisualise la synchro sans écrire en base.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $seasonSlug = $input->getOption('season-slug');
        $season = is_string($seasonSlug) && '' !== $seasonSlug
            ? $this->seasonRepository->findOneBy(['slug' => $seasonSlug])
            : $this->seasonRepository->findCurrentSeason();

        if (null === $season) {
            $io->error('Saison introuvable. Utilise --season-slug ou définis une saison en cours.');

            return Command::FAILURE;
        }

        $liveLink = (string) $input->getOption('live-link');
        $divisionId = (string) $input->getOption('division-id');
        $teamName = (string) $input->getOption('team-name');
        $competition = (string) $input->getOption('competition');
        $dryRun = (bool) $input->getOption('dry-run');
        $pruneMissing = (bool) $input->getOption('prune-missing');

        try {
            $result = $this->tournifyMatchSyncer->syncSeason(
                $season,
                $liveLink,
                $divisionId,
                $teamName,
                $competition,
                $pruneMissing,
                $dryRun,
            );

            $this->tournifySyncRunLogger->logResult(
                $season,
                $liveLink,
                $divisionId,
                $teamName,
                $competition,
                $dryRun,
                $result,
            );
        } catch (\Throwable $exception) {
            $this->tournifySyncRunLogger->logFailure(
                $season,
                $liveLink,
                $divisionId,
                $teamName,
                $competition,
                $dryRun,
                $exception,
            );
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->definitionList(
            ['Saison' => sprintf('%s (%s)', (string) $season->getName(), (string) $season->getSlug())],
            ['Matchs source' => (string) $result['source_matches']],
            ['Créés' => (string) $result['created']],
            ['Mis à jour' => (string) $result['updated']],
            ['Supprimés' => (string) $result['removed']],
            ['Mode' => $dryRun ? 'dry-run' : 'écriture base'],
        );

        if ([] !== $result['synced']) {
            $io->section('Matchs synchronisés');
            $io->table(
                ['Adversaire', 'Date', 'Statut'],
                array_map(
                    static fn (array $match): array => [$match['opponent'], $match['match_date'], $match['status']],
                    $result['synced']
                )
            );
        }

        if ([] !== $result['removed_matches']) {
            $io->section('Matchs supprimés');
            $io->table(
                ['Adversaire', 'Date'],
                array_map(
                    static fn (array $match): array => [$match['opponent'], $match['match_date']],
                    $result['removed_matches']
                )
            );
        }

        $io->success($dryRun ? 'Prévisualisation Tournify terminée.' : 'Synchronisation Tournify terminée.');

        return Command::SUCCESS;
    }
}
