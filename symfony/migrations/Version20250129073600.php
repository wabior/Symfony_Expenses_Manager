<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129073600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id to Category entity';
    }

    public function up(Schema $schema): void
    {
        // This migration is no longer needed as the category table already has user_id column
        // with proper constraints defined in Version20250101000001
    }

    public function down(Schema $schema): void
    {
        // This migration is no longer needed - no changes to revert
    }
}
