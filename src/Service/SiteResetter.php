<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

final class SiteResetter
{
    /**
     * @var array<string, string>
     */
    private const TABLES_TO_CLEAR = [
        'home_section' => "sections d'accueil",
        'tournify_sync_run' => 'journaux Tournify',
        'player_photo' => 'photos joueurs',
        'match_game' => 'matchs',
        'ranking_entry' => 'classements',
        'article' => 'articles',
        'page' => 'pages',
        'club_settings' => 'parametres du club',
        'social_link' => 'reseaux sociaux',
        'partner' => 'partenaires',
        'player' => 'joueurs',
        'team_identity' => 'identites equipes',
        'season' => 'saisons',
    ];

    /**
     * @var list<string>
     */
    private const UPLOAD_DIRECTORIES = [
        'public/assets/partners',
        'public/uploads/articles',
        'public/uploads/home-sections',
        'public/uploads/pages',
        'public/uploads/players',
        'public/uploads/partners',
        'public/uploads/team-identities',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'cache.app')]
        private readonly CacheInterface $cache,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{
     *     deleted_rows: array<string, int>,
     *     deleted_files: int,
     *     total_rows: int,
     *     warnings: list<string>
     * }
     */
    public function resetContent(): array
    {
        $deletedRows = [];
        $warnings = [];
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $transactionStarted = false;

        try {
            $connection->beginTransaction();
            $transactionStarted = true;

            foreach (self::TABLES_TO_CLEAR as $table => $label) {
                $deletedRows[$label] = $connection->executeStatement(
                    sprintf('DELETE FROM %s', $platform->quoteIdentifier($table))
                );
            }

            $connection->commit();
            $transactionStarted = false;
        } catch (\Throwable $exception) {
            if ($transactionStarted) {
                $connection->rollBack();
            }

            throw $exception;
        }

        $this->entityManager->clear();
        $deletedFiles = $this->clearUploadDirectories($warnings);

        if (!$this->cache->clear()) {
            $warnings[] = "Le cache applicatif n'a pas pu etre vide automatiquement.";
        }

        return [
            'deleted_rows' => $deletedRows,
            'deleted_files' => $deletedFiles,
            'total_rows' => array_sum($deletedRows),
            'warnings' => $warnings,
        ];
    }

    /**
     * @param list<string> $warnings
     */
    private function clearUploadDirectories(array &$warnings): int
    {
        $deletedFiles = 0;

        foreach (self::UPLOAD_DIRECTORIES as $relativeDirectory) {
            $absoluteDirectory = $this->projectDir.'/'.$relativeDirectory;

            if (!is_dir($absoluteDirectory)) {
                if (!@mkdir($absoluteDirectory, 0777, true) && !is_dir($absoluteDirectory)) {
                    $warnings[] = sprintf('Le dossier "%s" n\'a pas pu etre cree.', $relativeDirectory);
                }

                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteDirectory, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $item) {
                $pathname = $item->getPathname();

                if ($item->isDir()) {
                    if (!@rmdir($pathname) && is_dir($pathname)) {
                        $warnings[] = sprintf('Le sous-dossier "%s" n\'a pas pu etre supprime.', $pathname);
                    }

                    continue;
                }

                if ('.gitkeep' === $item->getFilename()) {
                    continue;
                }

                if (@unlink($pathname)) {
                    ++$deletedFiles;
                    continue;
                }

                if (is_file($pathname)) {
                    $warnings[] = sprintf('Le fichier "%s" n\'a pas pu etre supprime.', $pathname);
                }
            }
        }

        return $deletedFiles;
    }
}
