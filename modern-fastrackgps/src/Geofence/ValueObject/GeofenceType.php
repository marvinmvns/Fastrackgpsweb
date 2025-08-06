<?php

declare(strict_types=1);

namespace FastrackGps\Geofence\ValueObject;

enum GeofenceType: string
{
    case CIRCLE = 'circle';
    case POLYGON = 'polygon';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CIRCLE => 'Circular',
            self::POLYGON => 'Polígono',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CIRCLE => 'circle-geofence.png',
            self::POLYGON => 'polygon-geofence.png',
        };
    }

    public function supportsRadius(): bool
    {
        return $this === self::CIRCLE;
    }
}