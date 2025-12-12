# Scripts - Narzędzia CLI dla Symfony Expenses Manager

Ten katalog zawiera narzędzia CLI do automatyzacji zadań związanych z rozwojem aplikacji.

## 🔄 GitHub Issues Sync (`github_sync.sh`)

### Opis
Skrypt synchronizuje issues z GitHub z lokalną dokumentacją, umożliwiając AI dostęp do aktualnych informacji o zadaniach.

### Wymagania
- **GitHub CLI** zainstalowany i skonfigurowany
- **jq** zainstalowany (do przetwarzania JSON)

### Instalacja wymagań

```bash
# GitHub CLI
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
sudo apt update
sudo apt install gh jq

# Logowanie do GitHub
gh auth login
```

### Użycie

```bash
# Przejdź do katalogu głównego projektu
cd /path/to/Symfony_Expenses_Manager

# Sprawdź status issues na GitHub
./scripts/github_sync.sh status

# Zsynchronizuj issues do pliku docs/github_context.md
./scripts/github_sync.sh sync
```

### Co robi skrypt

#### Komenda `status`:
- Pokazuje liczbę otwartych/zamkniętych issues
- Wyświetla 5 najnowszych otwartych issues
- Sprawdza czy GitHub CLI jest zainstalowany i skonfigurowany

#### Komenda `sync`:
- Pobiera wszystkie issues (otwarte i zamknięte) z GitHub
- Konwertuje je na format Markdown
- Zapisuje do `docs/github_context.md`
- AI może czytać ten plik aby mieć kontekst aktualnych zadań

### Przykład użycia w pracy z AI

```bash
# Przed sesją pracy z AI
./scripts/github_sync.sh sync

# AI będzie miała dostęp do aktualnych issues w docs/github_context.md
```

### Plik wyjściowy

Skrypt tworzy `docs/github_context.md` z:
- Podsumowaniem liczby issues
- Szczegółami wszystkich otwartych issues
- Szczegółami wszystkich zamkniętych issues
- Informacjami o milestone'ach, etykietach, datach

### Bezpieczeństwo

- Skrypt wymaga potwierdzenia przed synchronizacją
- Używa oficjalnego GitHub CLI
- Nie przechowuje wrażliwych danych
- Wszystkie operacje są lokalne

### Troubleshooting

#### "gh: command not found"
```bash
# Zainstaluj GitHub CLI
sudo apt install gh
gh auth login
```

#### "jq: command not found"
```bash
# Zainstaluj jq
sudo apt install jq
```

#### "Authentication required"
```bash
# Zaloguj się do GitHub
gh auth login
```

#### Błąd dostępu do repo
```bash
# Sprawdź czy masz dostęp do repo wabior/Symfony_Expenses_Manager
gh repo view wabior/Symfony_Expenses_Manager
```

## 🤖 Integracja z AI

### Jak AI korzysta z synchronizacji

Po uruchomieniu `./scripts/github_sync.sh sync`, plik `docs/github_context.md` zawiera:

#### 📋 **Aktualne informacje o issues:**
- **Numery i tytuły** wszystkich issues (#31, #32, itd.)
- **Statusy** - otwarte/zamknięte
- **Przypisane milestone'y** - cele rozwoju
- **Daty** utworzenia i aktualizacji
- **Etykiety** i priorytety

#### 🎯 **Korzyści dla AI:**
- **Pełny kontekst zadań** - wie które zadania są do zrobienia
- **Priorytety** - zna kolejność milestone'ów
- **Postęp prac** - może śledzić co zostało ukończone
- **Dokładne wymagania** - ma dostęp do pełnych opisów
- **Spójność** - wszystkie informacje są zsynchronizowane

#### 💡 **Jak AI może pomagać:**
- **Implementacja zadań** zgodnie z opisami z GitHub
- **Śledzenie postępów** w milestone'ach
- **Aktualizacje statusów** - sugestie kiedy zamknąć issues
- **Nowe pomysły** - propozycje dodatkowych zadań
- **Code review** - sprawdzenie zgodności z wymaganiami

### Przykład wykorzystania przez AI

```markdown
Widzę że issue #31 "User-Expense Relationship & Security" jest otwarty.
Według wymagań muszę:
1. Dodać user_id do tabeli expense
2. Zaktualizować encję Expense
3. Zmodyfikować zapytania żeby filtrować po użytkowniku
4. Dodać migrację dla istniejących danych

Czy chcesz żebym zaczął implementację?
```

## 📋 Workflow

1. **Przed rozpoczęciem pracy**: `./scripts/github_sync.sh status`
2. **Gdy potrzebujesz kontekstu**: `./scripts/github_sync.sh sync`
3. **AI czyta**: `docs/github_context.md`
4. **Regularne aktualizacje**: według potrzeb
