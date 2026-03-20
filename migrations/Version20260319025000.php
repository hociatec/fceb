<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319025000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add advanced visibility and accent settings to home sections';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("ALTER TABLE home_section
            ADD accent_tone VARCHAR(16) NOT NULL DEFAULT 'green',
            ADD show_tag TINYINT(1) NOT NULL DEFAULT 1,
            ADD show_meta TINYINT(1) NOT NULL DEFAULT 1,
            ADD show_excerpt TINYINT(1) NOT NULL DEFAULT 1,
            ADD show_score TINYINT(1) NOT NULL DEFAULT 1");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE home_section DROP COLUMN accent_tone, DROP COLUMN show_tag, DROP COLUMN show_meta, DROP COLUMN show_excerpt, DROP COLUMN show_score');
    }
}
