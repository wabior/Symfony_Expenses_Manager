<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Deactivate home menu item since we have logo for navigation
 */
final class Version20250101000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deactivate home menu item since we have logo for navigation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE menu SET activated = 0 WHERE path = \'/\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE menu SET activated = 1 WHERE path = \'/\'');
    }
}