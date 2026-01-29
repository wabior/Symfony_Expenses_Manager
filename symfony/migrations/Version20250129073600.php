<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129073600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id to Category entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_64C19C1A76ED395 ON category (user_id)');
        
        // Update existing categories to have a default user (you may need to adjust this)
        $this->addSql('UPDATE category SET user_id = (SELECT MIN(id) FROM user) WHERE user_id IS NULL');
        
        // Now make the column NOT NULL
        $this->addSql('ALTER TABLE category MODIFY user_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1A76ED395');
        $this->addSql('DROP INDEX IDX_64C19C1A76ED395 ON category');
        $this->addSql('ALTER TABLE category DROP user_id');
    }
}
