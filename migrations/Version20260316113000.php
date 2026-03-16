<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move homepage editorial content from club settings to home sections';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE home_section ADD content LONGTEXT DEFAULT NULL, ADD secondary_content LONGTEXT DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL');

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT 'featured_article', title, subtitle, NULL, NULL, NULL, display_order, is_enabled
            FROM home_section
            WHERE section_key = 'latest_article'
              AND NOT EXISTS (
                  SELECT 1 FROM (SELECT id FROM home_section WHERE section_key = 'featured_article') AS existing_featured
              )");
        $this->addSql("DELETE FROM home_section WHERE section_key = 'latest_article'");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT 'last_result', title, subtitle, NULL, NULL, NULL, display_order, is_enabled
            FROM home_section
            WHERE section_key = 'last_match'
              AND NOT EXISTS (
                  SELECT 1 FROM (SELECT id FROM home_section WHERE section_key = 'last_result') AS existing_last_result
              )");
        $this->addSql("DELETE FROM home_section WHERE section_key = 'last_match'");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT
                'intro',
                COALESCE(NULLIF((SELECT home_intro_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Cécifoot La Bassée'),
                COALESCE(NULLIF((SELECT home_intro_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Un club qui assume à la fois l''exigence sportive, l''accessibilité et l''ancrage local.'),
                COALESCE(NULLIF((SELECT home_intro_lead FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'À La Bassée, le cécifoot se construit autour d''un cadre clair : engagement, solidarité, écoute, progression et goût du jeu. Le club défend une pratique accessible, sérieuse et vivante, pensée pour accueillir durablement les joueurs, les proches, les bénévoles et les partenaires.'),
                COALESCE(NULLIF((SELECT home_intro_media_note FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Chaque visuel publié sur le site a vocation à montrer le terrain, les temps forts du collectif et la réalité de la vie du club.'),
                NULL,
                10,
                1
            WHERE NOT EXISTS (
                SELECT 1 FROM home_section WHERE section_key = 'intro'
            )");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT
                'featured_article',
                COALESCE(NULLIF((SELECT home_featured_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Actualité à la une'),
                COALESCE(NULLIF((SELECT home_featured_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Le contenu principal à retenir en ce moment, avec un angle éditorial plus net.'),
                'Les actualités doivent montrer ce qui se passe réellement au club : matchs, événements, coulisses et vie associative.',
                'Autres actualités',
                NULL,
                20,
                1
            WHERE NOT EXISTS (
                SELECT 1 FROM home_section WHERE section_key = 'featured_article'
            )");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT
                'next_match',
                COALESCE(NULLIF((SELECT home_upcoming_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Prochain match'),
                COALESCE(NULLIF((SELECT home_upcoming_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Le prochain rendez-vous sportif du club, avec les repères utiles en un coup d''œil.'),
                NULL,
                NULL,
                NULL,
                30,
                1
            WHERE NOT EXISTS (
                SELECT 1 FROM home_section WHERE section_key = 'next_match'
            )");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT
                'upcoming_matches',
                'Matchs à venir',
                'Les prochaines rencontres déjà programmées après le prochain rendez-vous.',
                NULL,
                NULL,
                NULL,
                40,
                1
            WHERE NOT EXISTS (
                SELECT 1 FROM home_section WHERE section_key = 'upcoming_matches'
            )");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, content, secondary_content, image, display_order, is_enabled)
            SELECT
                'last_result',
                COALESCE(NULLIF((SELECT home_last_result_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Dernier résultat'),
                COALESCE(NULLIF((SELECT home_last_result_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Le dernier score enregistré, avec son compte-rendu quand il existe.'),
                NULL,
                NULL,
                NULL,
                50,
                1
            WHERE NOT EXISTS (
                SELECT 1 FROM home_section WHERE section_key = 'last_result'
            )");

        $this->addSql("UPDATE home_section
            SET
                title = COALESCE(NULLIF((SELECT home_intro_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Cécifoot La Bassée'),
                subtitle = COALESCE(NULLIF((SELECT home_intro_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Un club qui assume à la fois l''exigence sportive, l''accessibilité et l''ancrage local.'),
                content = COALESCE(NULLIF((SELECT home_intro_lead FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'À La Bassée, le cécifoot se construit autour d''un cadre clair : engagement, solidarité, écoute, progression et goût du jeu. Le club défend une pratique accessible, sérieuse et vivante, pensée pour accueillir durablement les joueurs, les proches, les bénévoles et les partenaires.'),
                secondary_content = COALESCE(NULLIF((SELECT home_intro_media_note FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Chaque visuel publié sur le site a vocation à montrer le terrain, les temps forts du collectif et la réalité de la vie du club.'),
                display_order = 10
            WHERE section_key = 'intro'");

        $this->addSql("UPDATE home_section
            SET
                title = COALESCE(NULLIF((SELECT home_featured_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Actualité à la une'),
                subtitle = COALESCE(NULLIF((SELECT home_featured_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Le contenu principal à retenir en ce moment, avec un angle éditorial plus net.'),
                content = 'Les actualités doivent montrer ce qui se passe réellement au club : matchs, événements, coulisses et vie associative.',
                secondary_content = 'Autres actualités',
                display_order = 20
            WHERE section_key = 'featured_article'");

        $this->addSql("UPDATE home_section
            SET
                title = COALESCE(NULLIF((SELECT home_upcoming_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Prochain match'),
                subtitle = COALESCE(NULLIF((SELECT home_upcoming_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Le prochain rendez-vous sportif du club, avec les repères utiles en un coup d''œil.'),
                content = NULL,
                secondary_content = NULL,
                display_order = 30
            WHERE section_key = 'next_match'");

        $this->addSql("UPDATE home_section
            SET
                title = 'Matchs à venir',
                subtitle = 'Les prochaines rencontres déjà programmées après le prochain rendez-vous.',
                content = NULL,
                secondary_content = NULL,
                display_order = 40
            WHERE section_key = 'upcoming_matches'");

        $this->addSql("UPDATE home_section
            SET
                title = COALESCE(NULLIF((SELECT home_last_result_title FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Dernier résultat'),
                subtitle = COALESCE(NULLIF((SELECT home_last_result_subtitle FROM club_settings ORDER BY id ASC LIMIT 1), ''), 'Le dernier score enregistré, avec son compte-rendu quand il existe.'),
                content = NULL,
                secondary_content = NULL,
                display_order = 50
            WHERE section_key = 'last_result'");

        $this->addSql('ALTER TABLE club_settings DROP COLUMN home_intro_title, DROP COLUMN home_intro_subtitle, DROP COLUMN home_intro_lead, DROP COLUMN home_intro_media_note, DROP COLUMN home_featured_title, DROP COLUMN home_featured_subtitle, DROP COLUMN home_upcoming_title, DROP COLUMN home_upcoming_subtitle, DROP COLUMN home_last_result_title, DROP COLUMN home_last_result_subtitle');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings ADD home_intro_title VARCHAR(120) DEFAULT NULL, ADD home_intro_subtitle VARCHAR(255) DEFAULT NULL, ADD home_intro_lead LONGTEXT DEFAULT NULL, ADD home_intro_media_note LONGTEXT DEFAULT NULL, ADD home_featured_title VARCHAR(120) DEFAULT NULL, ADD home_featured_subtitle VARCHAR(255) DEFAULT NULL, ADD home_upcoming_title VARCHAR(120) DEFAULT NULL, ADD home_upcoming_subtitle VARCHAR(255) DEFAULT NULL, ADD home_last_result_title VARCHAR(120) DEFAULT NULL, ADD home_last_result_subtitle VARCHAR(255) DEFAULT NULL');

        $this->addSql("UPDATE club_settings
            SET
                home_intro_title = (SELECT title FROM home_section WHERE section_key = 'intro' ORDER BY id ASC LIMIT 1),
                home_intro_subtitle = (SELECT subtitle FROM home_section WHERE section_key = 'intro' ORDER BY id ASC LIMIT 1),
                home_intro_lead = (SELECT content FROM home_section WHERE section_key = 'intro' ORDER BY id ASC LIMIT 1),
                home_intro_media_note = (SELECT secondary_content FROM home_section WHERE section_key = 'intro' ORDER BY id ASC LIMIT 1),
                home_featured_title = (SELECT title FROM home_section WHERE section_key = 'featured_article' ORDER BY id ASC LIMIT 1),
                home_featured_subtitle = (SELECT subtitle FROM home_section WHERE section_key = 'featured_article' ORDER BY id ASC LIMIT 1),
                home_upcoming_title = (SELECT title FROM home_section WHERE section_key = 'next_match' ORDER BY id ASC LIMIT 1),
                home_upcoming_subtitle = (SELECT subtitle FROM home_section WHERE section_key = 'next_match' ORDER BY id ASC LIMIT 1),
                home_last_result_title = (SELECT title FROM home_section WHERE section_key = 'last_result' ORDER BY id ASC LIMIT 1),
                home_last_result_subtitle = (SELECT subtitle FROM home_section WHERE section_key = 'last_result' ORDER BY id ASC LIMIT 1)");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, display_order, is_enabled)
            SELECT 'latest_article', title, subtitle, display_order, is_enabled
            FROM home_section
            WHERE section_key = 'featured_article'
              AND NOT EXISTS (
                  SELECT 1 FROM (SELECT id FROM home_section WHERE section_key = 'latest_article') AS existing_latest_article
              )");
        $this->addSql("DELETE FROM home_section WHERE section_key = 'featured_article'");

        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, display_order, is_enabled)
            SELECT 'last_match', title, subtitle, display_order, is_enabled
            FROM home_section
            WHERE section_key = 'last_result'
              AND NOT EXISTS (
                  SELECT 1 FROM (SELECT id FROM home_section WHERE section_key = 'last_match') AS existing_last_match
              )");
        $this->addSql("DELETE FROM home_section WHERE section_key = 'last_result'");

        $this->addSql("DELETE FROM home_section WHERE section_key IN ('intro', 'next_match')");
        $this->addSql('ALTER TABLE home_section DROP COLUMN image, DROP COLUMN secondary_content, DROP COLUMN content');
    }
}
