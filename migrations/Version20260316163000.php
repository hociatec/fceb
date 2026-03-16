<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une liaison explicite entre un match et son article lie';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE match_game ADD linked_article_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_F6CB4E6C57A69F65 FOREIGN KEY (linked_article_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F6CB4E6C57A69F65 ON match_game (linked_article_id)');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE match_game DROP FOREIGN KEY FK_F6CB4E6C57A69F65');
        $this->addSql('DROP INDEX IDX_F6CB4E6C57A69F65 ON match_game');
        $this->addSql('ALTER TABLE match_game DROP linked_article_id');
    }
}
