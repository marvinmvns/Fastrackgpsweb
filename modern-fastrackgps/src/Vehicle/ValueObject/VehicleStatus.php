<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\ValueObject;

enum VehicleStatus: string
{
    case TRACKING = 'R';      // Rastreando
    case OFFLINE = 'D';       // Desligado
    case NO_SIGNAL = 'S';     // Sem sinal
    case BLOCKED = 'B';       // Bloqueado

    public static function fromString(string $status): self
    {
        return match (strtoupper($status)) {
            'R', 'TRACKING', 'RASTREANDO' => self::TRACKING,
            'D', 'OFFLINE', 'DESLIGADO' => self::OFFLINE,
            'S', 'NO_SIGNAL', 'SEM_SINAL' => self::NO_SIGNAL,
            'B', 'BLOCKED', 'BLOQUEADO' => self::BLOCKED,
            default => self::OFFLINE,
        };
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::TRACKING => 'Rastreando',
            self::OFFLINE => 'Desligado',
            self::NO_SIGNAL => 'Sem Sinal',
            self::BLOCKED => 'Bloqueado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TRACKING => '#00FF00',   // Verde
            self::OFFLINE => '#CCCCCC',    // Cinza
            self::NO_SIGNAL => '#FF0000',  // Vermelho
            self::BLOCKED => '#000000',    // Preto
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::TRACKING => 'status_rastreando.png',
            self::OFFLINE => 'status_desligado.png',
            self::NO_SIGNAL => 'status_sem_sinal.png',
            self::BLOCKED => 'bloqueado.png',
        };
    }
}