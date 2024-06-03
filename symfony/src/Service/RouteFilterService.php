<?php

namespace App\Service;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RouteFilterService
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getFilteredRoutes(): array
    {
        $routeCollection = $this->router->getRouteCollection();
        $routes = [];

        foreach ($routeCollection as $routeName => $route) {
            if ($this->isSystemRoute($routeName, $route->getPath())) {
                continue;
            }

            $routes[] = [
                'name' => $routeName,
                'path' => $route->getPath(),
            ];
        }

        return $routes;
    }

    private function isSystemRoute(string $routeName, string $routePath): bool
    {
        // Pomijanie tras systemowych, które mają prefiks "_" lub "/_"
        if (strpos($routeName, '_') === 0 || strpos($routePath, '/_') === 0) {
            return true;
        }

        return false;
    }
}
