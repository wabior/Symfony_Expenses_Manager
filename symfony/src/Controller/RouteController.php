<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ExpenseService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

class RouteController extends BaseController
{
    public function __construct(
        RouterInterface $router,
        private ExpenseService $expensesService,
        protected MenuRepository $menuRepository,
    )
    {
    }

    #[Route('/', name: 'home', options: ['friendly_name' => 'Start', 'order' => 1])]
    public function home(): Response
    {
        return $this->renderWithRoutes('home.html.twig');
    }

    #[Route('/about', name: 'about', options: ['friendly_name' => 'O nas', 'order' => 4])]
    public function about(): Response
    {
        return $this->renderWithRoutes('about.html.twig');
    }

    #[Route('/expenses/{year?}/{month?}', name: 'expenses', requirements: ['year' => '\d{4}', 'month' => '\d{1,2}'], options: ['friendly_name' => 'Wydatki', 'order' => 2])]
    public function expenses(int $year = null, int $month = null): Response
    {
        $year = $year ?? (int)date('Y');
        $month = $month ?? (int)date('m');

        $expenses = $this->expensesService->getExpensesByMonth($year, $month);
        $navigationMonths = $this->expensesService->getNavigationMonths($year, $month);

        return $this->renderWithRoutes('expenses/index.html.twig', array_merge([
            'expenses' => $expenses,
        ], $navigationMonths));
    }

    #[Route('/expenses/add', name: 'expenses_add', methods: ['GET', 'POST'])]
    public function addExpense(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $this->expensesService->addExpense($request);
            return $this->redirectToRoute('expenses');
        }

        $categories = $this->expensesService->getAllCategories();

        return $this->renderWithRoutes('expenses/add.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/expenses/update-status/{id}', name: 'update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newStatus = $data['status'] ?? null;

        if (in_array($newStatus, ['unpaid', 'paid', 'partially_paid'])) {
            $expense = $this->expensesService->updateExpenseStatus($id, $newStatus);

            if ($expense) {
                return new JsonResponse([
                    'success' => true,
                    'paymentDate' => $expense->getPaymentDate() ? $expense->getPaymentDate()->format('Y-m-d') : 'N/A',
                ]);
            }
        }

        return new JsonResponse(['success' => false], 400);
    }

}
