<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Simplify category table - remove name_english column and rename name_polish to name
 */
final class Version20250127100400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Simplify category table - remove name_english column and rename name_polish to name';
    }

    public function up(Schema $schema): void
    {
        // This migration is no longer needed as the category table structure
        // has been properly defined in Version20250101000001
        // Category table already has simplified structure (name, user_id)
    }

    public function down(Schema $schema): void
    {
        // This migration is no longer needed - no changes to revert
    }
}
