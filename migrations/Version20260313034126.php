<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313034126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ranking_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_name VARCHAR(120) NOT NULL, points INTEGER NOT NULL, wins INTEGER NOT NULL, losses INTEGER NOT NULL, goal_difference INTEGER NOT NULL, display_order INTEGER NOT NULL, season_id INTEGER NOT NULL, CONSTRAINT FK_26E5CAC14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_26E5CAC14EC001D1 ON ranking_entry (season_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_ranking_entry_season_team ON ranking_entry (season_id, team_name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ranking_entry');
    }
}
