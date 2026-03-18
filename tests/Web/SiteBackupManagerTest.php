<?php

namespace App\Tests\Web;

use App\Entity\Article;
use App\Entity\ClubSettings;
use App\Enum\ArticleHomepageSlot;
use App\Enum\ContentStatus;
use App\Service\SiteBackupManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SiteBackupManagerTest extends DatabaseWebTestCase
{
    private string $tempProjectDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempProjectDir = sys_get_temp_dir().'/fceb-backup-test-'.bin2hex(random_bytes(6));
        mkdir($this->tempProjectDir, 0777, true);
        mkdir($this->tempProjectDir.'/var/backups', 0777, true);
        mkdir($this->tempProjectDir.'/public/uploads/articles', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempProjectDir);
        parent::tearDown();
    }

    public function testBackupAndRestoreRoundTripPreservesDatabaseAndUploads(): void
    {
        $this->persist(
            (new ClubSettings())
                ->setClubName('Club de test')
                ->setPublicEmail('club@example.com')
                ->setPhone('01 02 03 04 05')
                ->setAddress('1 rue du Test')
                ->setMapUrl('https://example.com/map')
        );

        $originalArticle = $this->persist(
            (new Article())
                ->setTitle('Article sauvegarde')
                ->setSlug('article-sauvegarde')
                ->setExcerpt('Extrait sauvegarde')
                ->setContent('<p>Contenu sauvegarde</p>')
                ->setPublishedAt(new \DateTimeImmutable('2026-03-18 12:00:00'))
                ->setHomepageSlot(ArticleHomepageSlot::None)
                ->setStatus(ContentStatus::Published)
        );

        file_put_contents(
            $this->tempProjectDir.'/public/uploads/articles/cover.txt',
            'backup-file-content'
        );

        $manager = new SiteBackupManager($this->entityManager, $this->tempProjectDir);
        $backupPath = $manager->createBackupFile();

        self::assertFileExists($backupPath);
        self::assertStringContainsString('article-sauvegarde', (string) file_get_contents($backupPath));

        $this->entityManager->remove($originalArticle);
        $this->entityManager->flush();

        $replacementArticle = (new Article())
            ->setTitle('Article modifie')
            ->setSlug('article-modifie')
            ->setExcerpt('Extrait modifie')
            ->setContent('<p>Contenu modifie</p>')
            ->setPublishedAt(new \DateTimeImmutable('2026-03-19 12:00:00'))
            ->setHomepageSlot(ArticleHomepageSlot::None)
            ->setStatus(ContentStatus::Draft);
        $this->persist($replacementArticle);

        unlink($this->tempProjectDir.'/public/uploads/articles/cover.txt');
        file_put_contents(
            $this->tempProjectDir.'/public/uploads/articles/other.txt',
            'other-content'
        );

        $uploadedBackup = new UploadedFile(
            $backupPath,
            basename($backupPath),
            'application/json',
            test: true
        );

        $result = $manager->restoreFromUploadedBackup($uploadedBackup);
        $this->entityManager->clear();

        self::assertSame(14, $result['tables']);
        self::assertSame(2, $result['rows']);
        self::assertSame(1, $result['files']);

        /** @var Article|null $restoredArticle */
        $restoredArticle = $this->entityManager->getRepository(Article::class)->findOneBy(['slug' => 'article-sauvegarde']);
        self::assertNotNull($restoredArticle);
        self::assertSame('Article sauvegarde', $restoredArticle->getTitle());
        self::assertNull($this->entityManager->getRepository(Article::class)->findOneBy(['slug' => 'article-modifie']));

        self::assertFileExists($this->tempProjectDir.'/public/uploads/articles/cover.txt');
        self::assertSame('backup-file-content', file_get_contents($this->tempProjectDir.'/public/uploads/articles/cover.txt'));
        self::assertFileDoesNotExist($this->tempProjectDir.'/public/uploads/articles/other.txt');
    }

    private function removeDirectory(string $directory): void
    {
        if ('' === $directory || !is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($directory);
    }
}
