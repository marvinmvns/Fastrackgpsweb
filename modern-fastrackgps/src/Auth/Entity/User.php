<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Entity;

use FastrackGps\Auth\Domain\UserRole;
use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Security\InputSanitizer;
use DateTimeImmutable;

final class User
{
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $deactivationDate;

    public function __construct(
        private readonly int $id,
        private string $name,
        private string $email,
        private string $username,
        private readonly string $passwordHash,
        private string $cpf,
        private string $phone1,
        private string $phone2,
        private string $address,
        private readonly UserRole $role,
        private bool $isActive = true,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $deactivationDate = null,
        private ?string $observations = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->deactivationDate = $deactivationDate;
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: InputSanitizer::sanitizeString($data['nome'] ?? ''),
            email: InputSanitizer::sanitizeEmail($data['email'] ?? '') ?? '',
            username: InputSanitizer::sanitizeString($data['apelido'] ?? ''),
            passwordHash: $data['senha'] ?? '',
            cpf: InputSanitizer::sanitizeString($data['cpf'] ?? ''),
            phone1: $data['telefone1'] ?? '',
            phone2: $data['telefone2'] ?? '',
            address: InputSanitizer::sanitizeString($data['endereco'] ?? ''),
            role: $data['master'] === 'S' ? UserRole::MASTER : UserRole::USER,
            isActive: ($data['ativo'] ?? 'S') === 'S',
            deactivationDate: $data['data_inativacao'] ? 
                new DateTimeImmutable($data['data_inativacao']) : null,
            observations: $data['observacao'] ?? null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getPhone1(): string
    {
        return $this->phone1;
    }

    public function getPhone2(): string
    {
        return $this->phone2;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->deactivationDate === null) {
            return true;
        }

        return $this->deactivationDate > new DateTimeImmutable();
    }

    public function getDaysUntilDeactivation(): ?int
    {
        if ($this->deactivationDate === null) {
            return null;
        }

        $now = new DateTimeImmutable();
        $diff = $this->deactivationDate->diff($now);
        
        return $diff->invert ? $diff->days : -$diff->days;
    }

    public function verifyPassword(string $password): bool
    {
        // Legacy MD5 support with upgrade path
        if (strlen($this->passwordHash) === 32 && ctype_xdigit($this->passwordHash)) {
            return md5($password) === $this->passwordHash;
        }

        return password_verify($password, $this->passwordHash);
    }

    public function needsPasswordUpgrade(): bool
    {
        return strlen($this->passwordHash) === 32 && ctype_xdigit($this->passwordHash);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN || $this->role === UserRole::MASTER;
    }

    public function isMaster(): bool
    {
        return $this->role === UserRole::MASTER;
    }

    public function updateProfile(
        string $name,
        string $email,
        string $cpf,
        string $phone1,
        string $phone2,
        string $address
    ): self {
        $user = clone $this;
        $user->name = InputSanitizer::sanitizeString($name);
        $user->email = InputSanitizer::sanitizeEmail($email) ?? $this->email;
        $user->cpf = InputSanitizer::sanitizeString($cpf);
        $user->phone1 = $phone1;
        $user->phone2 = $phone2;
        $user->address = InputSanitizer::sanitizeString($address);
        
        $user->validate();
        return $user;
    }

    public function deactivate(?DateTimeImmutable $deactivationDate = null): self
    {
        $user = clone $this;
        $user->isActive = false;
        $user->deactivationDate = $deactivationDate ?? new DateTimeImmutable();
        return $user;
    }

    public function activate(): self
    {
        $user = clone $this;
        $user->isActive = true;
        $user->deactivationDate = null;
        return $user;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->name,
            'email' => $this->email,
            'apelido' => $this->username,
            'cpf' => $this->cpf,
            'telefone1' => $this->phone1,
            'telefone2' => $this->phone2,
            'endereco' => $this->address,
            'master' => $this->role === UserRole::MASTER ? 'S' : 'N',
            'ativo' => $this->isActive ? 'S' : 'N',
            'data_inativacao' => $this->deactivationDate?->format('Y-m-d'),
            'observacao' => $this->observations,
        ];
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw ValidationException::fieldRequired('name');
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::invalidFormat('email', 'valid email address');
        }

        if (empty($this->username)) {
            throw ValidationException::fieldRequired('username');
        }

        if (strlen($this->username) < 3) {
            throw ValidationException::invalidFormat('username', 'at least 3 characters');
        }

        if (!empty($this->cpf) && !$this->isValidCpf($this->cpf)) {
            throw ValidationException::invalidFormat('cpf', 'valid CPF format');
        }
    }

    private function isValidCpf(string $cpf): bool
    {
        // Remove non-numeric characters
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Check if has 11 digits
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check for known invalid patterns
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        // Validate check digits
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if ((int) $cpf[9] !== $digit1) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return (int) $cpf[10] === $digit2;
    }
}