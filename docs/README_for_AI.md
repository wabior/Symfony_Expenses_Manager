# Dokumentacja dla AI - Jak korzystać z plików

## Struktura dokumentacji

### Pliki główne dla Cursor AI:
- **`.cursorrules`** (katalog główny) - Podstawowe informacje o projekcie
- **`.cursor/rules.md`** - Szczegółowe reguły kodowania i konwencje

### Dokumentacja techniczna w `docs/`:
```
docs/
├── README_for_AI.md                    # Ten plik - instrukcje użycia
├── milestones.md                       # Kluczowe milestone'y rozwoju
├── github_issues.md                    # Szczegółowa lista zadań GitHub
├── github_context.md                   # Aktualny stan issues z GitHub (auto-generated)
├── ai_project_overview.md              # Analiza aplikacji i struktura projektu
├── ai_development_plan.md              # Plan rozwoju na przyszłość
├── ai_recurring_expenses_spec.md       # Specyfikacja wydatków cyklicznych (ze skalowalnością)
├── aws_deployment_plan.md              # Plan deploymentu na AWS
└── pre_deployment_checklist.md         # Checklist przed wdrożeniem
```

### Narzędzia CLI w `scripts/`:
```
scripts/
├── github_sync.sh                      # Synchronizacja issues z GitHub
└── README.md                           # Dokumentacja narzędzi CLI
```

## Jak korzystać z dokumentacji

### Przed rozpoczęciem pracy nad nową funkcją:

1. **Przeczytaj `.cursorrules`** - podstawowe informacje o projekcie
2. **Sprawdź `.cursor/rules.md`** - konwencje kodowania i przykłady
3. **Zobacz `docs/milestones.md`** - kluczowe cele rozwoju i status milestone'ów
4. **Zobacz `docs/github_issues.md`** - szczegółowa lista wszystkich zadań do wykonania
5. **Zsynchronizuj z GitHub**: `./scripts/github_sync.sh sync` - pobierz aktualny stan issues
6. **Zobacz `docs/github_context.md`** - aktualny stan issues z GitHub (po synchronizacji)
7. **Zobacz `docs/ai_project_overview.md`** - zrozumienie aktualnego stanu aplikacji i struktury
8. **Sprawdź `docs/ai_development_plan.md`** - czy nowa funkcja jest już zaplanowana
9. **Zobacz `docs/ai_recurring_expenses_spec.md`** - specyfikacja wydatków cyklicznych (w tym analiza skalowalności)

### Dla konkretnych zadań:

#### Dodawanie wydatków cyklicznych:
- Przeczytaj `docs/ai_recurring_expenses_spec.md`
- Zawiera kompletną specyfikację techniczną

#### Analiza wydajności/skalowalności:
- Przeczytaj `docs/ai_scalability_analysis.md`
- Zawiera analizę różnych podejść i rekomendacje

#### Modyfikacja istniejących funkcji:
- Zacznij od `docs/ai_project_overview.md` - znajdź odpowiednie pliki i szczegóły implementacji

#### Nowe funkcje nieujęte w planie:
- Dodaj opis do `docs/ai_development_plan.md`
- Stwórz podobną specyfikację jak dla wydatków cyklicznych

## Kluczowe informacje do zapamiętania

### Architektura aplikacji:
- **Symfony 6** z Doctrine ORM
- **MVC pattern** z service layer
- **MySQL** baza danych
- **Twig** szablony + **Tailwind CSS**
- **JavaScript** dla interaktywności

### Główne encje:
- `User` - użytkownicy (`symfony/src/Entity/User.php`)
- `Expense` - wydatki (`symfony/src/Entity/Expense.php`)
- `Category` - kategorie wydatków (`symfony/src/Entity/Category.php`)
- `Menu` - elementy nawigacji (`symfony/src/Entity/Menu.php`)
- Planowana: `ExpenseOccurrence` - wystąpienia wydatków cyklicznych

### Pliki konfiguracyjne:
- `symfony/config/packages/doctrine.yaml` - konfiguracja Doctrine
- `symfony/config/routes.yaml` - routing aplikacji
- `symfony/config/packages/security.yaml` - bezpieczeństwo

### Workflow developmentu:
1. **Encja** (jeśli potrzeba nowe pola)
2. **Migracja bazy danych**
3. **Serwis** (logika biznesowa)
4. **Kontroler** (endpoint API)
5. **Szablon** (UI)
6. **JavaScript** (jeśli potrzeba interaktywności)

## Najczęstsze zadania i gdzie je implementować

| Zadanie | Pliki do modyfikacji |
|---------|---------------------|
| Dodanie nowego pola do wydatku | `Expense.php`, migracja, `ExpenseService`, szablony |
| Nowa strona | Kontroler, szablon, routing |
| Logika biznesowa | Odpowiedni serwis |
| Zapytania do bazy | Repository |
| Interfejs użytkownika | Szablony Twig |
| Interaktywność | JavaScript w templates/ |

## Przydatne ścieżki

### Kod aplikacji:
- Encje: `symfony/src/Entity/`
- Kontrolery: `symfony/src/Controller/`
- Serwisy: `symfony/src/Service/`
- Szablony: `symfony/templates/`

### Konfiguracja:
- Routing: `symfony/config/routes.yaml`
- Baza danych: `symfony/config/doctrine.yaml`
- Bezpieczeństwo: `symfony/config/security.yaml`

### Baza danych:
- Migracje: `symfony/migrations/`

## Status aplikacji (Version 1.0)

### Dostępne funkcje:
✅ Logowanie/rejestracja
✅ Dodawanie wydatków z kategoriami
✅ Wyświetlanie wydatków wg miesięcy
✅ Zmiana statusu płatności (AJAX)
✅ Zarządzanie kategoriami
✅ Nawigacja między miesiącami

### Przygotowanie do deploymentu AWS:
✅ Naprawiona konfiguracja bazy danych (MySQL zamiast PostgreSQL)
✅ Utworzona konfiguracja produkcyjna (.env.prod)
✅ Przetestowany build assets produkcyjnych
✅ Przygotowany checklist pre-deployment
✅ Kompletny plan deploymentu na AWS

### Planowane milestone'y (Version 2.0+):
🔄 Secure Multi-User Expense Management
🔄 Complete Expense CRUD Operations
🔄 Recurring Expenses System Operational
🔄 Polished User Interface & Experience
🔄 Professional Reporting & Analytics
🔄 Advanced Features & Future-Proofing

Zobacz `docs/milestones.md` dla szczegółowych opisów i statusu.

## Zasady bezpieczeństwa

- Wszystkie kontrolery wymagają `ROLE_USER`
- CSRF protection na formularzach
- Prepared statements przez Doctrine
- Walidacja danych po stronie serwera

## Testowanie

- **Unit tests**: Serwisy i repozytoria
- **Integration tests**: Kontrolery z bazą
- **E2E tests**: Pełne scenariusze przez UI

## Deployment

- **Lokalnie:** Docker + Docker Compose
- **Produkcja:** AWS (EC2 + RDS) - Free Tier przez 12 miesięcy
- Symfony Flex dla pakietów
- Webpack Encore dla assetów
- Doctrine migrations dla bazy

### Przygotowanie do AWS Deployment

1. **Konfiguracja bazy danych:** Zmieniona z PostgreSQL na MySQL dla kompatybilności z AWS RDS
2. **Środowisko produkcyjne:** Utworzony plik `.env.prod` z konfiguracją bezpieczną
3. **Assets produkcyjne:** Przetestowany build dla środowiska produkcyjnego
4. **Checklist deployment:** Szczegółowa lista kroków w `docs/pre_deployment_checklist.md`
5. **Plan deployment:** Kompletny przewodnik w `docs/aws_deployment_plan.md`

---

## Checklist przed implementacją

- [ ] Przeczytano `.cursorrules` i `.cursor/rules.md`
- [ ] Sprawdzono `docs/ai_project_overview.md` - aktualny stan systemu i struktura
- [ ] Sprawdzono `docs/ai_development_plan.md` - czy funkcja jest zaplanowana
- [ ] Przeczytano odpowiednią dokumentację techniczną
- [ ] Zrozumiano aktualną strukturę projektu
- [ ] Sprawdzono czy funkcja nie jest już zaimplementowana
- [ ] Zaplanowano zmiany w bazie danych (jeśli potrzeba)
- [ ] Określono potrzebne pliki do modyfikacji
- [ ] Przemyślano bezpieczeństwo i walidację
- [ ] Sprawdzono `docs/ai_scalability_analysis.md` (dla funkcji wpływających na wydajność)
- [ ] Zaplanowano testy

## Integracja z GitHub

### Jak skonfigurowaliśmy połączenie z GitHub

1. **Instalacja GitHub CLI:**
   ```bash
   # Ubuntu/Debian
   curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
   echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
   sudo apt update && sudo apt install gh jq
   ```

2. **Autoryzacja:**
   ```bash
   gh auth login  # Wybór metody autoryzacji (token/SSH/web)
   ```

3. **Synchronizacja danych:**
   - `scripts/github_sync.sh status` - sprawdź status issues
   - `scripts/github_sync.sh sync` - synchronizuj wszystkie issues do `docs/github_context.md`

### Co możemy robić dzięki integracji z GitHub

#### 🤖 **Dla AI:**
- **Aktualny kontekst zadań** - AI zawsze wie które issues są otwarte/zamknięte
- **Przypisane milestone'y** - AI zna cele rozwoju i priorytety
- **Status rozwoju** - AI może śledzić postęp prac
- **Dokładne wymagania** - AI ma dostęp do pełnych opisów i kryteriów akceptacji

#### 👥 **Dla programistów:**
- **Synchronizacja przed pracą** - zawsze aktualne informacje o zadaniach
- **Sprawdzanie milestone'ów** - `gh api repos/wabior/Symfony_Expenses_Manager/milestones`
- **Lista issues** - `gh issue list --repo wabior/Symfony_Expenses_Manager`
- **Szczegóły issue** - `gh issue view 123 --repo wabior/Symfony_Expenses_Manager`

#### 📊 **Dostępne dane:**
- **Issues**: numery, tytuły, statusy, etykiety, milestone'y, daty
- **Milestone'y**: cele, opisy, kryteria akceptacji, liczba issues
- **Postęp**: które zadania są ukończone, które w trakcie

### Jak czytać milestone'y

#### Przegląd milestone'ów:
```bash
gh api repos/wabior/Symfony_Expenses_Manager/milestones | jq -r '.[] | "\(.number). \(.title) - \(.open_issues) issues"'
```

#### Szczegóły konkretnego milestone'u:
```bash
gh api repos/wabior/Symfony_Expenses_Manager/milestones/1 | jq '.description'
```

#### Issues w milestone'ach:
```bash
gh issue list --repo wabior/Symfony_Expenses_Manager --milestone "Secure Multi-User Expense Management"
```

### Pliki związane z GitHub

- **`docs/github_context.md`** - automatycznie generowany plik z aktualnymi issues (po synchronizacji)
- **`docs/milestones.md`** - ręcznie zarządzane opisy milestone'ów
- **`docs/github_issues.md`** - szczegółowe opisy wszystkich zadań do wykonania
- **`scripts/github_sync.sh`** - skrypt do synchronizacji z GitHub

### Workflow pracy z GitHub

1. **Przed rozpoczęciem pracy:** `./scripts/github_sync.sh sync`
2. **AI czyta kontekst** z `docs/github_context.md`
3. **Implementacja** zgodnie z milestone'ami
4. **Aktualizacja statusów** issues na GitHub
5. **Synchronizacja** przed następną sesją

---

## AWS Deployment & DevOps Learning

### Architektura AWS (Free Tier):
- **EC2 t2.micro**: Serwer aplikacji (750h/miesiąc przez 12 miesięcy)
- **RDS db.t2.micro**: Baza danych MySQL (750h/miesiąc przez 12 miesięcy)
- **Route 53**: DNS ($0.50/miesiąc)
- **Certificate Manager**: Bezpłatny SSL
- **CloudWatch**: Monitoring

### Koszt w pierwszym roku: **$0**
Po roku: ~$21.50/miesiąc

### Ścieżka nauki DevOps:
1. **Podstawy AWS** (1-2 miesiące): Console, EC2, RDS, VPC, IAM
2. **Infrastructure as Code** (2-3 miesiące): CloudFormation, Terraform
3. **CI/CD** (3-4 miesiące): CodePipeline, GitHub Actions, Docker
4. **Advanced** (4-6 miesięcy): Monitoring, Auto Scaling, Security

### Pliki deployment:
- `docs/aws_deployment_plan.md` - Kompletny plan deploymentu
- `docs/pre_deployment_checklist.md` - Checklist przed każdym wdrożeniem

---

**Pamiętaj**: Zawsze aktualizuj dokumentację po wprowadzeniu zmian!
- Dodaj nowe funkcje do `docs/ai_development_plan.md`
- Stwórz specyfikacje techniczne w stylu `docs/ai_recurring_expenses_spec.md`
- Zaktualizuj `.cursorrules` przy zmianach architektury
- Dodaj reguły kodowania do `.cursor/rules.md`
- Synchronizuj z GitHub przed każdą sesją pracy: `./scripts/github_sync.sh sync`