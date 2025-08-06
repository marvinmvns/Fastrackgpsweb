<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Service;

use FastrackGps\Auth\Domain\User;

interface SessionManagerInterface
{
    public function startSession(User $user): void;
    
    public function destroySession(): void;
    
    public function getCurrentUser(): ?User;
    
    public function isActive(): bool;
    
    public function regenerateId(): void;
}