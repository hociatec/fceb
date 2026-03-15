<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315224500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add club settings entity for global site and homepage administration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE club_settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, club_name VARCHAR(160) NOT NULL, public_email VARCHAR(255) NOT NULL, phone VARCHAR(60) NOT NULL, address VARCHAR(255) NOT NULL, map_url VARCHAR(255) NOT NULL, home_intro_title VARCHAR(120) DEFAULT NULL, home_intro_subtitle VARCHAR(255) DEFAULT NULL, home_intro_lead CLOB DEFAULT NULL, home_intro_media_note CLOB DEFAULT NULL, home_featured_title VARCHAR(120) DEFAULT NULL, home_featured_subtitle VARCHAR(255) DEFAULT NULL, home_upcoming_title VARCHAR(120) DEFAULT NULL, home_upcoming_subtitle VARCHAR(255) DEFAULT NULL, home_last_result_title VARCHAR(120) DEFAULT NULL, home_last_result_subtitle VARCHAR(255) DEFAULT NULL)');
        $this->addSql("INSERT INTO club_settings (club_name, public_email, phone, address, map_url, home_intro_title, home_intro_subtitle, home_intro_lead, home_intro_media_note, home_featured_title, home_featured_subtitle, home_upcoming_title, home_upcoming_subtitle, home_last_result_title, home_last_result_subtitle)
            VALUES (
                'Cécifoot La Bassée',
                'contact@fceb.fr',
                '06 80 31 38 68',
                'Stade Roland Joly, 42 rue de Lille, 59480 La Bassée',
                'https://www.google.com/maps/search/?api=1&query=Stade+Roland+Joly+42+rue+de+Lille+59480+La+Bassee',
                'Cécifoot La Bassée',
                'Un club qui assume à la fois l''exigence sportive, l''accessibilité et l''ancrage local.',
                'À La Bassée, le cécifoot se construit autour d''un cadre clair : engagement, solidarité, écoute, progression et goût du jeu. Le club défend une pratique accessible, sérieuse et vivante, pensée pour accueillir durablement les joueurs, les proches, les bénévoles et les partenaires.',
                'Chaque visuel publié sur le site a vocation à montrer le terrain, les temps forts du collectif et la réalité de la vie du club.',
                'Actualité à la une',
                'Le contenu principal à retenir en ce moment, avec un angle éditorial plus net.',
                'Prochain match',
                'Le prochain rendez-vous sportif du club, avec les repères utiles en un coup d''œil.',
                'Dernier résultat',
                'Le dernier score enregistré, avec son compte-rendu quand il existe.'
            )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE club_settings');
    }
}
