# Izolacja danych użytkownika - Abstrakcja

## Stworzona abstrakcja: `BaseUserService`

### Kluczowe metody:

1. **`getCurrentUser()`** - bezpieczne pobieranie zalogowanego użytkownika
2. **`requireAuthenticatedUser()`** - wymaga zalogowania, rzuca wyjątek jeśli brak
3. **`findByUser()`** - automatyczne filtrowanie po użytkowniku
4. **`findOneByUser()`** - znajdowanie jednej encji po użytkowniku
5. **`countByUser()`** - liczenie encji po użytkowniku
6. **`createEntityWithUser()`** - tworzenie encji z przypisanym użytkownikiem
7. **`ensureEntityBelongsToUser()`** - weryfikacja dostępu do encji

### Zastosowanie:

**CategoryService:**
```php
// Zamiast:
$user = $this->tokenStorage->getToken()->getUser();
return $this->entityManager->getRepository(Category::class)->findBy(['user' => $user]);

// Teraz:
return $this->findByUser(Category::class);
```

**ExpenseService:**
```php
// Zamiast:
$expense = new Expense();
$expense->setUser($this->getCurrentUser());

// Teraz:
$expense = $this->createEntityWithUser(Expense::class);
```

### Korzyści:

✅ **Bezpieczeństwo** - automatyczna filtracja po użytkowniku  
✅ **DRY** - brak powtarzającego się kodu  
✅ **Spójność** - wszystkie serwisy używają tych samych metod  
✅ **Utrzymanie** - zmiana w jednym miejscu affects wszystkie serwisy  
✅ **Ochrona przed błędami** - nie da się zapomnieć o filtracji  

### Przyszłe rozszerzenia:

Każdy nowy serwis dziedziczący po `BaseUserService` automatycznie otrzyma:
- Izolację danych
- Bezpieczne pobieranie użytkownika
- Wbudowane walidacje dostępu

**Przykład nowego serwisu:**
```php
class BudgetService extends BaseUserService
{
    public function getUserBudgets(): array
    {
        return $this->findByUser(Budget::class);
    }
    
    public function createBudget(string $name): Budget
    {
        $budget = $this->createEntityWithUser(Budget::class);
        $budget->setName($name);
        return $budget;
    }
}
```
