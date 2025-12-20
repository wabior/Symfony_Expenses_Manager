# Specyfikacja techniczna: Wydatki cykliczne (Recurring Expenses)

## Wymagania biznesowe

### Opis funkcji
System wydatków cyklicznych umożliwia użytkownikom tworzenie wydatków, które automatycznie powtarzają się w kolejnych okresach czasu. Cykliczność jest definiowana jako liczba miesięcy co ile wydatek się powtarza. Głównym przypadkiem użycia jest tworzenie nowego miesiąca, gdzie wszystkie wydatki cykliczne są automatycznie dodawane do nowego okresu.

### Cykl wydatków
- **0 miesięcy** - wydatek nie powtarza się (standardowy, niecykliczny)
- **1 miesiąc** - wydatek powtarza się co miesiąc
- **2 miesiące** - wydatek powtarza się co 2 miesiące
- **3 miesiące** - wydatek powtarza się co 3 miesiące
- **4 miesiące** - wydatek powtarza się co 4 miesiące
- **6 miesięcy** - wydatek powtarza się co 6 miesięcy
- **12 miesięcy** - wydatek powtarza się co 12 miesięcy (rocznie)

### Główne scenariusze użycia

1. **Definiowanie cykliczności wydatku**
   - Użytkownik dodaje nowy wydatek
   - Wybiera cykl powtarzania z select: 0, 1, 2, 3, 4, 6, 12 miesięcy
   - Jeśli cykl > 0, wydatek jest uznawany za cykliczny

2. **Tworzenie nowego miesiąca**
   - Użytkownik klika przycisk "Utwórz nowy miesiąc"
   - System sprawdza wszystkie wydatki cykliczne (recurring_frequency > 0)
   - Dla każdego wydatku cyklicznego sprawdza czy powinien się powtórzyć w nowym miesiącu
   - Tworzy duplikaty wydatków, które spełniają warunki cyklu

3. **Wyświetlanie wydatków cyklicznych**
   - Wydatki cykliczne mają specjalne oznaczenie w interfejsie
   - Inny kolor/wizualny wyróżnik

## Zmiany w bazie danych

### Nowa tabela `expense_occurrence` (Rekomendowane podejście dla skalowalności)

```sql
-- Tabela definicji wydatków cyklicznych
CREATE TABLE expense (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    recurring_frequency INT DEFAULT 0 NOT NULL COMMENT '0=jednorazowy, 1-12=miesięczny cykl',
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES category(id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- Tabela wystąpień wydatków (unika duplikacji danych)
CREATE TABLE expense_occurrence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_id INT NOT NULL,
    occurrence_date DATE NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'unpaid',
    payment_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expense_id) REFERENCES expense(id) ON DELETE CASCADE,
    UNIQUE KEY unique_expense_date (expense_id, occurrence_date)
);
```

### Indeksy dla wydajności

```sql
-- Indeksy dla tabeli expense
CREATE INDEX idx_expense_recurring ON expense(recurring_frequency);
CREATE INDEX idx_expense_user ON expense(user_id);

-- Indeksy dla tabeli expense_occurrence
CREATE INDEX idx_occurrence_date ON expense_occurrence(occurrence_date);
CREATE INDEX idx_occurrence_expense ON expense_occurrence(expense_id);
CREATE INDEX idx_occurrence_status ON expense_occurrence(payment_status);
```

## Zmiany w encjach

### Expense Entity - definicja wydatku cyklicznego

```php
#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ORM\Table(name: 'expense')]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $userId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $recurringFrequency = 0; // 0=jednorazowy, 1-12=miesięczny cykl

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'expense', targetEntity: ExpenseOccurrence::class, cascade: ['persist', 'remove'])]
    private Collection $occurrences;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    // Gettery i settery...
}

class ExpenseOccurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Expense::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Expense $expense;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $occurrenceDate;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'unpaid'])]
    private string $paymentStatus = 'unpaid';

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    // Gettery i settery...
}
```

### Nowe metody w Expense

```php
public function isRecurring(): bool
{
    return $this->recurringFrequency > 0;
}

public function getRecurringFrequency(): int
{
    return $this->recurringFrequency;
}

public function setRecurringFrequency(int $recurringFrequency): self
{
    $this->recurringFrequency = $recurringFrequency;
    return $this;
}

public function getOccurrences(): Collection
{
    return $this->occurrences;
}

public function addOccurrence(ExpenseOccurrence $occurrence): self
{
    if (!$this->occurrences->contains($occurrence)) {
        $this->occurrences[] = $occurrence;
        $occurrence->setExpense($this);
    }

    return $this;
}
```

### Nowe metody w ExpenseOccurrence

```php
public function getExpense(): Expense
{
    return $this->expense;
}

public function setExpense(Expense $expense): self
{
    $this->expense = $expense;
    return $this;
}

public function getOccurrenceDate(): \DateTimeInterface
{
    return $this->occurrenceDate;
}

public function setOccurrenceDate(\DateTimeInterface $occurrenceDate): self
{
    $this->occurrenceDate = $occurrenceDate;
    return $this;
}

public function getPaymentStatus(): string
{
    return $this->paymentStatus;
}

public function setPaymentStatus(string $paymentStatus): self
{
    $this->paymentStatus = $paymentStatus;
    return $this;
}

public function isPaid(): bool
{
    return $this->paymentStatus === 'paid';
}
```

## Zmiany w serwisach

### ExpenseService - nowe metody

```php
/**
 * Tworzy wystąpienie wydatku dla konkretnej daty
 */
public function createExpenseOccurrence(Expense $expense, \DateTimeInterface $occurrenceDate): ExpenseOccurrence
{
    $occurrence = new ExpenseOccurrence();
    $occurrence->setExpense($expense);
    $occurrence->setOccurrenceDate($occurrenceDate);
    $occurrence->setPaymentStatus('unpaid');
    $occurrence->setPaymentDate(null);

    return $occurrence;
}

/**
 * Pobiera wystąpienia wydatków dla danego miesiąca
 */
public function getExpenseOccurrencesByMonth(int $year, int $month): array
{
    $startDate = new \DateTime("$year-$month-01");
    $endDate = (clone $startDate)->modify('+1 month -1 day');

    return $this->entityManager->getRepository(ExpenseOccurrence::class)
        ->findByDateRange($startDate, $endDate);
}

/**
 * Pobiera nieopłacone wystąpienia wydatków dla danego miesiąca
 */
public function getUnpaidExpenseOccurrences(int $year, int $month): array
{
    $startDate = new \DateTime("$year-$month-01");
    $endDate = (clone $startDate)->modify('+1 month -1 day');

    return $this->entityManager->getRepository(ExpenseOccurrence::class)
        ->findUnpaidByDateRange($startDate, $endDate);
}

/**
 * Tworzy następny miesiąc - dodaje wystąpienia dla wszystkich wydatków cyklicznych
 */
public function createNextMonth(int $year, int $month): array
{
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;

    $nextMonthStart = new \DateTime("$nextYear-$nextMonth-01");
    $nextMonthEnd = (clone $nextMonthStart)->modify('+1 month -1 day');

    // Pobierz wszystkie wydatki cykliczne
    $recurringExpenses = $this->entityManager->getRepository(Expense::class)
        ->findRecurringExpenses();

    $createdOccurrences = [];

    foreach ($recurringExpenses as $expense) {
        // Sprawdź czy wydatek powinien wystąpić w następnym miesiącu
        if ($this->shouldExpenseOccurInMonth($expense, $year, $month, $nextYear, $nextMonth)) {
            // Sprawdź czy wystąpienie już istnieje dla tego wydatku w następnym miesiącu
            $existing = $this->entityManager->getRepository(ExpenseOccurrence::class)
                ->findByExpenseAndDateRange($expense, $nextMonthStart, $nextMonthEnd);

            if (empty($existing)) {
                $occurrence = $this->createExpenseOccurrence($expense, $nextMonthStart);
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
public function shouldExpenseOccurInMonth(Expense $expense, int $fromYear, int $fromMonth, int $toYear, int $toMonth): bool
{
    $frequency = $expense->getRecurringFrequency();

    // Jeśli nie jest cykliczny, nie występuje
    if ($frequency <= 0) {
        return false;
    }

    // Oblicz liczbę miesięcy między oboma okresami
    $fromDate = new \DateTime("$fromYear-$fromMonth-01");
    $toDate = new \DateTime("$toYear-$toMonth-01");

    $monthsDiff = ($toDate->format('Y') - $fromDate->format('Y')) * 12 +
                  ($toDate->format('n') - $fromDate->format('n'));

    // Wydatek występuje jeśli różnica miesięcy jest podzielna przez cykl
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

    $occurrence->setPaymentStatus($status);
    if ($paymentDate) {
        $occurrence->setPaymentDate($paymentDate);
    }

    $this->entityManager->flush();
}
```

## Zmiany w repozytoriach

### ExpenseRepository - nowe metody

```php
/**
 * Znajduje wszystkie wydatki cykliczne (recurring_frequency > 0)
 */
public function findRecurringExpenses(): array
{
    return $this->createQueryBuilder('e')
        ->where('e.recurringFrequency > :frequency')
        ->setParameter('frequency', 0)
        ->getQuery()
        ->getResult();
}

/**
 * Znajduje wydatki cykliczne dla konkretnego użytkownika
 */
public function findRecurringExpensesByUser(int $userId): array
{
    return $this->createQueryBuilder('e')
        ->where('e.recurringFrequency > :frequency')
        ->andWhere('e.userId = :userId')
        ->setParameter('frequency', 0)
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult();
}
```

### ExpenseOccurrenceRepository - nowa klasa

```php
class ExpenseOccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseOccurrence::class);
    }

    /**
     * Znajduje wystąpienia wydatków w przedziale dat
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('eo')
            ->where('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('eo.occurrenceDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje nieopłacone wystąpienia wydatków w przedziale dat
     */
    public function findUnpaidByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('eo')
            ->where('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->andWhere('eo.paymentStatus != :paidStatus')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('paidStatus', 'paid')
            ->orderBy('eo.occurrenceDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje wystąpienia dla konkretnego wydatku w przedziale dat
     */
    public function findByExpenseAndDateRange(Expense $expense, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('eo')
            ->where('eo.expense = :expense')
            ->andWhere('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->setParameter('expense', $expense)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje wystąpienia z pełnymi danymi wydatku (JOIN)
     */
    public function findWithExpenseData(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('eo')
            ->join('eo.expense', 'e')
            ->leftJoin('e.category', 'c')
            ->where('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->select('eo', 'e', 'c')
            ->orderBy('eo.occurrenceDate', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
```

## Zmiany w kontrolerach

### ExpenseController - nowe metody

```php
#[Route('/expenses/create-next-month/{year}/{month}', name: 'expenses_create_next_month', methods: ['POST'])]
public function createNextMonth(int $year, int $month): Response
{
    try {
        $createdOccurrences = $this->expenseService->createNextMonth($year, $month);

        $this->addFlash('success', sprintf('Utworzono %d wystąpień wydatków cyklicznych dla następnego miesiąca', count($createdOccurrences)));

        // Przekieruj do następnego miesiąca
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;

        return $this->redirectToRoute('expenses', ['year' => $nextYear, 'month' => $nextMonth]);
    } catch (\Exception $e) {
        $this->addFlash('error', 'Wystąpił błąd podczas tworzenia nowego miesiąca: ' . $e->getMessage());
        return $this->redirectToRoute('expenses', ['year' => $year, 'month' => $month]);
    }
}

#[Route('/expenses/occurrence/{id}/status', name: 'expenses_update_occurrence_status', methods: ['POST'])]
public function updateOccurrenceStatus(int $id, Request $request): JsonResponse
{
    try {
        $status = $request->request->get('status');
        $paymentDate = $request->request->get('payment_date');

        $paymentDateObj = $paymentDate ? new \DateTime($paymentDate) : null;

        $this->expenseService->updateOccurrencePaymentStatus($id, $status, $paymentDateObj);

        return new JsonResponse(['success' => true]);
    } catch (\Exception $e) {
        return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
    }
}
```

## Zmiany w szablonach

### expenses/index.html.twig - wyświetlanie wystąpień wydatków

```twig
{# Zmienna z wystąpieniami wydatków dla bieżącego miesiąca #}
{% set currentMonthOccurrences = expenseOccurrences %}

{% for occurrence in currentMonthOccurrences %}
    <tr id="occurrence-{{ occurrence.id }}" class="expense-row{% if occurrence.expense.isRecurring %} recurring-expense{% endif %}">
        <td class="table-cell">{{ occurrence.occurrenceDate|date('d.m.Y') }}</td>
        <td class="table-cell">{{ occurrence.expense.name }}</td>
        <td class="table-cell">{{ occurrence.expense.amount|number_format(2, ',', ' ') }} zł</td>
        <td class="table-cell">
            {% if occurrence.expense.category %}
                {{ occurrence.expense.category.namePolish }}
            {% else %}
                Brak kategorii
            {% endif %}
        </td>
        <td class="table-cell status-cell cursor-pointer relative" data-id="{{ occurrence.id }}">
            <span class="status-text hover:text-shadow
                {% if occurrence.paymentStatus == 'unpaid'%} text-red-500 {% endif %}
                {% if occurrence.paymentStatus == 'paid'%} text-green-600 {% endif %}
            ">
                {{ occurrence.paymentStatus|capitalize }}
            </span>
            {% if occurrence.expense.isRecurring %}
                <span class="recurring-indicator" title="Cykl: co {{ occurrence.expense.recurringFrequency }} {% if occurrence.expense.recurringFrequency == 1 %}miesiąc{% else %}miesięcy{% endif %}">🔄{{ occurrence.expense.recurringFrequency }}</span>
            {% endif %}
            <select class="status-select absolute right-2 top-2 p-1 hidden w-full cursor-pointer"
                    data-occurrence-id="{{ occurrence.id }}">
                <option value="unpaid"{% if occurrence.paymentStatus == 'unpaid' %} selected{% endif %}>Nieopłacony</option>
                <option value="paid"{% if occurrence.paymentStatus == 'paid' %} selected{% endif %}>Opłacony</option>
                <option value="partially_paid"{% if occurrence.paymentStatus == 'partially_paid' %} selected{% endif %}>Częściowo opłacony</option>
            </select>
        </td>
    </tr>
{% endfor %}
```

Dodaj przycisk "Utwórz nowy miesiąc":

```twig
<div class="flex justify-between items-center mb-4" aria-label="Month navigation">
    {# ... istniejąca nawigacja ... #}
    <div class="flex space-x-4">
        {# ... istniejące przyciski ... #}
        <a href="{{ path('expenses_create_next_month', { year: year, month: month }) }}"
           class="inline-block w-auto bg-purple-500 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded"
           onclick="return confirm('Czy na pewno chcesz utworzyć nowy miesiąc? Wszystkie wydatki cykliczne zostaną dodane automatycznie.')">
            Utwórz nowy miesiąc
        </a>
    </div>
</div>
```

### expenses/add.html.twig - select dla cyklu wydatków

```twig
<div>
    <label for="recurringFrequency" class="block text-lg font-semibold text-gray-800">Cykl powtarzania:</label>
    <select id="recurringFrequency" name="recurringFrequency" class="mt-2 block w-full px-4 py-3 border-2 border-gray-400 rounded-lg text-lg">
        <option value="0">Nie powtarza się</option>
        <option value="1">Co 1 miesiąc</option>
        <option value="2">Co 2 miesiące</option>
        <option value="3">Co 3 miesiące</option>
        <option value="4">Co 4 miesiące</option>
        <option value="6">Co 6 miesięcy</option>
        <option value="12">Co 12 miesięcy</option>
    </select>
    <p class="text-sm text-gray-600 mt-1">Wydatki z cyklem > 0 będą automatycznie dodawane w następnych miesiącach.</p>
</div>
```


## Zmiany w CSS

Dodaj style dla wydatków cyklicznych:

```css
.recurring-expense {
    background-color: #f8f9ff;
}

.recurring-indicator {
    margin-left: 5px;
    font-size: 0.8em;
    opacity: 0.7;
}
```

## JavaScript

### expenses/index.js - obsługa wystąpień wydatków

```javascript
// Obsługa zmiany statusu płatności dla wystąpień
document.querySelectorAll('.status-cell').forEach(cell => {
    const occurrenceId = cell.dataset.id;
    const statusText = cell.querySelector('.status-text');
    const statusSelect = cell.querySelector('.status-select');

    // Kliknięcie w komórkę pokazuje select
    cell.addEventListener('click', (e) => {
        e.stopPropagation();
        statusText.classList.add('hidden');
        statusSelect.classList.remove('hidden');
        statusSelect.focus();
    });

    // Zmiana wartości w select
    statusSelect.addEventListener('change', async () => {
        const newStatus = statusSelect.value;

        try {
            const response = await fetch(`/expenses/occurrence/${occurrenceId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `status=${newStatus}`
            });

            const data = await response.json();

            if (data.success) {
                // Aktualizuj wyświetlany tekst
                statusText.textContent = newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                statusText.className = `status-text hover:text-shadow ${
                    newStatus === 'paid' ? 'text-green-600' :
                    newStatus === 'unpaid' ? 'text-red-500' : 'text-yellow-600'
                }`;

                // Ukryj select, pokaż tekst
                statusSelect.classList.add('hidden');
                statusText.classList.remove('hidden');
            } else {
                alert('Błąd podczas aktualizacji statusu: ' + data.error);
            }
        } catch (error) {
            alert('Błąd połączenia: ' + error.message);
        }
    });

    // Kliknięcie poza select ukrywa go
    document.addEventListener('click', (e) => {
        if (!cell.contains(e.target)) {
            statusSelect.classList.add('hidden');
            statusText.classList.remove('hidden');
        }
    });
});

// Dodaj obsługę wskaźników cyklicznych
document.querySelectorAll('.recurring-indicator').forEach(indicator => {
    indicator.addEventListener('click', (e) => {
        e.stopPropagation();
        const frequency = indicator.textContent.replace('🔄', '');
        alert(`To jest wydatek cykliczny - powtarza się co ${frequency} ${frequency == 1 ? 'miesiąc' : 'miesięcy'}.`);
    });
});
```

## Testowanie

### Scenariusze testów jednostkowych

1. **Tworzenie wystąpienia wydatku**
   - Utworzenie ExpenseOccurrence dla danego Expense
   - Sprawdzenie czy expense jest prawidłowo ustawione
   - Weryfikacja domyślnego statusu 'unpaid'

2. **Tworzenie następnego miesiąca**
   - Mock ExpenseRepository::findRecurringExpenses()
   - Mock ExpenseService::shouldExpenseOccurInMonth()
   - Sprawdzenie czy createExpenseOccurrence jest wywoływane tylko dla wydatków które powinny wystąpić
   - Weryfikacja dat wystąpienia

3. **Sprawdzanie cyklu powtarzania**
   - Wydatek co 1 miesiąc powinien występować co miesiąc
   - Wydatek co 2 miesiące powinien występować co 2 miesiące
   - Wydatek co 12 miesięcy powinien występować co rok

4. **Aktualizacja statusu wystąpienia**
   - Sprawdzenie zmiany paymentStatus
   - Opcjonalne ustawienie paymentDate

### Scenariusze testów integracyjnych

1. **Pełny cykl tworzenia miesiąca**
   - Dodanie wydatku z cyklem co 1 miesiąc
   - Utworzenie następnego miesiąca
   - Sprawdzenie czy wystąpienie istnieje w tabeli expense_occurrence

2. **Cykl powtarzania wystąpień**
   - Wydatek co 2 miesiące - sprawdzenie czy wystąpienia są tworzone co drugi miesiąc
   - Wydatek co 3 miesiące - sprawdzenie cyklu
   - Wydatek co 12 miesięcy - sprawdzenie cyklu rocznego

3. **Wielokrotne tworzenie miesięcy**
   - Zapobieganie duplikatom wystąpień
   - Zachowanie oryginalnych wystąpień

## Bezpieczeństwo

### Walidacja danych
- Sprawdzić czy użytkownik ma dostęp do modyfikowanych wydatków
- Walidacja dat (tylko przyszłe miesiące?)
- Ochrona przed tworzeniem zbyt wielu wydatków na raz

### SQL Injection
- Używać tylko prepared statements przez Doctrine

## Wydajność

### Optymalizacje
- Batch insert dla wielu wydatków
- Indeksy na kluczowych kolumnach
- Lazy loading dla parent/child relacji

### Limity
- Maksymalna liczba wydatków do przeniesienia na raz (np. 100)
- Timeout dla długich operacji

## Migracja

### Plan migracji bazy danych

1. **Backup** - pełne kopie zapasowe
2. **Maintenance mode** - wyłączenie aplikacji
3. **Dodanie kolumn** - ALTER TABLE dla nowych pól
4. **Aktualizacja aplikacji** - deploy nowego kodu
5. **Testy** - weryfikacja działania
6. **Włączenie** - przywrócenie dostępu

### Backward compatibility
- Wszystkie istniejące wydatki mają isRecurring = false
- parentExpense = null dla starych wydatków
- Brak zmian w istniejących funkcjach

## Dokumentacja

### Dla użytkowników
- Jak oznaczyć wydatek jako cykliczny
- Jak tworzyć nowe miesiące
- Wyjaśnienie wskaźników wizualnych

### Dla developerów
- Opis nowych pól i metod
- Przykłady użycia API
- Schemat relacji między wydatkami