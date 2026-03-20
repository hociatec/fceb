<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319020000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Align home section admin content with current homepage configuration";
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("UPDATE home_section
            SET subtitle = NULL,
                content = NULL,
                secondary_content = NULL,
                display_order = 40
            WHERE section_key = 'featured_article'");

        $this->addSql("UPDATE home_section
            SET display_order = 30
            WHERE section_key = 'next_match'");

        $this->addSql("UPDATE home_section
            SET display_order = 50,
                is_enabled = 0
            WHERE section_key = 'upcoming_matches'");

        $this->addSql("UPDATE home_section
            SET content = NULL,
                display_order = 20
            WHERE section_key = 'last_result'");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("UPDATE home_section
            SET subtitle = 'Le contenu principal à retenir en ce moment, avec un angle éditorial plus net.',
                content = 'Les actualités doivent montrer ce qui se passe réellement au club : matchs, événements, coulisses et vie associative.',
                secondary_content = 'Autres actualités',
                display_order = 20
            WHERE section_key = 'featured_article'");

        $this->addSql("UPDATE home_section
            SET display_order = 30
            WHERE section_key = 'next_match'");

        $this->addSql("UPDATE home_section
            SET display_order = 40,
                is_enabled = 1
            WHERE section_key = 'upcoming_matches'");

        $this->addSql("UPDATE home_section
            SET content = NULL,
                display_order = 50
            WHERE section_key = 'last_result'");
    }
}
