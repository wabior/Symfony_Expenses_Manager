# Analiza aplikacji i struktura projektu - Symfony Expenses Manager

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

## Głównne komponenty systemu

### 1. Encje (Entity)
**Lokalizacja**: `symfony/src/Entity/`

| Encja | Opis | Kluczowe pola |
|-------|------|---------------|
| `User` | Użytkownicy systemu | id, email, password, roles |
| `Expense` | Wydatki | id, name, amount, date, paymentStatus, category, isRecurring* |
| `Category` | Kategorie wydatków | id, nameEnglish, namePolish |
| `Menu` | Elementy menu | id, routeName, friendlyName, path, order, activated |

*Pole `isRecurring` będzie dodane w następnej wersji

### 2. Kontrolery (Controllers)
**Lokalizacja**: `symfony/src/Controller/`

| Kontroler | Metody | Opis |
|-----------|--------|------|
| `RouteController` | home, about | Strony statyczne |
| `ExpenseController` | index, add, updateStatus | Zarządzanie wydatkami |
| `CategoryController` | index, add | Zarządzanie kategoriami |
| `SecurityController` | login, logout | Autoryzacja |
| `MenuController` | index, save | Admin - zarządzanie menu |
| `BaseController` | - | Klasa bazowa z wspólną logiką |

### 3. Serwisy (Services)
**Lokalizacja**: `symfony/src/Service/`

| Serwis | Metody kluczowe | Opis |
|--------|-----------------|------|
| `ExpenseService` | getExpensesByMonth, addExpense, updateExpenseStatus | Logika wydatków |
| `CategoryService` | getAllCategories, addCategory | Logika kategorii |
| `RenderService` | renderWithRoutes | Renderowanie z menu |
| `RouteService` | getAllRoutes | Pobieranie dostępnych routów |

### 4. Repozytoria (Repositories)
**Lokalizacja**: `symfony/src/Repository/`

| Repozytorium | Metody | Opis |
|--------------|--------|------|
| `ExpenseRepository` | findByMonth | Zapytania dla wydatków |
| `CategoryRepository` | - | Zapytania dla kategorii |
| `MenuRepository` | - | Zapytania dla menu |
| `UserRepository` | - | Zapytania dla użytkowników |

### 5. Szablony (Templates)
**Lokalizacja**: `symfony/templates/`

```
templates/
├── base.html.twig          # Layout główny
├── home.html.twig          # Strona główna
├── about.html.twig         # Strona "O nas"
├── expenses/
│   ├── index.html.twig     # Lista wydatków
│   └── add.html.twig       # Formularz dodawania
├── categories/
│   ├── index.html.twig     # Lista kategorii
│   └── add.html.twig       # Formularz kategorii
├── security/
│   └── login.html.twig     # Formularz logowania
└── admin/
    └── menu.html.twig      # Zarządzanie menu
```

### 6. JavaScript
**Lokalizacja**: `symfony/templates/expenses/index.js`, `assets/js/payment_status.js`

| Plik | Funkcjonalności |
|------|----------------|
| `expenses/index.js` | AJAX update statusu płatności |
| `payment_status.js` | Obsługa pól daty płatności |

## Kluczowe wzorce architektoniczne

### 1. MVC Pattern
- **Model**: Encje Doctrine
- **View**: Szablony Twig
- **Controller**: Kontrolery Symfony

### 2. Service Layer
- Logika biznesowa wydzielona do serwisów
- Dependency Injection przez Symfony

### 3. Repository Pattern
- Abstrakcja dostępu do danych
- Doctrine ORM jako implementacja

### 4. Template Inheritance
- `base.html.twig` jako layout główny
- Dziedziczenie dla spójnego UI

## Routing i bezpieczeństwo

### Routing
**Plik**: `symfony/config/routes.yaml`
- Attribute-based routing w kontrolerach
- RESTful konwencje

### Bezpieczeństwo
**Plik**: `symfony/config/security.yaml`
- Form-based authentication
- Role-based access control
- CSRF protection

## Workflow developmentu

### 1. Dodawanie nowej funkcji
1. **Encja** → migracja bazy danych
2. **Serwis** → logika biznesowa
3. **Kontroler** → endpoint API
4. **Szablon** → interfejs użytkownika
5. **JavaScript** → interaktywność (jeśli potrzeba)

### 2. Typowe zmiany dla wydatków
- `ExpenseService` - logika biznesowa
- `ExpenseController` - API endpoints
- `ExpenseRepository` - zapytania do bazy
- `templates/expenses/` - UI
- `expenses/index.js` - frontend logic

### 3. Testowanie
- **Unit tests**: Serwisy, repozytoria
- **Integration tests**: Kontrolery z bazą danych
- **E2E tests**: Pełne scenariusze przez UI

## Najważniejsze konwencje

### 1. Nazewnictwo
- **Encje**: PascalCase, singular (Expense, Category)
- **Tabele**: snake_case, plural (expense, category)
- **Metody**: camelCase (getExpensesByMonth, addExpense)
- **Routy**: kebab-case (expenses-add, categories-index)

### 2. Struktura katalogów
- **src/**: Kod PHP aplikacji
- **templates/**: Szablony Twig
- **public/**: Statyczne assety
- **config/**: Konfiguracja Symfony
- **migrations/**: Migracje Doctrine

### 3. Statusy wydatków
- `unpaid` - nieopłacony (czerwony)
- `paid` - opłacony (zielony)
- `partially_paid` - częściowo opłacony

## Aktualny stan funkcjonalności

### Dostępne funkcje (Version 1.0):
1. ✅ Logowanie/rejestracja użytkowników (z izolacją danych)
2. ✅ Dodawanie kategorii (bez edycji/usuwania)
3. ✅ Dodawanie wydatków (bez edycji/usuwania)
4. ✅ Wyświetlanie wydatków wg miesięcy
5. ✅ Zmiana statusu płatności (unpaid/paid) przez AJAX
6. ✅ Nawigacja między miesiącami
7. ✅ Menu administracyjne

### Krytyczne problemy bezpieczeństwa (rozwiązane w schemacie bazy danych):
1. ✅ **IZOLACJA DANYCH** - Wydatki są przypisane do użytkowników poprzez user_id
2. ✅ **ROW-LEVEL SECURITY** - Użytkownicy widzą tylko swoje wydatki
3. ✅ **USER-EXPENSE RELATIONSHIP** - Relacja między User a Expense istnieje

### Brakujące funkcje krytyczne:
1. ❌ Pełne zarządzanie kategoriami (edycja/usuwanie)
2. ❌ Pełne zarządzanie wydatkami (edycja/usuwanie)
3. ❌ Operacje masowe na wydatkach

### Brakujące funkcje zaawansowane:
1. ❌ Wydatki cykliczne (recurring expenses)
2. ❌ Raporty/statystyki i dashboard
3. ❌ Eksport danych (CSV/PDF/Excel)
4. ❌ Zaawansowane wyszukiwanie
5. ❌ Sortowanie tabel i filtry
6. ❌ Wielojęzyczność (angielski/polski)
7. ❌ Optymalizacja mobilna i dark mode

## Plan rozwoju - Milestone'y

Zobacz `docs/milestones.md` dla szczegółowego planu rozwoju podzielonego na kluczowe milestone'y:

1. **Secure Multi-User Expense Management** - Bezpieczna izolacja danych użytkowników
2. **Complete Expense CRUD Operations** - Pełne zarządzanie wydatkami
3. **Recurring Expenses System Operational** - System wydatków cyklicznych
4. **Polished User Interface & Experience** - Profesjonalny interfejs użytkownika
5. **Professional Reporting & Analytics** - Raporty i analityka
6. **Advanced Features & Future-Proofing** - Zaawansowane funkcje

**Aktualny status wszystkich milestone'ów**: Nie rozpoczęte

## Implementacja bezpieczeństwa danych

Wszystkie wydatki, kategorie i wystąpienia wydatków są przypisane do użytkowników poprzez kolumnę `user_id` z wymuszaniem na poziomie bazy danych (klucze obce). Izolacja danych została zaimplementowana od pierwszej migracji schematu.

## Rozszerzenia planowane

### Version 2.0 - Wydatki cykliczne
**Nowe encje**:
- `Expense`: definicja wydatku z polem `recurring_frequency` (int, 0-12)
- `ExpenseOccurrence`: wystąpienia wydatków z datą i statusem płatności

**Nowe funkcjonalności**:
- Checkbox przy dodawaniu wydatku
- Przycisk "Utwórz nowy miesiąc"
- Kopiowanie nieopłaconych wydatków cyklicznych
- Tabela `expense_occurrence` zawiera już `user_id` dla izolacji danych

### Version 2.1 - CRUD pełny
- Edycja wydatków
- Usuwanie wydatków
- Edycja kategorii
- Usuwanie kategorii

### Version 3.0 - Zaawansowane funkcje
- Raporty i statystyki
- Załączniki
- Integracje z bankami
- Wieloużytkownikowość

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

## Przydatne komendy

### Symfony
```bash
# Uruchomienie serwera deweloperskiego
symfony server:start

# Wyczyszczenie cache
php bin/console cache:clear

# Migracje (uwaga: zawierają schemat z izolacją danych)
php bin/console doctrine:migrations:migrate
```

### Docker
```bash
# Budowanie i uruchamianie
docker-compose up --build

# Uruchamianie komend w kontenerze
docker-compose exec php php bin/console
```

### Assets
```bash
# Budowanie CSS/JS
npm run build

# Watch mode podczas developmentu
npm run watch
```

## Debugging

### Logs
- **Symfony logs**: `var/log/dev.log`
- **PHP errors**: `var/log/php_errors.log`
- **Docker logs**: `docker-compose logs`

### Narzędzia debugowania
- **Symfony Profiler**: `/dev/_profiler`
- **Doctrine queries**: W profilerze lub `bin/console doctrine:query:sql`
- **Browser DevTools**: Dla JavaScript

## Checklist przed commitem

- [ ] Uruchomione testy: `php bin/phpunit`
- [ ] Lintowanie kodu: `php bin/console lint:twig` + `php bin/console lint:yaml`
- [ ] Migracje: `php bin/console doctrine:migrations:migrate`
- [ ] Cache wyczyszczony: `php bin/console cache:clear`
- [ ] Assets zbudowane: `npm run build`
- [ ] Manualne testy kluczowych funkcji</contents>
</xai:function_call">docs/ai_project_overview.md
