<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313013208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, slug VARCHAR(190) NOT NULL, excerpt VARCHAR(400) DEFAULT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL, placement VARCHAR(255) NOT NULL, season_id INTEGER DEFAULT NULL, author_id INTEGER DEFAULT NULL, CONSTRAINT FK_23A0E664EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23A0E66989D9B62 ON article (slug)');
        $this->addSql('CREATE INDEX IDX_23A0E664EC001D1 ON article (season_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
        $this->addSql('CREATE TABLE match_game (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, opponent VARCHAR(160) NOT NULL, competition VARCHAR(120) DEFAULT NULL, location VARCHAR(160) DEFAULT NULL, match_date DATETIME NOT NULL, side VARCHAR(20) NOT NULL, our_score INTEGER DEFAULT NULL, opponent_score INTEGER DEFAULT NULL, status VARCHAR(255) NOT NULL, season_id INTEGER NOT NULL, CONSTRAINT FK_424480FE4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_424480FE4EC001D1 ON match_game (season_id)');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, content CLOB NOT NULL, placement VARCHAR(255) NOT NULL, is_published BOOLEAN NOT NULL, menu_order INTEGER NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
        $this->addSql('CREATE TABLE partner (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, website_url VARCHAR(255) DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, is_visible BOOLEAN NOT NULL, display_order INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE season (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, is_current BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F0E45BA9989D9B62 ON season (slug)');
        $this->addSql('CREATE TABLE social_link (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(80) NOT NULL, url VARCHAR(255) NOT NULL, icon VARCHAR(80) DEFAULT NULL, is_visible BOOLEAN NOT NULL, display_order INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(120) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE match_game');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE social_link');
        $this->addSql('DROP TABLE "user"');
    }
}
