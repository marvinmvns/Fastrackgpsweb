<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use FastrackGps\Auth\Domain\User;
use FastrackGps\Auth\Domain\UserRole;
use FastrackGps\Core\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCanCreateValidUser(): void
    {
        $user = new User(
            id: 1,
            username: 'testuser',
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: UserRole::USER
        );

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals(UserRole::USER, $user->getRole());
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isMaster());
    }

    public function testAdminUserHasCorrectPermissions(): void
    {
        $user = new User(
            id: 2,
            username: 'admin',
            email: 'admin@example.com',
            passwordHash: password_hash('admin123', PASSWORD_DEFAULT),
            role: UserRole::ADMIN
        );

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isMaster());
    }

    public function testMasterUserHasCorrectPermissions(): void
    {
        $user = new User(
            id: 3,
            username: 'master',
            email: 'master@example.com',
            passwordHash: password_hash('master123', PASSWORD_DEFAULT),
            role: UserRole::MASTER
        );

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isMaster());
    }

    public function testPasswordVerification(): void
    {
        $password = 'securePassword123';
        $user = new User(
            id: 1,
            username: 'testuser',
            email: 'test@example.com',
            passwordHash: password_hash($password, PASSWORD_DEFAULT),
            role: UserRole::USER
        );

        $this->assertTrue($user->verifyPassword($password));
        $this->assertFalse($user->verifyPassword('wrongPassword'));
    }

    public function testThrowsExceptionForEmptyUsername(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This field is required');

        new User(
            id: 1,
            username: '',
            email: 'test@example.com',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: UserRole::USER
        );
    }

    public function testThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid format');

        new User(
            id: 1,
            username: 'testuser',
            email: 'invalid-email',
            passwordHash: password_hash('password123', PASSWORD_DEFAULT),
            role: UserRole::USER
        );
    }
}