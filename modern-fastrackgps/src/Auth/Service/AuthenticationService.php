<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Service;

use FastrackGps\Auth\Domain\User;
use FastrackGps\Auth\Repository\UserRepositoryInterface;
use FastrackGps\Core\Exception\ValidationException;
use Psr\Log\LoggerInterface;

final class AuthenticationService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly SessionManagerInterface $sessionManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function authenticate(string $usernameOrEmail, string $password): User
    {
        $this->logger->info('Authentication attempt', ['username' => $usernameOrEmail]);

        $user = $this->findUserByCredentials($usernameOrEmail);

        if ($user === null) {
            $this->logger->warning('Authentication failed - user not found', ['username' => $usernameOrEmail]);
            throw new ValidationException(['credentials' => 'Invalid username or password']);
        }

        if (!$user->isActive()) {
            $this->logger->warning('Authentication failed - user inactive', ['user_id' => $user->getId()]);
            throw new ValidationException(['credentials' => 'Account is disabled']);
        }

        if (!$user->verifyPassword($password)) {
            $this->logger->warning('Authentication failed - invalid password', ['user_id' => $user->getId()]);
            throw new ValidationException(['credentials' => 'Invalid username or password']);
        }

        $this->sessionManager->startSession($user);
        $this->logger->info('Authentication successful', ['user_id' => $user->getId()]);

        return $user;
    }

    public function logout(): void
    {
        $this->sessionManager->destroySession();
        $this->logger->info('User logged out');
    }

    public function getCurrentUser(): ?User
    {
        return $this->sessionManager->getCurrentUser();
    }

    public function isAuthenticated(): bool
    {
        return $this->sessionManager->isActive();
    }

    private function findUserByCredentials(string $usernameOrEmail): ?User
    {
        // Try to find by email first, then by username
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->userRepository->findByEmail($usernameOrEmail);
        }

        return $this->userRepository->findByUsername($usernameOrEmail);
    }
}