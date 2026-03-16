<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime le match en trop RC Lens Cecifoot du 8 novembre 2025 pour aligner le calendrier sur Tournify';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            DELETE FROM match_game
            WHERE opponent = 'RC Lens Cecifoot'
              AND match_date = '2025-11-08 14:00:00'
              AND location = 'Lens'
              AND side = 'away'
              AND status = 'completed'
              AND our_score = 0
              AND opponent_score = 1
              AND competition = 'Championnat'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            INSERT INTO match_game (opponent, competition, location, match_date, side, our_score, opponent_score, status, season_id)
            SELECT
                'RC Lens Cecifoot',
                'Championnat',
                'Lens',
                '2025-11-08 14:00:00',
                'away',
                0,
                1,
                'completed',
                id
            FROM season
            WHERE slug = 'saison-2025-2026'
              AND NOT EXISTS (
                  SELECT 1
                  FROM match_game
                  WHERE opponent = 'RC Lens Cecifoot'
                    AND match_date = '2025-11-08 14:00:00'
                    AND location = 'Lens'
                    AND side = 'away'
                    AND competition = 'Championnat'
              )
        SQL);
    }
}
