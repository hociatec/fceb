<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seeds editable database content that previously lived in fixtures';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `user` (email, roles, password, full_name, reset_password_token, reset_password_expires_at)
            SELECT 'admin@cecifoot-labassee.local', '[\"ROLE_ADMIN\"]', '\$2y\$13\$3HRnq3yz5Efa2r5yJdLnsutiDDETmc7YAoPt3oprfXRjHg1fo8v9m', 'Administrateur Cecifoot', NULL, NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM `user` WHERE email = 'admin@cecifoot-labassee.local'
            )");

        $this->addSql("INSERT INTO season (name, slug, start_date, end_date, is_current)
            SELECT 'Saison 2025-2026', 'saison-2025-2026', '2025-09-01', '2026-06-30', 1
            WHERE NOT EXISTS (
                SELECT 1 FROM season WHERE slug = 'saison-2025-2026'
            )");

        $this->addSql("INSERT INTO season (name, slug, start_date, end_date, is_current)
            SELECT 'Saison 2024-2025', 'saison-2024-2025', '2024-09-01', '2025-06-30', 0
            WHERE NOT EXISTS (
                SELECT 1 FROM season WHERE slug = 'saison-2024-2025'
            )");

        $this->addSql("INSERT INTO article (title, slug, excerpt, content, published_at, is_published, placement, season_id, author_id, cover_image, status, meta_title, meta_description)
            SELECT
                'La Bassee, nouveau participant de la poule Challenger',
                'la-bassee-nouveau-participant-poule-challenger',
                'Le FC Cecifoot 59 La Bassee figure parmi les nouveaux engages de la poule Challenger 2025-2026.',
                'D''apres Lensois.com, La Bassee fait partie des nouveaux participants de la poule Challenger pour la saison 2025-2026, aux cotes notamment du FC Nantes. Cette entree dans le championnat national confirme la structuration du projet cecifoot local et son ancrage dans le territoire.',
                '2025-10-31 10:00:00',
                1,
                'homepage',
                (SELECT id FROM season WHERE slug = 'saison-2025-2026'),
                (SELECT id FROM `user` WHERE email = 'admin@cecifoot-labassee.local'),
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM article WHERE slug = 'la-bassee-nouveau-participant-poule-challenger'
            )");

        $this->addSql("INSERT INTO article (title, slug, excerpt, content, published_at, is_published, placement, season_id, author_id, cover_image, status, meta_title, meta_description)
            SELECT
                'La Bassee accueillera une journee Challenger le 28 mars 2026',
                'la-bassee-accueillera-journee-challenger-28-mars-2026',
                'Le calendrier federal handisport annonce une journee B1 Challenger a La Bassee le 28 mars 2026.',
                'Le calendrier 2026 de la Federation francaise handisport annonce une journee de Championnat de France B1 Challenger a La Bassee le 28 mars 2026. La fiche Ou pratiquer de la FFH reference egalement cette date pour le club, avec un contact local dedie.',
                '2026-03-01 09:00:00',
                1,
                'current_season',
                (SELECT id FROM season WHERE slug = 'saison-2025-2026'),
                (SELECT id FROM `user` WHERE email = 'admin@cecifoot-labassee.local'),
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM article WHERE slug = 'la-bassee-accueillera-journee-challenger-28-mars-2026'
            )");

        $this->addSql("INSERT INTO article (title, slug, excerpt, content, published_at, is_published, placement, season_id, author_id, cover_image, status, meta_title, meta_description)
            SELECT
                'Retour sur les premieres references publiques du club',
                'retour-premieres-references-publiques-du-club',
                'Le club apparait dans les repertoires handisport et dans les resultats publies de la saison.',
                'Les repertoires publics de la FFH mentionnent le FC Cecifoot 59 La Bassee avec un contact specifique, tandis que les resultats publies par les structures voisines confirment sa presence dans la competition nationale B1 Challenger.',
                '2025-06-28 18:00:00',
                1,
                'archive',
                (SELECT id FROM season WHERE slug = 'saison-2024-2025'),
                (SELECT id FROM `user` WHERE email = 'admin@cecifoot-labassee.local'),
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM article WHERE slug = 'retour-premieres-references-publiques-du-club'
            )");

        $this->addSql("INSERT INTO match_game (opponent, competition, location, match_date, side, our_score, opponent_score, status, season_id)
            SELECT
                'Clermont Joker''s',
                'Championnat de France B1 Challenger',
                'La Bassee',
                '2026-03-28 11:00:00',
                'home',
                NULL,
                NULL,
                'scheduled',
                (SELECT id FROM season WHERE slug = 'saison-2025-2026')
            WHERE NOT EXISTS (
                SELECT 1 FROM match_game WHERE opponent = 'Clermont Joker''s' AND match_date = '2026-03-28 11:00:00'
            )");

        $this->addSql("INSERT INTO match_game (opponent, competition, location, match_date, side, our_score, opponent_score, status, season_id)
            SELECT
                'RC Lens Cecifoot',
                'Championnat de France B1 Challenger',
                'Lens',
                '2025-11-08 14:00:00',
                'away',
                0,
                1,
                'completed',
                (SELECT id FROM season WHERE slug = 'saison-2025-2026')
            WHERE NOT EXISTS (
                SELECT 1 FROM match_game WHERE opponent = 'RC Lens Cecifoot' AND match_date = '2025-11-08 14:00:00'
            )");

        $this->addSql("INSERT INTO page (title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description)
            SELECT
                'Contact',
                'contact',
                'Contact cecifoot reference par la Federation francaise handisport : martine.cecifoot59@gmail.com - 06 86 94 74 77. Point d''ancrage local : Mairie de La Bassee, place du General de Gaulle, 59480 La Bassee.',
                'footer',
                1,
                10,
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM page WHERE slug = 'contact'
            )");

        $this->addSql("INSERT INTO page (title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description)
            SELECT
                'CGU',
                'cgu',
                'Conditions generales d''utilisation du site public, de l''espace membre et de l''API du club.',
                'footer',
                1,
                20,
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM page WHERE slug = 'cgu'
            )");

        $this->addSql("INSERT INTO page (title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description)
            SELECT
                'Partenaires',
                'partenaires',
                'Le club s''inscrit dans l''ecosysteme sportif local de La Bassee et dans le reseau handisport national. Retrouvez les partenaires et structures utiles depuis cette page.',
                'footer',
                1,
                30,
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM page WHERE slug = 'partenaires'
            )");

        $this->addSql("INSERT INTO page (title, slug, content, placement, is_published, menu_order, hero_image, status, meta_title, meta_description)
            SELECT
                'Le club',
                'le-club',
                'Le projet cecifoot de La Bassee s''inscrit dans le Football Club Esperance de La Bassee, reference sur le portail associatif municipal avec une pratique au Stade Roland Joly. Le repertoire handisport recense egalement le FC Cecifoot 59 La Bassee avec un contact specifique pour la discipline.',
                'header',
                1,
                10,
                NULL,
                'published',
                NULL,
                NULL
            WHERE NOT EXISTS (
                SELECT 1 FROM page WHERE slug = 'le-club'
            )");

        $this->addSql("INSERT INTO social_link (label, url, icon, is_visible, display_order)
            SELECT 'Facebook', 'https://facebook.com/cecifootlabassee', 'facebook', 1, 10
            WHERE NOT EXISTS (
                SELECT 1 FROM social_link WHERE label = 'Facebook' AND url = 'https://facebook.com/cecifootlabassee'
            )");

        $this->addSql("INSERT INTO social_link (label, url, icon, is_visible, display_order)
            SELECT 'Instagram', 'https://instagram.com/cecifootlabassee', 'instagram', 1, 20
            WHERE NOT EXISTS (
                SELECT 1 FROM social_link WHERE label = 'Instagram' AND url = 'https://instagram.com/cecifootlabassee'
            )");

        $this->addSql("INSERT INTO partner (name, website_url, logo_url, is_visible, display_order)
            SELECT 'Ville de La Bassee', 'https://www.ville-labassee.fr', 'https://placehold.co/300x140?text=Ville+de+La+Bassee', 1, 10
            WHERE NOT EXISTS (
                SELECT 1 FROM partner WHERE name = 'Ville de La Bassee'
            )");

        $this->addSql("INSERT INTO partner (name, website_url, logo_url, is_visible, display_order)
            SELECT 'Federation Francaise Handisport', 'https://www.handisport.org', 'https://placehold.co/300x140?text=FFH', 1, 20
            WHERE NOT EXISTS (
                SELECT 1 FROM partner WHERE name = 'Federation Francaise Handisport'
            )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM article WHERE slug IN (
            'la-bassee-nouveau-participant-poule-challenger',
            'la-bassee-accueillera-journee-challenger-28-mars-2026',
            'retour-premieres-references-publiques-du-club'
        )");

        $this->addSql("DELETE FROM match_game
            WHERE (opponent = 'Clermont Joker''s' AND match_date = '2026-03-28 11:00:00')
               OR (opponent = 'RC Lens Cecifoot' AND match_date = '2025-11-08 14:00:00')");

        $this->addSql("DELETE FROM page WHERE slug IN ('contact', 'cgu', 'partenaires', 'le-club')");
        $this->addSql("DELETE FROM social_link WHERE url IN ('https://facebook.com/cecifootlabassee', 'https://instagram.com/cecifootlabassee')");
        $this->addSql("DELETE FROM partner WHERE name IN ('Ville de La Bassee', 'Federation Francaise Handisport')");
        $this->addSql("DELETE FROM season WHERE slug IN ('saison-2025-2026', 'saison-2024-2025')");
        $this->addSql("DELETE FROM `user` WHERE email = 'admin@cecifoot-labassee.local'");
    }
}
