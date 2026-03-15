<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
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
        $stats = [
            ['label' => 'Saisons', 'value' => $this->seasonRepository->count([]), 'icon' => 'fa fa-calendar', 'url' => $this->crudUrl(SeasonCrudController::class)],
            ['label' => 'Articles', 'value' => $this->articleRepository->count([]), 'icon' => 'fa fa-newspaper', 'url' => $this->crudUrl(ArticleCrudController::class)],
            ['label' => 'Accueil', 'value' => $this->homeSectionRepository->count([]), 'icon' => 'fa fa-panorama', 'url' => $this->crudUrl(HomeSectionCrudController::class)],
            ['label' => 'Matchs', 'value' => $this->matchGameRepository->count([]), 'icon' => 'fa fa-futbol', 'url' => $this->crudUrl(MatchGameCrudController::class)],
            ['label' => 'Classement', 'value' => $this->rankingEntryRepository->count([]), 'icon' => 'fa fa-list-ol', 'url' => $this->crudUrl(RankingEntryCrudController::class)],
            ['label' => 'Effectif', 'value' => $this->playerRepository->count([]), 'icon' => 'fa fa-user-group', 'url' => $this->crudUrl(PlayerCrudController::class)],
            ['label' => 'Pages', 'value' => $this->pageRepository->count([]), 'icon' => 'fa fa-file-lines', 'url' => $this->crudUrl(PageCrudController::class)],
            ['label' => 'Équipes', 'value' => $this->teamIdentityRepository->count([]), 'icon' => 'fa fa-shield-halved', 'url' => $this->crudUrl(TeamIdentityCrudController::class)],
            ['label' => 'Partenaires', 'value' => $this->partnerRepository->count([]), 'icon' => 'fa fa-handshake', 'url' => $this->crudUrl(PartnerCrudController::class)],
        ];

        $quickLinks = [
            ['label' => 'Gérer les saisons', 'url' => $this->crudUrl(SeasonCrudController::class)],
            ['label' => 'Gérer les articles', 'url' => $this->crudUrl(ArticleCrudController::class)],
            ['label' => "Gérer l'accueil", 'url' => $this->crudUrl(HomeSectionCrudController::class)],
            ['label' => 'Gérer les matchs', 'url' => $this->crudUrl(MatchGameCrudController::class)],
            ['label' => 'Gérer le classement', 'url' => $this->crudUrl(RankingEntryCrudController::class)],
            ['label' => "Gérer l'effectif", 'url' => $this->crudUrl(PlayerCrudController::class)],
            ['label' => 'Gérer les pages', 'url' => $this->crudUrl(PageCrudController::class)],
            ['label' => 'Gérer les équipes', 'url' => $this->crudUrl(TeamIdentityCrudController::class)],
            ['label' => 'Voir le site public', 'url' => '/'],
        ];

        if ($this->isGranted('ROLE_ADMIN')) {
            $stats[] = ['label' => 'Utilisateurs', 'value' => $this->userRepository->count([]), 'icon' => 'fa fa-users', 'url' => $this->crudUrl(UserCrudController::class)];
            $quickLinks[] = ['label' => 'Gérer les utilisateurs', 'url' => $this->crudUrl(UserCrudController::class)];
        }

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'quickLinks' => $quickLinks,
            'latestArticles' => $this->articleRepository->findLatestPublished(5),
            'currentSeason' => $this->seasonRepository->findCurrentSeason(),
            'socialCount' => $this->socialLinkRepository->count([]),
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
}
