<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class BaseUserService
{
    protected EntityManagerInterface $entityManager;
    protected TokenStorageInterface $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Pobiera aktualnie zalogowanego użytkownika
     */
    protected function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }
        
        $user = $token->getUser();
        if (!$user instanceof User) {
            return null;
        }
        
        return $user;
    }

    /**
     * Sprawdza czy użytkownik jest zalogowany, rzuca wyjątek jeśli nie
     */
    protected function requireAuthenticatedUser(): User
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            throw new \RuntimeException('Użytkownik musi być zalogowany');
        }
        return $user;
    }

    /**
     * Filtruje wyniki repozytorium po zalogowanym użytkowniku
     */
    protected function findByUser(string $entityClass, array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $user = $this->requireAuthenticatedUser();
        $criteria['user'] = $user;
        
        return $this->entityManager->getRepository($entityClass)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Znajduje jedną encję po użytkowniku i dodatkowych kryteriach
     */
    protected function findOneByUser(string $entityClass, array $criteria = []): ?object
    {
        $user = $this->requireAuthenticatedUser();
        $criteria['user'] = $user;
        
        return $this->entityManager->getRepository($entityClass)->findOneBy($criteria);
    }

    /**
     * Liczy encje po użytkowniku
     */
    protected function countByUser(string $entityClass, array $criteria = []): int
    {
        $user = $this->requireAuthenticatedUser();
        $criteria['user'] = $user;
        
        return $this->entityManager->getRepository($entityClass)->count($criteria);
    }

    /**
     * Tworzy nową encję z przypisanym użytkownikiem
     */
    protected function createEntityWithUser(string $entityClass): object
    {
        $user = $this->requireAuthenticatedUser();
        $entity = new $entityClass();
        
        if (method_exists($entity, 'setUser')) {
            $entity->setUser($user);
        }
        
        return $entity;
    }

    /**
     * Sprawdza czy encja należy do zalogowanego użytkownika
     */
    protected function ensureEntityBelongsToUser(object $entity): void
    {
        if (!method_exists($entity, 'getUser')) {
            return;
        }

        $user = $this->requireAuthenticatedUser();
        if ($entity->getUser() !== $user) {
            throw new \RuntimeException('Brak dostępu do tej encji');
        }
    }
}
