<?php

namespace App\Service;

use App\Entity\Expense;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;

class ExpenseService
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

    public function getExpensesByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month');

        return $this->entityManager->getRepository(Expense::class)->findByMonth($startDate, $endDate);
    }

    public function addExpense(Request $request): void
    {
        $expense = new Expense();
        $expense->setName($request->request->get('name'));
        $expense->setAmount($request->request->get('amount'));
        $expense->setDate(new \DateTime($request->request->get('date')));
        $expense->setPaymentStatus($request->request->get('paymentStatus'));

        if ($paymentDate = $request->request->get('paymentDate')) {
            $expense->setPaymentDate(new \DateTime($paymentDate));
        }

        if ($categoryId = $request->request->get('category')) {
            $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
            if ($category) {
                $expense->setCategory($category);
            }
        }

        $this->entityManager->persist($expense);
        $this->entityManager->flush();
    }

    public function updateExpenseStatus(int $id, string $status): ?Expense
    {
        $expense = $this->entityManager->getRepository(Expense::class)->find($id);

        if ($expense) {
            $expense->setPaymentStatus($status);

            if ($status !== 'unpaid') {
                $expense->setPaymentDate(new \DateTime());
            } else {
                $expense->setPaymentDate(null);
            }

            $this->entityManager->flush();

            return $expense;
        }

        return null;
    }


    public function getAllCategories(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    public function getNavigationMonths(int $year, int $month): array
    {
        $prevMonth = ($month == 1) ? 12 : $month - 1;
        $prevYear = ($month == 1) ? $year - 1 : $year;
        $nextMonth = ($month == 12) ? 1 : $month + 1;
        $nextYear = ($month == 12) ? $year + 1 : $year;

        return [
            'year' => $year,
            'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
            'prevYear' => $prevYear,
            'prevMonth' => str_pad($prevMonth, 2, '0', STR_PAD_LEFT),
            'nextYear' => $nextYear,
            'nextMonth' => str_pad($nextMonth, 2, '0', STR_PAD_LEFT),
        ];
    }
}
