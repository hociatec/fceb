<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne les 12 matchs de La Bassee sur les horaires, lieux et ordre Tournify';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot', location = 'LENS', side = 'away', competition = 'Championnat', status = 'completed', our_score = 0, opponent_score = 1 WHERE match_date = '2025-11-08 09:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot', location = 'LENS', side = 'home', competition = 'Championnat', status = 'completed', our_score = 0, opponent_score = 1 WHERE match_date = '2025-11-08 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot', location = 'NANTES', side = 'home', competition = 'Championnat', status = 'completed', our_score = 1, opponent_score = 0 WHERE match_date = '2025-11-29 11:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'Clermont Jokers', location = 'NANTES', match_date = '2025-11-29 14:15:00', side = 'away', competition = 'Championnat', status = 'completed', our_score = 1, opponent_score = 2 WHERE match_date = '2025-11-29 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot', location = 'LENS', side = 'home', competition = 'Championnat', status = 'completed', our_score = 1, opponent_score = 0 WHERE match_date = '2026-03-07 11:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot', location = 'LENS', side = 'away', competition = 'Championnat', status = 'completed', our_score = 1, opponent_score = 0 WHERE match_date = '2026-03-07 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'Clermont Jokers', location = 'LA BASSEE', side = 'away', competition = 'Championnat', status = 'scheduled', our_score = NULL, opponent_score = NULL WHERE match_date = '2026-03-28 11:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot', location = 'LA BASSEE', side = 'away', competition = 'Championnat', status = 'scheduled', our_score = NULL, opponent_score = NULL WHERE match_date = '2026-03-28 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot', location = 'SAINT-MANDE/PRECY SUR OISE', side = 'away', competition = 'Championnat', status = 'scheduled', our_score = NULL, opponent_score = NULL WHERE match_date = '2026-04-25 09:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot', location = 'SAINT-MANDE/PRECY SUR OISE', side = 'away', competition = 'Championnat', status = 'scheduled', our_score = NULL, opponent_score = NULL WHERE match_date = '2026-04-25 11:45:00'");
        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot', location = 'LENS | PHASE FINALE - TERRAIN 2', side = 'home', competition = 'Championnat', status = 'scheduled', our_score = NULL, opponent_score = NULL WHERE match_date = '2026-06-06 09:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'Clermont Jokers', location = 'LENS | PHASE FINALE - TERRAIN 2', side = 'home', competition = 'Championnat', status = 'scheduled', our_score = NULL, opponent_score = NULL WHERE match_date = '2026-06-06 13:00:00'");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot', location = 'Lens - Stade Carpentier', side = 'away' WHERE match_date = '2025-11-08 09:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot', location = 'Lens - Stade Carpentier', side = 'home' WHERE match_date = '2025-11-08 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot', location = 'Rezé - Stade Léo-Lagrange', side = 'away' WHERE match_date = '2025-11-29 11:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'Clermont Jokers', location = 'Rezé - Stade Léo-Lagrange', match_date = '2025-11-29 16:15:00', side = 'away' WHERE match_date = '2025-11-29 14:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot', location = 'Lens - Stade Carpentier', side = 'home' WHERE match_date = '2026-03-07 11:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot', location = 'Lens - Stade Carpentier', side = 'away' WHERE match_date = '2026-03-07 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'Clermont Jokers', location = 'La Bassée', side = 'away' WHERE match_date = '2026-03-28 11:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot', location = 'La Bassée', side = 'away' WHERE match_date = '2026-03-28 16:15:00'");
        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot', location = 'Saint-Mandé / Précy-sur-Oise', side = 'away' WHERE match_date = '2026-04-25 09:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot', location = 'Saint-Mandé / Précy-sur-Oise', side = 'away' WHERE match_date = '2026-04-25 11:45:00'");
        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot', location = 'Lens - Phase finale - Terrain 2', side = 'home' WHERE match_date = '2026-06-06 09:00:00'");
        $this->addSql("UPDATE match_game SET opponent = 'Clermont Jokers', location = 'Lens - Phase finale - Terrain 2', side = 'home' WHERE match_date = '2026-06-06 13:00:00'");
    }
}
