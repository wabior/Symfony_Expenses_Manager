<?php

namespace App\Service;

use App\Entity\Expense;
use App\Entity\ExpenseOccurrence;
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

    public function getCurrentUser(): User
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


    public function getExpenseById(int $id): ?Expense
    {
        $user = $this->getCurrentUser();

        $expense = $this->entityManager->getRepository(Expense::class)->findOneBy([
            'id' => $id,
            'user' => $user
        ]);

        return $expense;
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
        $recurringFrequency = (int) $request->request->get('recurringFrequency', 0);

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

        if ($recurringFrequency < 0 || $recurringFrequency > 12) {
            throw new \InvalidArgumentException('Recurring frequency must be between 0 and 12');
        }

        $expense = new Expense();
        $expense->setName($name);
        $expense->setAmount($amount);
        $expense->setDate(new \DateTime($date));
        $expense->setPaymentStatus($paymentStatus);
        $expense->setRecurringFrequency($recurringFrequency);
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

        // Dla wydatków cyklicznych utwórz wystąpienie w miesiącu dodania
        if ($recurringFrequency > 0) {
            $expenseDate = new \DateTime($date);
            $occurrence = $this->createExpenseOccurrence($expense, $expenseDate);
            $this->entityManager->persist($occurrence);
            $this->entityManager->flush();
        }
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

    public function updateExpense(Request $request, int $id): void
    {
        $expense = $this->entityManager->find(Expense::class, $id);
        $user = $this->getCurrentUser();

        if (!$expense || $expense->getUser() !== $user) {
            throw new \Exception('Expense not found or access denied');
        }

        $data = $request->request->all();

        // Walidacja
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name required');
        }

        if (empty($data['amount']) || !is_numeric($data['amount'])) {
            throw new \InvalidArgumentException('Amount required');
        }

        // Aktualizacja pól
        $expense->setName($data['name']);
        $expense->setAmount($data['amount']);
        $expense->setDate(new \DateTime($data['date']));
        $expense->setRecurringFrequency((int) $data['recurringFrequency']);

        // Kategoria
        $category = $this->entityManager->getRepository(Category::class)->find($data['category']);

        if (!$category) {
            throw new \InvalidArgumentException('Invalid category selected');
        }

        $expense->setCategory($category);

        // Data płatności
        if (!empty($data['paymentDate'])) {
            $expense->setPaymentDate(new \DateTime($data['paymentDate']));
        }

        $this->entityManager->flush();
    }

    public function deleteExpense(int $id): void
    {
        $expense = $this->entityManager->find(Expense::class, $id);
        $user = $this->getCurrentUser();

        if (!$expense || $expense->getUser() !== $user) {
            throw new \Exception('Expense not found or access denied');
        }

        $this->entityManager->remove($expense);
        $this->entityManager->flush();
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

    /**
     * Tworzy wystąpienie wydatku dla konkretnej daty
     */
    public function createExpenseOccurrence(Expense $expense, \DateTimeInterface $date): ExpenseOccurrence
    {
        $occurrence = new ExpenseOccurrence();
        $occurrence->setExpense($expense);
        $occurrence->setUser($expense->getUser());
        $occurrence->setOccurrenceDate($date);
        $occurrence->setPaymentStatus('unpaid');

        return $occurrence;
    }

    /**
     * Pobiera wystąpienia wydatków i bezpośrednie wydatki niecykliczne dla danego miesiąca
     */
    public function getExpenseOccurrencesByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month -1 day');
        $user = $this->getCurrentUser();

        // Pobierz wystąpienia wydatków cyklicznych
        $occurrences = $this->entityManager->getRepository(ExpenseOccurrence::class)
            ->findWithExpenseData($startDate, $endDate, $user);

        // Pobierz wydatki niecykliczne dla tego miesiąca
        $nonRecurringExpenses = $this->entityManager->getRepository(Expense::class)
            ->findByMonth($startDate, $endDate, $user);

        // Filtruj tylko wydatki niecykliczne (bez wystąpień)
        $nonRecurringWithoutOccurrences = [];
        foreach ($nonRecurringExpenses as $expense) {
            if ($expense->getRecurringFrequency() === 0) {
                $nonRecurringWithoutOccurrences[] = $expense;
            }
        }

        // Połącz wyniki - wystąpienia + wydatki niecykliczne
        return array_merge($occurrences, $nonRecurringWithoutOccurrences);
    }

    /**
     * Tworzy wystąpienia dla wydatków cyklicznych w podanym miesiącu
     */
    public function createOccurrencesForMonth(int $year, int $month): array
    {
        $monthStart = new \DateTime("$year-$month-01");
        $monthEnd = (clone $monthStart)->modify('+1 month -1 day');

        $user = $this->getCurrentUser();
        $recurringExpenses = $this->entityManager->getRepository(Expense::class)
            ->findRecurringExpenses($user);

        $createdOccurrences = [];

        foreach ($recurringExpenses as $expense) {
            // Sprawdź czy wydatek powinien wystąpić w tym miesiącu zgodnie z cyklem
            $expenseCreationDate = $expense->getDate();
            $creationYear = (int) $expenseCreationDate->format('Y');
            $creationMonth = (int) $expenseCreationDate->format('n');

            if ($this->shouldExpenseOccurInMonth($expense, $creationYear, $creationMonth, $year, $month)) {
                // Sprawdź czy wystąpienie już istnieje dla tego wydatku w tym miesiącu
                $existing = $this->entityManager->getRepository(ExpenseOccurrence::class)
                    ->findByExpenseAndDateRange($expense, $monthStart, $monthEnd);

                if (empty($existing)) {
                    $occurrence = $this->createExpenseOccurrence($expense, $monthStart);
                    $this->entityManager->persist($occurrence);
                    $createdOccurrences[] = $occurrence;
                }
            }
        }

        $this->entityManager->flush();

        return $createdOccurrences;
    }

    /**
     * Sprawdza czy wydatek cykliczny powinien wystąpić w danym miesiącu
     */
    private function shouldExpenseOccurInMonth(Expense $expense, int $fromYear, int $fromMonth, int $toYear, int $toMonth): bool
    {
        $frequency = $expense->getRecurringFrequency();

        if ($frequency <= 0) {
            return false;
        }

        $fromDate = new \DateTime("$fromYear-$fromMonth-01");
        $toDate = new \DateTime("$toYear-$toMonth-01");

        $monthsDiff = ($toDate->format('Y') - $fromDate->format('Y')) * 12 +
            ($toDate->format('n') - $fromDate->format('n'));

        return $monthsDiff % $frequency === 0;
    }

    /**
     * Aktualizuje status płatności wystąpienia wydatku
     */
    public function updateOccurrencePaymentStatus(int $occurrenceId, string $status, ?\DateTimeInterface $paymentDate = null): void
    {
        $occurrence = $this->entityManager->find(ExpenseOccurrence::class, $occurrenceId);
        $user = $this->getCurrentUser();

        if (!$occurrence || $occurrence->getExpense()->getUser() !== $user) {
            throw new \Exception('Expense occurrence not found or access denied');
        }

        $occurrence->setPaymentStatus($status);
        if ($paymentDate && $status !== 'unpaid') {
            $occurrence->setPaymentDate($paymentDate);
        } elseif ($status === 'unpaid') {
            $occurrence->setPaymentDate(null);
        }

        $this->entityManager->flush();
    }

}
