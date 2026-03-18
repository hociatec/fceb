<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SiteBackupManager
{
    /**
     * @var list<string>
     */
    private const EXPORT_TABLES = [
        'user',
        'season',
        'article',
        'page',
        'club_settings',
        'social_link',
        'partner',
        'player',
        'player_photo',
        'team_identity',
        'ranking_entry',
        'match_game',
        'home_section',
        'tournify_sync_run',
    ];

    /**
     * @var list<string>
     */
    private const RESTORE_DELETE_ORDER = [
        'home_section',
        'tournify_sync_run',
        'player_photo',
        'ranking_entry',
        'match_game',
        'article',
        'page',
        'club_settings',
        'social_link',
        'partner',
        'player',
        'team_identity',
        'season',
        'user',
    ];

    /**
     * @var list<string>
     */
    private const RESTORE_INSERT_ORDER = [
        'user',
        'season',
        'article',
        'page',
        'club_settings',
        'social_link',
        'partner',
        'player',
        'player_photo',
        'team_identity',
        'ranking_entry',
        'match_game',
        'home_section',
        'tournify_sync_run',
    ];

    /**
     * @var list<string>
     */
    private const FILE_DIRECTORIES = [
        'public/assets/partners',
        'public/uploads/articles',
        'public/uploads/home-sections',
        'public/uploads/pages',
        'public/uploads/partners',
        'public/uploads/players',
        'public/uploads/team-identities',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function createBackupFile(): string
    {
        $backupDirectory = $this->projectDir.'/var/backups';
        if (!is_dir($backupDirectory) && !@mkdir($backupDirectory, 0777, true) && !is_dir($backupDirectory)) {
            throw new \RuntimeException('Le dossier des sauvegardes n\'a pas pu être créé.');
        }

        $backup = [
            'schema_version' => 1,
            'generated_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'database_name' => (string) $this->entityManager->getConnection()->getDatabase(),
            'tables' => $this->exportTables(),
            'files' => $this->exportFiles(),
        ];

        $path = sprintf(
            '%s/site-backup-%s.json',
            $backupDirectory,
            (new \DateTimeImmutable())->format('Ymd-His')
        );

        $json = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false === $json) {
            throw new \RuntimeException('La sauvegarde n\'a pas pu être sérialisée en JSON.');
        }

        if (false === @file_put_contents($path, $json)) {
            throw new \RuntimeException('Le fichier de sauvegarde n\'a pas pu être écrit.');
        }

        return $path;
    }

    /**
     * @return array{tables: int, rows: int, files: int}
     */
    public function restoreFromUploadedBackup(UploadedFile $uploadedFile): array
    {
        $content = @file_get_contents($uploadedFile->getPathname());
        if (false === $content) {
            throw new \RuntimeException('Impossible de lire le fichier de sauvegarde importé.');
        }

        try {
            /** @var mixed $payload */
            $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Le fichier importé n\'est pas un JSON valide.', 0, $exception);
        }

        if (!is_array($payload) || !isset($payload['tables']) || !is_array($payload['tables'])) {
            throw new \RuntimeException('Le format de sauvegarde est invalide : section "tables" absente.');
        }

        $files = $payload['files'] ?? [];
        if (!is_array($files)) {
            throw new \RuntimeException('Le format de sauvegarde est invalide : section "files" incorrecte.');
        }

        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        $this->restoreTables($connection, $schemaManager, $payload['tables']);
        $restoredFiles = $this->restoreFiles($files);
        $this->entityManager->clear();

        $rowCount = 0;
        foreach (self::RESTORE_INSERT_ORDER as $table) {
            $rows = $payload['tables'][$table]['rows'] ?? [];
            if (is_array($rows)) {
                $rowCount += count($rows);
            }
        }

        return [
            'tables' => count(self::RESTORE_INSERT_ORDER),
            'rows' => $rowCount,
            'files' => $restoredFiles,
        ];
    }

    /**
     * @return array<string, array{columns: list<string>, rows: list<array<string, mixed>>}>
     */
    private function exportTables(): array
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();
        $export = [];

        foreach (self::EXPORT_TABLES as $table) {
            $columns = array_keys($schemaManager->listTableColumns($table));
            $orderBy = in_array('id', $columns, true) ? ' ORDER BY id ASC' : '';
            $rows = $connection->fetchAllAssociative(sprintf('SELECT * FROM `%s`%s', $table, $orderBy));

            $export[$table] = [
                'columns' => $columns,
                'rows' => array_map([$this, 'normalizeRowForJson'], $rows),
            ];
        }

        return $export;
    }

    /**
     * @return list<array{path: string, content_base64: string}>
     */
    private function exportFiles(): array
    {
        $files = [];

        foreach (self::FILE_DIRECTORIES as $relativeDirectory) {
            $absoluteDirectory = $this->projectDir.'/'.$relativeDirectory;
            if (!is_dir($absoluteDirectory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteDirectory, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if (!$item->isFile() || '.gitkeep' === $item->getFilename()) {
                    continue;
                }

                $pathname = $item->getPathname();
                $contents = @file_get_contents($pathname);
                if (false === $contents) {
                    throw new \RuntimeException(sprintf('Impossible de lire le fichier "%s" pour la sauvegarde.', $pathname));
                }

                $relativePath = str_replace('\\', '/', substr($pathname, strlen($this->projectDir) + 1));
                $files[] = [
                    'path' => $relativePath,
                    'content_base64' => base64_encode($contents),
                ];
            }
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function normalizeRowForJson(array $row): array
    {
        foreach ($row as $column => $value) {
            if (is_resource($value)) {
                $row[$column] = stream_get_contents($value);
            }
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $tables
     */
    private function restoreTables(Connection $connection, AbstractSchemaManager $schemaManager, array $tables): void
    {
        foreach (self::RESTORE_INSERT_ORDER as $table) {
            if (!isset($tables[$table]) || !is_array($tables[$table])) {
                throw new \RuntimeException(sprintf('La table "%s" est absente de la sauvegarde.', $table));
            }
        }

        $transactionStarted = false;
        try {
            $connection->beginTransaction();
            $transactionStarted = true;
            $this->setForeignKeyChecks($connection, false);

            foreach (self::RESTORE_DELETE_ORDER as $table) {
                $connection->executeStatement(sprintf('DELETE FROM `%s`', $table));
            }

            foreach (self::RESTORE_INSERT_ORDER as $table) {
                $tablePayload = $tables[$table];
                $rows = $tablePayload['rows'] ?? null;
                if (!is_array($rows)) {
                    throw new \RuntimeException(sprintf('Les lignes de la table "%s" sont invalides.', $table));
                }

                $validColumns = array_keys($schemaManager->listTableColumns($table));
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        throw new \RuntimeException(sprintf('Une ligne de la table "%s" est invalide.', $table));
                    }

                    $filteredRow = array_intersect_key($row, array_flip($validColumns));
                    if ([] === $filteredRow) {
                        continue;
                    }

                    $this->insertRow($connection, $table, $filteredRow);
                }
            }

            $this->setForeignKeyChecks($connection, true);
            $connection->commit();
            $transactionStarted = false;
        } catch (\Throwable $exception) {
            if ($transactionStarted) {
                $this->setForeignKeyChecks($connection, true);
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @param list<array<string, mixed>> $files
     */
    private function restoreFiles(array $files): int
    {
        $this->clearManagedDirectories();
        $restoredFiles = 0;

        foreach ($files as $file) {
            if (!is_array($file) || !isset($file['path'], $file['content_base64'])) {
                throw new \RuntimeException('Le format d\'un fichier sauvegardé est invalide.');
            }

            $relativePath = ltrim(str_replace('\\', '/', (string) $file['path']), '/');
            if (!$this->isPathAllowed($relativePath)) {
                throw new \RuntimeException(sprintf('Le fichier "%s" n\'est pas autorisé dans une restauration.', $relativePath));
            }

            $absolutePath = $this->projectDir.'/'.$relativePath;
            $directory = dirname($absolutePath);
            if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Le dossier "%s" n\'a pas pu être créé.', $directory));
            }

            $decodedContent = base64_decode((string) $file['content_base64'], true);
            if (false === $decodedContent) {
                throw new \RuntimeException(sprintf('Le contenu du fichier "%s" est invalide.', $relativePath));
            }

            if (false === @file_put_contents($absolutePath, $decodedContent)) {
                throw new \RuntimeException(sprintf('Le fichier "%s" n\'a pas pu être restauré.', $relativePath));
            }

            ++$restoredFiles;
        }

        return $restoredFiles;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertRow(Connection $connection, string $table, array $row): void
    {
        $columns = array_keys($row);
        $columnSql = implode(', ', array_map(static fn (string $column): string => sprintf('`%s`', $column), $columns));
        $valueSql = implode(', ', array_map(static fn (string $column): string => ':'.$column, $columns));

        $connection->executeStatement(
            sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table, $columnSql, $valueSql),
            $row
        );
    }

    private function clearManagedDirectories(): void
    {
        foreach (self::FILE_DIRECTORIES as $relativeDirectory) {
            $absoluteDirectory = $this->projectDir.'/'.$relativeDirectory;
            if (!is_dir($absoluteDirectory) && !@mkdir($absoluteDirectory, 0777, true) && !is_dir($absoluteDirectory)) {
                throw new \RuntimeException(sprintf('Le dossier "%s" n\'a pas pu être préparé.', $relativeDirectory));
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteDirectory, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $item) {
                $pathname = $item->getPathname();

                if ($item->isDir()) {
                    @rmdir($pathname);
                    continue;
                }

                if ('.gitkeep' === $item->getFilename()) {
                    continue;
                }

                @unlink($pathname);
            }
        }
    }

    private function isPathAllowed(string $relativePath): bool
    {
        if (str_contains($relativePath, '../') || str_contains($relativePath, '..\\')) {
            return false;
        }

        foreach (self::FILE_DIRECTORIES as $directory) {
            $normalizedDirectory = str_replace('\\', '/', $directory).'/';
            if (str_starts_with($relativePath, $normalizedDirectory)) {
                return true;
            }
        }

        return false;
    }

    private function setForeignKeyChecks(Connection $connection, bool $enabled): void
    {
        if (!$connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $connection->executeStatement(sprintf('SET FOREIGN_KEY_CHECKS=%d', $enabled ? 1 : 0));
    }
}
