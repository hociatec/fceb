<?php

namespace App\Tests\Web;

use App\Controller\Admin\ArticleCrudController;
use App\Controller\Admin\DashboardController;
use App\Controller\Admin\MatchGameCrudController;
use App\Controller\Admin\PageCrudController;
use App\Controller\Admin\PlayerCrudController;
use App\Controller\Admin\RankingEntryCrudController;
use App\Controller\Admin\SeasonCrudController;
use App\Entity\Article;
use App\Entity\MatchGame;
use App\Entity\Page;
use App\Entity\Player;
use App\Entity\RankingEntry;
use App\Entity\Season;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DomCrawler\Crawler;

class AdminCrudFlowsTest extends DatabaseWebTestCase
{
    public function testAdminCanCreateSeasonFromAdmin(): void
    {
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', $this->adminUrl(SeasonCrudController::class, Action::NEW));
        $this->submitAdminForm($crawler, [
            'name' => 'Saison 2026-2027',
            'slug' => 'saison-2026-2027',
            'startDate' => '2026-09-01',
            'endDate' => '2027-06-30',
            'isCurrent' => '1',
        ]);

        self::assertResponseRedirects();

        /** @var Season|null $season */
        $season = $this->entityManager->getRepository(Season::class)->findOneBy(['slug' => 'saison-2026-2027']);
        self::assertNotNull($season);
        self::assertSame('Saison 2026-2027', $season->getName());
        self::assertTrue($season->isCurrent());
    }

    public function testEditorCanCreateArticleFromAdmin(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);

        $crawler = $this->client->request('GET', $this->adminUrl(ArticleCrudController::class, Action::NEW));
        $this->submitAdminForm($crawler, [
            'title' => 'Nouvel article admin',
            'slug' => 'nouvel-article-admin',
            'excerpt' => 'Résumé admin',
            'content' => '<div>Contenu article admin</div>',
            'publishedAt' => '2026-03-16T10:00',
            'status' => '1',
        ]);

        self::assertResponseRedirects();

        /** @var Article|null $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBy(['slug' => 'nouvel-article-admin']);
        self::assertNotNull($article);
        self::assertSame('Nouvel article admin', $article->getTitle());
        self::assertSame('Résumé admin', $article->getExcerpt());
    }

    public function testEditorCanCreatePageFromAdmin(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);

        $crawler = $this->client->request('GET', $this->adminUrl(PageCrudController::class, Action::NEW));
        $this->submitAdminForm($crawler, [
            'title' => 'Page admin',
            'slug' => 'page-admin',
            'content' => '<p>Contenu page admin</p>',
            'placement' => '1',
            'menuOrder' => '2',
            'status' => '1',
        ]);

        self::assertResponseRedirects();

        /** @var Page|null $page */
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(['slug' => 'page-admin']);
        self::assertNotNull($page);
        self::assertSame('Page admin', $page->getTitle());
        self::assertSame(2, $page->getMenuOrder());
    }

    public function testEditorCanCreatePlayerFromAdmin(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);

        $crawler = $this->client->request('GET', $this->adminUrl(PlayerCrudController::class, Action::NEW));
        $this->submitAdminForm($crawler, [
            'name' => 'Joueur Admin',
            'slug' => 'joueur-admin',
            'birthDate' => '2000-01-02',
            'nationality' => 'Française',
            'preferredPosition' => 'Attaquant',
            'preferredFoot' => 'Droitier',
            'description' => 'Présentation du joueur depuis l’admin.',
            'displayOrder' => '1',
            'status' => '1',
        ]);

        self::assertResponseRedirects();

        /** @var Player|null $player */
        $player = $this->entityManager->getRepository(Player::class)->findOneBy(['slug' => 'joueur-admin']);
        self::assertNotNull($player);
        self::assertSame('Joueur Admin', $player->getName());
        self::assertSame('Attaquant', $player->getPreferredPosition());
    }

    public function testEditorCanCreateMatchAndRankingEntryFromAdmin(): void
    {
        $editor = $this->createUser('editor@example.com', 'Secret123!', ['ROLE_EDITOR'], 'Editor User');
        $this->client->loginUser($editor);
        $season = $this->persist(
            (new Season())
                ->setName('Saison Test')
                ->setSlug('saison-test')
                ->setStartDate(new \DateTimeImmutable('2026-09-01'))
                ->setEndDate(new \DateTimeImmutable('2027-06-30'))
                ->setIsCurrent(true)
        );

        $matchCrawler = $this->client->request('GET', $this->adminUrl(MatchGameCrudController::class, Action::NEW));
        $this->submitAdminForm($matchCrawler, [
            'season' => (string) $season->getId(),
            'competition' => 'Championnat',
            'opponent' => 'RC Lens Cécifoot',
            'location' => 'La Bassée',
            'matchDate' => '2026-10-01T14:30',
            'side' => 'home',
            'status' => '0',
            'ourScore' => '',
            'opponentScore' => '',
        ]);

        self::assertResponseRedirects();

        /** @var MatchGame|null $match */
        $match = $this->entityManager->getRepository(MatchGame::class)->findOneBy(['opponent' => 'RC Lens Cécifoot']);
        self::assertNotNull($match);
        self::assertSame('La Bassée', $match->getLocation());

        $rankingCrawler = $this->client->request('GET', $this->adminUrl(RankingEntryCrudController::class, Action::NEW));
        $this->submitAdminForm($rankingCrawler, [
            'season' => (string) $season->getId(),
            'teamName' => 'Cécifoot La Bassée',
            'points' => '12',
            'wins' => '4',
            'losses' => '1',
            'goalDifference' => '8',
            'displayOrder' => '1',
        ]);

        self::assertResponseRedirects();

        /** @var RankingEntry|null $entry */
        $entry = $this->entityManager->getRepository(RankingEntry::class)->findOneBy(['teamName' => 'Cécifoot La Bassée']);
        self::assertNotNull($entry);
        self::assertSame(12, $entry->getPoints());
    }

    private function loginAsAdmin(): void
    {
        $admin = $this->createUser('admin@example.com', 'Secret123!', ['ROLE_ADMIN'], 'Admin User');
        $this->client->loginUser($admin);
    }

    private function adminUrl(string $controller, ?string $action = null, ?int $entityId = null): string
    {
        $generator = static::getContainer()->get(AdminUrlGenerator::class);

        $generator
            ->unsetAll()
            ->setDashboard(DashboardController::class)
            ->setController($controller);

        if (null !== $action) {
            $generator->setAction($action);
        }

        if (null !== $entityId) {
            $generator->setEntityId($entityId);
        }

        return $generator->generateUrl();
    }

    /**
     * @param array<string, mixed> $fieldValues
     */
    private function submitAdminForm(Crawler $crawler, array $fieldValues): void
    {
        $formCrawler = $crawler->filter('form')->last();
        $availableNames = $formCrawler
            ->filter('input[name], select[name], textarea[name]')
            ->each(static fn (Crawler $node): string => (string) $node->attr('name'));
        $resolvedValues = [];

        foreach ($fieldValues as $suffix => $value) {
            $resolvedName = null;

            foreach ($availableNames as $name) {
                if ($name === $suffix || str_ends_with($name, sprintf('[%s]', $suffix)) || str_ends_with($name, sprintf('[%s][]', $suffix))) {
                    $resolvedName = $name;
                    break;
                }
            }

            self::assertNotNull($resolvedName, sprintf('Champ admin introuvable pour le suffixe "%s".', $suffix));
            $resolvedValues[$resolvedName] = $value;
        }

        $this->client->submit($formCrawler->form($resolvedValues));
    }
}
