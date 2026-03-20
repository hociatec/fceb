<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove deprecated upcoming matches home section';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("DELETE FROM home_section WHERE section_key = 'upcoming_matches'");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql(<<<'SQL'
            INSERT INTO home_section (
                section_key,
                title,
                content,
                image,
                title_tag,
                text_alignment,
                layout_width,
                show_image,
                image_position,
                appearance,
                accent_tone,
                show_tag,
                show_meta,
                show_excerpt,
                show_score,
                upcoming_matches_limit,
                display_order,
                is_enabled
            )
            SELECT
                'upcoming_matches',
                'Matchs à venir',
                NULL,
                NULL,
                'h2',
                'left',
                'default',
                1,
                'start',
                'default',
                'green',
                1,
                1,
                1,
                1,
                4,
                50,
                0
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1 FROM home_section WHERE section_key = 'upcoming_matches'
            )
        SQL);
    }
}
