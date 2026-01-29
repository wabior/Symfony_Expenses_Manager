<?php

namespace App\Service;

use App\Entity\Category;

class CategoryService extends BaseUserService
{
    public function getAllCategories(): array
    {
        return $this->findByUser(Category::class);
    }

    public function addCategory(string $name): void
    {
        $category = $this->createEntityWithUser(Category::class);
        $category->setName($name);

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    public function hasCategories(): bool
    {
        return $this->countByUser(Category::class) > 0;
    }
}
