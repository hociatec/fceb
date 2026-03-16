<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relie explicitement les matchs termines a leurs articles quand date de match et date de publication correspondent';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game m
            INNER JOIN article a ON a.published_at = m.match_date
            SET m.linked_article_id = a.id
            WHERE m.status = 'completed'
              AND m.linked_article_id IS NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game m
            INNER JOIN article a ON a.id = m.linked_article_id
            SET m.linked_article_id = NULL
            WHERE m.status = 'completed'
              AND a.published_at = m.match_date
        SQL);
    }
}
