<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313164000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create player table';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, photo VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, age INT DEFAULT NULL, display_order INT NOT NULL, is_published TINYINT(1) NOT NULL, PRIMARY KEY(id), UNIQUE INDEX UNIQ_98197A65A64C9B3 (slug)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('DROP TABLE player');
    }
}
