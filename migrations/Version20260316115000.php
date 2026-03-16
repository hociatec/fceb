<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260316115000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align player photo index naming with Doctrine mapping';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $table = $schema->getTable('player_photo');
        if ($table->hasIndex('IDX_7B1EFBB899E6F5DF') && !$table->hasIndex('IDX_ABC5FF5E99E6F5DF')) {
            $this->addSql('ALTER TABLE player_photo RENAME INDEX IDX_7B1EFBB899E6F5DF TO IDX_ABC5FF5E99E6F5DF');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $this->abortIf(!$platform instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform, sprintf('Migration can only be executed safely on MySQL/MariaDB, current platform: %s.', $platform::class));

        $table = $schema->getTable('player_photo');
        if ($table->hasIndex('IDX_ABC5FF5E99E6F5DF') && !$table->hasIndex('IDX_7B1EFBB899E6F5DF')) {
            $this->addSql('ALTER TABLE player_photo RENAME INDEX IDX_ABC5FF5E99E6F5DF TO IDX_7B1EFBB899E6F5DF');
        }
    }
}
