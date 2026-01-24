# 💰 Symfony Expenses Manager

[![Symfony](https://img.shields.io/badge/Symfony-7.1-000000?style=flat&logo=symfony)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)](https://mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker)](https://docker.com/)
[![AWS Ready](https://img.shields.io/badge/AWS-Deploy-FF9900?style=flat&logo=amazon-aws)](https://aws.amazon.com/)

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

### 📋 **Wymagania wstępne**
- Docker i Docker Compose
- Port 8000 wolny na localhost

---

## 🔧 **Środowisko Development (Docker)**

### Szybkie uruchomienie

```bash
# 1. Sklonuj repozytorium
git clone https://github.com/wabior/Symfony_Expenses_Manager.git
cd Symfony_Expenses_Manager

# 2. Skonfiguruj środowisko
cp symfony/.env symfony/.env.local
# Edytuj symfony/.env.local i zmień hasła:
# MYSQL_PASSWORD=twoje_bezpieczne_haslo_2024
# MYSQL_ROOT_PASSWORD=twoje_bezpieczne_haslo_root_2024
# APP_SECRET=twoj_sekretny_klucz

# 3. Uruchom kontenery Docker
docker compose up -d --build

# 4. Zainstaluj zależności (jeśli potrzebne)
docker compose exec php bash -c "cd /var/www/html/symfony && composer install && npm install"

# 5. Wykonaj migracje bazy danych
docker compose exec php bash -c "cd /var/www/html/symfony && php bin/console doctrine:migrations:migrate --no-interaction"
```

**Aplikacja będzie dostępna pod adresem:** **http://localhost:8000**  
**PhpMyAdmin:** **http://localhost:8080** (login: `symfony`, hasło: to z `.env.local`)

### 🔐 **Konfiguracja środowiska development**

Pliki konfiguracyjne są przygotowane do pracy:

- **`symfony/.env`** - szablon konfiguracji (nie zmieniaj bezpośrednio)
- **`symfony/.env.local`** - Twoje lokalne hasła i sekrety (nie commitowane)
- **`docker-compose.yml`** - używa zmiennych środowiskowych z `.env.local`

**Ważne:** Zawsze twórz `.env.local` z bezpiecznymi hasłami przed pierwszym uruchomieniem!

---

## 🌐 **Środowisko Produkcyjne (AWS)**

### 📋 **Wymagania produkcyjne**
- EC2 instance (Ubuntu 22.04 LTS)
- AWS RDS MySQL 8.0
- Apache2 + PHP 8.2+
- Domena z SSL

### 🚀 **Deployment na AWS**

```bash
# 1. Przygotowanie serwera EC2
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 php8.2 php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-intl -y

# 2. Instalacja Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 3. Klonowanie repozytorium
cd /var/www/html
sudo git clone https://github.com/wabior/Symfony_Expenses_Manager.git
sudo chown -R ubuntu:ubuntu Symfony_Expenses_Manager

# 4. Konfiguracja produkcyjna
cd Symfony_Expenses_Manager/symfony
cp .env.prod.example .env.prod.local

# 5. Wypełnij dane produkcyjne
nano .env.prod.local
```

### 🔐 **Konfiguracja `.env.prod.local`**

```bash
# Przykładowa konfiguracja produkcyjna
APP_ENV=prod
APP_SECRET=twoj-super-secret-klucz-prod-12345

# AWS RDS MySQL:
DATABASE_URL="mysql://aws_user:aws_password@mydb.abc123.eu-west-1.rds.amazonaws.com:3306/symfony_prod?serverVersion=8.0&charset=utf8mb4"

# Email (opcjonalnie):
MAILER_DSN=smtp://username:password@gmail.com:587?encryption=tls
```

### ⚙️ **Finalizacja deploymentu**

```bash
# 6. Instalacja zależności produkcyjnych
composer install --no-dev --optimize-autoloader

# 7. Build assets
npm install
npm run build

# 8. Kompilacja środowiska
composer dump-env prod

# 9. Cache i migracje
php bin/console cache:clear --env=prod
php bin/console doctrine:migrations:migrate --no-interaction

# 10. Ustawienia uprawnień
sudo chown -R www-data:www-data /var/www/html/Symfony_Expenses_Manager
sudo chmod -R 755 /var/www/html/Symfony_Expenses_Manager
sudo chmod -R 777 /var/www/html/Symfony_Expenses_Manager/symfony/var

# 11. Restart Apache
sudo systemctl restart apache2
```

### 🔧 **Konfiguracja Apache**

**Virtual Host (`/etc/apache2/sites-available/symfony.conf`):**
```apache
<VirtualHost *:80>
    ServerName twoja-domena.com
    DocumentRoot /var/www/html/Symfony_Expenses_Manager/symfony/public
    
    <Directory /var/www/html/Symfony_Expenses_Manager/symfony/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/symfony_error.log
    CustomLog ${APACHE_LOG_DIR}/symfony_access.log combined
</VirtualHost>
```

```bash
# Aktywacja konfiguracji
sudo a2ensite symfony
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 📖 **Użytkowanie**

### Jak zostać administratorem:
1. **Zarejestruj konto** przez http://localhost:8000/register
2. **Zaloguj się do PhpMyAdmin** (http://localhost:8080)
3. **W tabeli `user`** znajdź swojego użytkownika i edytuj kolumnę `roles` na:
   ```
   ["ROLE_ADMIN"]
   ```
4. **Zaloguj się ponownie** - masz dostęp do panelu admina!

### Dla nowych użytkowników:
1. Przejdź na stronę główną
2. Kliknij **"Zarejestruj się"** lub **"Zaloguj"**
3. Zarejestruj nowe konto lub zaloguj się
4. Rozpocznij zarządzanie wydatkami!

### Funkcje dostępne po zalogowaniu:
- **📊 Wydatki**: Przeglądaj i zarządzaj swoimi wydatkami
- **📂 Kategorie**: Twórz i organizuj kategorie wydatków  
- **⚙️ Panel admin**: Zarządzaj ustawieniami menu (tylko administratorzy)

---

## 🛠️ **Przydatne komendy**

### Development (Docker)
```bash
# Zarządzanie kontenerami
docker compose down                    # Zatrzymaj kontenery
docker compose down -v                 # Zatrzymaj i usuń wolumeny (kasuje bazę!)
docker compose logs -f php             # Podgląd logów PHP
docker compose logs -f db              # Podgląd logów bazy danych

# Dostęp do kontenerów
docker compose exec php bash           # Konsola PHP
docker compose exec db mysql -u symfony -p$MYSQL_PASSWORD symfony  # Konsola MySQL (użyj hasła z .env.local)

# Symfony komendy
docker compose exec php php /var/www/html/symfony/bin/console cache:clear
docker compose exec php php /var/www/html/symfony/bin/console doctrine:migrations:migrate
docker compose exec php php /var/www/html/symfony/bin/console doctrine:fixtures:load
```

### Produkcja (AWS)
```bash
# Zarządzanie aplikacją
php bin/console cache:clear --env=prod
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

# Logi
sudo tail -f /var/log/apache2/error.log
tail -f /var/www/html/Symfony_Expenses_Manager/symfony/var/log/prod.log
```

---

## 📁 **Struktura projektu**

```
Symfony_Expenses_Manager/
├── docker-compose.yml          # Konfiguracja Docker (development)
├── Dockerfile                  # Build PHP container
├── symfony/                    # Aplikacja Symfony
│   ├── src/
│   │   ├── Controller/         # Kontrolery aplikacji
│   │   ├── Entity/            # Encje Doctrine
│   │   ├── Repository/        # Repozytoria Doctrine
│   │   ├── Service/           # Logika biznesowa
│   │   └── Form/              # Formularze Symfony
│   ├── templates/             # Szablony Twig
│   ├── config/                # Konfiguracja Symfony
│   ├── public/                # Pliki publiczne
│   ├── migrations/             # Migracje bazy danych
│   ├── .env                   # Domyślna konfiguracja (development)
│   ├── .env.local             # Lokalne sekrety (nie commitowane)
│   └── .env.prod.example      # Szablon konfiguracji produkcyjnej
├── docs/                      # Dokumentacja
└── README.md                  # Ten plik
```

---

## 🔒 **Bezpieczeństwo**

### Development
- **Autentyfikacja**: Symfony Security z bezpiecznym hashowaniem
- **Autoryzacja**: Role-based access control (ROLE_USER, ROLE_ADMIN)
- **CSRF Protection**: Ochrona przed atakami CSRF
- **SQL Injection**: Chronione przez Doctrine ORM
- **XSS Protection**: Escaping w szablonach Twig

### Produkcja
- **Sekrety**: `.env.prod.local` nie jest commitowany
- **APP_SECRET**: Unikalny klucz dla produkcji
- **SSL**: Wymagany HTTPS na produkcji
- **Firewall**: Odpowiednie reguły Security Group w AWS

---

## 🤝 **Współpraca**

Zachęcamy do tworzenia issues i pull requestów! 

### Jak przyczynić się do rozwoju:
1. **Fork** projektu
2. **Utwórz branch** dla swojej funkcji: `git checkout -b feature/nazwa-funkcji`
3. **Commituj zmiany**: `git commit -m 'Dodaj nową funkcję'`
4. **Push do brancha**: `git push origin feature/nazwa-funkcji`
5. **Utwórz Pull Request**

---

## 📄 **Licencja**

Ten projekt jest dostępny na licencji **proprietary** - wszystkie prawa zastrzeżone.

---

## 🙏 **Podziękowania**

- [Symfony](https://symfony.com/) - Framework PHP
- [Doctrine](https://www.doctrine-project.org/) - ORM dla PHP
- [Tailwind CSS](https://tailwindcss.com/) - Framework CSS
- [Docker](https://www.docker.com/) - Konteneryzacja aplikacji
- [AWS](https://aws.amazon.com/) - Chmura produkcyjna

---

**Rozwijane przez**: [wabior](https://github.com/wabior)
