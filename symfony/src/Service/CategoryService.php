<?php

namespace App\Service;

use App\Entity\Category;

class CategoryService extends BaseUserService
{
    public const DEFAULT_CATEGORY_NAME = 'Bez kategorii';

    public function getAllCategories(): array
    {
        return $this->findByUser(Category::class);
    }

    /**
     * Zwraca domyślną kategorię użytkownika ("Bez kategorii"), tworząc ją jeśli nie istnieje.
     */
    public function getOrCreateDefaultCategory(): Category
    {
        /** @var Category|null $existing */
        $existing = $this->findOneByUser(Category::class, ['name' => self::DEFAULT_CATEGORY_NAME]);

        if ($existing instanceof Category) {
            return $existing;
        }

        /** @var Category $category */
        $category = $this->createEntityWithUser(Category::class);
        $category->setName(self::DEFAULT_CATEGORY_NAME);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
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
