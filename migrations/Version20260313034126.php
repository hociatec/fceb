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
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE ranking_entry (id INT AUTO_INCREMENT NOT NULL, season_id INT NOT NULL, team_name VARCHAR(120) NOT NULL, points INT NOT NULL, wins INT NOT NULL, losses INT NOT NULL, goal_difference INT NOT NULL, display_order INT NOT NULL, INDEX IDX_26E5CAC14EC001D1 (season_id), PRIMARY KEY(id), UNIQUE INDEX uniq_ranking_entry_season_team (season_id, team_name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ranking_entry ADD CONSTRAINT FK_26E5CAC14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('DROP TABLE ranking_entry');
    }
}
