<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ExpenseService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

class RouteController extends BaseController
{
    private ExpenseService $expensesService;

    public function __construct(
        RouterInterface $router,
        ExpenseService $expensesService,
        MenuRepository $menuRepository
    )
    {
        parent::__construct($router, $menuRepository);
        $this->expensesService = $expensesService;
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

    #[Route('/expenses', name: 'expenses', options: ['friendly_name' => 'Wydatki', 'order' => 2])]
    public function expenses(): Response
    {
        $expenses = $this->expensesService->getAllExpenses();
        return $this->renderWithRoutes('expenses/index.html.twig', [
            'expenses' => $expenses,
        ]);
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
}
