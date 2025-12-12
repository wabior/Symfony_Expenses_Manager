<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Service\ExpenseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExpenseController extends BaseController
{
    protected RouterInterface $router;
    private ExpenseService $expenseService;

    public function __construct(RouterInterface $router, EntityManagerInterface $entityManager, ExpenseService $expenseService, MenuRepository $menuRepository)
    {
        $this->expenseService = $expenseService;
        parent::__construct($router, $menuRepository);
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

        $expenses = $this->expenseService->getExpensesByMonth($yearInt, $monthInt);
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
                // Handle error - could add flash message or log error
                // For now, redirect back with error
                return $this->redirectToRoute('expenses_add');
            }
        }

        $categories = $this->expenseService->getAllCategories();

        return $this->renderWithRoutes('expenses/add.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/expenses/update-status/{id}', name: 'expenses_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!$status || !in_array($status, ['unpaid', 'paid'])) {
            return new JsonResponse(['success' => false], 400);
        }

        $expense = $this->expenseService->updateExpenseStatus($id, $status);

        if ($expense) {
            return new JsonResponse([
                'success' => true,
                'paymentDate' => $expense->getPaymentDate()?->format('Y-m-d') ?: 'N/A'
            ]);
        }

        return new JsonResponse(['success' => false], 404);
    }
}