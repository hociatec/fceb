<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316124000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Harmonise les libelles de competition des matchs importes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET competition = 'Championnat de France B1 Challenger'
            WHERE TRIM(competition) IN ('Championnat', 'Championnat B1 Challenger', 'Championnat de France B1 Challenger')
        SQL);

        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET competition = 'Coupe de France'
            WHERE LOWER(TRIM(competition)) = 'coupe de france'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE match_game
            SET competition = 'Championnat'
            WHERE competition = 'Championnat de France B1 Challenger'
        SQL);
    }
}
