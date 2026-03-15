<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313182000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password reset token fields to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD reset_password_token VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD reset_password_expires_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E32A5A5A ON user (reset_password_token)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cette migration ajoute des colonnes SQLite sans rollback direct.');
    }
}
