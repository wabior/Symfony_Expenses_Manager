<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add user_id column and index to expense_occurrence table for better query performance
 */
final class Version20251220123206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id column and index to expense_occurrence table for better query performance';
    }

    public function up(Schema $schema): void
    {
        // Add user_id column to expense_occurrence table
        $this->addSql('ALTER TABLE expense_occurrence ADD COLUMN user_id INT NOT NULL AFTER expense_id');

        // Populate user_id from related expense records
        $this->addSql('UPDATE expense_occurrence eo SET user_id = (SELECT e.user_id FROM expense e WHERE e.id = eo.expense_id)');

        // Add foreign key constraint
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT FK_EXPENSE_OCCURRENCE_USER FOREIGN KEY (user_id) REFERENCES user (id)');

        // Create index on user_id for better query performance
        $this->addSql('CREATE INDEX idx_occurrence_user ON expense_occurrence(user_id)');

        // Create composite index for common queries (user + date)
        $this->addSql('CREATE INDEX idx_occurrence_user_date ON expense_occurrence(user_id, occurrence_date)');
    }

    public function down(Schema $schema): void
    {
        // Drop composite index
        $this->addSql('DROP INDEX idx_occurrence_user_date ON expense_occurrence');

        // Drop index
        $this->addSql('DROP INDEX idx_occurrence_user ON expense_occurrence');

        // Drop foreign key
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY FK_EXPENSE_OCCURRENCE_USER');

        // Drop column
        $this->addSql('ALTER TABLE expense_occurrence DROP COLUMN user_id');
    }
}
