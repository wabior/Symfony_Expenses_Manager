# Szczegółowe reguły kodowania - Symfony Expenses Manager

## Architektura aplikacji

### MVC Pattern z Service Layer
```
Controller (HTTP) → Service (Logic) → Repository (Data) → Entity (Model)
```

### Przykład struktury kontrolera:
```php
#[Route('/expenses/{year}/{month}', name: 'expenses')]
public function index(Request $request, int $year, int $month): Response
{
    $data = $this->expenseService->getExpensesByMonth($year, $month);
    return $this->renderWithRoutes('expenses/index.html.twig', $data);
}
```

## Konwencje nazewnictwa

### Klasy i pliki:
- **Encje**: `PascalCase.php` (Expense.php, Category.php)
- **Kontrolery**: `PascalCaseController.php` (ExpenseController.php)
- **Serwisy**: `PascalCaseService.php` (ExpenseService.php)
- **Repository**: `PascalCaseRepository.php` (ExpenseRepository.php)

### Metody i właściwości:
- **Metody**: `camelCase` (getExpensesByMonth, addExpense)
- **Właściwości**: `camelCase` (`$entityManager`, `$expenseService`)
- **Stałe**: `UPPER_SNAKE_CASE` (`STATUS_PAID = 'paid'`)

### Baza danych:
- **Tabele**: `snake_case` (`expense`, `expense_occurrence`)
- **Kolumny**: `snake_case` (`payment_status`, `created_at`)
- **Klucz obcy**: `table_name_id` (`category_id`, `user_id`)

## Struktura plików

### Encje (Entity)
```php
<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\ExpenseRepository")]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Gettery i settery
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
}
```

### Serwisy (Service)
```php
<?php
namespace App\Service;

class ExpenseService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getExpensesByMonth(int $year, int $month): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month');

        return $this->entityManager
            ->getRepository(Expense::class)
            ->findByMonth($startDate, $endDate);
    }
}
```

### Kontrolery (Controller)
```php
<?php
namespace App\Controller;

use App\Service\ExpenseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExpenseController extends BaseController
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    #[Route('/expenses/{year}/{month}', name: 'expenses')]
    public function index(int $year, int $month): Response
    {
        $expenses = $this->expenseService->getExpensesByMonth($year, $month);
        return $this->renderWithRoutes('expenses/index.html.twig', [
            'expenses' => $expenses
        ]);
    }
}
```

## Twig Templates

### Struktura szablonu:
```twig
{% extends 'base.html.twig' %}

{% block title %}Page Title{% endblock %}

{% block content %}
<div class="container mx-auto">
    <h1 class="text-xl font-bold mb-4">Title</h1>

    {% for item in items %}
        <div class="item">{{ item.name }}</div>
    {% endfor %}
</div>
{% endblock %}
```

### CSS Classes (Tailwind):
- **Containers**: `container mx-auto`
- **Spacing**: `mb-4`, `mt-4`, `p-4`
- **Typography**: `text-xl font-bold`, `text-lg`
- **Colors**: `text-red-500` (errors), `text-green-600` (success)

## JavaScript

### AJAX calls:
```javascript
fetch('/expenses/update-status/' + expenseId, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({status: newStatus})
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Update UI
        location.reload();
    }
});
```

## Bezpieczeństwo

### Walidacja formularzy:
```php
#[Assert\NotBlank]
#[Assert\Length(min: 3, max: 255)]
private ?string $name = null;
```

### CSRF Protection:
```twig
<form action="{{ path('expense_add') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token('expense_add') }}">
</form>
```

## Testowanie

### Unit test dla serwisu:
```php
class ExpenseServiceTest extends TestCase
{
    public function testGetExpensesByMonth(): void
    {
        $mockRepo = $this->createMock(ExpenseRepository::class);
        $mockRepo->expects($this->once())
            ->method('findByMonth')
            ->willReturn([]);

        $service = new ExpenseService($this->entityManager);
        $result = $service->getExpensesByMonth(2024, 1);

        $this->assertIsArray($result);
    }
}
```

## Git Workflow

### Nazewnictwo branchy:
- **Format**: `{numer-issue}-{tytuł-issue}`
- **Przykłady**:
  - `123-add-user-authentication`
  - `45-fix-payment-status-bug`
  - `67-update-expense-validation`

### Commit message format:
- **Nagłówek**: taki sam jak nazwa brancha (bez numeru issue jeśli nie dotyczy)
- **Przykłady**:
  ```
  123-add-user-authentication

  Implement user authentication system with login/logout
  - Add User entity with password hashing
  - Create security configuration
  - Add login/logout forms
  ```

### Dodawanie nowej funkcji:
1. **Utwórz branch** zgodnie z konwencją nazewnictwa
2. **Entity** - dodaj pola/migrację
3. **Repository** - metody dostępu do danych
4. **Service** - logika biznesowa
5. **Controller** - endpoint HTTP
6. **Template** - interfejs użytkownika
7. **Testy** - pokrycie kodu
8. **Commit** z tytułem takim jak branch

## Wydajność

### Optymalizacje zapytań:
- Używaj JOIN zamiast wielu zapytań
- Dodawaj indeksy dla często filtrowanych kolumn
- Używaj pagination dla dużych list
- Cache dla statycznych danych

### Przykład optymalnego zapytania:
```php
$this->createQueryBuilder('e')
    ->select('e', 'c')  // JOIN category
    ->join('e.category', 'c')
    ->where('e.date >= :start')
    ->andWhere('e.date < :end')
    ->setParameters(['start' => $start, 'end' => $end])
    ->getQuery()
    ->getResult();
```

## Powiązana dokumentacja

Zobacz również:
- `docs/ai_analysis.md` - Analiza obecnego systemu
- `docs/ai_development_plan.md` - Plan rozwoju
- `docs/ai_scalability_analysis.md` - Zagadnienia wydajności