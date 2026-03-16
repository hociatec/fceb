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
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE season (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, is_current TINYINT(1) NOT NULL, PRIMARY KEY(id), UNIQUE INDEX UNIQ_F0E45BA9989D9B62 (slug)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(120) NOT NULL, PRIMARY KEY(id), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, content LONGTEXT NOT NULL, placement VARCHAR(255) NOT NULL, is_published TINYINT(1) NOT NULL, menu_order INT NOT NULL, PRIMARY KEY(id), UNIQUE INDEX UNIQ_140AB620989D9B62 (slug)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, website_url VARCHAR(255) DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, is_visible TINYINT(1) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_link (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(80) NOT NULL, url VARCHAR(255) NOT NULL, icon VARCHAR(80) DEFAULT NULL, is_visible TINYINT(1) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE match_game (id INT AUTO_INCREMENT NOT NULL, season_id INT NOT NULL, opponent VARCHAR(160) NOT NULL, competition VARCHAR(120) DEFAULT NULL, location VARCHAR(160) DEFAULT NULL, match_date DATETIME NOT NULL, side VARCHAR(20) NOT NULL, our_score INT DEFAULT NULL, opponent_score INT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_424480FE4EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE match_game ADD CONSTRAINT FK_424480FE4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, season_id INT DEFAULT NULL, author_id INT DEFAULT NULL, title VARCHAR(180) NOT NULL, slug VARCHAR(190) NOT NULL, excerpt VARCHAR(400) DEFAULT NULL, content LONGTEXT NOT NULL, published_at DATETIME NOT NULL, is_published TINYINT(1) NOT NULL, placement VARCHAR(255) NOT NULL, INDEX IDX_23A0E664EC001D1 (season_id), INDEX IDX_23A0E66F675F31B (author_id), PRIMARY KEY(id), UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E664EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE match_game');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE social_link');
        $this->addSql('DROP TABLE `user`');
    }
}
