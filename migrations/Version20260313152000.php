<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313152000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create team identities table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE team_identity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, team_name VARCHAR(160) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_833242D22E9705D0 ON team_identity (team_name)');
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('Cécifoot 59 La Bassée', 'assets/teams/cecifoot-la-bassee.svg')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('FC Cécifoot 59 La Bassée', 'assets/teams/cecifoot-la-bassee.svg')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('RC Lens', 'assets/teams/rc-lens-official.png')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('RC Lens Cécifoot', 'assets/teams/rc-lens-official.png')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('FC Nantes', 'assets/teams/fc-nantes-official.svg')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('FC Nantes Cécifoot', 'assets/teams/fc-nantes-official.svg')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('AS Saint-Mandé', 'assets/teams/as-saint-mande-official.jpg')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('AS Saint-Mandé Cécifoot', 'assets/teams/as-saint-mande-official.jpg')");
        $this->addSql("INSERT INTO team_identity (team_name, logo_path) VALUES ('Clermont Jokers', 'assets/teams/clermont-jokers-official.jpg')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE team_identity');
    }
}
