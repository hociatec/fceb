<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319024000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add visual customization fields to home sections';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("ALTER TABLE home_section
            ADD text_alignment VARCHAR(16) NOT NULL DEFAULT 'left',
            ADD layout_width VARCHAR(16) NOT NULL DEFAULT 'wide',
            ADD show_image TINYINT(1) NOT NULL DEFAULT 1,
            ADD image_position VARCHAR(16) NOT NULL DEFAULT 'start',
            ADD appearance VARCHAR(16) NOT NULL DEFAULT 'default'");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE home_section DROP COLUMN text_alignment, DROP COLUMN layout_width, DROP COLUMN show_image, DROP COLUMN image_position, DROP COLUMN appearance');
    }
}
