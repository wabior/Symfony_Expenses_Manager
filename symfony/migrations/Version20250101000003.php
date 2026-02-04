<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add recurring expenses support with expense_occurrence table
 */
final class Version20250101000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recurring expenses support with expense_occurrence table';
    }

    public function up(Schema $schema): void
    {
        // Create expense_occurrence table for recurring expenses
        $this->addSql('CREATE TABLE expense_occurrence (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expense_id INT NOT NULL,
            user_id INT NOT NULL,
            occurrence_date DATE NOT NULL,
            payment_status VARCHAR(20) DEFAULT \'unpaid\',
            payment_date DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT FK_EXPENSE_OCCURRENCE_EXPENSE FOREIGN KEY (expense_id) REFERENCES expense(id) ON DELETE CASCADE,
            CONSTRAINT FK_EXPENSE_OCCURRENCE_USER FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
            UNIQUE KEY unique_expense_date (expense_id, occurrence_date)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create indexes for performance
        $this->addSql('CREATE INDEX idx_occurrence_date ON expense_occurrence(occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_expense ON expense_occurrence(expense_id)');
        $this->addSql('CREATE INDEX idx_occurrence_user ON expense_occurrence(user_id)');
        $this->addSql('CREATE INDEX idx_occurrence_status ON expense_occurrence(payment_status)');
        $this->addSql('CREATE INDEX idx_occurrence_user_date ON expense_occurrence(user_id, occurrence_date)');
        $this->addSql('CREATE INDEX idx_expense_recurring ON expense(recurring_frequency)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints created by this migration
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY IF EXISTS FK_EXPENSE_OCCURRENCE_EXPENSE');
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY IF EXISTS FK_EXPENSE_OCCURRENCE_USER');

        // Also try to drop any existing Doctrine-generated constraints (for compatibility)
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY IF EXISTS FK_EE9BEFAF395DB7B');
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY IF EXISTS FK_EE9BEFAA76ED395');

        // Drop indexes
        $this->addSql('DROP INDEX IF EXISTS idx_expense_recurring ON expense');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_status ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_user ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_expense ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_date ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_user_date ON expense_occurrence');

        // Drop unique constraint
        $this->addSql('ALTER TABLE expense_occurrence DROP INDEX IF EXISTS unique_expense_date');

        // Drop expense_occurrence table
        $this->addSql('DROP TABLE IF EXISTS expense_occurrence');
    }
}