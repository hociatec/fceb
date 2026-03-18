<?php

namespace App\Tests\Web;

use App\Entity\Article;
use App\Entity\ClubSettings;
use App\Entity\Page;
use App\Entity\Player;
use App\Enum\ArticleHomepageSlot;
use App\Enum\ContentStatus;
use App\Enum\PagePlacement;

class ContentVisibilityTest extends DatabaseWebTestCase
{
    public function testDraftArticleRequiresAdminPreview(): void
    {
        $article = (new Article())
            ->setTitle('Article brouillon')
            ->setSlug('article-brouillon')
            ->setExcerpt('Extrait du brouillon')
            ->setContent('<p>Contenu brouillon</p>')
            ->setPublishedAt(new \DateTimeImmutable('2026-03-15 10:00:00'))
            ->setHomepageSlot(ArticleHomepageSlot::None)
            ->setStatus(ContentStatus::Draft);

        $this->persist($article);

        $this->client->request('GET', '/actualites/article-brouillon');
        self::assertResponseStatusCodeSame(404);

        $admin = $this->createUser('admin@example.com', 'Secret123!', ['ROLE_ADMIN'], 'Admin User');
        $this->client->loginUser($admin);
        $this->client->request('GET', '/actualites/article-brouillon?preview=1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Article brouillon');
    }

    public function testDraftPageRequiresAdminPreview(): void
    {
        $page = (new Page())
            ->setTitle('Page brouillon')
            ->setSlug('page-brouillon')
            ->setContent('<p>Contenu page brouillon</p>')
            ->setPlacement(PagePlacement::None)
            ->setStatus(ContentStatus::Draft);

        $this->persist($page);

        $this->client->request('GET', '/pages/page-brouillon');
        self::assertResponseStatusCodeSame(404);

        $admin = $this->createUser('admin@example.com', 'Secret123!', ['ROLE_ADMIN'], 'Admin User');
        $this->client->loginUser($admin);
        $this->client->request('GET', '/pages/page-brouillon?preview=1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Page brouillon');
    }

    public function testDraftPlayerRequiresAdminPreview(): void
    {
        $player = (new Player())
            ->setName('Joueur Brouillon')
            ->setSlug('joueur-brouillon')
            ->setDescription('Description du joueur brouillon.')
            ->setStatus(ContentStatus::Draft);

        $this->persist($player);

        $this->client->request('GET', '/effectif/joueur-brouillon');
        self::assertResponseStatusCodeSame(404);

        $admin = $this->createUser('admin@example.com', 'Secret123!', ['ROLE_ADMIN'], 'Admin User');
        $this->client->loginUser($admin);
        $this->client->request('GET', '/effectif/joueur-brouillon?preview=1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Joueur Brouillon');
    }

    public function testArticleNavigationLinksPreviousAndNextPublishedArticles(): void
    {
        $older = (new Article())
            ->setTitle('Article plus ancien')
            ->setSlug('article-plus-ancien')
            ->setExcerpt('Plus ancien')
            ->setContent('<p>Ancien contenu</p>')
            ->setPublishedAt(new \DateTimeImmutable('2026-03-13 10:00:00'))
            ->setHomepageSlot(ArticleHomepageSlot::None)
            ->setStatus(ContentStatus::Published);

        $middle = (new Article())
            ->setTitle('Article du milieu')
            ->setSlug('article-du-milieu')
            ->setExcerpt('Milieu')
            ->setContent('<p>Milieu contenu</p>')
            ->setPublishedAt(new \DateTimeImmutable('2026-03-14 10:00:00'))
            ->setHomepageSlot(ArticleHomepageSlot::None)
            ->setStatus(ContentStatus::Published);

        $newer = (new Article())
            ->setTitle('Article plus recent')
            ->setSlug('article-plus-recent')
            ->setExcerpt('Plus recent')
            ->setContent('<p>Recent contenu</p>')
            ->setPublishedAt(new \DateTimeImmutable('2026-03-15 10:00:00'))
            ->setHomepageSlot(ArticleHomepageSlot::None)
            ->setStatus(ContentStatus::Published);

        $this->persist($older);
        $this->persist($middle);
        $this->persist($newer);

        $this->client->request('GET', '/actualites/article-du-milieu');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href="/actualites/article-plus-ancien"]');
        self::assertSelectorExists('a[href="/actualites/article-plus-recent"]');
    }

    public function testHomepageUsesClubSettingsWhenConfigured(): void
    {
        $settings = (new ClubSettings())
            ->setClubName('FC Test Club')
            ->setPublicEmail('club@example.com')
            ->setPhone('01 02 03 04 05')
            ->setAddress('1 rue du Test')
            ->setMapUrl('https://example.com/map');

        $this->persist($settings);
        $this->clearAppCache();

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('club@example.com', $content);
        self::assertStringContainsString('01 02 03 04 05', $content);
        self::assertStringContainsString('1 rue du Test', $content);
    }
}
