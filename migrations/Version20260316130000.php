<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime les doublons exacts de matchs importes sur une meme saison/date/competition';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            DELETE duplicate
            FROM match_game duplicate
            INNER JOIN match_game canonical
                ON duplicate.id > canonical.id
               AND duplicate.season_id = canonical.season_id
               AND duplicate.match_date = canonical.match_date
               AND duplicate.competition = canonical.competition
               AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(duplicate.opponent, ' ', ''), '''', ''), '-', ''), 'é', 'e')) =
                   LOWER(REPLACE(REPLACE(REPLACE(REPLACE(canonical.opponent, ' ', ''), '''', ''), '-', ''), 'é', 'e'))
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Impossible de restaurer automatiquement les doublons de matchs supprimes.');
    }
}
