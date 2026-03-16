<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316191000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renseigne la liaison explicite manquante entre le match Clermont Jokers du 29/11/2025 et son article';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game m
            INNER JOIN article a
                ON DATE(a.published_at) = DATE(m.match_date)
               AND LOWER(a.title) LIKE '%clermont jokers%'
            SET m.linked_article_id = a.id
            WHERE m.status = 'completed'
              AND m.linked_article_id IS NULL
              AND m.opponent = 'Clermont Jokers'
              AND m.match_date = '2025-11-29 14:15:00'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET linked_article_id = NULL
            WHERE status = 'completed'
              AND opponent = 'Clermont Jokers'
              AND match_date = '2025-11-29 14:15:00'
        SQL);
    }
}
