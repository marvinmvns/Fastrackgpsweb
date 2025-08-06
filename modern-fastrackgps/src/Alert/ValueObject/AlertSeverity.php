<?php

declare(strict_types=1);

namespace FastrackGps\Alert\ValueObject;

enum AlertSeverity: string
{
    case CRITICAL = 'critical';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CRITICAL => 'Crítico',
            self::HIGH => 'Alto',
            self::MEDIUM => 'Médio',
            self::LOW => 'Baixo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CRITICAL => '#DC2626', // Vermelho escuro
            self::HIGH => '#EF4444',     // Vermelho
            self::MEDIUM => '#F59E0B',   // Amarelo/Laranja
            self::LOW => '#10B981',      // Verde
        };
    }

    public function getBackgroundColor(): string
    {
        return match ($this) {
            self::CRITICAL => '#FEE2E2', // Vermelho claro
            self::HIGH => '#FECACA',     // Vermelho bem claro
            self::MEDIUM => '#FEF3C7',   // Amarelo claro
            self::LOW => '#D1FAE5',      // Verde claro
        };
    }

    public function getPriority(): int
    {
        return match ($this) {
            self::CRITICAL => 4,
            self::HIGH => 3,
            self::MEDIUM => 2,
            self::LOW => 1,
        };
    }

    public function shouldNotifyImmediately(): bool
    {
        return $this === self::CRITICAL || $this === self::HIGH;
    }
}