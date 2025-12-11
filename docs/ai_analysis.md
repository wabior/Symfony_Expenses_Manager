# Analiza aplikacji Symfony Expenses Manager

## Struktura aplikacji

### Architektura
- **Framework**: Symfony 6.x
- **Baza danych**: MySQL z Doctrine ORM
- **Frontend**: Twig templates + JavaScript (bez frameworka JS)
- **Stylizacja**: Tailwind CSS
- **Build tool**: Webpack Encore
- **Konteneryzacja**: Docker + Docker Compose

## Baza danych

### Tabele

#### 1. `user`
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `email` (VARCHAR(180), UNIQUE)
- `roles` (JSON)
- `password` (VARCHAR(255))

**Opis**: Tabela użytkowników dla systemu autoryzacji.

#### 2. `category`
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `name_english` (VARCHAR(255))
- `name_polish` (VARCHAR(255))

**Opis**: Kategorie wydatków z nazwami w dwóch językach.

#### 3. `expense`
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(255)) - nazwa wydatku
- `amount` (DECIMAL(10,2)) - kwota
- `date` (DATE) - data wydatku
- `payment_date` (DATE, NULL) - data płatności
- `payment_status` (VARCHAR(20), DEFAULT 'unpaid') - status płatności
  - Możliwe wartości: 'unpaid', 'paid', 'partially_paid'
- `category_id` (INT, FOREIGN KEY → category.id)
- `created_at` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- `updated_at` (DATETIME, DEFAULT CURRENT_TIMESTAMP)

**Opis**: Główna tabela wydatków.

#### 4. `menu`
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `route_name` (VARCHAR(255))
- `friendly_name` (VARCHAR(255))
- `path` (VARCHAR(255))
- `order` (INT) - kolejność w menu
- `activated` (TINYINT(1)) - czy pozycja menu jest aktywna

**Opis**: Tabela do zarządzania pozycjami menu (admin panel).

### Relacje
- `expense.category_id` → `category.id` (Many-to-One)

## Encje (Entity)

### Expense
**Pola**:
- id, name, amount, date, paymentDate, paymentStatus, category, createdAt, updatedAt

**Metody ważne**:
- `onPrePersist()` - ustawia createdAt i updatedAt
- `onPreUpdate()` - aktualizuje updatedAt

### Category
**Pola**:
- id, nameEnglish, namePolish

### User
**Pola**:
- id, email, roles, password

**Interfejsy**:
- UserInterface, PasswordAuthenticatedUserInterface

### Menu
**Pola**:
- id, routeName, friendlyName, path, order, activated

## Kontrolery

### RouteController
- `home` (GET /) - strona główna
- `about` (GET /about) - strona "O nas"

### ExpenseController
- `index` (GET /expenses/{year}/{month}) - lista wydatków dla miesiąca
- `add` (GET/POST /expenses/add) - dodawanie nowego wydatku
- `updateStatus` (POST /expenses/update-status/{id}) - aktualizacja statusu płatności (AJAX)

### CategoryController
- `index` (GET /categories) - lista kategorii
- `add` (GET/POST /categories/add) - dodawanie nowej kategorii

### SecurityController
- `login` (GET/POST /login) - logowanie
- `logout` (GET /logout) - wylogowanie

### MenuController (Admin)
- `index` (GET /admin/menu) - zarządzanie menu
- `save` (POST /admin/menu/save) - zapis zmian w menu

## Serwisy

### ExpenseService
**Metody**:
- `getAllExpenses()` - wszystkie wydatki
- `getExpensesByMonth($year, $month)` - wydatki dla konkretnego miesiąca
- `addExpense(Request $request)` - dodanie nowego wydatku
- `updateExpenseStatus($id, $status)` - aktualizacja statusu płatności
- `getAllCategories()` - wszystkie kategorie
- `getNavigationMonths($year, $month)` - dane nawigacji między miesiącami

### CategoryService
**Metody**:
- `getAllCategories()` - wszystkie kategorie
- `addCategory($nameEnglish, $namePolish)` - dodanie kategorii

## Repozytoria

### ExpenseRepository
**Metody**:
- `findByMonth(\DateTime $startDate, \DateTime $endDate)` - wydatki w przedziale dat

## Szablony (Templates)

### expenses/index.html.twig
**Funkcjonalności**:
- Lista wydatków w tabeli
- Nawigacja między miesiącami (< Poprzedni/Następny >)
- Przycisk "Add New Expense"
- Klikalne statusy płatności (zmiana przez AJAX)

### expenses/add.html.twig
**Formularz dodawania wydatku**:
- name (text)
- amount (number, step 0.01)
- paymentStatus (select: unpaid/paid/partially_paid)
- date (date)
- category (select z kategoriami)
- paymentDate (date, opcjonalne)

### categories/index.html.twig
- Lista kategorii w tabeli
- Przycisk "Add New Category"

### categories/add.html.twig
- Formularz: nameEnglish, namePolish

## JavaScript

### expenses/index.js
**Funkcjonalności**:
- Kliknięcie na status płatności pokazuje select
- AJAX update statusu płatności
- Automatyczna aktualizacja paymentDate przy zmianie statusu

### payment_status.js
- Obsługa pól paymentDate w formularzach

## Routing

### Główne routy (z menu):
- `/` - home (Start)
- `/expenses` - expenses (Wydatki)
- `/categories` - categories (Kategorie)
- `/about` - about (O nas)
- `/admin/menu` - admin menu

### Pozostałe:
- `/login` - logowanie
- `/logout` - wylogowanie
- `/expenses/add` - dodawanie wydatku
- `/categories/add` - dodawanie kategorii
- `/expenses/update-status/{id}` - API update statusu

## Bezpieczeństwo

- **Autoryzacja**: Symfony Security
- **Role**: ROLE_USER (wymagane dla większości akcji)
- **Login**: email + password
- **CSRF**: token w formularzach

## Docker/Konteneryzacja

- **docker-compose.yml**: konfiguracja usług
- **Dockerfile**: obraz aplikacji PHP
- **compose.override.yaml**: development overrides

## Fixtures

### CategoryFixtures
Tworzy podstawowe kategorie:
- Food/Jedzenie
- Rent/Czynsz
- Utilities/Media
- Entertainment/Rozrywka
- Travel/Podróże
- Healthcare/Zdrowie
- Education/Edukacja
- Shopping/Zakupy
- Others/Inne

## Aktualny stan funkcjonalności

### Dostępne funkcje:
1. ✅ Logowanie/rejestracja użytkowników
2. ✅ Zarządzanie kategoriami (CRUD)
3. ✅ Dodawanie wydatków
4. ✅ Wyświetlanie wydatków wg miesięcy
5. ✅ Zmiana statusu płatności (unpaid/paid)
6. ✅ Nawigacja między miesiącami
7. ✅ Menu administracyjne

### Brakujące funkcje:
1. ❌ Wydatki cykliczne (recurring expenses)
2. ❌ Tworzenie nowego miesiąca z przeniesieniem nieopłaconych wydatków cyklicznych
3. ❌ Edycja/usuwanie wydatków
4. ❌ Edycja/usuwanie kategorii
5. ❌ Raporty/statystyki
6. ❌ Eksport danych
7. ❌ Wielowalutowość
8. ❌ Załączniki do wydatków

## Konfiguracja

### Środowisko:
- **PHP**: 8.1+
- **Symfony**: 6.x
- **MySQL**: 8.0+
- **Node.js**: dla assetów

### Packages:
- doctrine/orm
- symfony/security-bundle
- symfony/twig-bundle
- symfony/webpack-encore-bundle
- tailwindcss (via npm)

## Struktura plików

### Katalog główny:
- `.cursorrules` - Reguły projektu dla AI
- `.cursor/rules.md` - Szczegółowe konwencje kodowania
- `docs/` - Dokumentacja techniczna dla AI

### Symfony aplikacja:
```
symfony/
├── config/
│   ├── bundles.php
│   ├── doctrine.yaml
│   ├── framework.yaml
│   ├── routes.yaml
│   └── security.yaml
├── migrations/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Repository/
│   ├── Service/
│   └── DataFixtures/
├── templates/
│   ├── base.html.twig
│   ├── expenses/
│   ├── categories/
│   ├── security/
│   └── admin/
└── public/
```

### Dokumentacja dla AI:
```
docs/
├── README_for_AI.md              # Indeks dokumentacji
├── ai_analysis.md                # Analiza aplikacji (ten plik)
├── ai_development_plan.md        # Plan rozwoju
├── ai_recurring_expenses_spec.md # Spec wydatków cyklicznych
├── ai_scalability_analysis.md    # Analiza skalowalności i wydajności
└── ai_project_structure.md       # Struktura projektu
```

**Zobacz również**: `docs/ai_scalability_analysis.md` - szczegółowa analiza podejść do wydatków cyklicznych pod kątem wydajności.