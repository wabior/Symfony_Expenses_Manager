<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MenuRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RegistrationController extends BaseController
{
    public function __construct(
        RouterInterface $router,
        MenuRepository $menuRepository,
        RequestStack $requestStack
    ) {
        parent::__construct($router, $menuRepository, $requestStack);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Redirect logged-in users
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $errors = [];
        $email = '';
        $password = '';
        $confirmPassword = '';

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('registration', $token))) {
                $errors['general'] = 'Nieprawidłowy token CSRF.';
            }

            $email = $request->request->get('email', '');
            $password = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirm_password', '');

            // Validate email
            if (empty($email)) {
                $errors['email'] = 'Email jest wymagany.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Podaj prawidłowy adres email.';
            } elseif ($userRepository->findByEmail($email)) {
                $errors['email'] = 'Użytkownik z tym adresem email już istnieje.';
            }

            // Validate password
            if (empty($password)) {
                $errors['password'] = 'Hasło jest wymagane.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Hasło musi mieć co najmniej 8 znaków.';
            }

            // Validate password confirmation
            if (empty($confirmPassword)) {
                $errors['confirm_password'] = 'Potwierdzenie hasła jest wymagane.';
            } elseif ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Hasła nie są identyczne.';
            }

            // If no errors, create user
            if (empty($errors)) {
                $user = new User();
                $user->setEmail($email);
                $user->setRoles(null); // NULL means default roles will be applied

                // Hash the password
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Create the user
                try {
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // Add success message and redirect
                    $this->addFlash('success', 'Konto zostało utworzone pomyślnie. Możesz się teraz zalogować.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $errors['general'] = 'Wystąpił błąd podczas tworzenia konta.';
                }
            }
        }

        return $this->renderWithRoutes('security/register.html.twig', [
            'errors' => $errors,
            'email' => $email,
        ]);
    }
}