# Plan rozwoju aplikacji Symfony Expenses Manager

## Aktualny stan (Version 1.0)

Aplikacja umożliwia podstawowe zarządzanie wydatkami miesięcznymi z następującymi funkcjami:
- ✅ Dodawanie wydatków z kategoriami
- ✅ Wyświetlanie wydatków wg miesięcy
- ✅ Zmiana statusu płatności (unpaid/paid)
- ✅ Zarządzanie kategoriami
- ✅ System użytkowników z autoryzacją

## Priorytetowe funkcje do dodania (Version 2.0)

### 1. Wydatki cykliczne (Recurring Expenses)

#### Wymagania biznesowe:
- Możliwość oznaczenia wydatku jako cyklicznego
- Cykliczne wydatki powinny być automatycznie przenoszone do następnych miesięcy
- Przy tworzeniu nowego miesiąca - kopiowanie nieopłaconych wydatków cyklicznych

#### Zmiany w bazie danych:
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

#### Zmiany w encjach:
- **Expense**: definicja wydatku cyklicznego z polem `recurringFrequency` (int)
- **ExpenseOccurrence**: wystąpienia wydatków z datą, statusem płatności
- Relacja: Expense -> ExpenseOccurrence (One-to-Many)

#### Nowe metody w ExpenseService:
- `createExpenseOccurrence(Expense $expense, \DateTimeInterface $date)`
- `getExpenseOccurrencesByMonth(int $year, int $month)`
- `createNextMonth(int $year, int $month)` - tworzy wystąpienia dla wydatków cyklicznych

### 2. Tworzenie nowego miesiąca (Month Creation)

#### Wymagania biznesowe:
- Ręczne tworzenie nowego miesiąca przez użytkownika
- Automatyczne przeniesienie wszystkich nieopłaconych wydatków cyklicznych
- Możliwość wyboru które wydatki przenieść
- Zachowanie oryginalnych dat, tylko zmiana miesiąca/roku

#### Nowe funkcjonalności:
- Przycisk "Utwórz nowy miesiąc" w interfejsie
- Modal/Strona z wyborem wydatków do przeniesienia
- Batch processing dla wydajności
- Logika unikania duplikatów

#### Nowe metody w ExpenseService:
- `createNextMonth(int $fromYear, int $fromMonth, array $selectedExpenseIds = null)`
- `getUnpaidRecurringExpenses(int $year, int $month)`
- `createExpenseOccurrence(Expense $expense, \DateTimeInterface $date)`

### 3. Usprawnienia UI/UX

#### Dla wydatków cyklicznych:
- Ikona/wskaźnik przy wydatkach cyklicznych
- Różne kolory dla wydatków cyklicznych vs jednorazowych
- Tooltip z informacją o cyklu

#### Dla tworzenia miesiąca:
- Dedykowany przycisk w nawigacji miesięcznej
- Progress bar podczas przetwarzania
- Potwierdzenie z podsumowaniem

## Średnioterminowe funkcje (Version 2.1-2.5)

### 4. Edycja i usuwanie wydatków

#### Wymagania:
- Edycja wszystkich pól wydatku
- Usuwanie z potwierdzeniem
- Audit log zmian
- Soft delete dla wydatków cyklicznych (tylko bieżący miesiąc)

#### Nowe routy:
- `PUT /expenses/{id}` - aktualizacja wydatku
- `DELETE /expenses/{id}` - usunięcie wydatku

### 5. Zaawansowane zarządzanie kategoriami

#### Wymagania:
- Edycja nazw kategorii
- Usuwanie kategorii (tylko jeśli brak powiązanych wydatków)
- Hierarchia kategorii (parent-child)
- Kolory kategorii dla lepszej wizualizacji

### 6. Raporty i statystyki

#### Wymagania:
- Wykresy wydatków wg kategorii/miesięcy
- Trendy wydatków
- Budżety miesięczne
- Eksport do PDF/Excel
- Statystyki płatności (na czas, opóźnienia)

### 7. Usprawnienia wydajności

#### Optymalizacje:
- Paginated listy wydatków
- Cache dla statycznych danych
- Lazy loading dla relacji
- Indeksy bazodanowe dla często używanych zapytań

## Długoterminowe funkcje (Version 3.0+)

### 8. Zaawansowane funkcje cykliczne

#### Wymagania:
- Niestandardowe interwały (co 2 miesiące, co kwartał itp.)
- Data zakończenia cyklu
- Pauza/wznowienie cyklu
- Wyjątki dla konkretnych miesięcy

### 9. Wieloużytkownikowość

#### Wymagania:
- Współdzielenie wydatków między użytkownikami
- Grupy użytkowników
- Podział kosztów
- Osobne budżety grupowe

### 10. Integracje

#### Wymagania:
- Import z banków (API)
- Synchronizacja z kalendarzem
- Integracja z aplikacjami finansowymi
- API REST dla zewnętrznych aplikacji

### 11. Załączniki i multimedia

#### Wymagania:
- Zdjęcia paragonów
- Pliki PDF faktur
- OCR dla automatycznego rozpoznawania danych
- Organizacja załączników

## Dokumentacja i organizacja projektu

### Pliki dokumentacyjne dla AI:
- `.cursorrules` - Reguły projektu dla Cursor AI
- `.cursor/rules.md` - Szczegółowe konwencje kodowania
- `docs/README_for_AI.md` - Indeks dokumentacji
- `docs/ai_analysis.md` - Analiza obecnego systemu
- `docs/ai_development_plan.md` - Ten plik - plan rozwoju
- `docs/ai_recurring_expenses_spec.md` - Specyfikacja wydatków cyklicznych
- `docs/ai_scalability_analysis.md` - Analiza skalowalności
- `docs/ai_project_structure.md` - Struktura projektu

### Workflow dokumentacji:
1. Nowe funkcje dodawać do tego pliku
2. Tworzyć szczegółowe specyfikacje (jak dla wydatków cyklicznych)
3. Aktualizować `.cursorrules` przy zmianach architektury
4. Dodawać przykłady kodu do `.cursor/rules.md`

## Architektura i techniczne usprawnienia

### 12. Refaktoring kodu

#### Zadania:
- Wprowadzenie DTO (Data Transfer Objects)
- Command/Query separation
- Event-driven architecture dla operacji wsadowych
- Unit/Integration tests
- API documentation (OpenAPI/Swagger)

### 13. Bezpieczeństwo i skalowalność

#### Zadania:
- Rate limiting dla API
- Audit logs dla wszystkich zmian
- Backup/restore functionality
- Horizontal scaling preparation
- Monitoring i alerting

## Harmonogram rozwoju

### Faza 1 (1-2 miesiące) - Wydatki cykliczne
1. Migracja bazy danych
2. Aktualizacja encji Expense
3. Dodanie pól w formularzach
4. Implementacja logiki tworzenia miesiąca

### Faza 2 (1 miesiąc) - Edycja/usuwanie
1. Formularze edycji
2. Logika usuwania
3. Walidacja i bezpieczeństwo

### Faza 3 (2 miesiące) - Raporty
1. Podstawowe wykresy
2. Statystyki miesięczne
3. Eksport danych

### Faza 4 (3 miesiące) - Zaawansowane funkcje
1. Hierarchia kategorii
2. Załączniki
3. Integracje z bankami

## Techniczne wyzwania

### Wydatki cykliczne:
- Unikanie duplikatów przy tworzeniu miesięcy
- Obsługa zmian w cyklu (co jeśli zmienię kwotę wydatku cyklicznego?)
- Cascade updates dla powiązanych wydatków

### Wydajność:
- Batch operations dla tworzenia miesięcy
- Optymalizacja zapytań z wieloma JOIN
- Cache dla często używanych danych

### UI/UX:
- Responsywność na mobile
- Loading states dla operacji AJAX
- Error handling i user feedback

## Testowanie

### Strategia testów:
- Unit tests dla serwisów
- Integration tests dla kontrolerów
- E2E tests dla kluczowych scenariuszy
- Performance tests dla operacji wsadowych

### Krytyczne scenariusze do testów:
1. Tworzenie miesiąca z wieloma wydatkami cyklicznymi
2. Jednoczesne operacje na tych samych danych
3. Rollback przy błędach
4. Walidacja danych wejściowych

## Migracja danych

### Plan migracji z v1.0 do v2.0:
1. Backup pełnej bazy danych
2. Stop aplikacji (maintenance mode)
3. Uruchomienie migracji
4. Aktualizacja kodu aplikacji
5. Testy funkcjonalne
6. Włączenie aplikacji

## Ryzyka i mitigation

### Ryzyka biznesowe:
- Utrata danych podczas migracji → Regularne backupy
- Problemy z wydajnością przy wielu danych → Optymalizacja zapytań
- Złożoność UX → Iteracyjne testowanie z użytkownikami

### Ryzyka techniczne:
- Problemy z relacjami self-referencing → Dokładne testowanie
- Wydajność przy tworzeniu miesięcy → Batch processing + queue
- Zgodność wsteczna → Wersjonowanie API