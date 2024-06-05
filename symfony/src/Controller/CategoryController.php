<?php

namespace App\Controller;

use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class CategoryController extends BaseController
{
    protected RouterInterface $router;
    private EntityManagerInterface $entityManager;
    private CategoryService $categoryService;

    public function __construct(RouterInterface $router, EntityManagerInterface $entityManager, CategoryService $categoryService)
    {
        $this->entityManager = $entityManager;
        $this->categoryService = $categoryService;
        parent::__construct($router);
    }

    #[Route('/categories', name: 'categories', options: ['friendly_name' => 'Kategorie', 'order' => 3])]
    public function index(): Response
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->renderWithRoutes('categories/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/categories/add', name: 'category_add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $nameEnglish = $request->request->get('nameEnglish');
            $namePolish = $request->request->get('namePolish');
            $this->categoryService->addCategory($nameEnglish, $namePolish);

            return $this->redirectToRoute('categories');
        }

        $categories = $this->categoryService->getAllCategories();

        return $this->renderWithRoutes('categories/add.html.twig', [
            'categories' => $categories,
        ]);
    }
}
