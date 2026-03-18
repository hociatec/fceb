<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Enum\ContentStatus;
use App\Enum\ArticleHomepageSlot;
use App\Enum\TournifySyncStatus;
use App\Entity\HomeSection;
use App\Entity\MatchGame;
use App\Repository\ArticleRepository;
use App\Repository\ClubSettingsRepository;
use App\Repository\HomeSectionRepository;
use App\Repository\MatchGameRepository;
use App\Repository\PageRepository;
use App\Repository\PartnerRepository;
use App\Repository\PlayerRepository;
use App\Repository\RankingEntryRepository;
use App\Repository\SeasonRepository;
use App\Repository\SocialLinkRepository;
use App\Repository\TeamIdentityRepository;
use App\Repository\TournifySyncRunRepository;
use App\Repository\UserRepository;
use App\Service\MatchArticleResolver;
use App\Service\SiteBackupManager;
use App\Service\SiteResetter;
use App\Service\Tournify\TournifyMatchSyncer;
use App\Service\Tournify\TournifySyncRunLogger;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly ArticleRepository $articleRepository,
        private readonly HomeSectionRepository $homeSectionRepository,
        private readonly ClubSettingsRepository $clubSettingsRepository,
        private readonly MatchGameRepository $matchGameRepository,
        private readonly PageRepository $pageRepository,
        private readonly SocialLinkRepository $socialLinkRepository,
        private readonly PartnerRepository $partnerRepository,
        private readonly TeamIdentityRepository $teamIdentityRepository,
        private readonly RankingEntryRepository $rankingEntryRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly UserRepository $userRepository,
        private readonly TournifySyncRunRepository $tournifySyncRunRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly MatchArticleResolver $matchArticleResolver,
        private readonly SiteBackupManager $siteBackupManager,
        private readonly SiteResetter $siteResetter,
        private readonly TournifyMatchSyncer $tournifyMatchSyncer,
        private readonly TournifySyncRunLogger $tournifySyncRunLogger,
    ) {
    }

    public function index(): Response
    {
        $currentSeason = $this->seasonRepository->findCurrentSeason();
        $publishedArticles = $this->articleRepository->count(['status' => ContentStatus::Published]);
        $draftArticles = $this->articleRepository->count(['status' => ContentStatus::Draft]);
        $publishedPages = $this->pageRepository->count(['status' => ContentStatus::Published]);
        $draftPages = $this->pageRepository->count(['status' => ContentStatus::Draft]);
        $playerCount = $this->playerRepository->count([]);
        $partnerCount = $this->partnerRepository->count([]);
        $socialCount = $this->socialLinkRepository->count([]);
        $homeSectionCount = $this->homeSectionRepository->count([]);
        $clubSettingsCount = $this->clubSettingsRepository->count([]);
        $teamIdentityCount = $this->teamIdentityRepository->count([]);
        $currentSeasonCount = $this->seasonRepository->count(['isCurrent' => true]);
        $missingHomeSectionChoices = $this->homeSectionRepository->missingSectionChoices();
        $expectedHomeSectionCount = count(HomeSection::AVAILABLE_KEYS);
        $featuredHomeSection = $this->homeSectionRepository->findConfiguredByKey(HomeSection::KEY_FEATURED_ARTICLE);
        $manualFeaturedArticle = $featuredHomeSection?->getFeaturedArticle();
        $manualSecondaryArticles = $featuredHomeSection?->getManualSecondaryArticles() ?? [];
        $manualVisibleSecondaryCount = count(array_filter(
            $manualSecondaryArticles,
            static fn (Article $article): bool => $article->isVisibleOnSite()
        ));
        $invalidManagedHomeArticlesCount = $this->countInvalidManagedHomeArticles($featuredHomeSection);
        $featuredSlotCount = $this->articleRepository->countCurrentlyVisibleByHomepageSlot(ArticleHomepageSlot::Featured);
        $secondarySlotCount = $this->articleRepository->countCurrentlyVisibleByHomepageSlot(ArticleHomepageSlot::Secondary);
        $scheduledPublicationsCount = $this->articleRepository->countScheduledPublications();
        $publishedArticleCandidates = $this->articleRepository->findLatestPublished(50);
        $completedMatchesCount = $this->matchGameRepository->count(['status' => \App\Enum\MatchStatus::Completed]);
        $scheduledMatchesCount = $this->matchGameRepository->count(['status' => \App\Enum\MatchStatus::Scheduled]);
        $userCount = $this->userRepository->count([]);
        $completedMatches = $this->matchGameRepository->findBy(['status' => \App\Enum\MatchStatus::Completed], ['matchDate' => 'DESC']);
        $completedArticleSummary = $this->matchArticleResolver->summarize($completedMatches, $publishedArticleCandidates);
        $latestSyncRun = $this->tournifySyncRunRepository->findLatestRun();
        $recentSyncRuns = $this->tournifySyncRunRepository->findLatestRuns(5);
        $summaryStats = [
            [
                'label' => 'Joueurs inscrits',
                'value' => (string) $playerCount,
                'meta' => 'Fiches effectif enregistrées',
            ],
            [
                'label' => 'Partenaires inscrits',
                'value' => (string) $partnerCount,
                'meta' => 'Partenaires affichables sur le site',
            ],
            [
                'label' => 'Matchs joués',
                'value' => (string) $completedMatchesCount,
                'meta' => 'Rencontres terminées',
            ],
            [
                'label' => 'Matchs à venir',
                'value' => (string) $scheduledMatchesCount,
                'meta' => 'Rencontres programmées',
            ],
            [
                'label' => 'Articles publiés',
                'value' => (string) $publishedArticles,
                'meta' => 'Contenus visibles maintenant',
            ],
            [
                'label' => 'Comptes admin',
                'value' => (string) $userCount,
                'meta' => 'Accès à l’administration',
            ],
        ];

        $statGroups = [
            [
                'label' => 'Contenus',
                'description' => 'Saison, articles, accueil et pages publiques.',
                'items' => [
                    ['label' => 'Saisons', 'value' => $this->seasonRepository->count([]), 'icon' => 'fa fa-calendar', 'url' => $this->crudUrl(SeasonCrudController::class)],
                    ['label' => 'Articles publiés', 'value' => $publishedArticles, 'icon' => 'fa fa-newspaper', 'url' => $this->crudUrl(ArticleCrudController::class)],
                    ['label' => 'Blocs accueil', 'value' => $homeSectionCount, 'icon' => 'fa fa-panorama', 'url' => $this->crudUrl(HomeSectionCrudController::class)],
                    ['label' => 'Paramètres club', 'value' => $clubSettingsCount, 'icon' => 'fa fa-sliders', 'url' => $this->crudUrl(ClubSettingsCrudController::class)],
                    ['label' => 'Pages publiées', 'value' => $publishedPages, 'icon' => 'fa fa-file-lines', 'url' => $this->crudUrl(PageCrudController::class)],
                ],
            ],
            [
                'label' => 'Sport',
                'description' => 'Calendrier, classement, effectif et identités équipes.',
                'items' => [
                    ['label' => 'Matchs', 'value' => $this->matchGameRepository->count([]), 'icon' => 'fa fa-futbol', 'url' => $this->crudUrl(MatchGameCrudController::class)],
                    ['label' => 'Classement', 'value' => $this->rankingEntryRepository->count([]), 'icon' => 'fa fa-list-ol', 'url' => $this->crudUrl(RankingEntryCrudController::class)],
                    ['label' => 'Effectif', 'value' => $playerCount, 'icon' => 'fa fa-user-group', 'url' => $this->crudUrl(PlayerCrudController::class)],
                    ['label' => 'Équipes', 'value' => $teamIdentityCount, 'icon' => 'fa fa-shield-halved', 'url' => $this->crudUrl(TeamIdentityCrudController::class)],
                ],
            ],
            [
                'label' => 'Communication',
                'description' => 'Partenaires et présence externe du club.',
                'items' => [
                    ['label' => 'Réseaux sociaux', 'value' => $socialCount, 'icon' => 'fa fa-share-nodes', 'url' => $this->crudUrl(SocialLinkCrudController::class)],
                    ['label' => 'Partenaires', 'value' => $partnerCount, 'icon' => 'fa fa-handshake', 'url' => $this->crudUrl(PartnerCrudController::class)],
                ],
            ],
        ];

        $contentChecks = [];

        if (!$currentSeason) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Saison en cours', 'message' => "Aucune saison n'est définie comme saison en cours.", 'url' => $this->crudUrl(SeasonCrudController::class)];
        } elseif ($currentSeasonCount > 1) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Saison en cours', 'message' => "Plusieurs saisons sont marquées comme saison en cours. L'administration doit être régularisée.", 'url' => $this->crudUrl(SeasonCrudController::class)];
        }

        if (0 === $publishedArticles) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Actualités', 'message' => "Aucun article publié n'est visible sur le site.", 'url' => $this->crudUrl(ArticleCrudController::class)];
        }

        $manualFeaturedVisible = $manualFeaturedArticle instanceof Article && $manualFeaturedArticle->isVisibleOnSite();
        if (!$manualFeaturedVisible && 0 === $featuredSlotCount) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Accueil · À la une', 'message' => "Aucun article visible ne peut alimenter l'actualité à la une de l'accueil.", 'url' => $this->crudUrl(ArticleCrudController::class)];
        }

        if (0 === $manualVisibleSecondaryCount && 0 === $secondarySlotCount) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Accueil · Autres actualités', 'message' => "Aucun article visible n'est prêt pour la liste « Autres actualités ».", 'url' => $this->crudUrl(ArticleCrudController::class)];
        } elseif ($secondarySlotCount > 3) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Accueil · Autres actualités', 'message' => sprintf('%d articles sont marqués « Autres actualités » : seuls les 3 plus récents seront repris automatiquement.', $secondarySlotCount), 'url' => $this->crudUrl(ArticleCrudController::class)];
        }

        if ($invalidManagedHomeArticlesCount > 0) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Accueil · Sélection manuelle', 'message' => sprintf('%d article(s) choisis manuellement pour l’accueil ne sont pas encore visibles publiquement.', $invalidManagedHomeArticlesCount), 'url' => $this->crudUrl(HomeSectionCrudController::class)];
        }

        if (0 === $playerCount) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Effectif', 'message' => "L'effectif est vide : aucune fiche joueur n'est encore renseignée.", 'url' => $this->crudUrl(PlayerCrudController::class)];
        }

        if (0 === $partnerCount) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Partenaires', 'message' => "Aucun partenaire n'est encore affiché publiquement.", 'url' => $this->crudUrl(PartnerCrudController::class)];
        }

        if (0 === $socialCount) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Réseaux sociaux', 'message' => "Aucun réseau social n'est configuré dans le footer.", 'url' => $this->crudUrl(SocialLinkCrudController::class)];
        }

        if (0 === $homeSectionCount) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Accueil', 'message' => "Aucun bloc d'accueil administrable n'est configuré.", 'url' => $this->crudActionUrl(HomeSectionCrudController::class, Action::NEW)];
        } elseif ([] !== $missingHomeSectionChoices) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Accueil', 'message' => sprintf('Il manque %d bloc(s) d’accueil sur %d attendus.', count($missingHomeSectionChoices), $expectedHomeSectionCount), 'url' => $this->crudActionUrl(HomeSectionCrudController::class, Action::NEW)];
        }

        if (0 === $clubSettingsCount) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Paramètres du club', 'message' => "Les paramètres globaux du club ne sont pas encore initialisés.", 'url' => $this->crudActionUrl(ClubSettingsCrudController::class, Action::NEW)];
        }

        if (0 === $teamIdentityCount) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Identités équipe', 'message' => "Les logos d'équipe ne sont pas encore renseignés.", 'url' => $this->crudUrl(TeamIdentityCrudController::class)];
        }

        if ($completedArticleSummary['unresolved'] > 0) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Compte-rendus matchs', 'message' => sprintf('%d match(s) terminés n’ont encore aucun compte-rendu détecté.', $completedArticleSummary['unresolved']), 'url' => $this->crudUrl(MatchGameCrudController::class)];
        } elseif ($completedArticleSummary['automatic'] > 0) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Compte-rendus matchs', 'message' => sprintf('%d match(s) terminés sont reliés automatiquement à leur article, sans liaison explicite enregistrée.', $completedArticleSummary['automatic']), 'url' => $this->crudUrl(MatchGameCrudController::class)];
        }

        if ($scheduledPublicationsCount > 0) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Publications planifiées', 'message' => sprintf('%d article(s) publiés avec une date future attendent encore leur mise en ligne automatique.', $scheduledPublicationsCount), 'url' => $this->crudUrl(ArticleCrudController::class)];
        }

        if (null === $latestSyncRun) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Tournify', 'message' => "Aucune synchronisation Tournify n'a encore été lancée depuis l'administration.", 'url' => $this->crudUrl(TournifySyncRunCrudController::class)];
        } elseif (TournifySyncStatus::Failure === $latestSyncRun->getStatus()) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Tournify', 'message' => 'Le dernier lancement Tournify a échoué. Vérifie le journal avant de republier les matchs.', 'url' => $this->crudUrl(TournifySyncRunCrudController::class)];
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $statGroups[] = [
                'label' => 'Comptes',
                'description' => 'Utilisateurs ayant accès à l’administration.',
                'items' => [
                    ['label' => 'Utilisateurs', 'value' => $userCount, 'icon' => 'fa fa-users', 'url' => $this->crudUrl(UserCrudController::class)],
                ],
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'summaryStats' => $summaryStats,
            'statGroups' => $statGroups,
            'latestArticles' => $this->articleRepository->findLatestPublished(4),
            'currentSeason' => $currentSeason,
            'socialCount' => $socialCount,
            'draftArticles' => $draftArticles,
            'draftPages' => $draftPages,
            'scheduledArticles' => $this->articleRepository->findScheduledPublications(5),
            'contentChecks' => $contentChecks,
            'tournifySync' => [
                'liveLink' => TournifyMatchSyncer::DEFAULT_LIVE_LINK,
                'divisionId' => TournifyMatchSyncer::DEFAULT_DIVISION_ID,
                'teamName' => TournifyMatchSyncer::DEFAULT_TEAM_NAME,
                'matchesUrl' => $this->crudUrl(MatchGameCrudController::class),
                'historyUrl' => $this->crudUrl(TournifySyncRunCrudController::class),
            ],
            'latestSyncRun' => $latestSyncRun,
            'recentSyncRuns' => $recentSyncRuns,
        ]);
    }

    #[Route('/admin/tournify/sync-matches', name: 'admin_tournify_sync_matches', methods: ['POST'])]
    public function syncTournifyMatches(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EDITOR');

        if (!$this->isCsrfTokenValid('admin_tournify_sync_matches', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $season = $this->seasonRepository->findCurrentSeason();
        if (null === $season) {
            $this->addFlash('danger', "Synchronisation Tournify impossible : aucune saison n'est définie comme saison en cours.");

            return $this->redirectToRoute('admin');
        }

        $dryRun = $request->request->getBoolean('dry_run');
        $liveLink = TournifyMatchSyncer::DEFAULT_LIVE_LINK;
        $divisionId = TournifyMatchSyncer::DEFAULT_DIVISION_ID;
        $teamName = TournifyMatchSyncer::DEFAULT_TEAM_NAME;

        try {
            $result = $this->tournifyMatchSyncer->syncSeason(
                $season,
                $liveLink,
                $divisionId,
                $teamName,
                dryRun: $dryRun,
            );

            $this->tournifySyncRunLogger->logResult(
                $season,
                $liveLink,
                $divisionId,
                $teamName,
                MatchGame::COMPETITION_CHAMPIONNAT,
                $dryRun,
                $result,
            );

            $this->addFlash(
                'success',
                sprintf(
                    $dryRun
                        ? 'Prévisualisation Tournify : %d matchs source, %d créés, %d mis à jour, %d supprimés.'
                        : 'Synchronisation Tournify terminée : %d matchs source, %d créés, %d mis à jour, %d supprimés.',
                    $result['source_matches'],
                    $result['created'],
                    $result['updated'],
                    $result['removed'],
                )
            );
        } catch (\Throwable $exception) {
            $this->tournifySyncRunLogger->logFailure(
                $season,
                $liveLink,
                $divisionId,
                $teamName,
                MatchGame::COMPETITION_CHAMPIONNAT,
                $dryRun,
                $exception,
            );
            $this->addFlash('danger', sprintf('La synchronisation Tournify a échoué : %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/site/reset', name: 'admin_reset_site', methods: ['POST'])]
    public function resetSite(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('admin_reset_site', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $confirmation = trim((string) $request->request->get('confirmation'));
        if ('REINITIALISER' !== strtoupper($confirmation)) {
            $this->addFlash('danger', 'Réinitialisation annulée : saisis REINITIALISER pour confirmer.');

            return $this->redirectToRoute('admin');
        }

        try {
            $result = $this->siteResetter->resetContent();
            $this->addFlash(
                'success',
                sprintf(
                    'Réinitialisation terminée : %d enregistrement(s) supprimé(s), %d fichier(s) uploadé(s) nettoyé(s). Les comptes utilisateurs sont conservés.',
                    $result['total_rows'],
                    $result['deleted_files'],
                )
            );

            foreach ($result['warnings'] as $warning) {
                $this->addFlash('warning', $warning);
            }
        } catch (\Throwable $exception) {
            $this->addFlash('danger', sprintf('La réinitialisation du site a échoué : %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/site/backup', name: 'admin_site_backup', methods: ['POST'])]
    public function downloadSiteBackup(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('admin_site_backup', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $backupPath = $this->siteBackupManager->createBackupFile();
        $response = new BinaryFileResponse($backupPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($backupPath)
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    #[Route('/admin/site/restore-backup', name: 'admin_restore_site_backup', methods: ['POST'])]
    public function restoreSiteBackup(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('admin_restore_site_backup', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $confirmation = trim((string) $request->request->get('confirmation'));
        if ('RESTAURER' !== strtoupper($confirmation)) {
            $this->addFlash('danger', 'Restauration annulée : saisis RESTAURER pour confirmer.');

            return $this->redirectToRoute('admin');
        }

        $uploadedFile = $request->files->get('backup_file');
        if (!$uploadedFile instanceof UploadedFile || !$uploadedFile->isValid()) {
            $this->addFlash('danger', 'Aucun fichier de sauvegarde valide n\'a été envoyé.');

            return $this->redirectToRoute('admin');
        }

        try {
            $result = $this->siteBackupManager->restoreFromUploadedBackup($uploadedFile);
            $this->addFlash(
                'success',
                sprintf(
                    'Restauration terminée : %d table(s), %d ligne(s) et %d fichier(s) restaurés.',
                    $result['tables'],
                    $result['rows'],
                    $result['files'],
                )
            );
        } catch (\Throwable $exception) {
            $this->addFlash('danger', sprintf('La restauration a échoué : %s', $exception->getMessage()));
        }

        return $this->redirectToRoute('admin');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration Cécifoot La Bassée')
            ->renderContentMaximized();
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('assets/admin-theme.css')
            ->addJsFile('assets/admin-slug.js')
            ->addJsFile('assets/admin-page-editor.js');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-house');
        yield MenuItem::section('Contenus');
        yield MenuItem::linkTo(SeasonCrudController::class, 'Saisons', 'fa fa-calendar');
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper');
        yield MenuItem::linkTo(HomeSectionCrudController::class, "Blocs d'accueil", 'fa fa-panorama');
        yield MenuItem::linkTo(ClubSettingsCrudController::class, 'Paramètres du club', 'fa fa-sliders');
        yield MenuItem::linkTo(MatchGameCrudController::class, 'Matchs', 'fa fa-futbol');
        yield MenuItem::linkTo(TournifySyncRunCrudController::class, 'Journal Tournify', 'fa fa-rotate');
        yield MenuItem::linkTo(RankingEntryCrudController::class, 'Classement', 'fa fa-list-ol');
        yield MenuItem::linkTo(PlayerCrudController::class, 'Effectif', 'fa fa-user-group');
        yield MenuItem::linkTo(PlayerPhotoCrudController::class, 'Photos joueurs', 'fa fa-images');
        yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fa fa-file-lines');
        yield MenuItem::linkTo(TeamIdentityCrudController::class, 'Équipes', 'fa fa-shield-halved');
        yield MenuItem::section('Communication');
        yield MenuItem::linkTo(SocialLinkCrudController::class, 'Réseaux sociaux', 'fa fa-share-nodes');
        yield MenuItem::linkTo(PartnerCrudController::class, 'Partenaires', 'fa fa-handshake');
        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Comptes');
            yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');
        }
        yield MenuItem::linkToRoute('Voir le site', 'fa fa-arrow-up-right-from-square', 'site_home');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-right-from-bracket');
    }

    private function crudUrl(string $controllerFqcn): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController($controllerFqcn)
            ->generateUrl();
    }

    private function crudActionUrl(string $controllerFqcn, string $action): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController($controllerFqcn)
            ->setAction($action)
            ->generateUrl();
    }

    private function countInvalidManagedHomeArticles(?HomeSection $featuredHomeSection): int
    {
        if (!$featuredHomeSection instanceof HomeSection) {
            return 0;
        }

        $count = 0;
        $featuredArticle = $featuredHomeSection->getFeaturedArticle();
        if ($featuredArticle instanceof Article && !$featuredArticle->isVisibleOnSite()) {
            ++$count;
        }

        foreach ($featuredHomeSection->getManualSecondaryArticles() as $article) {
            if (!$article->isVisibleOnSite()) {
                ++$count;
            }
        }

        return $count;
    }
}
