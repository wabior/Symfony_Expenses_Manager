<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use App\Controller\BaseController;
use Symfony\Component\Routing\RouterInterface;

class RenderService extends BaseController
{
    public function __construct(RouterInterface $router)
    {
        parent::__construct($router);
    }

    public function renderHome(): Response
    {
        return $this->renderWithRoutes('home.html.twig');
    }

    public function renderAbout(): Response
    {
        return $this->renderWithRoutes('about.html.twig');
    }
}
