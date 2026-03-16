<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313101500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajoute la configuration des sections d'accueil";
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('CREATE TABLE home_section (id INT AUTO_INCREMENT NOT NULL, section_key VARCHAR(80) NOT NULL, title VARCHAR(120) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, display_order INT NOT NULL, is_enabled TINYINT(1) NOT NULL, PRIMARY KEY(id), UNIQUE INDEX UNIQ_348298D4AF8F7D94 (section_key)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO home_section (section_key, title, subtitle, display_order, is_enabled) VALUES
            ('latest_article', 'Dernière actualité', 'Le dernier article mis en avant par le club.', 10, 1),
            ('upcoming_matches', 'Matchs à venir', 'Les prochaines rencontres déjà programmées.', 20, 1),
            ('last_match', 'Dernier match', 'Le dernier résultat disponible du club.', 30, 1)");
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('DROP TABLE home_section');
    }
}
