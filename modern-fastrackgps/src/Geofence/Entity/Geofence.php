<?php

declare(strict_types=1);

namespace FastrackGps\Geofence\Entity;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\ValueObject\Coordinates;
use FastrackGps\Geofence\ValueObject\GeofenceType;
use FastrackGps\Security\InputSanitizer;
use DateTimeImmutable;

final class Geofence
{
    /**
     * @param Coordinates[] $coordinates
     */
    public function __construct(
        private readonly int $id,
        private readonly string $deviceImei,
        private readonly int $clientId,
        private string $name,
        private string $description,
        private readonly GeofenceType $type,
        private array $coordinates,
        private bool $isActive = true,
        private bool $alertOnEnter = true,
        private bool $alertOnExit = true,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private ?DateTimeImmutable $updatedAt = null
    ) {
        $this->validate();
    }

    public static function createCircular(
        string $deviceImei,
        int $clientId,
        string $name,
        string $description,
        Coordinates $center,
        float $radiusMeters
    ): self {
        // Generate circle coordinates
        $coordinates = self::generateCircleCoordinates($center, $radiusMeters);

        return new self(
            id: 0,
            deviceImei: $deviceImei,
            clientId: $clientId,
            name: InputSanitizer::sanitizeString($name),
            description: InputSanitizer::sanitizeString($description),
            type: GeofenceType::CIRCLE,
            coordinates: $coordinates
        );
    }

    public static function createPolygon(
        string $deviceImei,
        int $clientId,
        string $name,
        string $description,
        array $coordinates
    ): self {
        $coordinateObjects = array_map(
            fn(array $coord) => new Coordinates($coord['lat'], $coord['lng']),
            $coordinates
        );

        return new self(
            id: 0,
            deviceImei: $deviceImei,
            clientId: $clientId,
            name: InputSanitizer::sanitizeString($name),
            description: InputSanitizer::sanitizeString($description),
            type: GeofenceType::POLYGON,
            coordinates: $coordinateObjects
        );
    }

    public static function fromArray(array $data): self
    {
        $coordinates = [];
        if (!empty($data['coordinates'])) {
            $coordData = json_decode($data['coordinates'], true);
            foreach ($coordData as $coord) {
                $coordinates[] = new Coordinates($coord['lat'], $coord['lng']);
            }
        }

        return new self(
            id: (int) $data['id'],
            deviceImei: $data['device_imei'],
            clientId: (int) $data['client_id'],
            name: $data['name'],
            description: $data['description'] ?? '',
            type: GeofenceType::from($data['type']),
            coordinates: $coordinates,
            isActive: (bool) $data['is_active'],
            alertOnEnter: (bool) $data['alert_on_enter'],
            alertOnExit: (bool) $data['alert_on_exit'],
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDeviceImei(): string
    {
        return $this->deviceImei;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): GeofenceType
    {
        return $this->type;
    }

    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function shouldAlertOnEnter(): bool
    {
        return $this->alertOnEnter;
    }

    public function shouldAlertOnExit(): bool
    {
        return $this->alertOnExit;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function containsPoint(Coordinates $point): bool
    {
        if (count($this->coordinates) < 3) {
            return false;
        }

        return $this->pointInPolygon($point, $this->coordinates);
    }

    public function getCenter(): Coordinates
    {
        if (empty($this->coordinates)) {
            return new Coordinates(0, 0);
        }

        $latSum = 0;
        $lngSum = 0;

        foreach ($this->coordinates as $coord) {
            $latSum += $coord->latitude;
            $lngSum += $coord->longitude;
        }

        return new Coordinates(
            $latSum / count($this->coordinates),
            $lngSum / count($this->coordinates)
        );
    }

    public function getArea(): float
    {
        if (count($this->coordinates) < 3) {
            return 0;
        }

        $area = 0;
        $n = count($this->coordinates);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $this->coordinates[$i]->latitude * $this->coordinates[$j]->longitude;
            $area -= $this->coordinates[$j]->latitude * $this->coordinates[$i]->longitude;
        }

        return abs($area) / 2;
    }

    public function update(
        string $name,
        string $description,
        array $coordinates = null,
        bool $alertOnEnter = null,
        bool $alertOnExit = null
    ): self {
        $geofence = clone $this;
        $geofence->name = InputSanitizer::sanitizeString($name);
        $geofence->description = InputSanitizer::sanitizeString($description);
        
        if ($coordinates !== null) {
            $geofence->coordinates = array_map(
                fn(array $coord) => new Coordinates($coord['lat'], $coord['lng']),
                $coordinates
            );
        }

        if ($alertOnEnter !== null) {
            $geofence->alertOnEnter = $alertOnEnter;
        }

        if ($alertOnExit !== null) {
            $geofence->alertOnExit = $alertOnExit;
        }

        $geofence->updatedAt = new DateTimeImmutable();
        $geofence->validate();

        return $geofence;
    }

    public function activate(): self
    {
        $geofence = clone $this;
        $geofence->isActive = true;
        $geofence->updatedAt = new DateTimeImmutable();
        return $geofence;
    }

    public function deactivate(): self
    {
        $geofence = clone $this;
        $geofence->isActive = false;
        $geofence->updatedAt = new DateTimeImmutable();
        return $geofence;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'device_imei' => $this->deviceImei,
            'client_id' => $this->clientId,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->value,
            'coordinates' => json_encode(array_map(
                fn(Coordinates $coord) => ['lat' => $coord->latitude, 'lng' => $coord->longitude],
                $this->coordinates
            )),
            'is_active' => $this->isActive,
            'alert_on_enter' => $this->alertOnEnter,
            'alert_on_exit' => $this->alertOnExit,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    private function validate(): void
    {
        if (empty($this->deviceImei)) {
            throw ValidationException::fieldRequired('device_imei');
        }

        if ($this->clientId <= 0) {
            throw ValidationException::fieldRequired('client_id');
        }

        if (empty($this->name)) {
            throw ValidationException::fieldRequired('name');
        }

        if (count($this->coordinates) < 3) {
            throw ValidationException::invalidFormat('coordinates', 'at least 3 coordinates for polygon');
        }
    }

    private function pointInPolygon(Coordinates $point, array $polygon): bool
    {
        $x = $point->latitude;
        $y = $point->longitude;
        $inside = false;

        $j = count($polygon) - 1;
        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]->latitude;
            $yi = $polygon[$i]->longitude;
            $xj = $polygon[$j]->latitude;
            $yj = $polygon[$j]->longitude;

            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }

    private static function generateCircleCoordinates(Coordinates $center, float $radiusMeters): array
    {
        $coordinates = [];
        $earthRadius = 6371000; // Earth radius in meters
        
        // Convert radius from meters to degrees
        $radiusDegrees = $radiusMeters / $earthRadius * (180 / M_PI);

        // Generate 32 points for circle
        for ($i = 0; $i < 32; $i++) {
            $angle = ($i * 360 / 32) * M_PI / 180;
            
            $lat = $center->latitude + ($radiusDegrees * cos($angle));
            $lng = $center->longitude + ($radiusDegrees * sin($angle) / cos($center->latitude * M_PI / 180));
            
            $coordinates[] = new Coordinates($lat, $lng);
        }

        return $coordinates;
    }
}