<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\Service;

use FastrackGps\Core\ValueObject\Coordinates;

interface GeocodingServiceInterface
{
    public function reverseGeocode(Coordinates $coordinates): ?string;
    
    public function geocode(string $address): ?Coordinates;
    
    public function isServiceAvailable(): bool;
}