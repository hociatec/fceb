<?php

namespace App\Controller\Admin;

use App\Enum\ContentStatus;
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
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

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
        private readonly AdminUrlGenerator $adminUrlGenerator,
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

        $stats = [
            ['label' => 'Saisons', 'value' => $this->seasonRepository->count([]), 'icon' => 'fa fa-calendar', 'url' => $this->crudUrl(SeasonCrudController::class)],
            ['label' => 'Articles publiés', 'value' => $publishedArticles, 'icon' => 'fa fa-newspaper', 'url' => $this->crudUrl(ArticleCrudController::class)],
            ['label' => 'Blocs accueil', 'value' => $homeSectionCount, 'icon' => 'fa fa-panorama', 'url' => $this->crudUrl(HomeSectionCrudController::class)],
            ['label' => 'Paramètres club', 'value' => $clubSettingsCount, 'icon' => 'fa fa-sliders', 'url' => $this->crudUrl(ClubSettingsCrudController::class)],
            ['label' => 'Matchs', 'value' => $this->matchGameRepository->count([]), 'icon' => 'fa fa-futbol', 'url' => $this->crudUrl(MatchGameCrudController::class)],
            ['label' => 'Classement', 'value' => $this->rankingEntryRepository->count([]), 'icon' => 'fa fa-list-ol', 'url' => $this->crudUrl(RankingEntryCrudController::class)],
            ['label' => 'Effectif', 'value' => $playerCount, 'icon' => 'fa fa-user-group', 'url' => $this->crudUrl(PlayerCrudController::class)],
            ['label' => 'Pages publiées', 'value' => $publishedPages, 'icon' => 'fa fa-file-lines', 'url' => $this->crudUrl(PageCrudController::class)],
            ['label' => 'Équipes', 'value' => $teamIdentityCount, 'icon' => 'fa fa-shield-halved', 'url' => $this->crudUrl(TeamIdentityCrudController::class)],
            ['label' => 'Partenaires', 'value' => $partnerCount, 'icon' => 'fa fa-handshake', 'url' => $this->crudUrl(PartnerCrudController::class)],
        ];

        $quickLinks = [
            ['label' => 'Nouvel article', 'url' => $this->crudActionUrl(ArticleCrudController::class, Action::NEW)],
            ['label' => 'Nouveau match', 'url' => $this->crudActionUrl(MatchGameCrudController::class, Action::NEW)],
            ['label' => 'Nouveau joueur', 'url' => $this->crudActionUrl(PlayerCrudController::class, Action::NEW)],
            ['label' => 'Nouvelle page', 'url' => $this->crudActionUrl(PageCrudController::class, Action::NEW)],
            ['label' => "Gérer l'accueil", 'url' => $this->crudUrl(HomeSectionCrudController::class)],
            ['label' => 'Paramètres du club', 'url' => $this->crudUrl(ClubSettingsCrudController::class)],
            ['label' => 'Gérer le classement', 'url' => $this->crudUrl(RankingEntryCrudController::class)],
            ['label' => 'Voir le site public', 'url' => '/'],
        ];

        $contentChecks = [];

        if (!$currentSeason) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Saison en cours', 'message' => "Aucune saison n'est définie comme saison en cours.", 'url' => $this->crudUrl(SeasonCrudController::class)];
        }

        if (0 === $publishedArticles) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Actualités', 'message' => "Aucun article publié n'est visible sur le site.", 'url' => $this->crudUrl(ArticleCrudController::class)];
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
            $contentChecks[] = ['level' => 'info', 'label' => 'Accueil', 'message' => "Aucun bloc d'accueil administrable n'est configuré.", 'url' => $this->crudUrl(HomeSectionCrudController::class)];
        }

        if (0 === $clubSettingsCount) {
            $contentChecks[] = ['level' => 'warning', 'label' => 'Paramètres du club', 'message' => "Les paramètres globaux du club ne sont pas encore initialisés.", 'url' => $this->crudUrl(ClubSettingsCrudController::class)];
        }

        if (0 === $teamIdentityCount) {
            $contentChecks[] = ['level' => 'info', 'label' => 'Identités équipe', 'message' => "Les logos d'équipe ne sont pas encore renseignés.", 'url' => $this->crudUrl(TeamIdentityCrudController::class)];
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $stats[] = ['label' => 'Utilisateurs', 'value' => $this->userRepository->count([]), 'icon' => 'fa fa-users', 'url' => $this->crudUrl(UserCrudController::class)];
            $quickLinks[] = ['label' => 'Gérer les utilisateurs', 'url' => $this->crudUrl(UserCrudController::class)];
        }

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'quickLinks' => $quickLinks,
            'latestArticles' => $this->articleRepository->findLatestPublished(5),
            'currentSeason' => $currentSeason,
            'socialCount' => $socialCount,
            'draftArticles' => $draftArticles,
            'draftPages' => $draftPages,
            'contentChecks' => $contentChecks,
        ]);
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
        yield MenuItem::linkTo(HomeSectionCrudController::class, 'Accueil', 'fa fa-panorama');
        yield MenuItem::linkTo(ClubSettingsCrudController::class, 'Paramètres du club', 'fa fa-sliders');
        yield MenuItem::linkTo(MatchGameCrudController::class, 'Matchs', 'fa fa-futbol');
        yield MenuItem::linkTo(RankingEntryCrudController::class, 'Classement', 'fa fa-list-ol');
        yield MenuItem::linkTo(PlayerCrudController::class, 'Effectif', 'fa fa-user-group');
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
}
