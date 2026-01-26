<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606170015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fill menu table with initial routes';
    }

    public function up(Schema $schema): void
    {
        // Ensure the menu table exists
        $this->addSql("CREATE TABLE IF NOT EXISTS menu (
            id INT AUTO_INCREMENT NOT NULL,
            route_name VARCHAR(255) NOT NULL,
            friendly_name VARCHAR(255) NOT NULL,
            path VARCHAR(255) NOT NULL,
            `order` INT NOT NULL,
            activated TINYINT(1) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        // Insert initial data into the menu table
        $this->addSql("INSERT INTO menu (route_name, friendly_name, path, `order`, activated) VALUES
            ('home', 'Start', '/', 1, 1),
            ('expenses', 'Wydatki', '/expenses', 2, 1),
            ('categories', 'Kategorie', '/categories', 3, 1),
            ('about', 'O nas', '/about', 5, 1),
            ('admin', 'admin', '/admin/menu', 4, 1)
            "
        );
    }

    public function down(Schema $schema): void
    {
        // Remove data and drop the menu table if migration is rolled back
        $this->addSql("DELETE FROM menu WHERE route_name IN ('home', 'expenses', 'categories', 'about', 'admin')");
        $this->addSql("DROP TABLE menu");
    }
}
