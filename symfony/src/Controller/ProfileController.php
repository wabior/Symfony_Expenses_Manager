<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ExpenseRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ProfileController extends BaseController
{
    public function __construct(
        RouterInterface $router,
        MenuRepository $menuRepository,
        RequestStack $requestStack
    ) {
        parent::__construct($router, $menuRepository, $requestStack);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->renderWithRoutes('profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/delete', name: 'profile_delete', methods: ['POST'])]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        ExpenseRepository $expenseRepository,
        CategoryRepository $categoryRepository,
        TokenStorageInterface $tokenStorage,
        #[Autowire(service: 'monolog.logger.user')] LoggerInterface $registrationLogger
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $token = $request->request->get('_csrf_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_account', $token))) {
            $this->addFlash('error', 'Nieprawidłowy token CSRF.');
            return $this->redirectToRoute('profile');
        }

        $registrationLogger->info('[PROFILE DELETE] Starting account deletion', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
        ]);

        try {
            // Delete user's expenses (occurrences will be cascaded)
            $expenses = $expenseRepository->findBy(['user' => $user]);
            foreach ($expenses as $expense) {
                $entityManager->remove($expense);
            }

            // Delete user's categories
            $categories = $categoryRepository->findBy(['user' => $user]);
            foreach ($categories as $category) {
                $entityManager->remove($category);
            }

            // Delete user
            $entityManager->remove($user);
            $entityManager->flush();

            $registrationLogger->info('[PROFILE DELETE] Account deleted successfully', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
            ]);

            $this->addFlash('success', 'Konto zostało usunięte.');
            $tokenStorage->setToken(null);

            // Logout and redirect
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $registrationLogger->error('[PROFILE DELETE] Exception during account deletion', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'message' => $e->getMessage(),
            ]);
            $this->addFlash('error', 'Wystąpił błąd podczas usuwania konta.');
            return $this->redirectToRoute('profile');
        }
    }
}
