<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Domain;

use FastrackGps\Core\Exception\ValidationException;

final class User
{
    public function __construct(
        private readonly int $id,
        private readonly string $username,
        private readonly string $email,
        private readonly string $passwordHash,
        private readonly UserRole $role,
        private readonly bool $isActive = true
    ) {
        $this->validate();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN || $this->role === UserRole::MASTER;
    }

    public function isMaster(): bool
    {
        return $this->role === UserRole::MASTER;
    }

    private function validate(): void
    {
        if (empty($this->username)) {
            throw ValidationException::fieldRequired('username');
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidFormat('email', 'valid email address');
        }
    }
}