<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class MenuController extends BaseController
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        MenuRepository $menuRepository
    )
    {
        parent::__construct($router, $menuRepository);
        $this->entityManager = $entityManager;
        $this->menuRepository = $menuRepository;
    }

    #[Route('/admin/menu', name: 'admin_menu')]
    public function index(MenuRepository $menuRepository): Response
    {
        $menuItems = $menuRepository->findAll();;

        return $this->renderWithRoutes('admin/menu.html.twig', [
            'menuItems' => $menuItems,
        ]);
    }

    #[Route('/admin/menu/save', name: 'admin_menu_save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        $data = $request->request->all();

        foreach ($this->menuRepository->findAll() as $menu) {
            $id = $menu->getId();
            $isActive = isset($data['menu'][$id]['activated']) ? (bool)$data['menu'][$id]['activated'] : false;
            $menu->setActivated($isActive);
            $this->entityManager->persist($menu);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('admin_menu');
    }
}
