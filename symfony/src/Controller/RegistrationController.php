<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MenuRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        CsrfTokenManagerInterface $csrfTokenManager,
        #[Autowire(service: 'monolog.logger.registration')] LoggerInterface $registrationLogger
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
            $registrationLogger->info('[REGISTRATION] POST request received', [
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);
            $token = $request->request->get('_csrf_token');
            $registrationLogger->debug('[REGISTRATION] CSRF token received', ['token' => $token]);

            if (!$csrfTokenManager->isTokenValid(new CsrfToken('registration', $token))) {
                $registrationLogger->warning('[REGISTRATION] Invalid CSRF token', [
                    'ip' => $request->getClientIp(),
                ]);
                $errors['general'] = 'Nieprawidłowy token CSRF.';
            } else {
                $registrationLogger->info('[REGISTRATION] CSRF token valid');
            }

            $email = $request->request->get('email', '');
            $password = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirm_password', '');

            $registrationLogger->info('[REGISTRATION] Form data received', [
                'email' => $email,
                'password_length' => strlen($password),
            ]);

            // Validate email
            if (empty($email)) {
                $registrationLogger->warning('[REGISTRATION] Email validation failed: empty');
                $errors['email'] = 'Email jest wymagany.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $registrationLogger->warning('[REGISTRATION] Email validation failed: invalid format', [
                    'email' => $email,
                ]);
                $errors['email'] = 'Podaj prawidłowy adres email.';
            } elseif ($userRepository->findByEmail($email)) {
                $registrationLogger->warning('[REGISTRATION] Email validation failed: user exists', [
                    'email' => $email,
                ]);
                $errors['email'] = 'Użytkownik z tym adresem email już istnieje.';
            } else {
                $registrationLogger->info('[REGISTRATION] Email validation passed', [
                    'email' => $email,
                ]);
            }

            // Validate password
            if (empty($password)) {
                $registrationLogger->warning('[REGISTRATION] Password validation failed: empty');
                $errors['password'] = 'Hasło jest wymagane.';
            } elseif (strlen($password) < 8) {
                $registrationLogger->warning('[REGISTRATION] Password validation failed: too short');
                $errors['password'] = 'Hasło musi mieć co najmniej 8 znaków.';
            } else {
                $registrationLogger->info('[REGISTRATION] Password validation passed');
            }

            // Validate password confirmation
            if (empty($confirmPassword)) {
                $registrationLogger->warning('[REGISTRATION] Password confirmation validation failed: empty');
                $errors['confirm_password'] = 'Potwierdzenie hasła jest wymagane.';
            } elseif ($password !== $confirmPassword) {
                $registrationLogger->warning('[REGISTRATION] Password confirmation validation failed: mismatch');
                $errors['confirm_password'] = 'Hasła nie są identyczne.';
            } else {
                $registrationLogger->info('[REGISTRATION] Password confirmation validation passed');
            }

            $registrationLogger->info('[REGISTRATION] Validation completed', [
                'error_count' => count($errors),
            ]);

            // If no errors, create user
            if (empty($errors)) {
                $registrationLogger->info('[REGISTRATION] All validations passed, proceeding with user creation', [
                    'email' => $email,
                ]);
                $user = new User();
                $user->setEmail($email);
                // Zapisujemy pustą tablicę ról - w encji i tak zawsze zostanie dodane ROLE_USER,
                // ale w bazie nie będzie problemu z NOT NULL na kolumnie JSON.
                $user->setRoles([]);

                // Hash the password
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Create the user
                try {
                    $registrationLogger->info('[REGISTRATION] Persisting new user', [
                        'email' => $email,
                    ]);
                    $entityManager->persist($user);
                    $registrationLogger->debug('[REGISTRATION] User entity persisted, flushing...');
                    $entityManager->flush();
                    $registrationLogger->info('[REGISTRATION] User flushed successfully', [
                        'email' => $email,
                    ]);

                    // Add success message and redirect
                    $this->addFlash('success', 'Konto zostało utworzone pomyślnie. Możesz się teraz zalogować.');
                    $registrationLogger->info('[REGISTRATION] Registration successful', [
                        'email' => $email,
                    ]);
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $registrationLogger->error('[REGISTRATION ERROR] Exception during user creation', [
                        'email' => $email,
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
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