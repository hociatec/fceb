<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316164000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne les noms d index MySQL sur les noms attendus par Doctrine';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings RENAME INDEX uniq_club_settings_singleton TO UNIQ_E6FE0A6C8F4C93DD');
        $this->addSql('ALTER TABLE match_game RENAME INDEX IDX_F6CB4E6C57A69F65 TO IDX_424480FEE5505DDB');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $this->addSql('ALTER TABLE club_settings RENAME INDEX UNIQ_E6FE0A6C8F4C93DD TO uniq_club_settings_singleton');
        $this->addSql('ALTER TABLE match_game RENAME INDEX IDX_424480FEE5505DDB TO IDX_F6CB4E6C57A69F65');
    }
}
