<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Repository;

use FastrackGps\Auth\Domain\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByUsername(string $username): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function save(User $user): void;
    
    public function delete(int $id): void;
    
    public function findAll(): array;
}