<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\ValueObject;

enum OperatingMode: string
{
    case GPRS = 'GPRS';
    case SMS = 'SMS';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::GPRS => 'GPRS (Rastreamento Online)',
            self::SMS => 'SMS (Comandos via SMS)',
        };
    }

    public function allowsRealTimeTracking(): bool
    {
        return $this === self::GPRS;
    }
}