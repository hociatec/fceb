<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260313143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image fields for articles and pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article ADD cover_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD hero_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP cover_image');
        $this->addSql('ALTER TABLE page DROP hero_image');
    }
}
