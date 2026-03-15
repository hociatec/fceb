<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313164000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create player table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, photo VARCHAR(255) DEFAULT NULL, description CLOB NOT NULL, age INTEGER DEFAULT NULL, display_order INTEGER NOT NULL, is_published BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_98197A65A64C9B3 ON player (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE player');
    }
}
