<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Service\CategoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ExpenseService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RouteController extends BaseController
{
    public function __construct(
        RouterInterface $router,
        protected MenuRepository $menuRepository,
        private ExpenseService $expensesService,
        private CategoryService $categoryService,
        RequestStack $requestStack
    )
    {
        parent::__construct($router, $menuRepository, $requestStack);
    }

    #[Route('/', name: 'home', options: ['friendly_name' => 'Start', 'order' => 1])]
    public function home(): Response
    {
        $hasCategories = $this->categoryService->hasCategories();
        
        return $this->renderWithRoutes('home.html.twig', [
            'hasCategories' => $hasCategories
        ]);
    }

    #[Route('/about', name: 'about', options: ['friendly_name' => 'O nas', 'order' => 4])]
    public function about(): Response
    {
        return $this->renderWithRoutes('about.html.twig');
    }

}
