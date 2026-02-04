<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make user.roles column nullable to allow NULL instead of always storing an empty JSON array.
 */
final class Version20250204000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow NULL values in user.roles JSON column';
    }

    public function up(Schema $schema): void
    {
        // Make roles column nullable (JSON DEFAULT NULL) so that we don't have to persist empty arrays.
        $this->addSql('ALTER TABLE user MODIFY roles JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert to non-nullable JSON column without default, matching original definition.
        $this->addSql('ALTER TABLE user MODIFY roles JSON NOT NULL');
    }
}

