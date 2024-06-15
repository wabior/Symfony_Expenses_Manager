<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Expense;
use App\Entity\Category;
use Faker\Factory;

class ExpenseFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Pobranie kategorii z bazy danych
        $categories = $manager->getRepository(Category::class)->findAll();

        // Upewnij się, że są dostępne kategorie
        if (empty($categories)) {
            throw new \Exception('No categories found. Please load CategoryFixtures first.');
        }

        // Definiowanie większej liczby nazw wydatków dla różnych kategorii
        $expenseNames = [
            'Jedzenie' => ['Groceries', 'Restaurant', 'Snacks', 'Beverages', 'Lunch', 'Dinner', 'Breakfast', 'Fast Food', 'Catering', 'Bakery'],
            'Czynsz' => ['Monthly Rent', 'Lease Payment', 'Security Deposit', 'Rent Insurance'],
            'Media' => ['Electricity Bill', 'Water Bill', 'Gas Bill', 'Internet Bill', 'Cable TV Bill', 'Phone Bill', 'Garbage Collection'],
            'Rozrywka' => ['Movie Tickets', 'Concert', 'Games', 'Streaming Subscription', 'Museum', 'Amusement Park', 'Sports Event', 'Theater'],
            'Podróże' => ['Flight Tickets', 'Hotel Booking', 'Taxi', 'Fuel', 'Car Rental', 'Bus Ticket', 'Train Ticket', 'Travel Insurance'],
            'Zdrowie' => ['Doctor Visit', 'Medication', 'Health Insurance', 'Dental Care', 'Eye Care', 'Hospital Bill', 'Fitness Membership', 'Therapy'],
            'Edukacja' => ['Books', 'Online Course', 'School Fees', 'Stationery', 'Tuition', 'Workshop', 'Seminar', 'College Fees'],
            'Zakupy' => ['Clothing', 'Electronics', 'Groceries', 'Furniture', 'Home Decor', 'Toys', 'Beauty Products', 'Accessories', 'Books', 'Pet Supplies'],
            'Inne' => ['Miscellaneous', 'Gift', 'Donation', 'Charity', 'Event', 'Subscription', 'Membership Fee', 'Bank Fee', 'Late Fee', 'Other']
        ];

        // Dodanie 100 wydatków
        for ($i = 0; $i < 100; $i++) {
            $category = $faker->randomElement($categories);
            $expense = new Expense();
            $expense->setName($faker->randomElement($expenseNames[$category->getNamePolish()]));
            $expense->setAmount($faker->randomFloat(2, 10, 1000));
            $expense->setDate($faker->dateTimeBetween('-6 months', 'now'));
            $expense->setPaymentDate($faker->optional()->dateTimeBetween('-6 months', 'now'));
            $expense->setPaymentStatus($faker->randomElement(['unpaid', 'paid', 'partially_paid']));
            $expense->setCategory($category);
            $manager->persist($expense);
        }

        $manager->flush();
    }
}
