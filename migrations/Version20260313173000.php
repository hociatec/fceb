<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add content status and SEO fields to articles, pages and players';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE article ADD status VARCHAR(255) NOT NULL DEFAULT 'published'");
        $this->addSql('ALTER TABLE article ADD meta_title VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD meta_description VARCHAR(320) DEFAULT NULL');
        $this->addSql("UPDATE article SET status = CASE WHEN is_published = 1 THEN 'published' ELSE 'draft' END");

        $this->addSql("ALTER TABLE page ADD status VARCHAR(255) NOT NULL DEFAULT 'published'");
        $this->addSql('ALTER TABLE page ADD meta_title VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD meta_description VARCHAR(320) DEFAULT NULL');
        $this->addSql("UPDATE page SET status = CASE WHEN is_published = 1 THEN 'published' ELSE 'draft' END");

        $this->addSql("ALTER TABLE player ADD status VARCHAR(255) NOT NULL DEFAULT 'published'");
        $this->addSql('ALTER TABLE player ADD meta_title VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD meta_description VARCHAR(320) DEFAULT NULL');
        $this->addSql("UPDATE player SET status = CASE WHEN is_published = 1 THEN 'published' ELSE 'draft' END");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cette migration ajoute des colonnes SQLite sans rollback direct.');
    }
}
