<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create player photo gallery table';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE player_photo (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, photo VARCHAR(255) NOT NULL, caption VARCHAR(180) DEFAULT NULL, display_order INT NOT NULL DEFAULT 0, INDEX IDX_7B1EFBB899E6F5DF (player_id), PRIMARY KEY(id), CONSTRAINT FK_7B1EFBB899E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('DROP TABLE player_photo');
    }
}
