# Plan Deploymentu Aplikacji Symfony Expenses Manager na AWS

## 📋 **Przegląd Projektu**

**Nazwa aplikacji:** Symfony Expenses Manager
**Framework:** Symfony 7.1
**PHP:** >= 8.2
**Baza danych:** MySQL 8.0 (aktualnie skonfigurowana jako PostgreSQL w .env)
**Frontend:** Twig templates + Tailwind CSS + JavaScript
**Assets:** Webpack Encore

## 🎯 **Cele Deploymentu**

1. **Uruchomienie aplikacji na AWS całkowicie za darmo** (Free Tier - 12 miesięcy)
2. **Nauka AWS DevOps** od podstaw do zaawansowanych technik
3. **Bezpieczna, skalowalna infrastruktura**
4. **Automatyzacja deploymentu**

## 🏗️ **Architektura Docelowa**

```
Internet → Route 53 (DNS) → CloudFront (CDN) → ALB (Load Balancer) → EC2 (App Server)
                                      ↓
Database: RDS MySQL (w prywatnym subnet)
                                      ↓
Storage: S3 (assets, backups)
                                      ↓
Monitoring: CloudWatch + X-Ray
```

## 📦 **Aktualny Stan Aplikacji**

### ✅ **Gotowe komponenty:**
- MVC struktura (Symfony)
- Uwierzytelnianie użytkowników
- CRUD dla wydatków i kategorii
- Migracje Doctrine
- Webpack Encore dla assets
- Docker Compose (lokalnie)

### ⚠️ **Problemy do rozwiązania:**
- **Konflikt bazy danych:** `.env` wskazuje PostgreSQL, ale docker-compose używa MySQL
- **Brak konfiguracji produkcyjnej** (APP_ENV=prod)
- **Brak CI/CD pipeline**
- **Brak monitoringu**

## 🚀 **Plan Deploymentu - Faza 1: Podstawy (1-2 tygodnie)**

### **Tydzień 1: Przygotowanie aplikacji**

#### **⚠️ WAŻNE - Bezpieczeństwo:**
- **NIGDY** nie commituj plików `.env*` z rzeczywistymi hasłami
- Użyj szablonu `.env.prod.example` i skopiuj do `.env.prod`
- Plik `.env.prod` jest automatycznie ignorowany przez `.gitignore`
- Użyj AWS Systems Manager Parameter Store dla wrażliwych danych w produkcji

#### **1.1 Naprawa konfiguracji bazy danych**
```bash
# W symfony/.env zmienić na MySQL dla RDS
DATABASE_URL="mysql://username:password@rds-endpoint:3306/symfony_db?serverVersion=8.0"
```

#### **1.2 Dodanie zmiennych środowiskowych dla produkcji**
```bash
# Skopiować szablon i wypełnić wrażliwe dane
cp .env.prod.example .env.prod

# Wygenerować nowy APP_SECRET
openssl rand -hex 32

# Wypełnić rzeczywiste dane AWS RDS
# APP_SECRET=generated-secret-key
# DATABASE_URL=mysql://user:pass@rds-endpoint/db
```

#### **1.3 Konfiguracja bezpieczeństwa**
- Wygenerować nowy APP_SECRET
- Skonfigurować trusted proxies
- Ustawić secure cookies

#### **1.4 Build assets dla produkcji**
```bash
npm run build
# lub
yarn build
```

### **Tydzień 2: AWS Setup podstawowy**

#### **2.1 Konto AWS i IAM**
- Utworzyć konto AWS (jeśli nie istnieje)
- Skonfigurować IAM user z programatic access
- Włączyć MFA
- Ustawić billing alerts

#### **2.2 VPC (Virtual Private Cloud)**
```bash
# Public subnet dla EC2
# Private subnet dla RDS
# Internet Gateway
# NAT Gateway (jeśli potrzeba wychodzącego internetu)
# Security Groups
```

#### **2.3 EC2 Instance**
- **Typ:** t2.micro (Free Tier)
- **AMI:** Ubuntu 22.04 LTS
- **Storage:** 8GB EBS (Free Tier)
- **Security Group:** SSH (22), HTTP (80), HTTPS (443)

#### **2.4 RDS Database**
- **Typ:** db.t2.micro (Free Tier)
- **Engine:** MySQL 8.0
- **Storage:** 20GB (Free Tier limit)
- **Multi-AZ:** Nie (oszczędność kosztów)
- **Backup:** 7 dni (bezpłatnie)

## 🛠️ **Faza 2: Infrastruktura jako Kod (2-4 tygodnie)**

### **Konfiguracja serwera EC2**

#### **Ubuntu Server Setup:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y
sudo a2enmod rewrite
sudo systemctl enable apache2

# Install PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-intl -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js & npm (dla Webpack)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Configure Apache
sudo nano /etc/apache2/sites-available/symfony.conf
```

#### **Apache Virtual Host:**
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

### **Deployment aplikacji**

#### **1. Przygotowanie kodu:**
```bash
# Na lokalnym komputerze
cd /path/to/symfony
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Utworzyć .env.prod z produkcyjnymi ustawieniami
```

#### **2. Wdrożenie na EC2:**
```bash
# Na EC2 przez SSH
sudo mkdir -p /var/www/html
sudo chown -R ubuntu:ubuntu /var/www/html

# Sklonować kod z Git
git clone https://github.com/your-repo/symfony-expenses.git /var/www/html/symfony

# Zainstalować zależności
cd /var/www/html/symfony
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Skopiować konfigurację środowiska
cp .env.prod .env.local
# Edytować .env.local z rzeczywistymi wartościami AWS
```

#### **3. Migracje bazy danych:**
```bash
# Uruchomić migracje
php bin/console doctrine:migrations:migrate --no-interaction

# Załadować fixtures (opcjonalnie dla testowych danych)
php bin/console doctrine:fixtures:load --no-interaction
```

#### **4. Permissions:**
```bash
# Ustawić poprawne uprawnienia
sudo chown -R www-data:www-data /var/www/html/symfony
sudo chmod -R 755 /var/www/html/symfony
sudo chmod -R 777 /var/www/html/symfony/var
```

## 🔒 **Faza 3: Bezpieczeństwo i SSL (1 tydzień)**

### **AWS Certificate Manager (bezpłatny SSL)**
```bash
# Request certificate dla domeny
# CloudFront + ALB dla HTTPS
```

### **Security Best Practices**
- Usuń domyślne reguły Security Group
- Użyj najmniejszych uprawnień IAM
- Włącz AWS GuardDuty
- Skonfiguruj AWS WAF (Web Application Firewall)

## 📊 **Faza 4: Monitoring i Logi (1 tydzień)**

### **CloudWatch Setup**
- Metrics dla EC2 (CPU, Memory, Disk)
- RDS monitoring
- Application logs
- Alerty (np. wysoki CPU > 80%)

### **Log Analysis**
```bash
# CloudWatch Logs dla Apache
# Symfony logs
# Database slow query logs
```

## 🔄 **Faza 5: CI/CD Pipeline (2-3 tygodnie)**

### **GitHub Actions (bezpłatne)**
```yaml
# .github/workflows/deploy.yml
name: Deploy to AWS
on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
    
    - name: Install dependencies
      run: composer install --no-dev --optimize-autoloader
    
    - name: Build assets
      run: |
        npm install
        npm run build
    
    - name: Deploy to EC2
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.EC2_HOST }}
        username: ubuntu
        key: ${{ secrets.EC2_SSH_KEY }}
        script: |
          cd /var/www/html/symfony
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm install
          npm run build
          php bin/console cache:clear
          php bin/console doctrine:migrations:migrate --no-interaction
```

## 🎓 **Ścieżka Nauki DevOps AWS**

### **Poziom 1: Podstawy (1-2 miesiące)**
- [ ] AWS Console navigation
- [ ] EC2, RDS, S3, VPC
- [ ] Security Groups, IAM
- [ ] Basic Linux administration
- [ ] Apache/PHP configuration

### **Poziom 2: Infrastructure as Code (2-3 miesiące)**
- [ ] CloudFormation templates
- [ ] Terraform
- [ ] AWS CLI
- [ ] Bash scripting

### **Poziom 3: CI/CD & Automation (3-4 miesiące)**
- [ ] GitHub Actions / GitLab CI
- [ ] AWS CodePipeline
- [ ] Docker containers
- [ ] Kubernetes (EKS)

### **Poziom 4: Advanced (4-6 miesięcy)**
- [ ] Monitoring & Alerting
- [ ] Auto Scaling
- [ ] Disaster Recovery
- [ ] Cost Optimization

## 💰 **Kosztorys (Free Tier)**

| Usługa | Free Tier Limit | Koszt po wyczerpaniu |
|--------|-----------------|----------------------|
| EC2 t2.micro | 750h/miesiąc (12 miesięcy) | ~$8/miesiąc |
| RDS db.t2.micro | 750h/miesiąc (12 miesięcy) | ~$13/miesiąc |
| S3 | 5GB + 20k GET/2k PUT | ~$0.02/GB |
| CloudFront | 1TB transfer | ~$0.085/GB |
| Route 53 | 1M queries | ~$0.50/miesiąc |
| **Łącznie w roku 1:** | **$0** | **~$21.50/miesiąc** |

## 📋 **Checklist Deploymentu**

### **Pre-deployment:**
- [ ] Przygotować domenę
- [ ] Skonfigurować AWS konto
- [ ] Naprawić konfigurację bazy danych
- [ ] Zbudować assets produkcyjne
- [ ] Utworzyć .env.prod

### **AWS Setup:**
- [ ] VPC z subnets
- [ ] Security Groups
- [ ] EC2 instance
- [ ] RDS database
- [ ] IAM roles

### **Application Deployment:**
- [ ] Zainstalować zależności na EC2
- [ ] Skonfigurować Apache
- [ ] Wdrożyć kod aplikacji
- [ ] Uruchomić migracje
- [ ] Skonfigurować SSL

### **Post-deployment:**
- [ ] Monitoring setup
- [ ] Backup configuration
- [ ] CI/CD pipeline
- [ ] Security hardening

## 🚨 **Plan Kontyngencyjny**

### **Gdy Free Tier się skończy:**
1. **EC2:** Przejść na t3.nano (~$4/miesiąc) lub Lightsail ($3.50/miesiąc)
2. **RDS:** Przejść na db.t3.micro (~$13/miesiąc)
3. **Optymalizacja kosztów:**
   - Scheduled start/stop EC2 w nocy
   - Reserved Instances (zniżki za zobowiązanie)
   - Spot Instances (tańsze, ale przerywane)

### **Backup Plan:**
- Eksport danych do S3
- Lokalna kopia bezpieczeństwa
- Możliwość szybkiego przeniesienia na inną chmurę

## 🔍 **Troubleshooting**

### **Common Issues:**
- **Błąd połączenia z RDS:** Sprawdź Security Groups i VPC
- **403 Forbidden:** Permissions dla www-data
- **Assets nie ładują się:** Webpack build lub permissions
- **Database migrations fail:** Sprawdź DATABASE_URL

### **Debug Tools:**
```bash
# Sprawdź logi Apache
sudo tail -f /var/log/apache2/error.log

# Sprawdź logi Symfony
tail -f /var/www/html/symfony/var/log/prod.log

# Test połączenia z bazą
php bin/console doctrine:query:sql "SELECT 1"
```

## 📚 **Dalsze Kroki po Deployment**

1. **Performance Optimization**
2. **User Analytics (Google Analytics)**
3. **Email Notifications (SES)**
4. **API Development**
5. **Mobile App**

---

**Data utworzenia:** $(date)
**Autor:** AI Assistant
**Status:** Gotowy do implementacji
