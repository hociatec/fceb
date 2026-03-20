<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319021000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin-managed footer editorial fields to club settings';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings ADD footer_badge VARCHAR(160) DEFAULT NULL, ADD footer_headline VARCHAR(255) DEFAULT NULL, ADD footer_text LONGTEXT DEFAULT NULL');
        $this->addSql('UPDATE club_settings SET footer_badge = NULL, footer_headline = NULL, footer_text = NULL');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings DROP COLUMN footer_badge, DROP COLUMN footer_headline, DROP COLUMN footer_text');
    }
}
