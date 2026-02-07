<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Clean up indexes from old migrations
 */
final class Version20250205000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean up indexes from old migrations';
    }

    public function up(Schema $schema): void
    {
        // Drop indexes if they exist
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_date ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_expense ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_user ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_status ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_occurrence_user_date ON expense_occurrence');
        $this->addSql('DROP INDEX IF EXISTS idx_expense_recurring ON expense');
    }

    public function down(Schema $schema): void
    {
        // Recreate indexes
        $this->addSql('CREATE INDEX idx_occurrence_date ON expense_occurrence(occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_expense ON expense_occurrence(expense_id)');
        $this->addSql('CREATE INDEX idx_occurrence_user ON expense_occurrence(user_id)');
        $this->addSql('CREATE INDEX idx_occurrence_status ON expense_occurrence(payment_status)');
        $this->addSql('CREATE INDEX idx_occurrence_user_date ON expense_occurrence(user_id, occurrence_date)');
        $this->addSql('CREATE INDEX idx_expense_recurring ON expense(recurring_frequency)');
    }
}
