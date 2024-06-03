<?php

namespace App\Service;

use App\Entity\Expense;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;

class ExpensesService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAllExpenses(): array
    {
        return $this->entityManager->getRepository(Expense::class)->findAll();
    }

    public function addExpense(Request $request): void
    {
        $name = $request->request->get('name');
        $amount = $request->request->get('amount');
        $date = $request->request->get('date');
        $category = $this->entityManager->getRepository(Category::class)->find($request->request->get('category'));

        $expense = new Expense();
        $expense->setName($name);
        $expense->setAmount($amount);
        $expense->setDate(new \DateTime($date));
        $expense->setCategory($category);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();
    }

    public function getAllCategories(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }
}
