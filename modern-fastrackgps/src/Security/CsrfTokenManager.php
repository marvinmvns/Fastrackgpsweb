<?php

declare(strict_types=1);

namespace FastrackGps\Security;

use FastrackGps\Core\Exception\ValidationException;

final class CsrfTokenManager
{
    private const TOKEN_LENGTH = 32;
    private const SESSION_KEY = '_csrf_tokens';

    public function generateToken(string $action = 'default'): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        
        $_SESSION[self::SESSION_KEY][$action] = $token;
        
        return $token;
    }

    public function validateToken(string $token, string $action = 'default'): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }

        $storedToken = $_SESSION[self::SESSION_KEY][$action] ?? null;
        
        if ($storedToken === null) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($storedToken, $token);
    }

    public function requireValidToken(string $token, string $action = 'default'): void
    {
        if (!$this->validateToken($token, $action)) {
            throw new ValidationException(['csrf' => 'Invalid CSRF token']);
        }
    }

    public function clearToken(string $action = 'default'): void
    {
        if (isset($_SESSION[self::SESSION_KEY][$action])) {
            unset($_SESSION[self::SESSION_KEY][$action]);
        }
    }
}