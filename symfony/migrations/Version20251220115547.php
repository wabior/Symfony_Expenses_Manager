<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for recurring expenses system with expense_occurrence table
 */
final class Version20251220115547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recurring expenses support with expense_occurrence table';
    }

    public function up(Schema $schema): void
    {
        // Add recurring_frequency field to expense table
        $this->addSql('ALTER TABLE expense ADD COLUMN recurring_frequency INT DEFAULT 0 NOT NULL COMMENT \'0=jednorazowy, 1-12=miesięczny cykl\'');

        // Create expense_occurrence table
        $this->addSql('CREATE TABLE expense_occurrence (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expense_id INT NOT NULL,
            occurrence_date DATE NOT NULL,
            payment_status VARCHAR(20) DEFAULT \'unpaid\',
            payment_date DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (expense_id) REFERENCES expense(id) ON DELETE CASCADE,
            UNIQUE KEY unique_expense_date (expense_id, occurrence_date)
        )');

        // Create indexes for performance
        $this->addSql('CREATE INDEX idx_occurrence_date ON expense_occurrence(occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_expense ON expense_occurrence(expense_id)');
        $this->addSql('CREATE INDEX idx_occurrence_status ON expense_occurrence(payment_status)');
        $this->addSql('CREATE INDEX idx_expense_recurring ON expense(recurring_frequency)');
    }

    public function down(Schema $schema): void
    {
        // Drop indexes
        $this->addSql('DROP INDEX idx_occurrence_date ON expense_occurrence');
        $this->addSql('DROP INDEX idx_occurrence_expense ON expense_occurrence');
        $this->addSql('DROP INDEX idx_occurrence_status ON expense_occurrence');
        $this->addSql('DROP INDEX idx_expense_recurring ON expense');

        // Drop expense_occurrence table
        $this->addSql('DROP TABLE expense_occurrence');

        // Remove recurring_frequency column
        $this->addSql('ALTER TABLE expense DROP COLUMN recurring_frequency');
    }
}
