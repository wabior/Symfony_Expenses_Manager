<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Service\ExpenseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RouteController extends BaseController
{
    private ExpenseService $expenseService;
    
    public function __construct(
        RouterInterface $router,
        protected MenuRepository $menuRepository,
        RequestStack $requestStack,
        ExpenseService $expenseService
    )
    {
        parent::__construct($router, $menuRepository, $requestStack);
        $this->expenseService = $expenseService;
    }

    #[Route('/', name: 'home', options: ['friendly_name' => 'Start', 'order' => 1])]
    public function home(): Response
    {
        $categories = [];
        $hasCategories = false;
        
        if ($this->getUser()) {
            $categories = $this->expenseService->getAllCategories();
            $hasCategories = !empty($categories);
        }
        
        return $this->renderWithRoutes('home.html.twig', [
            'hasCategories' => $hasCategories
        ]);
    }

    #[Route('/about', name: 'about', options: ['friendly_name' => 'O nas', 'order' => 4])]
    public function about(): Response
    {
        return $this->renderWithRoutes('about.html.twig');
    }

    #[Route('/rodo', name: 'rodo', options: ['friendly_name' => 'RODO', 'order' => 5])]
    public function contact(): Response
    {
        return $this->renderWithRoutes('rodo.html.twig');
    }
}
