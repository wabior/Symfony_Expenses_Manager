<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create basic database schema with categories, expenses, users and menu tables
 */
final class Version20250101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create basic database schema with categories, expenses, users and menu tables';
    }

    public function up(Schema $schema): void
    {
        // Create category table
        $this->addSql('CREATE TABLE category (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            name_polish VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create user table for authentication
        $this->addSql('CREATE TABLE user (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create expense table with all fields
        $this->addSql('CREATE TABLE expense (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            date DATE NOT NULL,
            payment_date DATE DEFAULT NULL,
            payment_status VARCHAR(20) DEFAULT \'unpaid\' NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            category_id INT NOT NULL,
            user_id INT NOT NULL,
            recurring_frequency INT DEFAULT 0 NOT NULL COMMENT \'0=jednorazowy, 1-12=miesięczny cykl\',
            INDEX IDX_2D3A8DA612469DE2 (category_id),
            INDEX IDX_2D3A8DA6A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create menu table for navigation
        $this->addSql('CREATE TABLE menu (
            id INT AUTO_INCREMENT NOT NULL,
            route_name VARCHAR(255) NOT NULL,
            friendly_name VARCHAR(255) NOT NULL,
            path VARCHAR(255) NOT NULL,
            `order` INT NOT NULL,
            activated TINYINT(1) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints first
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA6A76ED395');
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA612469DE2');

        // Drop tables in reverse order
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE expense');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE category');
    }
}