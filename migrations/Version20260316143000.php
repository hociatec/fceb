<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne les noms adverses des matchs sur les libelles Tournify';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("UPDATE match_game SET opponent = 'AS. Saint-Mandé Cécifoot' WHERE opponent = 'AS Saint-Mandé' OR opponent = 'AS Saint-Mandé Cécifoot' OR opponent = 'AS. Saint-Mandé'");
        $this->addSql("UPDATE match_game SET opponent = 'RC. Lens Cécifoot' WHERE opponent = 'RC Lens' OR opponent = 'RC Lens Cecifoot' OR opponent = 'RC Lens Cécifoot' OR opponent = 'RC. Lens'");
        $this->addSql("UPDATE match_game SET opponent = 'FC. Nantes Cécifoot' WHERE opponent = 'FC Nantes' OR opponent = 'FC Nantes Cecifoot' OR opponent = 'FC Nantes Cécifoot' OR opponent = 'FC. Nantes'");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql("UPDATE match_game SET opponent = 'AS Saint-Mandé' WHERE opponent = 'AS. Saint-Mandé Cécifoot'");
        $this->addSql("UPDATE match_game SET opponent = 'RC Lens' WHERE opponent = 'RC. Lens Cécifoot'");
        $this->addSql("UPDATE match_game SET opponent = 'FC Nantes' WHERE opponent = 'FC. Nantes Cécifoot'");
    }
}
