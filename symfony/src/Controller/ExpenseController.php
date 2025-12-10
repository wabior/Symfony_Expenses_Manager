<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Service\ExpenseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    #[Route('/expenses', name: 'expenses', defaults: ['year' => null, 'month' => null])]
    public function index(Request $request, ?int $year = null, ?int $month = null): Response
    {
        // Default to current month if no params provided
        if ($year === null || $month === null) {
            $now = new \DateTime();
            $year = $now->format('Y');
            $month = $now->format('n');
        }

        $expenses = $this->expenseService->getExpensesByMonth($year, $month);
        $categories = $this->expenseService->getAllCategories();
        $navigation = $this->expenseService->getNavigationMonths($year, $month);

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
            $this->expenseService->addExpense($request);
            return $this->redirectToRoute('expenses');
        }

        $categories = $this->expenseService->getAllCategories();

        return $this->renderWithRoutes('expenses/add.html.twig', [
            'categories' => $categories,
        ]);
    }
}