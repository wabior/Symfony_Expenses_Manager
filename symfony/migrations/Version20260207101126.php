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
        $table = $schema->getTable('expense_occurrence');
        
        // Add amount column only if it doesn't exist
        if (!$table->hasColumn('amount')) {
            $table->addColumn('amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'notnull' => true,
                'default' => '0.00'
            ]);
        }
        
        // Update existing columns using raw SQL (proper way for migrations)
        $this->addSql('ALTER TABLE expense CHANGE recurring_frequency recurring_frequency INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE expense_occurrence CHANGE payment_status payment_status VARCHAR(20) DEFAULT \'unpaid\' NOT NULL');
        $this->addSql('ALTER TABLE expense_occurrence CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        
        // Create indexes only if they don't exist
        if (!$table->hasIndex('idx_occurrence_user_expense')) {
            $table->addIndex(['user_id', 'expense_id'], 'idx_occurrence_user_expense');
        }
        if (!$table->hasIndex('idx_occurrence_user_date')) {
            $table->addIndex(['user_id', 'occurrence_date'], 'idx_occurrence_user_date');
        }
        if (!$table->hasIndex('idx_occurrence_expense_date')) {
            $table->addIndex(['expense_id', 'occurrence_date'], 'idx_occurrence_expense_date');
        }
        if (!$table->hasIndex('idx_occurrence_user_status_date')) {
            $table->addIndex(['user_id', 'payment_status', 'occurrence_date'], 'idx_occurrence_user_status_date');
        }
        if (!$table->hasIndex('idx_occurrence_user_expense_date')) {
            $table->addIndex(['user_id', 'expense_id', 'occurrence_date'], 'idx_occurrence_user_expense_date');
        }
        
        $expenseTable = $schema->getTable('expense');
        if (!$expenseTable->hasIndex('idx_expense_category_amount')) {
            $expenseTable->addIndex(['category_id', 'amount'], 'idx_expense_category_amount');
        }
        
        // Add foreign keys only if they don't exist
        if (!$table->hasForeignKey('fk_occurrence_expense')) {
            $table->addForeignKeyConstraint('expense', ['expense_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_occurrence_expense');
        }
        if (!$table->hasForeignKey('fk_occurrence_user')) {
            $table->addForeignKeyConstraint('user', ['user_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_occurrence_user');
        }
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
