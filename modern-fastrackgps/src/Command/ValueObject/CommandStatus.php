<?php

declare(strict_types=1);

namespace FastrackGps\Command\ValueObject;

enum CommandStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case CONFIRMED = 'confirmed';
    case FAILED = 'failed';
    case EXPIRED = 'expired';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::SENT => 'Enviado',
            self::CONFIRMED => 'Confirmado',
            self::FAILED => 'Falhou',
            self::EXPIRED => 'Expirado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => '#FFA500',    // Laranja
            self::SENT => '#0000FF',       // Azul
            self::CONFIRMED => '#00FF00', // Verde
            self::FAILED => '#FF0000',     // Vermelho
            self::EXPIRED => '#808080',    // Cinza
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'clock.png',
            self::SENT => 'sending.png',
            self::CONFIRMED => 'success.png',
            self::FAILED => 'error.png',
            self::EXPIRED => 'expired.png',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::CONFIRMED, self::FAILED, self::EXPIRED => true,
            default => false,
        };
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::PENDING => 'status-pending',
            self::SENT => 'status-sent',
            self::CONFIRMED => 'status-confirmed',
            self::FAILED => 'status-failed',
            self::EXPIRED => 'status-expired',
        };
    }
}