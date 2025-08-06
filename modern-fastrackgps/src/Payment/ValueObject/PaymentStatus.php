<?php

declare(strict_types=1);

namespace FastrackGps\Payment\ValueObject;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::PAID => 'Pago',
            self::OVERDUE => 'Em Atraso',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => '#FFA500',    // Laranja
            self::PAID => '#00FF00',       // Verde
            self::OVERDUE => '#FF0000',    // Vermelho
            self::CANCELLED => '#808080',  // Cinza
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDING => 'registra_pgto.gif',
            self::PAID => 'pagou.gif',
            self::OVERDUE => 'sem_pagamento.gif',
            self::CANCELLED => 'cancelled.gif',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isActive(): bool
    {
        return $this === self::PENDING || $this === self::OVERDUE;
    }
}