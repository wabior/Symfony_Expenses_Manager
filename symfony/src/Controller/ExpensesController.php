<?php

namespace App\Controller;

use App\Service\ExpenseService;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpensesController extends BaseController
{
    private ExpenseService $expensesService;
    private EntityManagerInterface $entityManager;

    public function __construct(ExpenseService $expensesService, EntityManagerInterface $entityManager)
    {
        $this->expensesService = $expensesService;
        $this->entityManager = $entityManager;
    }

    #[Route('/expenses', name: 'expenses', options: ['friendly_name' => 'Wydatki', 'order' => 2])]
    public function index(): Response
    {
        dd('index');
        // Pobierz wszystkie wydatki z serwisu
        $expenses = $this->expensesService->getAllExpenses();

        // Pokaż stronę z listą wydatków
        return $this->renderWithRoutes('expenses/index.html.twig', [
            'expenses' => $expenses,
        ]);
    }

    #[Route('/expenses/add', name: 'expenses_add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        dd('CONTROLLER');

        if ($request->isMethod('POST')) {
            $this->expensesService->addExpense($request);

            // Przekierowanie do strony z listą wydatków po dodaniu nowego wydatku
            return $this->redirectToRoute('expenses');
        }

        // Pobieranie kategorii z bazy danych
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        return $this->renderWithRoutes('expenses/add.html.twig', [
            'categories' => $categories,
        ]);
    }
}
