<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\MenuRepository;

class BaseController extends AbstractController
{
    public function __construct(
        protected RouterInterface $router,
        protected MenuRepository  $menuRepository,
        protected RequestStack $requestStack
    )
    {
    }

    protected function renderWithRoutes(string $view, array $parameters = [], ?Response $response = null): Response
    {
        // Pobierz aktualną nazwę trasy z requestu
        $currentRoute = $this->requestStack->getCurrentRequest()?->attributes->get('_route');

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
                'route_name' => $menuItem->getRouteName(),
                'active' => $currentRoute === $menuItem->getRouteName()
            ];
        }

        // Filtruj menu w zależności od statusu logowania użytkownika
        $user = $this->getUser();
        $filteredRoutes = array_filter($filteredRoutes, function ($route) use ($user) {
            // Elementy menu są aktywne tylko jeśli są włączone w bazie
            if (!$route['activated']) {
                return false;
            }

            $routeName = $route['route_name'];

            // Ukryj admin_menu dla użytkowników bez roli ROLE_ADMIN
            if ($routeName === 'admin_menu' && !$this->isGranted('ROLE_ADMIN')) {
                return false;
            }

            // Niektóre strony wymagają logowania
            $requiresAuth = in_array($routeName, ['expenses', 'categories', 'admin_menu']);

            if ($requiresAuth && !$user) {
                return false; // Ukryj jeśli wymaga autoryzacji ale użytkownik nie jest zalogowany
            }

            return true;
        });

        $parameters['menu_items'] = $filteredRoutes;
        $parameters['user'] = $user;

        return parent::render($view, $parameters, $response);
    }
}
