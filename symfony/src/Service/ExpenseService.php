<?php

namespace App\Service;

use App\Entity\Expense;
use App\Entity\ExpenseOccurrence;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExpenseService extends BaseUserService
{
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        private readonly CategoryService $categoryService
    ) {
        parent::__construct($entityManager, $tokenStorage);
    }

    public function getDefaultCategoryId(): int
    {
        return $this->categoryService->getOrCreateDefaultCategory()->getId();
    }

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

        // Utwórz wystąpienie dla wydatku w miesiącu dodania
        $expenseDate = new \DateTime($date);
        $occurrence = $this->createExpenseOccurrence($expense, $expenseDate);
        $this->entityManager->persist($occurrence);
        $this->entityManager->flush();
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

    public function deleteExpense(int $occurrenceId): void
    {
        $occurrence = $this->entityManager->find(ExpenseOccurrence::class, $occurrenceId);

        if (!$occurrence) {
            throw new \Exception('Expense occurrence not found');
        }

        $this->ensureEntityBelongsToUser($occurrence->getExpense());

        $expense = $occurrence->getExpense();

        // Usuń wystąpienie
        $this->entityManager->remove($occurrence);
        $this->entityManager->flush(); // Flush po usunięciu wystąpienia

        // Sprawdź czy wydatek ma jeszcze jakieś wystąpienia
        $remainingOccurrences = $this->entityManager->getRepository(ExpenseOccurrence::class)
            ->findBy(['expense' => $expense]);

        // Jeśli wydatek nie ma już żadnych wystąpień, usuń także wydatek
        if (empty($remainingOccurrences)) {
            $this->entityManager->remove($expense);
        }

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

    /**
     * Czyści sieroty - wydatki bez żadnych wystąpień
     */
    public function cleanupOrphanedExpenses(): int
    {
        $user = $this->requireAuthenticatedUser();
        
        // Znajdź wszystkie wydatki użytkownika
        $allExpenses = $this->findByUser(Expense::class);
        $removedCount = 0;

        foreach ($allExpenses as $expense) {
            // Sprawdź czy wydatek ma jakieś wystąpienia
            $occurrences = $this->entityManager->getRepository(ExpenseOccurrence::class)
                ->findBy(['expense' => $expense]);

            if (empty($occurrences)) {
                // Wydatek nie ma wystąpień - usuń go
                $this->entityManager->remove($expense);
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            $this->entityManager->flush();
        }

        return $removedCount;
    }

    public function getAllCategories(): array
    {
        return $this->findByUser(Category::class);
    }

    public function getCategoriesForSelect(): array
    {
        $defaultCategory = $this->categoryService->getOrCreateDefaultCategory();
        $categories = $this->getAllCategories();
        $categoryOptions = [];

        // Domyślna kategoria zawsze jako pierwsza opcja
        $categoryOptions[$defaultCategory->getId()] = $defaultCategory->getName();
        
        foreach ($categories as $category) {
            if ($category->getId() === $defaultCategory->getId()) {
                continue;
            }
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
        $occurrence->setAmount($expense->getAmount());

        return $occurrence;
    }

    /**
     * Pobiera wystąpienia wydatków dla danego miesiąca
     */
    public function getExpenseOccurrencesByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month -1 day');
        $user = $this->requireAuthenticatedUser();

        // Pobierz wszystkie wystąpienia wydatków dla tego miesiąca
        $occurrences = $this->entityManager->getRepository(ExpenseOccurrence::class)
            ->findWithExpenseData($startDate, $endDate, $user);

        return $occurrences;
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
     * Aktualizuje kwotę wszystkich wystąpień wydatku od podanej daty wzwyż
     */
    public function updateOccurrencesFromDate(Expense $expense, \DateTimeInterface $fromDate, string $newAmount): void
    {
        $occurrences = $this->entityManager->getRepository(ExpenseOccurrence::class)
            ->findByExpenseAndDateRange($expense, $fromDate, new \DateTime('2100-01-01')); // Future date to get all occurrences from fromDate

        foreach ($occurrences as $occurrence) {
            $occurrence->setAmount($newAmount);
        }

        $this->entityManager->flush();
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
