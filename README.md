# 💰 Symfony Expenses Manager

[![Symfony](https://img.shields.io/badge/Symfony-7.1-000000?style=flat&logo=symfony)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)](https://mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker)](https://docker.com/)

Aplikacja webowa do zarządzania osobistymi wydatkami zbudowana w Symfony 7.1.  
Umożliwia śledzenie, kategoryzowanie i analizę wydatków z intuicyjnym interfejsem użytkownika.

![Screenshot aplikacji](https://github.com/wabior/Symfony_Expenses_Manager/assets/50226176/82223d55-27cc-46cf-a3f5-48e045deaff7)

## ✨ Funkcjonalności

### 👤 **System użytkowników**
- ✅ Rejestracja nowych użytkowników
- ✅ Logowanie/wylogowanie
- ✅ Bezpieczne hashowanie haseł
- ✅ Ochrona CSRF

### 💸 **Zarządzanie wydatkami**
- ✅ Dodawanie, edycja i usuwanie wydatków
- ✅ Kategoryzowanie wydatków
- ✅ Przegląd wydatków wg miesięcy i lat
- ✅ Status płatności (opłacone/nieopłacone)
- ✅ Dynamiczne daty płatności

### 📂 **Kategorie wydatków**
- ✅ Tworzenie własnych kategorii
- ✅ Edycja i usuwanie kategorii
- ✅ Hierarchiczna struktura kategorii

### 🎛️ **Panel administratora**
- ✅ Zarządzanie pozycjami menu
- ✅ Konfiguracja widoczności elementów
- ✅ Ustawienia kolejności menu

### 🎨 **Interfejs użytkownika**
- ✅ Responsywny design (Tailwind CSS)
- ✅ Ciemny/jasny motyw
- ✅ Intuicyjna nawigacja
- ✅ Polskie tłumaczenie

## 🛠️ **Technologie**

- **Backend**: Symfony 7.1 (PHP 8.2+)
- **Baza danych**: MySQL 8.0
- **ORM**: Doctrine 3.1
- **Frontend**: Twig + Tailwind CSS
- **Build**: Webpack Encore
- **Konteneryzacja**: Docker & Docker Compose
- **Bezpieczeństwo**: Symfony Security Bundle

## 🚀 **Instalacja i uruchomienie**

### Wymagania wstępne
- Docker i Docker Compose
- Port 8000 wolny na localhost

### Szybkie uruchomienie

```bash
# 1. Sklonuj repozytorium
git clone https://github.com/wabior/Symfony_Expenses_Manager.git
cd Symfony_Expenses_Manager

# 2. Uruchom kontenery Docker
docker compose up -d --build

# 3. Zainstaluj zależności (jeśli potrzebne)
docker compose exec php bash -c "cd /var/www/html/symfony && composer install && npm install"

# 4. Wykonaj migracje bazy danych
docker compose exec php bash -c "cd /var/www/html/symfony && php bin/console doctrine:migrations:migrate --no-interaction"

# 5. Załaduj dane testowe (opcjonalne)
docker compose exec php bash -c "cd /var/www/html/symfony && php bin/console doctrine:fixtures:load --no-interaction"
```

Aplikacja będzie dostępna pod adresem: **http://localhost:8000**

## 📖 **Użytkowanie**

### Dla nowych użytkowników:
1. Przejdź na stronę główną
2. Kliknij **"Zaloguj"** lub **"Rejestracja"**
3. Zarejestruj nowe konto lub zaloguj się
4. Rozpocznij zarządzanie wydatkami!

### Funkcje dostępne po zalogowaniu:
- **📊 Wydatki**: Przeglądaj i zarządzaj swoimi wydatkami
- **📂 Kategorie**: Twórz i organizuj kategorie wydatków  
- **⚙️ Panel admin**: Zarządzaj ustawieniami menu (tylko administratorzy)

## 🛠️ **Przydatne komendy**

```bash
# Zarządzanie kontenerami
docker compose down                    # Zatrzymaj kontenery
docker compose down -v                 # Zatrzymaj i usuń wolumeny (kasuje bazę!)
docker compose logs -f php             # Podgląd logów PHP
docker compose logs -f db              # Podgląd logów bazy danych

# Dostęp do kontenerów
docker compose exec php bash           # Konsola PHP
docker compose exec db mysql -u symfony -psymfony symfony  # Konsola MySQL

# Symfony komendy
docker compose exec php php /var/www/html/symfony/bin/console cache:clear
docker compose exec php php /var/www/html/symfony/bin/console doctrine:migrations:migrate
docker compose exec php php /var/www/html/symfony/bin/console doctrine:fixtures:load
```

## 📁 **Struktura projektu**

```
symfony/
├── src/
│   ├── Controller/          # Kontrolery aplikacji
│   │   ├── CategoryController.php
│   │   ├── ExpenseController.php
│   │   ├── MenuController.php
│   │   ├── RegistrationController.php
│   │   └── SecurityController.php
│   ├── Entity/             # Encje Doctrine
│   │   ├── Category.php
│   │   ├── Expense.php
│   │   ├── Menu.php
│   │   └── User.php
│   ├── Repository/         # Repozytoria Doctrine
│   ├── Service/            # Logika biznesowa
│   └── Form/               # Formularze Symfony
├── templates/              # Szablony Twig
├── config/                 # Konfiguracja Symfony
├── public/                 # Pliki publiczne
└── migrations/             # Migracje bazy danych


## 🔒 **Bezpieczeństwo**

- **Autentyfikacja**: Symfony Security z bezpiecznym hashowaniem
- **Autoryzacja**: Role-based access control (ROLE_USER, ROLE_ADMIN)
- **CSRF Protection**: Ochrona przed atakami CSRF
- **SQL Injection**: Chronione przez Doctrine ORM
- **XSS Protection**: Escaping w szablonach Twig

## 🤝 **Współpraca**

Zachęcamy do tworzenia issues i pull requestów! 

### Jak przyczynić się do rozwoju:
1. **Fork** projektu
2. **Utwórz branch** dla swojej funkcji: `git checkout -b feature/nazwa-funkcji`
3. **Commituj zmiany**: `git commit -m 'Dodaj nową funkcję'`
4. **Push do brancha**: `git push origin feature/nazwa-funkcji`
5. **Utwórz Pull Request**

## 📄 **Licencja**

Ten projekt jest dostępny na licencji **proprietary** - wszystkie prawa zastrzeżone.

## 🙏 **Podziękowania**

- [Symfony](https://symfony.com/) - Framework PHP
- [Doctrine](https://www.doctrine-project.org/) - ORM dla PHP
- [Tailwind CSS](https://tailwindcss.com/) - Framework CSS
- [Docker](https://www.docker.com/) - Konteneryzacja aplikacji

---

**Rozwijane przez**: [wabior](https://github.com/wabior)
