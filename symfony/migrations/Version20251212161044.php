<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to assign existing expenses to the first admin user
 */
final class Version20251212161044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Assign existing expenses to first admin user for data migration';
    }

    public function up(Schema $schema): void
    {
        // Assign all existing expenses to the first user (admin)
        // This assumes there's at least one user in the system
        $this->addSql('SET @first_user_id = (SELECT id FROM user LIMIT 1)');
        $this->addSql('UPDATE expense SET user_id = @first_user_id WHERE user_id IS NULL');
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible - expenses will keep their user assignments
    }
}
