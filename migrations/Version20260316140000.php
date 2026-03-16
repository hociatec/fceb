<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Realigne le match Clermont Jokers du 28 mars 2026 sur le calendrier Tournify';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET side = 'away'
            WHERE opponent = 'Clermont Jokers'
              AND match_date = '2026-03-28 11:00:00'
              AND location = 'La Bassée'
              AND status = 'scheduled'
              AND competition = 'Championnat'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET side = 'home'
            WHERE opponent = 'Clermont Jokers'
              AND match_date = '2026-03-28 11:00:00'
              AND location = 'La Bassée'
              AND status = 'scheduled'
              AND competition = 'Championnat'
        SQL);
    }
}
