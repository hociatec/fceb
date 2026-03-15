<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260315233223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__article AS SELECT id, title, slug, excerpt, content, published_at, is_published, placement, season_id, author_id, cover_image, status, meta_title, meta_description FROM article');
        $this->addSql('DROP TABLE article');
        $this->addSql('CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, slug VARCHAR(190) NOT NULL, excerpt VARCHAR(400) DEFAULT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL, placement VARCHAR(255) NOT NULL, season_id INTEGER DEFAULT NULL, author_id INTEGER DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, meta_title VARCHAR(180) DEFAULT NULL, meta_description VARCHAR(320) DEFAULT NULL, CONSTRAINT FK_23A0E664EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON UPDATE NO ACTION ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO article (id, title, slug, excerpt, content, published_at, is_published, placement, season_id, author_id, cover_image, status, meta_title, meta_description) SELECT id, title, slug, excerpt, content, published_at, is_published, placement, season_id, author_id, cover_image, status, meta_title, meta_description FROM __temp__article');
        $this->addSql('DROP TABLE __temp__article');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
        $this->addSql('CREATE INDEX IDX_23A0E664EC001D1 ON article (season_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23A0E66989D9B62 ON article (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__home_section AS SELECT id, section_key, title, subtitle, display_order, is_enabled FROM home_section');
        $this->addSql('DROP TABLE home_section');
        $this->addSql('CREATE TABLE home_section (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, section_key VARCHAR(80) NOT NULL, title VARCHAR(120) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, display_order INTEGER NOT NULL, is_enabled BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO home_section (id, section_key, title, subtitle, display_order, is_enabled) SELECT id, section_key, title, subtitle, display_order, is_enabled FROM __temp__home_section');
        $this->addSql('DROP TABLE __temp__home_section');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9853B1B7514C78FB ON home_section (section_key)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, content CLOB NOT NULL, placement VARCHAR(255) NOT NULL, is_published BOOLEAN NOT NULL, menu_order INTEGER NOT NULL, hero_image VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, meta_title VARCHAR(180) DEFAULT NULL, meta_description VARCHAR(320) DEFAULT NULL)');
        $this->addSql('INSERT INTO page (id, title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description) SELECT id, title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, name, slug, photo, description, age, display_order, is_published, status, meta_title, meta_description, birth_date, nationality, preferred_position, preferred_foot FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, photo VARCHAR(255) DEFAULT NULL, description CLOB NOT NULL, age INTEGER DEFAULT NULL, display_order INTEGER NOT NULL, is_published BOOLEAN NOT NULL, status VARCHAR(255) NOT NULL, meta_title VARCHAR(180) DEFAULT NULL, meta_description VARCHAR(320) DEFAULT NULL, birth_date DATE DEFAULT NULL, nationality VARCHAR(120) DEFAULT NULL, preferred_position VARCHAR(120) DEFAULT NULL, preferred_foot VARCHAR(40) DEFAULT NULL)');
        $this->addSql('INSERT INTO player (id, name, slug, photo, description, age, display_order, is_published, status, meta_title, meta_description, birth_date, nationality, preferred_position, preferred_foot) SELECT id, name, slug, photo, description, age, display_order, is_published, status, meta_title, meta_description, birth_date, nationality, preferred_position, preferred_foot FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65989D9B62 ON player (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__ranking_entry AS SELECT id, team_name, points, wins, losses, goal_difference, display_order, season_id FROM ranking_entry');
        $this->addSql('DROP TABLE ranking_entry');
        $this->addSql('CREATE TABLE ranking_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_name VARCHAR(120) NOT NULL, points INTEGER NOT NULL, wins INTEGER NOT NULL, losses INTEGER NOT NULL, goal_difference INTEGER NOT NULL, display_order INTEGER NOT NULL, season_id INTEGER NOT NULL, CONSTRAINT FK_26E5CAC14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ranking_entry (id, team_name, points, wins, losses, goal_difference, display_order, season_id) SELECT id, team_name, points, wins, losses, goal_difference, display_order, season_id FROM __temp__ranking_entry');
        $this->addSql('DROP TABLE __temp__ranking_entry');
        $this->addSql('CREATE INDEX IDX_26E5CAC14EC001D1 ON ranking_entry (season_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__team_identity AS SELECT id, team_name, logo_path, aliases FROM team_identity');
        $this->addSql('DROP TABLE team_identity');
        $this->addSql('CREATE TABLE team_identity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_name VARCHAR(160) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, aliases CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO team_identity (id, team_name, logo_path, aliases) SELECT id, team_name, logo_path, aliases FROM __temp__team_identity');
        $this->addSql('DROP TABLE __temp__team_identity');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D2D735BA8FC28A7D ON team_identity (team_name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, full_name, reset_password_token, reset_password_expires_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(120) NOT NULL, reset_password_token VARCHAR(120) DEFAULT NULL, reset_password_expires_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, full_name, reset_password_token, reset_password_expires_at) SELECT id, email, roles, password, full_name, reset_password_token, reset_password_expires_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649452C9EC5 ON user (reset_password_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__article AS SELECT id, title, slug, excerpt, meta_title, meta_description, cover_image, content, published_at, is_published, status, placement, season_id, author_id FROM article');
        $this->addSql('DROP TABLE article');
        $this->addSql('CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(180) NOT NULL, slug VARCHAR(190) NOT NULL, excerpt VARCHAR(400) DEFAULT NULL, meta_title VARCHAR(180) DEFAULT NULL, meta_description VARCHAR(320) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, is_published BOOLEAN NOT NULL, status VARCHAR(255) DEFAULT \'published\' NOT NULL, placement VARCHAR(255) NOT NULL, season_id INTEGER DEFAULT NULL, author_id INTEGER DEFAULT NULL, CONSTRAINT FK_23A0E664EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO article (id, title, slug, excerpt, meta_title, meta_description, cover_image, content, published_at, is_published, status, placement, season_id, author_id) SELECT id, title, slug, excerpt, meta_title, meta_description, cover_image, content, published_at, is_published, status, placement, season_id, author_id FROM __temp__article');
        $this->addSql('DROP TABLE __temp__article');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23A0E66989D9B62 ON article (slug)');
        $this->addSql('CREATE INDEX IDX_23A0E664EC001D1 ON article (season_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__home_section AS SELECT id, section_key, title, subtitle, display_order, is_enabled FROM home_section');
        $this->addSql('DROP TABLE home_section');
        $this->addSql('CREATE TABLE home_section (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, section_key VARCHAR(80) NOT NULL, title VARCHAR(120) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, display_order INTEGER NOT NULL, is_enabled BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO home_section (id, section_key, title, subtitle, display_order, is_enabled) SELECT id, section_key, title, subtitle, display_order, is_enabled FROM __temp__home_section');
        $this->addSql('DROP TABLE __temp__home_section');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_348298D4AF8F7D94 ON home_section (section_key)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, title, slug, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, meta_title VARCHAR(180) DEFAULT NULL, meta_description VARCHAR(320) DEFAULT NULL, hero_image VARCHAR(255) DEFAULT NULL, content CLOB NOT NULL, placement VARCHAR(255) NOT NULL, is_published BOOLEAN NOT NULL, status VARCHAR(255) DEFAULT \'published\' NOT NULL, menu_order INTEGER NOT NULL)');
        $this->addSql('INSERT INTO page (id, title, slug, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order) SELECT id, title, slug, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__player AS SELECT id, name, slug, photo, meta_title, meta_description, description, age, birth_date, nationality, preferred_position, preferred_foot, display_order, is_published, status FROM player');
        $this->addSql('DROP TABLE player');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, photo VARCHAR(255) DEFAULT NULL, meta_title VARCHAR(180) DEFAULT NULL, meta_description VARCHAR(320) DEFAULT NULL, description CLOB NOT NULL, age INTEGER DEFAULT NULL, birth_date DATE DEFAULT NULL, nationality VARCHAR(120) DEFAULT NULL, preferred_position VARCHAR(120) DEFAULT NULL, preferred_foot VARCHAR(40) DEFAULT NULL, display_order INTEGER NOT NULL, is_published BOOLEAN NOT NULL, status VARCHAR(255) DEFAULT \'published\' NOT NULL)');
        $this->addSql('INSERT INTO player (id, name, slug, photo, meta_title, meta_description, description, age, birth_date, nationality, preferred_position, preferred_foot, display_order, is_published, status) SELECT id, name, slug, photo, meta_title, meta_description, description, age, birth_date, nationality, preferred_position, preferred_foot, display_order, is_published, status FROM __temp__player');
        $this->addSql('DROP TABLE __temp__player');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65A64C9B3 ON player (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__ranking_entry AS SELECT id, team_name, points, wins, losses, goal_difference, display_order, season_id FROM ranking_entry');
        $this->addSql('DROP TABLE ranking_entry');
        $this->addSql('CREATE TABLE ranking_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_name VARCHAR(120) NOT NULL, points INTEGER NOT NULL, wins INTEGER NOT NULL, losses INTEGER NOT NULL, goal_difference INTEGER NOT NULL, display_order INTEGER NOT NULL, season_id INTEGER NOT NULL, CONSTRAINT FK_26E5CAC14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ranking_entry (id, team_name, points, wins, losses, goal_difference, display_order, season_id) SELECT id, team_name, points, wins, losses, goal_difference, display_order, season_id FROM __temp__ranking_entry');
        $this->addSql('DROP TABLE __temp__ranking_entry');
        $this->addSql('CREATE INDEX IDX_26E5CAC14EC001D1 ON ranking_entry (season_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_ranking_entry_season_team ON ranking_entry (season_id, team_name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__team_identity AS SELECT id, team_name, logo_path, aliases FROM team_identity');
        $this->addSql('DROP TABLE team_identity');
        $this->addSql('CREATE TABLE team_identity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_name VARCHAR(160) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, aliases CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO team_identity (id, team_name, logo_path, aliases) SELECT id, team_name, logo_path, aliases FROM __temp__team_identity');
        $this->addSql('DROP TABLE __temp__team_identity');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_833242D22E9705D0 ON team_identity (team_name)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, full_name, reset_password_token, reset_password_expires_at FROM "user"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(120) NOT NULL, reset_password_token VARCHAR(120) DEFAULT NULL, reset_password_expires_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO "user" (id, email, roles, password, full_name, reset_password_token, reset_password_expires_at) SELECT id, email, roles, password, full_name, reset_password_token, reset_password_expires_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E32A5A5A ON "user" (reset_password_token)');
    }
}
