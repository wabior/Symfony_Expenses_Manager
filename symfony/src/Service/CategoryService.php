<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAllCategories(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    public function addCategory(string $nameEnglish, string $namePolish): void
    {
        $category = new Category();
        $category->setNameEnglish($nameEnglish);
        $category->setNamePolish($namePolish);

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
}
