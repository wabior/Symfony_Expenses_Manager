<?php

namespace App\Service;

use App\Entity\Expense;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;

class ExpenseService
{
    private EntityManagerInterface $entityManager;
    private \Symfony\Bundle\SecurityBundle\Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    private function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException('User must be authenticated');
        }
        return $user;
    }

    public function getAllExpenses(): array
    {
        $user = $this->getCurrentUser();
        return $this->entityManager->getRepository(Expense::class)->findByUser($user);
    }

    public function getExpensesByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month');
        $user = $this->getCurrentUser();

        return $this->entityManager->getRepository(Expense::class)->findByMonth($startDate, $endDate, $user);
    }

    public function addExpense(Request $request): void
    {
        // Basic validation
        $name = trim($request->request->get('name', ''));
        $amount = $request->request->get('amount');
        $date = $request->request->get('date');
        $paymentStatus = $request->request->get('paymentStatus');
        $categoryId = $request->request->get('category');

        if (empty($name)) {
            throw new \InvalidArgumentException('Expense name is required');
        }

        if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            throw new \InvalidArgumentException('Valid amount is required');
        }

        if (empty($date)) {
            throw new \InvalidArgumentException('Date is required');
        }

        if (empty($paymentStatus) || !in_array($paymentStatus, ['unpaid', 'paid', 'partially_paid'])) {
            throw new \InvalidArgumentException('Valid payment status is required');
        }

        if (empty($categoryId)) {
            throw new \InvalidArgumentException('Category is required');
        }

        $expense = new Expense();
        $expense->setName($name);
        $expense->setAmount($amount);
        $expense->setDate(new \DateTime($date));
        $expense->setPaymentStatus($paymentStatus);
        $expense->setUser($this->getCurrentUser());

        if ($paymentDate = $request->request->get('paymentDate')) {
            $expense->setPaymentDate(new \DateTime($paymentDate));
        }

        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            throw new \InvalidArgumentException('Invalid category selected');
        }
        $expense->setCategory($category);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();
    }

    public function updateExpenseStatus(int $id, string $status): ?Expense
    {
        $expense = $this->entityManager->getRepository(Expense::class)->find($id);
        $user = $this->getCurrentUser();

        if ($expense && $expense->getUser() === $user) {
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
