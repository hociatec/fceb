<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320003527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE home_section RENAME INDEX uniq_348298d4af8f7d94 TO UNIQ_9853B1B7514C78FB');
        $this->addSql('ALTER TABLE navigation_item RENAME INDEX idx_c5d58fa1c4663e4 TO IDX_289BF06CC4663E4');
        $this->addSql('ALTER TABLE page CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE page RENAME INDEX uniq_140ab6202e7d90e TO UNIQ_140AB62047280172');
        $this->addSql('ALTER TABLE player CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE player RENAME INDEX uniq_98197a65a64c9b3 TO UNIQ_98197A65989D9B62');
        $this->addSql('DROP INDEX uniq_ranking_entry_season_team ON ranking_entry');
        $this->addSql('ALTER TABLE team_identity RENAME INDEX uniq_833242d22e9705d0 TO UNIQ_D2D735BA8FC28A7D');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e32a5a5a TO UNIQ_8D93D649452C9EC5');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE status status VARCHAR(255) DEFAULT \'published\' NOT NULL');
        $this->addSql('ALTER TABLE home_section RENAME INDEX uniq_9853b1b7514c78fb TO UNIQ_348298D4AF8F7D94');
        $this->addSql('ALTER TABLE navigation_item RENAME INDEX idx_289bf06cc4663e4 TO IDX_C5D58FA1C4663E4');
        $this->addSql('ALTER TABLE page CHANGE status status VARCHAR(255) DEFAULT \'published\' NOT NULL');
        $this->addSql('ALTER TABLE page RENAME INDEX uniq_140ab62047280172 TO UNIQ_140AB6202E7D90E');
        $this->addSql('ALTER TABLE player CHANGE status status VARCHAR(255) DEFAULT \'published\' NOT NULL');
        $this->addSql('ALTER TABLE player RENAME INDEX uniq_98197a65989d9b62 TO UNIQ_98197A65A64C9B3');
        $this->addSql('CREATE UNIQUE INDEX uniq_ranking_entry_season_team ON ranking_entry (season_id, team_name)');
        $this->addSql('ALTER TABLE team_identity RENAME INDEX uniq_d2d735ba8fc28a7d TO UNIQ_833242D22E9705D0');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE `user` RENAME INDEX uniq_8d93d649452c9ec5 TO UNIQ_8D93D649E32A5A5A');
    }
}
