# Analiza skalowalności - Wydatki cykliczne

## Problem skalowalności

### Aktualne założenia:
- **15 wydatków miesięcznie** per użytkownik
- **Większość wydatków cyklicznych**
- **Wyświetlanie tylko bieżącego i najbliższego miesiąca**
- **Brak archiwizacji** starych danych

### Scenariusze skalowalności:

#### Scenariusz 1: Duplikacja rekordów Expense
```sql
-- Każdy wydatek cykliczny duplikowany w każdym miesiącu
INSERT INTO expense (name, amount, date, recurring_frequency, parent_expense_id)
SELECT name, amount, '2024-02-01', recurring_frequency, id
FROM expense WHERE recurring_frequency > 0 AND date = '2024-01-01'
```

**Problemy:**
- Duplikacja danych (nazwa, kwota, kategoria)
- Trudna zmiana cyklu wydatku (dotyczy wszystkich duplikatów)
- Problemy z integralnością danych

#### Scenariusz 2: Tabela `expense_occurrence` (Rekomendowany)
```sql
CREATE TABLE expense_occurrence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_id INT NOT NULL,
    occurrence_date DATE NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'unpaid',
    payment_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expense_id) REFERENCES expense(id),
    UNIQUE KEY unique_expense_date (expense_id, occurrence_date)
);
```

**Zalety:**
- **Brak duplikacji** danych podstawowych
- **Łatwa zmiana** cyklu (tylko w tabeli expense)
- **Integralność** - wystąpienia zależą od oryginału
- **Elastyczność** - wydatek może występować wiele razy w miesiącu

## Analiza wydajności

### Obliczenia dla różnych skal:

#### Objętość danych - 1 użytkownik:
- **15 wydatków miesięcznie** × **10 lat** = ~1,800 rekordów Expense
- **10 cyklicznych** × **120 miesięcy** = ~1,200 rekordów occurrence
- **Razem**: ~3,000 rekordów na użytkownika

#### Objętość danych - 1000 użytkowników:
- **Expense**: 1,800 × 1000 = 1,800,000 rekordów
- **Occurrence**: 1,200 × 1000 = 1,200,000 rekordów
- **Razem**: ~3,000,000 rekordów

#### Objętość danych - 20,000 użytkowników:
- **Expense**: 1,800 × 20,000 = 36,000,000 rekordów
- **Occurrence**: 1,200 × 20,000 = 24,000,000 rekordów
- **Razem**: ~60,000,000 rekordów

### Optymalizacja zapytań:

#### Wyświetlanie miesiąca:
```sql
-- Z JOIN (wydajne z indeksami)
SELECT e.name, e.amount, c.name_polish as category,
       eo.payment_status, eo.payment_date, eo.occurrence_date,
       CASE WHEN e.recurring_frequency > 0 THEN true ELSE false END as is_recurring
FROM expense_occurrence eo
JOIN expense e ON eo.expense_id = e.id
LEFT JOIN category c ON e.category_id = c.id
WHERE eo.occurrence_date BETWEEN '2024-01-01' AND '2024-01-31'
ORDER BY eo.occurrence_date, e.name
```

#### Indeksy dla wydajności:
```sql
CREATE INDEX idx_occurrence_date ON expense_occurrence(occurrence_date);
CREATE INDEX idx_occurrence_expense ON expense_occurrence(expense_id);
CREATE INDEX idx_expense_recurring ON expense(recurring_frequency);
```

## Architektura rozwiązania

### Tabela expense (definicje):
```sql
CREATE TABLE expense (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- właściciel wydatku
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    recurring_frequency INT DEFAULT 0, -- 0=jednorazowy, 1-12=miesięczny cykl
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES category(id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

### Tabela expense_occurrence (wystąpienia):
```sql
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

### Logika biznesowa:

#### Tworzenie nowego miesiąca:
```php
public function createNextMonth(int $year, int $month): void
{
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;

    // Znajdź wszystkie wydatki cykliczne
    $recurringExpenses = $this->expenseRepository->findRecurringExpenses();

    foreach ($recurringExpenses as $expense) {
        // Sprawdź czy powinno wystąpić w następnym miesiącu
        if ($this->shouldExpenseOccurInMonth($expense, $nextYear, $nextMonth)) {
            $this->createExpenseOccurrence($expense, $nextYear, $nextMonth);
        }
    }
}

private function shouldExpenseOccurInMonth(Expense $expense, int $year, int $month): bool
{
    $frequency = $expense->getRecurringFrequency();

    if ($frequency <= 0) return false;

    // Dla wydatków miesięcznych - zawsze
    if ($frequency == 1) return true;

    // Dla innych cyklów - sprawdź matematykę
    // (np. co 2 miesiące - sprawdź parzystość)
    return ($month % $frequency) == ($expense->getCreatedAt()->format('n') % $frequency);
}
```

## Strategie optymalizacji

### Wydajność zapytań:
- **Indeksy** na `occurrence_date` i `expense_id`
- **Partitioning** tabeli occurrence po miesiącach
- **Cache** wyników dla często wyświetlanych miesięcy

### Archiwizacja:
- Przenieś dane starsze niż 2 lata do tabel archiwalnych
- Kompresja starych danych
- Backup automatyczny

### Skalowalność pozioma:
- **Read replicas** dla raportów
- **Sharding** po user_id dla bardzo dużych instalacji
- **Queue system** dla wsadowych operacji (tworzenie miesięcy)

## Rekomendacja implementacyjna

### Dla aplikacji z 15 wydatkami miesięcznie:

1. **Implementuj tabelę `expense_occurrence`** - czysta architektura
2. **Dodaj pole `user_id`** do expense (wieloużytkownikowość)
3. **Implementuj logikę cyklu** w serwisie
4. **Dodaj indeksy** dla wydajności
5. **Rozważ cache** dla często wyświetlanych miesięcy

### Wydajność:
- **1000 użytkowników**: Bez problemu, standardowa baza danych
- **20,000 użytkowników**: Wymaga optymalizacji, możliwe sharding
- **Więcej**: Mikroserwisy lub rozłożona baza danych

### Monitoring wydajności:
- Czas wykonania zapytań miesięcznych
- Rozmiar tabel (automatyczne alerty)
- CPU usage podczas tworzenia nowych miesięcy

Taka architektura zapewni skalowalność przy zachowaniu prostoty implementacji.