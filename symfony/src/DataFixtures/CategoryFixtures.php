<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Food' => 'Jedzenie',
            'Rent' => 'Czynsz',
            'Utilities' => 'Media',
            'Entertainment' => 'Rozrywka',
            'Travel' => 'Podróże',
            'Healthcare' => 'Zdrowie',
            'Education' => 'Edukacja',
            'Shopping' => 'Zakupy',
            'Others' => 'Inne'
        ];

        foreach ($categories as $englishName => $polishName) {
            $category = new Category();
            $category->setNameEnglish($englishName);
            $category->setNamePolish($polishName);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
