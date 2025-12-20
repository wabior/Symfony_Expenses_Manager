<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * User fixtures for development and performance testing.
 *
 * TODO: Remove this file when user registration is implemented.
 * These users are provided for testing purposes only.
 *
 * Test users:
 * - admin@example.com / admin123 (ROLE_ADMIN, ROLE_USER)
 * - user@example.com / user123 (ROLE_USER)
 */
class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));

        $manager->persist($admin);

        // Create regular user
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(null); // NULL means default roles will be applied
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));

        $manager->persist($user);

        $manager->flush();
    }
}