<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207105659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optimized composite indexes for expense occurrences system';
    }

    public function up(Schema $schema): void
    {
        // Optimized composite indexes for query performance
        $this->addSql('CREATE INDEX idx_occurrence_user_expense ON expense_occurrence (user_id, expense_id) COMMENT "Composite index for finding user occurrences by expense"');
        $this->addSql('CREATE INDEX idx_occurrence_user_date ON expense_occurrence (user_id, occurrence_date) COMMENT "Composite index for monthly occurrence queries by user"');
        $this->addSql('CREATE INDEX idx_occurrence_expense_date ON expense_occurrence (expense_id, occurrence_date) COMMENT "Composite index for bulk updates by expense and date"');
        $this->addSql('CREATE INDEX idx_occurrence_user_status_date ON expense_occurrence (user_id, payment_status, occurrence_date) COMMENT "Composite index for filtering paid/unpaid occurrences"');
        $this->addSql('CREATE INDEX idx_occurrence_user_expense_date ON expense_occurrence (user_id, expense_id, occurrence_date) COMMENT "Composite index for complex queries by user, expense and date"');
        
        // Additional index for sorting expenses by category and amount
        $this->addSql('CREATE INDEX idx_expense_category_amount ON expense (category_id, amount) COMMENT "Index for sorting expenses by category and amount"');
    }

    public function down(Schema $schema): void
    {
        // Drop the indexes in reverse order
        $this->addSql('DROP INDEX idx_expense_category_amount ON expense');
        $this->addSql('DROP INDEX idx_occurrence_user_expense_date ON expense_occurrence');
        $this->addSql('DROP INDEX idx_occurrence_user_status_date ON expense_occurrence');
        $this->addSql('DROP INDEX idx_occurrence_expense_date ON expense_occurrence');
        $this->addSql('DROP INDEX idx_occurrence_user_date ON expense_occurrence');
        $this->addSql('DROP INDEX idx_occurrence_user_expense ON expense_occurrence');
    }
}
