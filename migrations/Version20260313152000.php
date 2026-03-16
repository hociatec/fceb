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
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE team_identity (id INT AUTO_INCREMENT NOT NULL, team_name VARCHAR(160) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id), UNIQUE INDEX UNIQ_833242D22E9705D0 (team_name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
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
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('DROP TABLE team_identity');
    }
}
