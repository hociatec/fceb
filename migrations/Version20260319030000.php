<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Merge editorial home section fields into content editor and drop obsolete columns';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            UPDATE home_section
            SET content = TRIM(CONCAT(
                COALESCE(content, ''),
                CASE
                    WHEN secondary_content IS NOT NULL AND TRIM(secondary_content) <> '' AND (content IS NULL OR TRIM(content) = '')
                        THEN CONCAT('<p>', secondary_content, '</p>')
                    WHEN secondary_content IS NOT NULL AND TRIM(secondary_content) <> ''
                        THEN CONCAT('\n', '<p>', secondary_content, '</p>')
                    ELSE ''
                END,
                CASE
                    WHEN primary_link_label IS NOT NULL AND TRIM(primary_link_label) <> '' AND primary_link_url IS NOT NULL AND TRIM(primary_link_url) <> ''
                        THEN CONCAT('\n[cta label="', REPLACE(primary_link_label, '"', '&quot;'), '" url="', REPLACE(primary_link_url, '"', '&quot;'), '"]')
                    ELSE ''
                END,
                CASE
                    WHEN secondary_link_label IS NOT NULL AND TRIM(secondary_link_label) <> '' AND secondary_link_url IS NOT NULL AND TRIM(secondary_link_url) <> ''
                        THEN CONCAT('\n[cta label="', REPLACE(secondary_link_label, '"', '&quot;'), '" url="', REPLACE(secondary_link_url, '"', '&quot;'), '"]')
                    ELSE ''
                END
            ))
        SQL);

        $this->addSql('ALTER TABLE home_section DROP COLUMN subtitle, DROP COLUMN secondary_content, DROP COLUMN primary_link_label, DROP COLUMN primary_link_url, DROP COLUMN primary_link_style, DROP COLUMN secondary_link_label, DROP COLUMN secondary_link_url, DROP COLUMN secondary_link_style');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("ALTER TABLE home_section
            ADD subtitle VARCHAR(255) DEFAULT NULL,
            ADD secondary_content LONGTEXT DEFAULT NULL,
            ADD primary_link_label VARCHAR(120) DEFAULT NULL,
            ADD primary_link_url VARCHAR(255) DEFAULT NULL,
            ADD primary_link_style VARCHAR(16) NOT NULL DEFAULT 'primary',
            ADD secondary_link_label VARCHAR(120) DEFAULT NULL,
            ADD secondary_link_url VARCHAR(255) DEFAULT NULL,
            ADD secondary_link_style VARCHAR(16) NOT NULL DEFAULT 'secondary'");
    }
}
