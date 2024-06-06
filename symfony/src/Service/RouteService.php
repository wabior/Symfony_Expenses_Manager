<?php

namespace App\Service;

use App\Entity\Menu;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\ORM\EntityManagerInterface;

class RouteService
{
    private RouterInterface $router;
    private EntityManagerInterface $entityManager;

    public function __construct(RouterInterface $router, EntityManagerInterface $entityManager)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    public function getRoutesForMenu(): array
    {
        $routeCollection = $this->router->getRouteCollection();
        $menuRepository = $this->entityManager->getRepository(Menu::class);
        $routes = [];

        foreach ($routeCollection as $routeName => $route) {
            $options = $route->getOptions();
            if (isset($options['friendly_name'])) {
                $menuItem = $menuRepository->findOneBy(['routeName' => $routeName]);
                if (!$menuItem) {
                    $menuItem = new Menu();
                    $menuItem->setRouteName($routeName);
                    $menuItem->setFriendlyName($options['friendly_name']);
                    $menuItem->setPath($route->getPath());
                    $menuItem->setOrder($options['order'] ?? PHP_INT_MAX);
                    $menuItem->setActivated(true);
                    $this->entityManager->persist($menuItem);
                    $this->entityManager->flush();
                }
                $routes[] = [
                    'name' => $routeName,
                    'path' => $route->getPath(),
                    'friendly_name' => $options['friendly_name'],
                    'order' => $options['order'] ?? PHP_INT_MAX,
                    'activated' => $menuItem->isActivated(),
                ];
            }
        }

        usort($routes, fn($a, $b) => $a['order'] <=> $b['order']);

        return $routes;
    }
}
