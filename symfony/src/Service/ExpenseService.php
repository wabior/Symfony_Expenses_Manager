<?php

namespace App\Service;

use App\Entity\Expense;
use App\Entity\ExpenseOccurrence;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpenseService extends BaseUserService
{
    public function getAllExpenses(): array
    {
        return $this->findByUser(Expense::class);
    }

    public function getExpenseById(int $id): ?Expense
    {
        return $this->findOneByUser(Expense::class, ['id' => $id]);
    }

    public function getExpensesByMonth(int $year, int $month): array
    {
        $user = $this->requireAuthenticatedUser();
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month');

        return $this->entityManager->getRepository(Expense::class)->findByMonth($startDate, $endDate, $user);
    }

    public function addExpense(Request $request): void
    {
        error_log('ExpenseService::addExpense() called');
        
        // Basic validation
        $name = trim($request->request->get('name', ''));
        $amount = $request->request->get('amount');
        $date = $request->request->get('date');
        $paymentStatus = $request->request->get('paymentStatus');
        $categoryId = $request->request->get('category');
        $recurringFrequency = (int) $request->request->get('recurringFrequency', 0);

        error_log('Expense data: name=' . $name . ', amount=' . $amount . ', date=' . $date . ', categoryId=' . $categoryId . ' (type: ' . gettype($categoryId) . ')');
        error_log('All request data: ' . json_encode($request->request->all()));

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
            error_log('Category validation failed - categoryId is empty');
            throw new \InvalidArgumentException('Category is required');
        }

        if ($recurringFrequency < 0 || $recurringFrequency > 12) {
            throw new \InvalidArgumentException('Recurring frequency must be between 0 and 12');
        }

        $expense = $this->createEntityWithUser(Expense::class);
        $expense->setName($name);
        $expense->setAmount($amount);
        $expense->setDate(new \DateTime($date));
        $expense->setPaymentStatus($paymentStatus);
        $expense->setRecurringFrequency($recurringFrequency);

        if ($paymentDate = $request->request->get('paymentDate')) {
            $expense->setPaymentDate(new \DateTime($paymentDate));
        }

        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            error_log('Category not found for ID: ' . $categoryId);
            throw new \InvalidArgumentException('Invalid category selected');
        }
        $expense->setCategory($category);

        error_log('About to persist expense');
        $this->entityManager->persist($expense);
        $this->entityManager->flush();
        error_log('Expense persisted with ID: ' . $expense->getId());

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
        $expense = $this->getExpenseById($id);

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

    public function updateExpense(Request $request, int $id): void
    {
        $expense = $this->getExpenseById($id);

        if (!$expense) {
            throw new \Exception('Expense not found or access denied');
        }

        $data = $request->request->all();
        $oldRecurringFrequency = $expense->getRecurringFrequency();
        $newRecurringFrequency = (int) $data['recurringFrequency'];

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
        $expense->setRecurringFrequency($newRecurringFrequency);

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

        // Zarządzanie wystąpieniami przy zmianie cyklu
        if ($oldRecurringFrequency === 0 && $newRecurringFrequency > 0) {
            // Zmiana z niecyklicznego na cykliczny - utwórz wystąpienie
            $expenseDate = new \DateTime($data['date']);
            $occurrence = $this->createExpenseOccurrence($expense, $expenseDate);
            $this->entityManager->persist($occurrence);
        } elseif ($oldRecurringFrequency > 0 && $newRecurringFrequency === 0) {
            // Zmiana z cyklicznego na niecykliczny - usuń wystąpienia
            $this->deleteExpenseOccurrences($expense);
        }

        $this->entityManager->flush();
    }

    public function deleteExpense(int $id): void
    {
        $expense = $this->getExpenseById($id);

        if (!$expense) {
            throw new \Exception('Expense not found or access denied');
        }

        // Usuń wystąpienia jeśli wydatek jest cykliczny
        if ($expense->getRecurringFrequency() > 0) {
            $this->deleteExpenseOccurrences($expense);
        }

        $this->entityManager->remove($expense);
        $this->entityManager->flush();
    }

    /**
     * Usuwa wszystkie wystąpienia dla danego wydatku
     */
    private function deleteExpenseOccurrences(Expense $expense): void
    {
        $occurrences = $this->entityManager->getRepository(ExpenseOccurrence::class)
            ->findBy(['expense' => $expense]);

        foreach ($occurrences as $occurrence) {
            $this->entityManager->remove($occurrence);
        }
    }

    public function getAllCategories(): array
    {
        return $this->findByUser(Category::class);
    }

    public function getCategoriesForSelect(): array
    {
        $categories = $this->getAllCategories();
        $categoryOptions = [];
        
        foreach ($categories as $category) {
            $categoryOptions[$category->getId()] = $category->getName();
        }
        
        return $categoryOptions;
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
        $user = $this->requireAuthenticatedUser();

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

        $user = $this->requireAuthenticatedUser();
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
        
        if (!$occurrence) {
            throw new \Exception('Expense occurrence not found');
        }

        $this->ensureEntityBelongsToUser($occurrence->getExpense());

        $occurrence->setPaymentStatus($status);
        if ($paymentDate && $status !== 'unpaid') {
            $occurrence->setPaymentDate($paymentDate);
        } elseif ($status === 'unpaid') {
            $occurrence->setPaymentDate(null);
        }

        $this->entityManager->flush();
    }
}
