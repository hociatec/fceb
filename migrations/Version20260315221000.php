<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315221000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add structured player profile fields and seed the first squad data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE player ADD birth_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD nationality VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD preferred_position VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD preferred_foot VARCHAR(40) DEFAULT NULL');

        $players = [
            [
                'name' => 'Youness Guarziz',
                'slug' => 'youness-guarziz',
                'birth_date' => '2006-11-24',
                'nationality' => 'Algérienne',
                'preferred_position' => 'Joueur de champ',
                'preferred_foot' => 'Droitier',
                'description' => "Youness Guarziz représente une génération jeune et ambitieuse au sein du collectif. Né le 24 novembre 2006, il apporte de l'énergie, de la fraîcheur et une vraie envie de progresser dans le cadre exigeant du club. Son profil s'inscrit dans une dynamique d'apprentissage, d'engagement et de développement sur la durée.",
                'meta_description' => 'Youness Guarziz, joueur de champ droitier du Cécifoot La Bassée, né en 2006 et engagé dans la progression du collectif.',
                'display_order' => 10,
            ],
            [
                'name' => 'Rédouane Bourar',
                'slug' => 'redouane-bourar',
                'birth_date' => '2001-03-01',
                'nationality' => 'Marocaine',
                'preferred_position' => 'Joueur de champ',
                'preferred_foot' => 'Droitier',
                'description' => "Rédouane Bourar fait partie des profils qui structurent l'équipe par leur sérieux et leur implication. Né le 1er mars 2001, il apporte de la continuité dans le travail collectif, de la disponibilité dans le jeu et une présence importante dans la vie du groupe. Son parcours s'inscrit dans un projet de club où l'investissement humain compte autant que la performance.",
                'meta_description' => 'Rédouane Bourar, joueur de champ droitier du Cécifoot La Bassée, né en 2001 et investi dans le collectif.',
                'display_order' => 20,
            ],
            [
                'name' => 'Hacène Sahraoui',
                'slug' => 'hacene-sahraoui',
                'birth_date' => '1998-12-27',
                'nationality' => 'Française',
                'preferred_position' => 'Joueur de champ',
                'preferred_foot' => 'Droitier',
                'description' => "Hacène Sahraoui apporte de la maturité et des repères au collectif. Né le 27 décembre 1998, il s'inscrit dans un registre d'engagement, d'écoute et de disponibilité pour le groupe. Sa présence contribue à la stabilité du projet sportif et à la cohérence du travail mené au fil de la saison.",
                'meta_description' => 'Hacène Sahraoui, joueur de champ droitier du Cécifoot La Bassée, né en 1998 et moteur du collectif.',
                'display_order' => 30,
            ],
            [
                'name' => 'Hocine Sahraoui',
                'slug' => 'hocine-sahraoui',
                'birth_date' => '1998-12-27',
                'nationality' => 'Française',
                'preferred_position' => 'Joueur de champ',
                'preferred_foot' => 'Droitier',
                'description' => "Hocine Sahraoui incarne un profil impliqué, au service du jeu et du collectif. Né le 27 décembre 1998, il contribue à l'équilibre de l'équipe par son investissement et son sens du cadre. Dans un effectif qui se construit sur la confiance et la rigueur, sa présence renforce la continuité du projet du club.",
                'meta_description' => 'Hocine Sahraoui, joueur de champ droitier du Cécifoot La Bassée, né en 1998 et engagé dans le projet du club.',
                'display_order' => 40,
            ],
            [
                'name' => 'Bakary Tracore',
                'slug' => 'bakary-tracore',
                'birth_date' => '2000-12-30',
                'nationality' => 'Malienne',
                'preferred_position' => 'Joueur de champ',
                'preferred_foot' => 'Droitier',
                'description' => "Bakary Tracore complète l'effectif avec un profil engagé et tourné vers le collectif. Né le 30 décembre 2000, il participe à la dynamique sportive du club avec sérieux et volonté. Sa présence renforce la diversité du groupe et la solidité d'un projet qui se construit dans la durée.",
                'meta_description' => 'Bakary Tracore, joueur de champ droitier du Cécifoot La Bassée, né en 2000 et engagé dans la dynamique collective.',
                'display_order' => 50,
            ],
        ];

        foreach ($players as $player) {
            $description = str_replace("'", "''", $player['description']);
            $metaDescription = str_replace("'", "''", $player['meta_description']);
            $name = str_replace("'", "''", $player['name']);
            $nationality = str_replace("'", "''", $player['nationality']);
            $preferredPosition = str_replace("'", "''", $player['preferred_position']);
            $preferredFoot = str_replace("'", "''", $player['preferred_foot']);
            $slug = $player['slug'];

            $this->addSql(sprintf(
                "INSERT INTO player (name, slug, photo, meta_title, meta_description, description, age, birth_date, nationality, preferred_position, preferred_foot, display_order, is_published, status)
                 VALUES ('%s', '%s', NULL, NULL, '%s', '%s', NULL, '%s', '%s', '%s', '%s', %d, 1, 'published')
                 ON CONFLICT(slug) DO UPDATE SET
                    name = excluded.name,
                    meta_description = excluded.meta_description,
                    description = excluded.description,
                    birth_date = excluded.birth_date,
                    nationality = excluded.nationality,
                    preferred_position = excluded.preferred_position,
                    preferred_foot = excluded.preferred_foot,
                    display_order = excluded.display_order,
                    is_published = excluded.is_published,
                    status = excluded.status",
                $name,
                $slug,
                $metaDescription,
                $description,
                $player['birth_date'],
                $nationality,
                $preferredPosition,
                $preferredFoot,
                $player['display_order'],
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM player WHERE slug IN ('youness-guarziz', 'redouane-bourar', 'hacene-sahraoui', 'hocine-sahraoui', 'bakary-tracore')");
        $this->addSql('ALTER TABLE player DROP COLUMN preferred_foot');
        $this->addSql('ALTER TABLE player DROP COLUMN preferred_position');
        $this->addSql('ALTER TABLE player DROP COLUMN nationality');
        $this->addSql('ALTER TABLE player DROP COLUMN birth_date');
    }
}
