![obraz](https://github.com/wabior/Symfony_Expenses_Manager/assets/50226176/82223d55-27cc-46cf-a3f5-48e045deaff7)

## Instalacja i uruchomienie

### Wymagania
- Docker i Docker Compose
- Port 8000 wolny

### Uruchomienie projektu

1. **Uruchom kontenery Docker:**
   ```bash
   docker compose up -d --build
   ```

2. **Zainstaluj zależności (jeśli potrzebne):**
   ```bash
   docker compose exec php bash -c "cd /var/www/html/symfony && composer install && npm install"
   ```

3. **Wykonaj migracje bazy danych:**
   ```bash
   docker compose exec php bash -c "cd /var/www/html/symfony && php bin/console doctrine:migrations:migrate --no-interaction"
   ```

4. **Opcjonalnie: załaduj dane testowe:**
   ```bash
   docker compose exec php bash -c "cd /var/www/html/symfony && php bin/console doctrine:fixtures:load --no-interaction"
   ```

5. **Aplikacja będzie dostępna pod adresem:**
   - http://localhost:8000

### Przydatne komendy

```bash
# Zatrzymanie kontenerów
docker compose down

# Zatrzymanie i usunięcie wolumenów (usuwa bazę danych)
docker compose down -v

# Podgląd logów
docker compose logs -f php
docker compose logs -f db

# Wejście do kontenera PHP
docker compose exec php bash

# Wejście do bazy danych MySQL
docker compose exec db mysql -u symfony -psymfony symfony
```
