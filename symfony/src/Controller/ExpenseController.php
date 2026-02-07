<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Service\ExpenseService;
use App\Entity\ExpenseOccurrence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExpenseController extends BaseController
{
    protected RouterInterface $router;
    private ExpenseService $expenseService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        RouterInterface $router,
        EntityManagerInterface $entityManager,
        ExpenseService $expenseService,
        MenuRepository $menuRepository,
        RequestStack $requestStack
        )
    {
        $this->expenseService = $expenseService;
        $this->entityManager = $entityManager;
        parent::__construct($router, $menuRepository, $requestStack);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/{year}/{month}', name: 'expenses', defaults: ['year' => null, 'month' => null], requirements: ['year' => '\d+', 'month' => '\d+'])]
    public function index(Request $request, ?string $year = null, ?string $month = null): Response
    {
        // Default to current month if no params provided
        if ($year === null || $month === null) {
            $now = new \DateTime();
            return $this->redirectToRoute('expenses', [
                'year' => $now->format('Y'),
                'month' => $now->format('n')
            ]);
        }

        $yearInt = $year !== null ? (int) $year : null;
        $monthInt = $month !== null ? (int) $month : null;

        $expenses = $this->expenseService->getExpenseOccurrencesByMonth($yearInt, $monthInt);
        $categories = $this->expenseService->getAllCategories();
        $navigation = $this->expenseService->getNavigationMonths($yearInt, $monthInt);

        return $this->renderWithRoutes('expenses/index.html.twig', [
            'expenses' => $expenses,
            'categories' => $categories,
            'year' => $year,
            'month' => $month,
            'prevYear' => $navigation['prevYear'],
            'prevMonth' => $navigation['prevMonth'],
            'nextYear' => $navigation['nextYear'],
            'nextMonth' => $navigation['nextMonth'],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/add', name: 'expenses_add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $this->expenseService->addExpense($request);
                $now = new \DateTime();
                return $this->redirectToRoute('expenses', [
                    'year' => $now->format('Y'),
                    'month' => $now->format('n')
                ]);
            } catch (\Exception $e) {
                // Log error for debugging
                error_log('Expense add error: ' . $e->getMessage());
                error_log('Expense add trace: ' . $e->getTraceAsString());
                
                // Add flash message for user feedback
                $this->addFlash('error', 'Wystąpił błąd podczas dodawania wydatku: ' . $e->getMessage());
                
                return $this->redirectToRoute('expenses_add');
            }
        }

        $categories = $this->expenseService->getAllCategories();
        $categoryOptions = $this->expenseService->getCategoriesForSelect();

        return $this->renderWithRoutes('expenses/add.html.twig', [
            'categories' => $categories,
            'categoryOptions' => $categoryOptions,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/update-status/{id}', name: 'expenses_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!$status || !in_array($status, ['unpaid', 'paid', 'partially_paid'])) {
            return new JsonResponse(['success' => false], 400);
        }

        // Sprawdź czy to ID wystąpienia czy wydatku
        $occurrence = $this->entityManager->find(ExpenseOccurrence::class, $id);
        
        if ($occurrence) {
            // To jest wystąpienie wydatku cyklicznego
            try {
                $this->expenseService->updateOccurrencePaymentStatus($id, $status);
                return new JsonResponse(['success' => true]);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
        } else {
            // To jest zwykły wydatek
            $expense = $this->expenseService->updateExpenseStatus($id, $status);
            
            if ($expense) {
                return new JsonResponse(['success' => true]);
            } else {
                return new JsonResponse(['success' => false], 404);
            }
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/import-recurring/{year}/{month}', name: 'expenses_import_recurring', methods: ['POST'])]
    public function importRecurringExpenses(int $year, int $month): Response
    {
        try {
            $createdOccurrences = $this->expenseService->createOccurrencesForMonth($year, $month);

            $this->addFlash('success', sprintf('Zaimportowano %d wystąpień wydatków cyklicznych do bieżącego miesiąca', count($createdOccurrences)));

            // Pozostajemy w tym samym miesiącu
            return $this->redirectToRoute('expenses', ['year' => $year, 'month' => $month]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Wystąpił błąd podczas importowania wydatków cyklicznych: ' . $e->getMessage());
            return $this->redirectToRoute('expenses', ['year' => $year, 'month' => $month]);
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/occurrence/{id}/status', name: 'expenses_update_occurrence_status', methods: ['POST'])]
    public function updateOccurrenceStatus(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $status = $data['status'] ?? null;

            if (!$status || !in_array($status, ['unpaid', 'paid', 'partially_paid'])) {
                return new JsonResponse(['success' => false, 'error' => 'Invalid status'], 400);
            }

            $this->expenseService->updateOccurrencePaymentStatus($id, $status);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/edit/{id}', name: 'expenses_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response {
        // Najpierw spróbuj znaleźć Expense
        $expense = $this->expenseService->getExpenseById($id);

        if ($expense) {
            // Jeśli Expense istnieje i jest cykliczny, przekieruj do edycji wystąpienia
            if ($expense->isRecurring()) {
                // Znajdź lub utwórz wystąpienie dla bieżącego miesiąca
                $currentDate = new \DateTime();
                $monthStart = new \DateTime($currentDate->format('Y-m-01'));
                $monthEnd = (clone $monthStart)->modify('+1 month -1 day');

                $existingOccurrence = $this->entityManager->getRepository(ExpenseOccurrence::class)
                    ->findByExpenseAndDateRange($expense, $monthStart, $monthEnd);

                if (!empty($existingOccurrence)) {
                    $occurrence = $existingOccurrence[0];
                } else {
                    $occurrence = $this->expenseService->createExpenseOccurrence($expense, $monthStart);
                    $this->entityManager->persist($occurrence);
                    $this->entityManager->flush();
                }

                return $this->redirectToRoute('expenses_edit', ['id' => $occurrence->getId()]);
            }

            // Jeśli Expense nie jest cykliczny, edytuj go bezpośrednio
            if ($request->isMethod('POST')) {
                try {
                    $this->expenseService->updateExpense($request, $id);
                    $this->addFlash('success', 'Expense updated successfully');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Expense update failed: ' . $e->getMessage());
                }
            }

            $categories = $this->expenseService->getAllCategories();
            $categoryOptions = $this->expenseService->getCategoriesForSelect();

            return $this->renderWithRoutes('expenses/edit.html.twig', [
                'expense' => $expense,
                'categories' => $categories,
                'categoryOptions' => $categoryOptions,
            ]);
        }

        // Jeśli nie znaleziono Expense, spróbuj znaleźć Occurrence
        $occurrence = $this->entityManager->find(ExpenseOccurrence::class, $id);

        if ($occurrence) {
            // Edycja wystąpienia
            if ($request->isMethod('POST')) {
                try {
                    $data = $request->request->all();
                    $newAmount = $data['amount'] ?? $occurrence->getAmount();
                    $applyToFuture = isset($data['apply_to_future']);

                    $occurrence->setAmount($newAmount);

                    if ($applyToFuture) {
                        // Zaktualizuj kwotę domyślną w Expense i wszystkie wystąpienia od tej daty
                        $expense = $occurrence->getExpense();
                        $expense->setAmount($newAmount);
                        $this->expenseService->updateOccurrencesFromDate($expense, $occurrence->getOccurrenceDate(), $newAmount);
                    }

                    $this->entityManager->flush();
                    $this->addFlash('success', 'Occurrence updated successfully');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Occurrence update failed: ' . $e->getMessage());
                }
            }

            return $this->renderWithRoutes('expenses/edit_occurrence.html.twig', [
                'occurrence' => $occurrence,
            ]);
        }

        // Jeśli nic nie znaleziono
        throw $this->createNotFoundException('Expense or occurrence not found');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/delete/{id}', name: 'expenses_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        try {
            $this->expenseService->deleteExpense($id);
            $this->addFlash('success', 'Expense deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Expense deletion failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('expenses');
    }
}
