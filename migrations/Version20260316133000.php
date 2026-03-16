<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige un match inverse et simplifie les libelles de competition';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET side = 'home'
            WHERE opponent = 'Clermont Jokers'
              AND match_date = '2026-03-28 11:00:00'
              AND location = 'La Bassée'
        SQL);

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET competition = 'Championnat'
            WHERE TRIM(competition) IN ('Championnat', 'Championnat B1 Challenger', 'Championnat de France B1 Challenger')
        SQL);
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET side = 'away'
            WHERE opponent = 'Clermont Jokers'
              AND match_date = '2026-03-28 11:00:00'
              AND location = 'La Bassée'
        SQL);

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET competition = 'Championnat de France B1 Challenger'
            WHERE competition = 'Championnat'
        SQL);
    }
}
