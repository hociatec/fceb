<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316130500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une cle technique unique pour garder club_settings en singleton';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings ADD singleton_key SMALLINT NOT NULL DEFAULT 1');
        $this->addSql('UPDATE club_settings SET singleton_key = 1');
        $this->addSql('ALTER TABLE club_settings ADD CONSTRAINT uniq_club_settings_singleton UNIQUE (singleton_key)');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings DROP INDEX uniq_club_settings_singleton');
        $this->addSql('ALTER TABLE club_settings DROP COLUMN singleton_key');
    }
}
