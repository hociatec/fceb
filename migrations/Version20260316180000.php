<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le pilotage manuel des actualites de l accueil, verrouille la liaison article match et journalise les synchros Tournify';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE tournify_sync_run (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, live_link VARCHAR(140) NOT NULL, division_id VARCHAR(80) NOT NULL, team_name VARCHAR(190) NOT NULL, competition VARCHAR(120) DEFAULT NULL, status VARCHAR(255) NOT NULL, is_dry_run TINYINT(1) NOT NULL, source_matches INT NOT NULL, created_count INT NOT NULL, updated_count INT NOT NULL, removed_count INT NOT NULL, message LONGTEXT DEFAULT NULL, details JSON DEFAULT NULL, season_id INT DEFAULT NULL, INDEX IDX_82001F904EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournify_sync_run ADD CONSTRAINT FK_82001F904EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE home_section ADD featured_article_id INT DEFAULT NULL, ADD secondary_article_one_id INT DEFAULT NULL, ADD secondary_article_two_id INT DEFAULT NULL, ADD secondary_article_three_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_section ADD CONSTRAINT FK_9853B1B7AC7778D9 FOREIGN KEY (featured_article_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE home_section ADD CONSTRAINT FK_9853B1B7C80BCB FOREIGN KEY (secondary_article_one_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE home_section ADD CONSTRAINT FK_9853B1B76B94EC04 FOREIGN KEY (secondary_article_two_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE home_section ADD CONSTRAINT FK_9853B1B7D54E8F1F FOREIGN KEY (secondary_article_three_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9853B1B7AC7778D9 ON home_section (featured_article_id)');
        $this->addSql('CREATE INDEX IDX_9853B1B7C80BCB ON home_section (secondary_article_one_id)');
        $this->addSql('CREATE INDEX IDX_9853B1B76B94EC04 ON home_section (secondary_article_two_id)');
        $this->addSql('CREATE INDEX IDX_9853B1B7D54E8F1F ON home_section (secondary_article_three_id)');

        $this->addSql('ALTER TABLE match_game DROP INDEX IDX_424480FEE5505DDB, ADD UNIQUE INDEX UNIQ_424480FEE5505DDB (linked_article_id)');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE match_game DROP INDEX UNIQ_424480FEE5505DDB, ADD INDEX IDX_424480FEE5505DDB (linked_article_id)');

        $this->addSql('ALTER TABLE home_section DROP FOREIGN KEY FK_9853B1B7AC7778D9');
        $this->addSql('ALTER TABLE home_section DROP FOREIGN KEY FK_9853B1B7C80BCB');
        $this->addSql('ALTER TABLE home_section DROP FOREIGN KEY FK_9853B1B76B94EC04');
        $this->addSql('ALTER TABLE home_section DROP FOREIGN KEY FK_9853B1B7D54E8F1F');
        $this->addSql('DROP INDEX IDX_9853B1B7AC7778D9 ON home_section');
        $this->addSql('DROP INDEX IDX_9853B1B7C80BCB ON home_section');
        $this->addSql('DROP INDEX IDX_9853B1B76B94EC04 ON home_section');
        $this->addSql('DROP INDEX IDX_9853B1B7D54E8F1F ON home_section');
        $this->addSql('ALTER TABLE home_section DROP featured_article_id, DROP secondary_article_one_id, DROP secondary_article_two_id, DROP secondary_article_three_id');

        $this->addSql('ALTER TABLE tournify_sync_run DROP FOREIGN KEY FK_82001F904EC001D1');
        $this->addSql('DROP TABLE tournify_sync_run');
    }
}
