<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Controller;

use FastrackGps\Auth\Service\AuthenticationService;
use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Http\JsonResponse;
use FastrackGps\Core\Http\Request;
use FastrackGps\Core\Http\Response;
use Psr\Log\LoggerInterface;

final class LoginController
{
    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function showLoginForm(): Response
    {
        if ($this->authService->isAuthenticated()) {
            return Response::redirect('/dashboard');
        }

        return Response::view('auth/login');
    }

    public function login(Request $request): Response
    {
        try {
            $username = $request->input('auth_user', '');
            $password = $request->input('auth_pw', '');

            if (empty($username) || empty($password)) {
                throw new ValidationException([
                    'credentials' => 'Username and password are required'
                ]);
            }

            $user = $this->authService->authenticate($username, $password);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'user' => [
                        'id' => $user->getId(),
                        'username' => $user->getUsername(),
                        'role' => $user->getRole()->value,
                    ],
                    'redirect' => $user->isMaster() ? '/admin' : '/dashboard'
                ]);
            }

            $redirectUrl = $user->isMaster() ? '/admin' : '/dashboard';
            return Response::redirect($redirectUrl);

        } catch (ValidationException $e) {
            $this->logger->warning('Login validation failed', ['errors' => $e->getErrors()]);

            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            return Response::view('auth/login', [
                'errors' => $e->getErrors(),
                'old' => $request->only(['auth_user'])
            ]);
        }
    }

    public function logout(): Response
    {
        $this->authService->logout();
        return Response::redirect('/login');
    }
}