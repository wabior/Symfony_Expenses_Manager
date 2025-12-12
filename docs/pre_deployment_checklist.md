# Pre-Deployment Checklist

## 🔍 **Kod i Konfiguracja**
- [ ] **BEZPIECZEŃSTWO:** Skopiować `.env.prod.example` do `.env.prod`
- [ ] Wygenerować nowy APP_SECRET: `openssl rand -hex 32`
- [ ] Zaktualizować DATABASE_URL z endpointem RDS
- [ ] Upewnić się, że `.env.prod` NIE jest commitowany (sprawdzić .gitignore)
- [ ] Przetestować `npm run build` - assets produkcyjne
- [ ] Zainstalować `composer install --no-dev --optimize-autoloader`
- [ ] Uruchomić migracje: `php bin/console doctrine:migrations:migrate`
- [ ] Wyczyścić cache: `php bin/console cache:clear`

## ☁️ **AWS Infrastructure**
- [ ] Utworzyć VPC z publicznym i prywatnym subnetem
- [ ] Uruchomić EC2 t2.micro (Ubuntu 22.04)
- [ ] Skonfigurować Security Groups (SSH:22, HTTP:80, HTTPS:443)
- [ ] Uruchomić RDS MySQL db.t2.micro
- [ ] Połączyć RDS z VPC EC2
- [ ] Ustawić backup RDS (7 dni)

## 🔐 **Bezpieczeństwo**
- [ ] Utworzyć IAM user z ograniczonymi uprawnieniami
- [ ] Skonfigurować AWS Certificate Manager (bezpłatny SSL)
- [ ] Ustawić Route 53 dla domeny
- [ ] Włączyć CloudWatch monitoring
- [ ] Skonfigurować AWS WAF (opcjonalnie)

## 🖥️ **Konfiguracja Serwera EC2**
```bash
# Update systemu
sudo apt update && sudo apt upgrade -y

# Apache & PHP
sudo apt install apache2 php8.2 php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-intl -y
sudo a2enmod rewrite

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js & npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## 📁 **Deployment Kodu**
```bash
# Na EC2
sudo mkdir -p /var/www/html
sudo chown -R ubuntu:ubuntu /var/www/html

# Sklonować repo
git clone https://github.com/your-repo/symfony-expenses.git /var/www/html/symfony

# Zainstalować zależności
cd /var/www/html/symfony
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Skopiować konfigurację
cp .env.prod .env.local
# Edytować .env.local z rzeczywistymi wartościami AWS
```

## 🗃️ **Baza Danych**
- [ ] Uruchomić migracje Doctrine
- [ ] Załadować podstawowe dane (fixtures)
- [ ] Sprawdzić połączenie aplikacji z bazą
- [ ] Utworzyć użytkownika administracyjnego

## 🌐 **Konfiguracja Apache**
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/symfony/public
    
    <Directory /var/www/html/symfony/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/symfony_error.log
    CustomLog ${APACHE_LOG_DIR}/symfony_access.log combined
</VirtualHost>
```

## 🔒 **SSL & Domain**
- [ ] Zarejestrować domenę (~\cd /home/jarek/projects/Symfony_Expenses_Manager/docs && echo "# Pre-Deployment Checklist

## 🔍 **Kod i Konfiguracja**
- [ ] **BEZPIECZEŃSTWO:** Skopiować `.env.prod.example` do `.env.prod`
- [ ] Wygenerować nowy APP_SECRET: `openssl rand -hex 32`
- [ ] Zaktualizować DATABASE_URL z endpointem RDS
- [ ] Upewnić się, że `.env.prod` NIE jest commitowany (sprawdzić .gitignore)
- [ ] Przetestować \`npm run build\` - assets produkcyjne
- [ ] Zainstalować \`composer install --no-dev --optimize-autoloader\`
- [ ] Uruchomić migracje: \`php bin/console doctrine:migrations:migrate\`
- [ ] Wyczyścić cache: \`php bin/console cache:clear\`

## ☁️ **AWS Infrastructure**
- [ ] Utworzyć VPC z publicznym i prywatnym subnetem
- [ ] Uruchomić EC2 t2.micro (Ubuntu 22.04)
- [ ] Skonfigurować Security Groups (SSH:22, HTTP:80, HTTPS:443)
- [ ] Uruchomić RDS MySQL db.t2.micro
- [ ] Połączyć RDS z VPC EC2
- [ ] Ustawić backup RDS (7 dni)

## 🔐 **Bezpieczeństwo**
- [ ] Utworzyć IAM user z ograniczonymi uprawnieniami
- [ ] Skonfigurować AWS Certificate Manager (bezpłatny SSL)
- [ ] Ustawić Route 53 dla domeny
- [ ] Włączyć CloudWatch monitoring
- [ ] Skonfigurować AWS WAF (opcjonalnie)

## 🖥️ **Konfiguracja Serwera EC2**
\`\`\`bash
# Update systemu
sudo apt update && sudo apt upgrade -y

# Apache & PHP
sudo apt install apache2 php8.2 php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-intl -y
sudo a2enmod rewrite

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js & npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
\`\`\`

## 📁 **Deployment Kodu**
\`\`\`bash
# Na EC2
sudo mkdir -p /var/www/html
sudo chown -R ubuntu:ubuntu /var/www/html

# Sklonować repo
git clone https://github.com/your-repo/symfony-expenses.git /var/www/html/symfony

# Zainstalować zależności
cd /var/www/html/symfony
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Skopiować konfigurację
cp .env.prod .env.local
# Edytować .env.local z rzeczywistymi wartościami AWS
\`\`\`

## 🗃️ **Baza Danych**
- [ ] Uruchomić migracje Doctrine
- [ ] Załadować podstawowe dane (fixtures)
- [ ] Sprawdzić połączenie aplikacji z bazą
- [ ] Utworzyć użytkownika administracyjnego

## 🌐 **Konfiguracja Apache**
\`\`\`apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/symfony/public
    
    <Directory /var/www/html/symfony/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/symfony_error.log
    CustomLog \${APACHE_LOG_DIR}/symfony_access.log combined
</VirtualHost>
\`\`\`

## 🔒 **SSL & Domain**
- [ ] Zarejestrować domenę (~\\$12/rok)
- [ ] Skonfigurować Route 53
- [ ] Wygenerować certyfikat SSL przez ACM
- [ ] Przekierować HTTP na HTTPS

## 📊 **Monitoring & Logi**
- [ ] Skonfigurować CloudWatch dla EC2
- [ ] Skonfigurować CloudWatch dla RDS
- [ ] Ustawić alarmy (CPU > 80%, pamięć)
- [ ] Skonfigurować logi aplikacji

## 🧪 **Testy**
- [ ] Sprawdzić czy strona główna się ładuje
- [ ] Przetestować logowanie
- [ ] Dodać przykładowy wydatek
- [ ] Sprawdzić responsywność na mobile
- [ ] Przetestować wszystkie funkcjonalności

## 🚀 **Post-Deployment**
- [ ] Utworzyć AMI z skonfigurowanego EC2
- [ ] Skonfigurować backup strategię
- [ ] Ustawić monitoring kosztów
- [ ] Zapisać wszystkie credentials bezpiecznie
- [ ] Udokumentować proces dla przyszłych deploymentów

---
**Data wykonania:** __________
**Wykonał:** _________________
**Środowisko:** _______________
**Wersja aplikacji:** _________
" > pre_deployment_checklist.md2/rok)
- [ ] Skonfigurować Route 53
- [ ] Wygenerować certyfikat SSL przez ACM
- [ ] Przekierować HTTP na HTTPS

## 📊 **Monitoring & Logi**
- [ ] Skonfigurować CloudWatch dla EC2
- [ ] Skonfigurować CloudWatch dla RDS
- [ ] Ustawić alarmy (CPU > 80%, pamięć)
- [ ] Skonfigurować logi aplikacji

## 🧪 **Testy**
- [ ] Sprawdzić czy strona główna się ładuje
- [ ] Przetestować logowanie
- [ ] Dodać przykładowy wydatek
- [ ] Sprawdzić responsywność na mobile
- [ ] Przetestować wszystkie funkcjonalności

## 🚀 **Post-Deployment**
- [ ] Utworzyć AMI z skonfigurowanego EC2
- [ ] Skonfigurować backup strategię
- [ ] Ustawić monitoring kosztów
- [ ] Zapisać wszystkie credentials bezpiecznie
- [ ] Udokumentować proces dla przyszłych deploymentów

---
**Data wykonania:** __________
**Wykonał:** _________________
**Środowisko:** _______________
**Wersja aplikacji:** _________

