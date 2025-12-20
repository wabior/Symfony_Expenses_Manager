# Struktura projektu - Podsumowanie dla AI

## Główne komponenty systemu

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

## Baza danych

### Schemat (aktualny)
```sql
-- Użytkownicy
CREATE TABLE user (id INT AUTO_INCREMENT, email VARCHAR(180) UNIQUE, roles JSON, password VARCHAR(255), PRIMARY KEY(id));

-- Kategorie
CREATE TABLE category (id INT AUTO_INCREMENT, name_english VARCHAR(255), name_polish VARCHAR(255), PRIMARY KEY(id));

-- Wydatki
CREATE TABLE expense (
    id INT AUTO_INCREMENT,
    name VARCHAR(255),
    amount DECIMAL(10,2),
    date DATE,
    payment_date DATE NULL,
    payment_status VARCHAR(20) DEFAULT 'unpaid',
    category_id INT,
    created_at DATETIME,
    updated_at DATETIME,
    PRIMARY KEY(id),
    FOREIGN KEY (category_id) REFERENCES category(id)
);

-- Menu
CREATE TABLE menu (
    id INT AUTO_INCREMENT,
    route_name VARCHAR(255),
    friendly_name VARCHAR(255),
    path VARCHAR(255),
    order INT,
    activated TINYINT(1),
    PRIMARY KEY(id)
);
```

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

## Rozszerzenia planowane

### Version 2.0 - Wydatki cykliczne
**Nowe encje**:
- `Expense`: definicja wydatku z polem `recurring_frequency` (int, 0-12)
- `ExpenseOccurrence`: wystąpienia wydatków z datą i statusem płatności

**Nowe funkcjonalności**:
- Checkbox przy dodawaniu wydatku
- Przycisk "Utwórz nowy miesiąc"
- Kopiowanie nieopłaconych wydatków cyklicznych

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

## Dokumentacja dla AI

### Pliki konfiguracyjne dla Cursor:
- `.cursorrules` - Podstawowe informacje o projekcie dla AI
- `.cursor/rules.md` - Szczegółowe reguły kodowania i przykłady

### Dokumentacja techniczna:
- `docs/README_for_AI.md` - Indeks wszystkich plików dokumentacyjnych
- `docs/ai_analysis.md` - Pełna analiza obecnego systemu
- `docs/ai_development_plan.md` - Plan rozwoju i nowe funkcje
- `docs/ai_recurring_expenses_spec.md` - Szczegółowa specyfikacja wydatków cyklicznych
- `docs/ai_scalability_analysis.md` - Analiza wydajności i skalowalności
- `docs/ai_project_structure.md` - Struktura projektu i workflow

## Przydatne komendy

### Symfony
```bash
# Uruchomienie serwera deweloperskiego
symfony server:start

# Wyczyszczenie cache
php bin/console cache:clear

# Migracje
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
- [ ] Manualne testy kluczowych funkcji