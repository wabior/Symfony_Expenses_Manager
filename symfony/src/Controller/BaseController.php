<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\MenuRepository;

class BaseController extends AbstractController
{
    protected RouterInterface $router;
    protected MenuRepository $menuRepository;

    public function __construct(RouterInterface $router, MenuRepository $menuRepository)
    {
        $this->router = $router;
        $this->menuRepository = $menuRepository;
    }

    protected function renderWithRoutes(string $view, array $parameters = [], ?Response $response = null): Response
    {
        // Pobierz elementy menu z bazy danych
        $menuItems = $this->menuRepository->findBy([], ['order' => 'ASC']);

        // Przekształć elementy menu na format używany w widoku
        $filteredRoutes = [];

        foreach ($menuItems as $menuItem) {
            $filteredRoutes[] = [
                'name' => $menuItem->getFriendlyName(),
                'path' => $menuItem->getPath(),
                'friendly_name' => $menuItem->getFriendlyName(),
                'order' => $menuItem->getOrder(),
                'activated' => $menuItem->isActivated(),
            ];
        }

        // Filtruj tylko aktywne elementy menu
        $filteredRoutes = array_filter($filteredRoutes, function ($route) {
            return $route['activated'];
        });

        $parameters['menu_items'] = $filteredRoutes;

        return parent::render($view, $parameters, $response);
    }
}
