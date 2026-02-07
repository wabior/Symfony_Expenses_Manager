<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207105647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optimized composite indexes for expense occurrences';
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
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY FK_EE9BEFAF395DB7B');
        $this->addSql('ALTER TABLE expense_occurrence DROP FOREIGN KEY FK_EE9BEFAA76ED395');
        $this->addSql('ALTER TABLE expense_occurrence DROP amount');
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT `fk_occurrence_expense` FOREIGN KEY (expense_id) REFERENCES expense (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expense_occurrence ADD CONSTRAINT `fk_occurrence_user` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_occurrence_user_status_date ON expense_occurrence (user_id, payment_status, occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_user_date ON expense_occurrence (user_id, occurrence_date)');
        $this->addSql('CREATE INDEX idx_occurrence_expense_date ON expense_occurrence (expense_id, occurrence_date)');
        $this->addSql('ALTER TABLE expense_occurrence RENAME INDEX idx_ee9befaa76ed395 TO idx_occurrence_user');
        $this->addSql('ALTER TABLE expense_occurrence RENAME INDEX idx_ee9befaf395db7b TO idx_occurrence_expense');
    }
}
