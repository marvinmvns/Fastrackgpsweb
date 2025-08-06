<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\ValueObject;

enum SignalQuality: string
{
    case GOOD = 'A';      // Active/Good signal
    case POOR = 'V';      // Void/Poor signal
    case FIXED = 'F';     // Fixed position

    public static function fromIndicator(string $indicator): self
    {
        return match (strtoupper($indicator)) {
            'A', 'GOOD', 'ACTIVE' => self::GOOD,
            'V', 'POOR', 'VOID' => self::POOR,
            'F', 'FIXED' => self::FIXED,
            default => self::POOR,
        };
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::GOOD => 'Sinal Bom',
            self::POOR => 'Sinal Fraco',
            self::FIXED => 'Posição Fixa',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::GOOD => '#00FF00',   // Verde
            self::POOR => '#FFFF00',   // Amarelo
            self::FIXED => '#0000FF',  // Azul
        };
    }

    public function isReliable(): bool
    {
        return $this === self::GOOD || $this === self::FIXED;
    }
}