<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute un choix explicite d'affichage des articles sur l'accueil";
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("ALTER TABLE article ADD homepage_slot VARCHAR(255) NOT NULL DEFAULT 'none'");
        $this->addSql("UPDATE article SET homepage_slot = 'none'");
        $this->addSql("UPDATE article SET homepage_slot = 'featured' WHERE id = (SELECT id FROM (SELECT id FROM article WHERE status = 'published' AND placement IN ('homepage', 'current_season') ORDER BY published_at DESC, id DESC LIMIT 1) featured_article)");
        $this->addSql("UPDATE article SET homepage_slot = 'secondary' WHERE id IN (SELECT id FROM (SELECT id FROM article WHERE status = 'published' AND homepage_slot = 'none' ORDER BY published_at DESC, id DESC LIMIT 3) secondary_articles)");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE article DROP homepage_slot');
    }
}
