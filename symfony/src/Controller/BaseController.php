<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    protected function renderWithRoutes(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $routeCollection = $this->router->getRouteCollection();
        $filteredRoutes = [];

        // Pierwsza iteracja: przefiltruj routy z przyjazną nazwą i dodaj je do tablicy
        foreach ($routeCollection as $routeName => $route) {
            if ($route->getOption('friendly_name')) {
                $filteredRoutes[] = [
                    'name' => $routeName,
                    'path' => $route->getPath(),
                    'friendly_name' => $route->getOption('friendly_name'),
                    'order' => $route->getOption('order') ?? null
                ];
            }
        }

        $routeCount = count($filteredRoutes);

        // Druga iteracja: ustaw odpowiednią wartość 'order' dla rout bez ustawionego 'order'
        foreach ($filteredRoutes as &$route) {
            if ($route['order'] === null) {
                $route['order'] = $routeCount;
            }
        }

        // Sortowanie rout na podstawie wartości 'order'
        usort($filteredRoutes, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        $parameters['menu_items'] = $filteredRoutes;

        return parent::render($view, $parameters, $response);
    }
}
