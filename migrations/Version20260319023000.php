<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319023000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add advanced customization fields to home sections';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("ALTER TABLE home_section
            ADD title_tag VARCHAR(8) NOT NULL DEFAULT 'h2',
            ADD primary_link_label VARCHAR(120) DEFAULT NULL,
            ADD primary_link_url VARCHAR(255) DEFAULT NULL,
            ADD primary_link_style VARCHAR(16) NOT NULL DEFAULT 'primary',
            ADD secondary_link_label VARCHAR(120) DEFAULT NULL,
            ADD secondary_link_url VARCHAR(255) DEFAULT NULL,
            ADD secondary_link_style VARCHAR(16) NOT NULL DEFAULT 'secondary'");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE home_section DROP COLUMN title_tag, DROP COLUMN primary_link_label, DROP COLUMN primary_link_url, DROP COLUMN primary_link_style, DROP COLUMN secondary_link_label, DROP COLUMN secondary_link_url, DROP COLUMN secondary_link_style');
    }
}
