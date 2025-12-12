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

### Nowe kolumny w tabeli `expense`

```sql
-- Liczba miesięcy co ile wydatek się powtarza
-- 0 = nie powtarza się, 1 = co miesiąc, 2 = co 2 miesiące, itd.
ALTER TABLE expense ADD COLUMN recurring_frequency INT DEFAULT 0 NOT NULL COMMENT 'Liczba miesięcy co ile wydatek się powtarza (0 = nie powtarza się)';

-- ID rodzica (dla śledzenia pochodzenia)
ALTER TABLE expense ADD COLUMN parent_expense_id INT DEFAULT NULL COMMENT 'ID wydatku, z którego pochodzi ten wydatek cykliczny';

-- Klucz obcy do samego siebie
ALTER TABLE expense ADD CONSTRAINT FK_PARENT_EXPENSE FOREIGN KEY (parent_expense_id) REFERENCES expense(id);
```

### Indeksy dla wydajności

```sql
-- Indeks dla szybkiego znajdowania wydatków cyklicznych
CREATE INDEX idx_expense_recurring ON expense(recurring_frequency);

-- Indeks dla znajdowania dzieci danego wydatku
CREATE INDEX idx_expense_parent_id ON expense(parent_expense_id);

-- Indeks złożony dla zapytań miesięcznych z uwzględnieniem cykliczności
CREATE INDEX idx_expense_month_recurring ON expense(date, recurring_frequency);
```

## Zmiany w encjach

### Expense Entity - nowe pola

```php
#[ORM\Column(type: "integer", options: ["default" => 0])]
private int $recurringFrequency = 0;

#[ORM\ManyToOne(targetEntity: Expense::class)]
#[ORM\JoinColumn(nullable: true)]
private ?Expense $parentExpense = null;

#[ORM\OneToMany(mappedBy: "parentExpense", targetEntity: Expense::class)]
private Collection $childExpenses;
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

public function getParentExpense(): ?Expense
{
    return $this->parentExpense;
}

public function setParentExpense(?Expense $expense): self
{
    $this->parentExpense = $expense;
    return $this;
}

public function getChildExpenses(): Collection
{
    return $this->childExpenses;
}
```

## Zmiany w serwisach

### ExpenseService - nowe metody

```php
/**
 * Tworzy wydatek cykliczny na podstawie szablonu
 */
public function createRecurringExpense(Expense $template, int $year, int $month): Expense
{
    $recurringExpense = new Expense();
    $recurringExpense->setName($template->getName());
    $recurringExpense->setAmount($template->getAmount());
    $recurringExpense->setCategory($template->getCategory());
    $recurringExpense->setIsRecurring(true);
    $recurringExpense->setParentExpense($template);
    $recurringExpense->setRecurringFrequency('monthly'); // na razie tylko miesięczne

    // Ustawienie daty na pierwszy dzień wskazanego miesiąca
    $date = new \DateTime("$year-$month-01");
    $recurringExpense->setDate($date);

    // Status zawsze unpaid dla nowych wydatków cyklicznych
    $recurringExpense->setPaymentStatus('unpaid');
    $recurringExpense->setPaymentDate(null);

    return $recurringExpense;
}

/**
 * Pobiera wszystkie wydatki cykliczne dla danego miesiąca
 */
public function getRecurringExpensesByMonth(int $year, int $month): array
{
    $startDate = new \DateTime("$year-$month-01");
    $endDate = (clone $startDate)->modify('+1 month');

    return $this->entityManager->getRepository(Expense::class)
        ->findRecurringByMonth($startDate, $endDate);
}

/**
 * Pobiera nieopłacone wydatki cykliczne dla danego miesiąca
 */
public function getUnpaidRecurringExpenses(int $year, int $month): array
{
    $startDate = new \DateTime("$year-$month-01");
    $endDate = (clone $startDate)->modify('+1 month');

    return $this->entityManager->getRepository(Expense::class)
        ->findUnpaidRecurringByMonth($startDate, $endDate);
}

/**
 * Tworzy następny miesiąc, dodając wszystkie wydatki cykliczne które powinny się powtórzyć
 */
public function createNextMonth(int $fromYear, int $fromMonth): array
{
    $nextMonth = $fromMonth == 12 ? 1 : $fromMonth + 1;
    $nextYear = $fromMonth == 12 ? $fromYear + 1 : $fromYear;

    // Pobierz wszystkie wydatki cykliczne z poprzedniego miesiąca
    $recurringExpenses = $this->getRecurringExpensesByMonth($fromYear, $fromMonth);

    $createdExpenses = [];

    foreach ($recurringExpenses as $expense) {
        // Sprawdź czy wydatek powinien się powtórzyć w tym miesiącu
        if ($this->shouldExpenseRepeatInMonth($expense, $fromYear, $fromMonth, $nextYear, $nextMonth)) {
            $newExpense = $this->createRecurringExpense($expense, $nextYear, $nextMonth);
            $this->entityManager->persist($newExpense);
            $createdExpenses[] = $newExpense;
        }
    }

    $this->entityManager->flush();

    return $createdExpenses;
}

/**
 * Sprawdza czy wydatek cykliczny powinien się powtórzyć w danym miesiącu
 */
public function shouldExpenseRepeatInMonth(Expense $expense, int $fromYear, int $fromMonth, int $toYear, int $toMonth): bool
{
    $frequency = $expense->getRecurringFrequency();

    // Jeśli nie jest cykliczny, nie powtarzaj
    if ($frequency <= 0) {
        return false;
    }

    // Oblicz liczbę miesięcy między oboma okresami
    $fromDate = new \DateTime("$fromYear-$fromMonth-01");
    $toDate = new \DateTime("$toYear-$toMonth-01");

    $monthsDiff = ($toDate->format('Y') - $fromDate->format('Y')) * 12 +
                  ($toDate->format('n') - $fromDate->format('n'));

    // Wydatek powtarza się jeśli różnica miesięcy jest podzielna przez cykl
    return $monthsDiff % $frequency === 0;
}

/**
 * Duplikuje pojedynczy wydatek do następnego miesiąca
 */
public function duplicateExpenseForNextMonth(Expense $expense, int $targetYear, int $targetMonth): Expense
{
    return $this->createRecurringExpense($expense, $targetYear, $targetMonth);
}
```

## Zmiany w repozytoriach

### ExpenseRepository - nowe metody

```php
/**
 * Znajduje wydatki cykliczne w przedziale dat
 */
public function findRecurringByMonth(\DateTime $startDate, \DateTime $endDate): array
{
    return $this->createQueryBuilder('e')
        ->where('e.date >= :startDate')
        ->andWhere('e.date < :endDate')
        ->andWhere('e.recurringFrequency > :frequency')
        ->setParameter('startDate', $startDate->format('Y-m-d'))
        ->setParameter('endDate', $endDate->format('Y-m-d'))
        ->setParameter('frequency', 0)
        ->getQuery()
        ->getResult();
}

/**
 * Znajduje nieopłacone wydatki cykliczne w przedziale dat
 */
public function findUnpaidRecurringByMonth(\DateTime $startDate, \DateTime $endDate): array
{
    return $this->createQueryBuilder('e')
        ->where('e.date >= :startDate')
        ->andWhere('e.date < :endDate')
        ->andWhere('e.recurringFrequency > :frequency')
        ->andWhere('e.paymentStatus != :paidStatus')
        ->setParameter('startDate', $startDate->format('Y-m-d'))
        ->setParameter('endDate', $endDate->format('Y-m-d'))
        ->setParameter('frequency', 0)
        ->setParameter('paidStatus', 'paid')
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
    $createdExpenses = $this->expenseService->createNextMonth($year, $month);

    $this->addFlash('success', sprintf('Utworzono %d wydatków cyklicznych dla następnego miesiąca', count($createdExpenses)));

    // Przekieruj do następnego miesiąca
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;

    return $this->redirectToRoute('expenses', ['year' => $nextYear, 'month' => $nextMonth]);
}
```

## Zmiany w szablonach

### expenses/index.html.twig - oznaczenie wydatków cyklicznych

```twig
{% for expense in expenses %}
    <tr id="expense-{{ expense.id }}"{% if expense.isRecurring %} class="recurring-expense"{% endif %}>
        {# ... istniejące kolumny ... #}
        <td class="table-cell status-cell cursor-pointer relative" data-id="{{ expense.id }}">
            <span class="status-text hover:text-shadow
                {% if expense.paymentStatus == 'unpaid'%} text-red-500 {% endif %}
                {% if expense.paymentStatus == 'paid'%} text-green-600 {% endif %}
            ">
                {{ expense.paymentStatus|capitalize }}
            </span>
            {% if expense.isRecurring %}
                <span class="recurring-indicator" title="Cykl: co {{ expense.recurringFrequency }} {% if expense.recurringFrequency == 1 %}miesiąc{% else %}miesięcy{% endif %}">🔄{{ expense.recurringFrequency }}</span>
            {% endif %}
            <select class="status-select absolute right-2 top-2 p-1 hidden w-full cursor-pointer">
                {# ... opcje statusu ... #}
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

### expenses/index.js - aktualizacja dla oznaczeń cyklicznych

```javascript
// Dodaj obsługę wskaźników cyklicznych
document.querySelectorAll('.recurring-indicator').forEach(indicator => {
    indicator.addEventListener('click', (e) => {
        e.stopPropagation();
        alert('To jest wydatek cykliczny - zostanie automatycznie przeniesiony do następnego miesiąca.');
    });
});
```

## Testowanie

### Scenariusze testów jednostkowych

1. **Tworzenie wydatku cyklicznego**
   - Utworzenie wydatku z recurringFrequency > 0
   - Sprawdzenie czy parentExpense jest ustawione

2. **Tworzenie następnego miesiąca**
   - Mock ExpenseService::getRecurringExpensesByMonth()
   - Mock ExpenseService::shouldExpenseRepeatInMonth()
   - Sprawdzenie czy createRecurringExpense jest wywoływane tylko dla wydatków które powinny się powtórzyć
   - Weryfikacja dat i statusów nowych wydatków

3. **Sprawdzanie cyklu powtarzania**
   - Wydatek co 1 miesiąc powinien się powtarzać co miesiąc
   - Wydatek co 2 miesiące powinien się powtarzać co 2 miesiące
   - Wydatek co 12 miesięcy powinien się powtarzać co rok

4. **Duplikacja wydatku**
   - Sprawdzenie czy wszystkie pola są kopiowane
   - Weryfikacja czy parentExpense jest ustawione
   - Sprawdzenie czy data jest zmieniona na nowy miesiąc

### Scenariusze testów integracyjnych

1. **Pełny cykl tworzenia miesiąca**
   - Dodanie wydatku z cyklem co 1 miesiąc
   - Utworzenie następnego miesiąca
   - Sprawdzenie czy wydatek istnieje w nowym miesiącu

2. **Cykl powtarzania wydatków**
   - Wydatek co 2 miesiące - sprawdzenie czy się powtarza co drugi miesiąc
   - Wydatek co 3 miesiące - sprawdzenie cyklu
   - Wydatek co 12 miesięcy - sprawdzenie cyklu rocznego

3. **Wielokrotne tworzenie miesięcy**
   - Zapobieganie duplikatom
   - Zachowanie oryginalnych wydatków

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