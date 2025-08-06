<?php

declare(strict_types=1);

namespace FastrackGps\Core\ValueObject;

use FastrackGps\Core\Exception\ValidationException;

final readonly class Coordinates
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['latitude'], $data['longitude'])) {
            throw ValidationException::fieldRequired('latitude, longitude');
        }

        return new self(
            (float) $data['latitude'],
            (float) $data['longitude']
        );
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function distanceTo(self $other): float
    {
        // Haversine formula for distance calculation
        $earthRadius = 6371000; // meters
        
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($other->latitude);
        $lonTo = deg2rad($other->longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    private function validate(): void
    {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw ValidationException::invalidFormat('latitude', 'between -90 and 90 degrees');
        }

        if ($this->longitude < -180 || $this->longitude > 180) {
            throw ValidationException::invalidFormat('longitude', 'between -180 and 180 degrees');
        }
    }
}