<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606161850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move category_id before created_at in the expense table';
    }

    public function up(Schema $schema): void
    {
        // Manually define the changes
        $this->addSql('ALTER TABLE expense MODIFY COLUMN category_id INT AFTER payment_status');
        $this->addSql('ALTER TABLE expense MODIFY COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER category_id');
        $this->addSql('ALTER TABLE expense MODIFY COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER created_at');
    }

    public function down(Schema $schema): void
    {
        // Manually revert the changes
        $this->addSql('ALTER TABLE expense MODIFY COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER payment_status');
        $this->addSql('ALTER TABLE expense MODIFY COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER created_at');
        $this->addSql('ALTER TABLE expense MODIFY COLUMN category_id INT AFTER updated_at');
    }
}
