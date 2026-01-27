<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Simplify category table - remove name_english column and rename name_polish to name
 */
final class Version20250127100400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Simplify category table - remove name_english column and rename name_polish to name';
    }

    public function up(Schema $schema): void
    {
        // Drop name_english column
        $this->addSql('ALTER TABLE category DROP COLUMN name_english');
        
        // Rename name_polish to name
        $this->addSql('ALTER TABLE category CHANGE name_polish name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Add back name_english column
        $this->addSql('ALTER TABLE category ADD name_english VARCHAR(255) NOT NULL');
        
        // Rename name back to name_polish
        $this->addSql('ALTER TABLE category CHANGE name name_polish VARCHAR(255) NOT NULL');
    }
}
