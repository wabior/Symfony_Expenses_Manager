<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Menu;

class MenuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Definiowanie pozycji menu
        $menus = [
            [
                'routeName' => 'home',
                'friendlyName' => 'Home',
                'path' => '/',
                'order' => 1,
                'activated' => true,
            ],
            [
                'routeName' => 'expenses',
                'friendlyName' => 'Expenses',
                'path' => '/expenses',
                'order' => 2,
                'activated' => true,
            ],
            [
                'routeName' => 'categories',
                'friendlyName' => 'Categories',
                'path' => '/categories',
                'order' => 3,
                'activated' => true,
            ],
            [
                'routeName' => 'admin_menu',
                'friendlyName' => 'Menu setup',
                'path' => '/admin/menu',
                'order' => 4,
                'activated' => true,
            ],
            [
                'routeName' => 'about',
                'friendlyName' => 'About',
                'path' => '/about',
                'order' => 5,
                'activated' => true,
            ],
        ];

        foreach ($menus as $menuData) {
            $menu = new Menu();
            $menu->setRouteName($menuData['routeName']);
            $menu->setFriendlyName($menuData['friendlyName']);
            $menu->setPath($menuData['path']);
            $menu->setOrder($menuData['order']);
            $menu->setActivated($menuData['activated']);
            $manager->persist($menu);
        }

        $manager->flush();
    }
}
