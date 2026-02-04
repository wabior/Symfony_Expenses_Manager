<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Populate menu table with initial navigation data
 */
final class Version20250101000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate menu table with initial navigation data';
    }

    public function up(Schema $schema): void
    {
        // Insert initial menu data
        $this->addSql("INSERT INTO menu (route_name, friendly_name, path, `order`, activated) VALUES
            ('home', 'Start', '/', 1, 1),
            ('expenses', 'Wydatki', '/expenses', 2, 1),
            ('categories', 'Kategorie', '/categories', 3, 1),
            ('admin_menu', 'Menu setup', '/admin/menu', 4, 1),
            ('about', 'O nas', '/about', 5, 1)
        ");
    }

    public function down(Schema $schema): void
    {
        // Remove all menu entries
        $this->addSql("DELETE FROM menu WHERE route_name IN ('home', 'expenses', 'categories', 'admin_menu', 'about')");
    }
}