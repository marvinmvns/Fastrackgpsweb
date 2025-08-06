<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\Entity;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\ValueObject\Coordinates;
use FastrackGps\Tracking\ValueObject\SignalQuality;
use DateTimeImmutable;

final class GpsPosition
{
    public function __construct(
        private readonly int $id,
        private readonly string $deviceImei,
        private readonly Coordinates $coordinates,
        private readonly DateTimeImmutable $timestamp,
        private readonly float $speed,
        private readonly float $course,
        private readonly SignalQuality $signalQuality,
        private readonly array $extendedInfo = [],
        private ?string $address = null,
        private readonly bool $ignitionOn = false,
        private readonly ?float $batteryLevel = null,
        private readonly ?float $altitude = null
    ) {
        $this->validate();
    }

    public static function fromGprmcData(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            deviceImei: $data['imei'] ?? '',
            coordinates: new Coordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            ),
            timestamp: new DateTimeImmutable($data['date']),
            speed: ((float) $data['speed']) * 1.609344, // Convert knots to km/h
            course: (float) ($data['course'] ?? 0),
            signalQuality: SignalQuality::fromIndicator($data['gpsSignalIndicator'] ?? 'A'),
            extendedInfo: json_decode($data['extended_info'] ?? '[]', true) ?: [],
            address: $data['address'] ?? null,
            ignitionOn: isset($data['ignicao']) ? (bool) $data['ignicao'] : false,
            batteryLevel: isset($data['battery']) ? (float) $data['battery'] : null,
            altitude: isset($data['altitude']) ? (float) $data['altitude'] : null
        );
    }

    public static function fromTraccarData(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            deviceImei: $data['device_imei'] ?? '',
            coordinates: new Coordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            ),
            timestamp: new DateTimeImmutable($data['time']),
            speed: ((float) $data['speed']) * 1.609344, // Convert knots to km/h
            course: (float) ($data['course'] ?? 0),
            signalQuality: SignalQuality::GOOD, // Traccar assumes good signal
            extendedInfo: json_decode($data['extended_info'] ?? '[]', true) ?: [],
            address: $data['address'] ?? null,
            altitude: isset($data['altitude']) ? (float) $data['altitude'] : null
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

    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getSpeed(): float
    {
        return $this->speed;
    }

    public function getCourse(): float
    {
        return $this->course;
    }

    public function getSignalQuality(): SignalQuality
    {
        return $this->signalQuality;
    }

    public function getExtendedInfo(): array
    {
        return $this->extendedInfo;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function isIgnitionOn(): bool
    {
        return $this->ignitionOn;
    }

    public function getBatteryLevel(): ?float
    {
        return $this->batteryLevel;
    }

    public function getAltitude(): ?float
    {
        return $this->altitude;
    }

    public function withAddress(string $address): self
    {
        $position = clone $this;
        $position->address = $address;
        return $position;
    }

    public function isMoving(): bool
    {
        return $this->speed > 5.0; // Consider moving if speed > 5 km/h
    }

    public function hasLowBattery(): bool
    {
        return $this->batteryLevel !== null && $this->batteryLevel < 20.0;
    }

    public function getFormattedSpeed(): string
    {
        return number_format($this->speed, 0) . ' Km/h';
    }

    public function getFormattedTimestamp(string $format = 'd/m/Y H:i:s'): string
    {
        return $this->timestamp->format($format);
    }

    public function getCardinalDirection(): string
    {
        $directions = [
            'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
        ];
        
        $index = (int) round($this->course / 22.5) % 16;
        return $directions[$index];
    }

    public function getAlerts(): array
    {
        $alerts = [];

        if ($this->hasLowBattery()) {
            $alerts[] = [
                'type' => 'low_battery',
                'message' => 'Bateria fraca',
                'icon' => 'battery-low.png'
            ];
        }

        if (isset($this->extendedInfo['panic'])) {
            $alerts[] = [
                'type' => 'panic',
                'message' => 'Botão de pânico acionado',
                'icon' => 'help.png'
            ];
        }

        if ($this->speed > 80) {
            $alerts[] = [
                'type' => 'speeding',
                'message' => 'Excesso de velocidade',
                'icon' => 'velocidade.png'
            ];
        }

        return $alerts;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'imei' => $this->deviceImei,
            'latitude' => $this->coordinates->latitude,
            'longitude' => $this->coordinates->longitude,
            'date' => $this->timestamp->format('Y-m-d H:i:s'),
            'speed' => $this->speed,
            'course' => $this->course,
            'gpsSignalIndicator' => $this->signalQuality->value,
            'extended_info' => json_encode($this->extendedInfo),
            'address' => $this->address,
            'ignicao' => $this->ignitionOn ? 1 : 0,
            'battery' => $this->batteryLevel,
            'altitude' => $this->altitude,
        ];
    }

    private function validate(): void
    {
        if (empty($this->deviceImei)) {
            throw ValidationException::fieldRequired('device_imei');
        }

        if ($this->speed < 0 || $this->speed > 500) {
            throw ValidationException::invalidFormat('speed', 'reasonable speed value');
        }

        if ($this->course < 0 || $this->course >= 360) {
            throw ValidationException::invalidFormat('course', 'valid bearing (0-359 degrees)');
        }
    }
}