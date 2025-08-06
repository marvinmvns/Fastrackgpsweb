<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Domain;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case MASTER = 'master';

    public function getPermissions(): array
    {
        return match ($this) {
            self::USER => ['view_own_vehicles', 'manage_own_vehicles'],
            self::ADMIN => ['view_all_vehicles', 'manage_all_vehicles', 'manage_users'],
            self::MASTER => ['*'], // All permissions
        };
    }

    public function canAccess(string $permission): bool
    {
        $permissions = $this->getPermissions();
        
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}