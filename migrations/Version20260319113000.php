<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin-managed navigation and seed static pages stored in database';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $schemaManager = $this->connection->createSchemaManager();
        $pageColumns = array_map(static fn ($column) => $column->getName(), $schemaManager->listTableColumns('page'));
        if (!in_array('system_key', $pageColumns, true)) {
            $this->addSql('ALTER TABLE page ADD system_key VARCHAR(80) DEFAULT NULL');
        }

        $pageIndexes = array_change_key_case($schemaManager->listTableIndexes('page'), CASE_LOWER);
        if (!isset($pageIndexes['uniq_140ab6202e7d90e'])) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB6202E7D90E ON page (system_key)');
        }

        $tables = array_map('strtolower', $schemaManager->listTableNames());
        if (!in_array('navigation_item', $tables, true)) {
            $this->addSql('CREATE TABLE navigation_item (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, label VARCHAR(160) NOT NULL, location VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, route_name VARCHAR(120) DEFAULT NULL, external_url VARCHAR(2048) DEFAULT NULL, display_order INT NOT NULL, is_enabled TINYINT(1) NOT NULL DEFAULT 1, open_in_new_tab TINYINT(1) NOT NULL DEFAULT 0, INDEX IDX_C5D58FA1C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE navigation_item ADD CONSTRAINT FK_C5D58FA1C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE SET NULL');
        }

        $this->addSql("UPDATE page SET system_key = 'faq' WHERE slug = 'faq' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'join' WHERE slug = 'rejoindre-le-club' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'partners' WHERE slug = 'partenaires' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'terms' WHERE slug = 'cgu' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'privacy' WHERE slug = 'politique-de-confidentialite' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'training' WHERE slug = 'entrainements' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'access' WHERE slug = 'acces' AND system_key IS NULL");
        $this->addSql("UPDATE page SET system_key = 'staff' WHERE slug = 'encadrement' AND system_key IS NULL");

        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Questions fréquentes', 'faq', 'faq', NULL, 'Des réponses simples aux questions les plus courantes sur le club, l''accueil et la découverte du cécifoot.', NULL, '<p>Le club accueille des joueurs, des proches, des bénévoles et des personnes qui souhaitent découvrir le cécifoot dans un cadre structuré.</p><p>Le plus simple pour commencer est de passer par la page de séance d’essai ou la page de contact afin d’échanger sur ton profil et tes attentes.</p><p>Le matériel, les créneaux et les modalités d’accueil peuvent être précisés directement par le club après un premier échange.</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'faq')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Nous rejoindre', 'rejoindre-le-club', 'join', NULL, 'Découvre comment rejoindre le Cécifoot La Bassée comme joueur, bénévole, encadrant ou partenaire du club.', NULL, '<p>Le club accueille des joueurs, des bénévoles, des proches et des personnes qui veulent découvrir le cécifoot dans un cadre structuré, exigeant et accessible.</p><p>Le plus simple est de commencer par une prise de contact ou une séance d’essai. Cela permet d’échanger sur ton profil, tes attentes et le fonctionnement concret du club.</p><p>Tu peux aussi proposer ton aide ou devenir partenaire selon la forme d’engagement qui te correspond.</p><p>[cta label="Demander une séance d’essai" url="/seance-decouverte"]</p><p>[cta label="Proposer mon aide" url="/benevolat" style="secondary"]</p><p>[cta label="Devenir partenaire" url="/devenir-partenaire" style="secondary"]</p><p>[cta label="Contacter le club" url="/contact" style="secondary"]</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'join')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Partenaires', 'partenaires', 'partners', NULL, 'Découvre les structures qui soutiennent le Cécifoot La Bassée et les différentes manières d’accompagner le club.', NULL, '<p>Le club s’appuie sur un écosystème local, associatif et institutionnel. Cette page remercie les soutiens existants et donne un cadre clair à ceux qui veulent s’engager à nos côtés.</p><p>Soutenir le Cécifoot La Bassée, c’est contribuer à une pratique sportive inclusive, à la vie associative locale et au développement d’un cadre exigeant pour les joueurs.</p><p>[cta label="Devenir partenaire" url="/devenir-partenaire"]</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'partners')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Conditions générales d''utilisation', 'cgu', 'terms', NULL, NULL, NULL, '<p>Le site a pour objet de présenter l’activité du club, ses actualités, ses saisons et ses informations pratiques.</p><p>L’utilisateur s’engage à utiliser les formulaires et les espaces de connexion de manière loyale, sans nuire au fonctionnement du site.</p><p>Le club se réserve le droit de faire évoluer les contenus, les accès et l’organisation du site à tout moment.</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'terms')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Politique de confidentialité', 'politique-de-confidentialite', 'privacy', NULL, NULL, NULL, '<p>Les données envoyées via le formulaire de contact sont utilisées uniquement pour répondre aux demandes adressées au club.</p><p>Le site limite la collecte aux informations nécessaires au traitement de la demande : nom, e-mail, objet et message.</p><p>Toute demande relative aux données personnelles peut être adressée au club via la page de contact.</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'privacy')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Entraînements et rythme du club', 'entrainements', 'training', NULL, NULL, NULL, '<p>Les entraînements s’organisent autour d’un cadre collectif, de repères de jeu précis et d’un accompagnement progressif selon le profil de chacun.</p><p>Les nouveaux arrivants peuvent être orientés vers une première prise de contact avant d’intégrer un créneau régulier.</p><p>Le club peut préciser les horaires, le matériel utile et les conditions de présence au moment de l’échange.</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'training')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Venir au club', 'acces', 'access', NULL, NULL, NULL, '<p>Le club est implanté à La Bassée et centralise ses informations de contact pour faciliter la première venue.</p><p>L’objectif est de rendre l’accès lisible, avec une prise de contact préalable si un accompagnement spécifique est utile.</p><p>Pour préparer un déplacement, le plus simple est d’utiliser la carte de contact ou d’écrire directement au club.</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'access')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO page (title, slug, system_key, meta_title, meta_description, hero_image, content, placement, is_published, status, menu_order)
            SELECT 'Encadrement et vie du club', 'encadrement', 'staff', NULL, NULL, NULL, '<p>Le club repose sur un encadrement sportif, associatif et bénévole qui permet de structurer les séances, les matchs et l’accueil.</p><p>Les proches, bénévoles et partenaires peuvent aussi contribuer à la vie du club selon leurs disponibilités.</p><p>Le projet du club cherche un équilibre entre exigence sportive, accessibilité et continuité associative.</p>', 'none', 1, 'published', 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM page WHERE system_key = 'staff')
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Saison', 'header', 'route', 'site_current_season', NULL, NULL, 10, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'header' AND route_name = 'site_current_season')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Actualités', 'header', 'route', 'site_articles', NULL, NULL, 20, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'header' AND route_name = 'site_articles')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Calendrier', 'header', 'route', 'site_calendar', NULL, NULL, 30, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'header' AND route_name = 'site_calendar')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Classement', 'header', 'route', 'site_ranking', NULL, NULL, 40, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'header' AND route_name = 'site_ranking')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Effectif', 'header', 'route', 'site_players', NULL, NULL, 50, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'header' AND route_name = 'site_players')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Nous rejoindre', 'header', 'route', 'site_join', NULL, NULL, 60, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'header' AND route_name = 'site_join')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'FAQ', 'footer', 'route', 'site_faq', NULL, NULL, 10, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'footer' AND route_name = 'site_faq')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Partenaires', 'footer', 'route', 'site_partners_static', NULL, NULL, 20, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'footer' AND route_name = 'site_partners_static')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'CGU', 'footer', 'route', 'site_terms', NULL, NULL, 30, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'footer' AND route_name = 'site_terms')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO navigation_item (label, location, type, route_name, page_id, external_url, display_order, is_enabled, open_in_new_tab)
            SELECT 'Confidentialité', 'footer', 'route', 'site_privacy', NULL, NULL, 40, 1, 0
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM navigation_item WHERE location = 'footer' AND route_name = 'site_privacy')
        SQL);
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("DELETE FROM navigation_item WHERE route_name IN ('site_current_season', 'site_articles', 'site_calendar', 'site_ranking', 'site_players', 'site_join', 'site_faq', 'site_partners_static', 'site_terms', 'site_privacy')");
        $this->addSql("DELETE FROM page WHERE system_key IN ('faq', 'join', 'partners', 'terms', 'privacy', 'training', 'access', 'staff')");
        $schemaManager = $this->connection->createSchemaManager();
        $tables = array_map('strtolower', $schemaManager->listTableNames());
        if (in_array('navigation_item', $tables, true)) {
            $this->addSql('ALTER TABLE navigation_item DROP FOREIGN KEY FK_C5D58FA1C4663E4');
            $this->addSql('DROP TABLE navigation_item');
        }

        $pageIndexes = array_change_key_case($schemaManager->listTableIndexes('page'), CASE_LOWER);
        if (isset($pageIndexes['uniq_140ab6202e7d90e'])) {
            $this->addSql('DROP INDEX UNIQ_140AB6202E7D90E ON page');
        }

        $pageColumns = array_map(static fn ($column) => $column->getName(), $schemaManager->listTableColumns('page'));
        if (in_array('system_key', $pageColumns, true)) {
            $this->addSql('ALTER TABLE page DROP system_key');
        }
    }
}
