<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Service;

use FastrackGps\Auth\Domain\User;
use FastrackGps\Auth\Repository\UserRepositoryInterface;
use FastrackGps\Core\Config\ConfigurationInterface;

final class PhpSessionManager implements SessionManagerInterface
{
    private const USER_KEY = 'auth_user_id';
    private const TOKEN_KEY = 'auth_token';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ConfigurationInterface $config
    ) {
        $this->configureSession();
    }

    public function startSession(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION[self::USER_KEY] = $user->getId();
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
    }

    public function destroySession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_unset();
            session_destroy();
        }

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }

    public function getCurrentUser(): ?User
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION[self::USER_KEY] ?? null;
        
        if ($userId === null) {
            return null;
        }

        return $this->userRepository->findById((int) $userId);
    }

    public function isActive(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION[self::USER_KEY], $_SESSION[self::TOKEN_KEY]);
    }

    public function regenerateId(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            session_regenerate_id(true);
        }
    }

    private function configureSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // Security settings
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $this->config->get('session.secure', '0'));
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Strict');
        
        // Session lifetime
        ini_set('session.gc_maxlifetime', (string) ($this->config->get('session.lifetime', 120) * 60));
        ini_set('session.cookie_lifetime', (string) ($this->config->get('session.lifetime', 120) * 60));
    }
}