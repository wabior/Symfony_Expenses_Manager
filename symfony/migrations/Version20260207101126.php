<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207101126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add amount column to expense_occurrence table';
    }

    public function up(Schema $schema): void
    {
        // Add amount column to expense_occurrence table with default value (only if not exists)
        $this->addSql('ALTER TABLE expense_occurrence ADD COLUMN IF NOT EXISTS amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        
        // Change existing columns with defaults
        $this->addSql('ALTER TABLE expense CHANGE recurring_frequency recurring_frequency INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE expense_occurrence CHANGE payment_status payment_status VARCHAR(20) DEFAULT \'unpaid\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        
        // Optimized composite indexes for query performance (with IF NOT EXISTS)
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_occurrence_user_expense ON expense_occurrence (user_id, expense_id) COMMENT "Composite index for finding user occurrences by expense"');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_occurrence_user_date ON expense_occurrence (user_id, occurrence_date) COMMENT "Composite index for monthly occurrence queries by user"');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_occurrence_expense_date ON expense_occurrence (expense_id, occurrence_date) COMMENT "Composite index for bulk updates by expense and date"');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_occurrence_user_status_date ON expense_occurrence (user_id, payment_status, occurrence_date) COMMENT "Composite index for filtering paid/unpaid occurrences"');
        
        // Additional indexes for sorting and complex queries (with IF NOT EXISTS)
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_occurrence_user_expense_date ON expense_occurrence (user_id, expense_id, occurrence_date) COMMENT "Composite index for complex queries by user, expense and date"');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_expense_category_amount ON expense (category_id, amount) COMMENT "Index for sorting expenses by category and amount"');
        
        // Foreign key constraints with readable names
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT fk_occurrence_expense FOREIGN KEY (expense_id) REFERENCES expense (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT fk_occurrence_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense CHANGE recurring_frequency recurring_frequency INT DEFAULT 0 NOT NULL COMMENT \'0=jednorazowy, 1-12=miesięczny cykl\'');
        $this->addSql('CREATE INDEX idx_expense_recurring ON expense (recurring_frequency)');
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY FK_EE9BEFAF395DB7B');
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY FK_EE9BEFAA76ED395');
        $this->addSql('ALTER TABLE expense_occurrence CHANGE payment_status payment_status VARCHAR(20) DEFAULT \'unpaid\', CHANGE amount amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT `FK_EXPENSE_OCCURRENCE_EXPENSE` FOREIGN KEY (expense_id) REFERENCES expense (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT `FK_EXPENSE_OCCURRENCE_USER` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_occurrence_status ON expense_occurrence (payment_status)');
        $this->addSql('CREATE UNIQUE INDEX unique_expense_date ON expense_occurrence (expense_id, occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_user_date ON expense_occurrence (user_id, occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_date ON expense_occurrence (occurrence_date)');
        $this->addSql('ALTER TABLE expense_occurrence RENAME INDEX idx_ee9befaa76ed395 TO idx_occurrence_user');
        $this->addSql('ALTER TABLE expense_occurrence RENAME INDEX idx_ee9befaf395db7b TO idx_occurrence_expense');
    }
}
