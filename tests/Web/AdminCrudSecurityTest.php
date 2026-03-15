<?php

namespace App\Tests\Web;

use App\Controller\Admin\ArticleCrudController;
use App\Controller\Admin\ClubSettingsCrudController;
use App\Controller\Admin\DashboardController;
use App\Controller\Admin\HomeSectionCrudController;
use App\Controller\Admin\MatchGameCrudController;
use App\Controller\Admin\PageCrudController;
use App\Controller\Admin\PartnerCrudController;
use App\Controller\Admin\PlayerCrudController;
use App\Controller\Admin\RankingEntryCrudController;
use App\Controller\Admin\SeasonCrudController;
use App\Controller\Admin\SocialLinkCrudController;
use App\Controller\Admin\TeamIdentityCrudController;
use App\Controller\Admin\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class AdminCrudSecurityTest extends DatabaseWebTestCase
{
    public function testEditorCanAccessMainAdminCrudIndexes(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);

        foreach ($this->editorCrudControllers() as $controller) {
            $this->client->request('GET', $this->adminUrl($controller));
            self::assertResponseIsSuccessful(sprintf('Admin index failed for %s', $controller));
        }
    }

    public function testEditorCannotAccessUserManagementCrud(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);

        $this->client->request('GET', $this->adminUrl(UserCrudController::class));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessUserManagementCrud(): void
    {
        $admin = $this->createUser('admin@example.com', 'Secret123!', ['ROLE_ADMIN'], 'Admin User');
        $this->client->loginUser($admin);

        $this->client->request('GET', $this->adminUrl(UserCrudController::class));

        self::assertResponseIsSuccessful();
    }

    public function testEditorCanOpenArticleCreationScreen(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);

        $this->client->request('GET', $this->adminUrl(ArticleCrudController::class, Action::NEW));

        self::assertResponseIsSuccessful();
    }

    /**
     * @return list<class-string>
     */
    private function editorCrudControllers(): array
    {
        return [
            SeasonCrudController::class,
            ArticleCrudController::class,
            HomeSectionCrudController::class,
            ClubSettingsCrudController::class,
            MatchGameCrudController::class,
            RankingEntryCrudController::class,
            PlayerCrudController::class,
            PageCrudController::class,
            TeamIdentityCrudController::class,
            SocialLinkCrudController::class,
            PartnerCrudController::class,
        ];
    }

    private function adminUrl(string $controller, ?string $action = null): string
    {
        $generator = static::getContainer()->get(AdminUrlGenerator::class);

        $generator
            ->unsetAll()
            ->setDashboard(DashboardController::class)
            ->setController($controller);

        if (null !== $action) {
            $generator->setAction($action);
        }

        return $generator->generateUrl();
    }
}
