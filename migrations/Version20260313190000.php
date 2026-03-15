<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add aliases to team identities and merge duplicate visible teams';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team_identity ADD aliases CLOB DEFAULT NULL');

        $this->addSql("UPDATE team_identity SET aliases = 'FC Cécifoot 59 La Bassée' WHERE team_name = 'Cécifoot 59 La Bassée'");
        $this->addSql("UPDATE team_identity SET aliases = 'RC Lens Cécifoot' WHERE team_name = 'RC Lens'");
        $this->addSql("UPDATE team_identity SET aliases = 'FC Nantes Cécifoot' WHERE team_name = 'FC Nantes'");
        $this->addSql("UPDATE team_identity SET aliases = 'AS Saint-Mandé Cécifoot' WHERE team_name = 'AS Saint-Mandé'");

        $this->addSql("DELETE FROM team_identity WHERE team_name IN ('FC Cécifoot 59 La Bassée', 'RC Lens Cécifoot', 'FC Nantes Cécifoot', 'AS Saint-Mandé Cécifoot')");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cette migration fusionne les doublons visibles.');
    }
}
