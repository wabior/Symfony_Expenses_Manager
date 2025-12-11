# Dokumentacja dla AI - Jak korzystać z plików

## Struktura dokumentacji

### Pliki główne dla Cursor AI:
- **`.cursorrules`** (katalog główny) - Podstawowe informacje o projekcie
- **`.cursor/rules.md`** - Szczegółowe reguły kodowania i konwencje

### Dokumentacja techniczna w `docs/`:
```
docs/
├── README_for_AI.md                    # Ten plik - instrukcje użycia
├── ai_analysis.md                      # Pełna analiza aplikacji
├── ai_development_plan.md              # Plan rozwoju na przyszłość
├── ai_recurring_expenses_spec.md       # Specyfikacja wydatków cyklicznych
├── ai_scalability_analysis.md          # Analiza skalowalności i wydajności
└── ai_project_structure.md             # Struktura projektu - podsumowanie
```

## Jak korzystać z dokumentacji

### Przed rozpoczęciem pracy nad nową funkcją:

1. **Przeczytaj `.cursorrules`** - podstawowe informacje o projekcie
2. **Sprawdź `.cursor/rules.md`** - konwencje kodowania i przykłady
3. **Zobacz `docs/ai_analysis.md`** - zrozumienie aktualnego stanu aplikacji
4. **Sprawdź `docs/ai_development_plan.md`** - czy nowa funkcja jest już zaplanowana
5. **Zobacz `docs/ai_project_structure.md`** - szybkie przypomnienie struktury

### Dla konkretnych zadań:

#### Dodawanie wydatków cyklicznych:
- Przeczytaj `docs/ai_recurring_expenses_spec.md`
- Zawiera kompletną specyfikację techniczną

#### Analiza wydajności/skalowalności:
- Przeczytaj `docs/ai_scalability_analysis.md`
- Zawiera analizę różnych podejść i rekomendacje

#### Modyfikacja istniejących funkcji:
- Zacznij od `docs/ai_project_structure.md` - znajdź odpowiednie pliki
- Sprawdź `docs/ai_analysis.md` dla szczegółów implementacji

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

### Planowane (Version 2.0):
🔄 Wydatki cykliczne
🔄 Tworzenie nowego miesiąca
🔄 Edycja/usuwanie wydatków

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

- Docker + Docker Compose
- Symfony Flex dla pakietów
- Webpack Encore dla assetów
- Doctrine migrations dla bazy

---

## Checklist przed implementacją

- [ ] Przeczytano `.cursorrules` i `.cursor/rules.md`
- [ ] Sprawdzono `docs/ai_analysis.md` - aktualny stan systemu
- [ ] Sprawdzono `docs/ai_development_plan.md` - czy funkcja jest zaplanowana
- [ ] Przeczytano odpowiednią dokumentację techniczną
- [ ] Zrozumiano aktualną strukturę projektu
- [ ] Sprawdzono czy funkcja nie jest już zaimplementowana
- [ ] Zaplanowano zmiany w bazie danych (jeśli potrzeba)
- [ ] Określono potrzebne pliki do modyfikacji
- [ ] Przemyślano bezpieczeństwo i walidację
- [ ] Sprawdzono `docs/ai_scalability_analysis.md` (dla funkcji wpływających na wydajność)
- [ ] Zaplanowano testy

---

**Pamiętaj**: Zawsze aktualizuj dokumentację po wprowadzeniu zmian!
- Dodaj nowe funkcje do `docs/ai_development_plan.md`
- Stwórz specyfikacje techniczne w stylu `docs/ai_recurring_expenses_spec.md`
- Zaktualizuj `.cursorrules` przy zmianach architektury
- Dodaj reguły kodowania do `.cursor/rules.md`