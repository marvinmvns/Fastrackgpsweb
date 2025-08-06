<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\Entity;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\ValueObject\Coordinates;
use FastrackGps\Security\InputSanitizer;
use FastrackGps\Vehicle\ValueObject\VehicleStatus;
use FastrackGps\Vehicle\ValueObject\OperatingMode;
use FastrackGps\Vehicle\ValueObject\Carrier;
use DateTimeImmutable;

final class Vehicle
{
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $lastUpdate;

    public function __construct(
        private readonly int $id,
        private readonly string $imei,
        private string $name,
        private string $identification,
        private readonly int $clientId,
        private string $chipNumber,
        private Carrier $carrier,
        private string $chipNumber2,
        private Carrier $carrier2,
        private string $color,
        private bool $isActive = true,
        private OperatingMode $operatingMode = OperatingMode::GPRS,
        private VehicleStatus $status = VehicleStatus::OFFLINE,
        private ?Coordinates $lastPosition = null,
        private ?DateTimeImmutable $lastPositionTime = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $lastUpdate = null
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->lastUpdate = $lastUpdate;
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        $lastPosition = null;
        if (isset($data['latitude'], $data['longitude'])) {
            $lastPosition = new Coordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            );
        }

        return new self(
            id: (int) $data['id'],
            imei: InputSanitizer::sanitizeString($data['imei']),
            name: InputSanitizer::sanitizeString($data['name'] ?? ''),
            identification: InputSanitizer::sanitizeString($data['identificacao'] ?? ''),
            clientId: (int) $data['cliente'],
            chipNumber: $data['numero_chip'] ?? '',
            carrier: Carrier::fromString($data['operadora_chip'] ?? ''),
            chipNumber2: $data['numero_chip2'] ?? '',
            carrier2: Carrier::fromString($data['operadora_chip2'] ?? ''),
            color: InputSanitizer::sanitizeString($data['cor_grafico'] ?? 'FF0000'),
            isActive: ($data['activated'] ?? 'S') === 'S',
            operatingMode: OperatingMode::from($data['modo_operacao'] ?? 'GPRS'),
            status: VehicleStatus::fromString($data['status_sinal'] ?? 'D'),
            lastPosition: $lastPosition,
            lastPositionTime: isset($data['date']) ? new DateTimeImmutable($data['date']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            lastUpdate: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getImei(): string
    {
        return $this->imei;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdentification(): string
    {
        return $this->identification;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getChipNumber(): string
    {
        return $this->chipNumber;
    }

    public function getCarrier(): Carrier
    {
        return $this->carrier;
    }

    public function getChipNumber2(): string
    {
        return $this->chipNumber2;
    }

    public function getCarrier2(): Carrier
    {
        return $this->carrier2;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getOperatingMode(): OperatingMode
    {
        return $this->operatingMode;
    }

    public function getStatus(): VehicleStatus
    {
        return $this->status;
    }

    public function getLastPosition(): ?Coordinates
    {
        return $this->lastPosition;
    }

    public function getLastPositionTime(): ?DateTimeImmutable
    {
        return $this->lastPositionTime;
    }

    public function isOnline(): bool
    {
        if ($this->lastPositionTime === null) {
            return false;
        }

        $now = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $this->lastPositionTime->getTimestamp();
        
        // Consider online if last update was within 5 minutes
        return $diff <= 300;
    }

    public function updatePosition(Coordinates $position, ?DateTimeImmutable $timestamp = null): self
    {
        $vehicle = clone $this;
        $vehicle->lastPosition = $position;
        $vehicle->lastPositionTime = $timestamp ?? new DateTimeImmutable();
        $vehicle->lastUpdate = new DateTimeImmutable();
        $vehicle->status = VehicleStatus::TRACKING;
        
        return $vehicle;
    }

    public function updateDetails(
        string $name,
        string $identification,
        string $chipNumber,
        Carrier $carrier,
        string $chipNumber2,
        Carrier $carrier2,
        string $color
    ): self {
        $vehicle = clone $this;
        $vehicle->name = InputSanitizer::sanitizeString($name);
        $vehicle->identification = InputSanitizer::sanitizeString($identification);
        $vehicle->chipNumber = $chipNumber;
        $vehicle->carrier = $carrier;
        $vehicle->chipNumber2 = $chipNumber2;
        $vehicle->carrier2 = $carrier2;
        $vehicle->color = InputSanitizer::sanitizeString($color);
        $vehicle->lastUpdate = new DateTimeImmutable();
        
        $vehicle->validate();
        return $vehicle;
    }

    public function activate(): self
    {
        $vehicle = clone $this;
        $vehicle->isActive = true;
        $vehicle->lastUpdate = new DateTimeImmutable();
        return $vehicle;
    }

    public function deactivate(): self
    {
        $vehicle = clone $this;
        $vehicle->isActive = false;
        $vehicle->status = VehicleStatus::OFFLINE;
        $vehicle->lastUpdate = new DateTimeImmutable();
        return $vehicle;
    }

    public function changeOperatingMode(OperatingMode $mode): self
    {
        $vehicle = clone $this;
        $vehicle->operatingMode = $mode;
        $vehicle->lastUpdate = new DateTimeImmutable();
        return $vehicle;
    }

    public function updateStatus(VehicleStatus $status): self
    {
        $vehicle = clone $this;
        $vehicle->status = $status;
        $vehicle->lastUpdate = new DateTimeImmutable();
        return $vehicle;
    }

    public function getFormattedChipNumber(): string
    {
        if (empty($this->chipNumber) || strlen($this->chipNumber) < 10) {
            return $this->chipNumber;
        }

        return sprintf(
            '(%s) %s-%s',
            substr($this->chipNumber, 0, 2),
            substr($this->chipNumber, 2, 4),
            substr($this->chipNumber, 6, 4)
        );
    }

    public function getFormattedChipNumber2(): string
    {
        if (empty($this->chipNumber2) || strlen($this->chipNumber2) < 10) {
            return $this->chipNumber2;
        }

        return sprintf(
            '(%s) %s-%s',
            substr($this->chipNumber2, 0, 2),
            substr($this->chipNumber2, 2, 4),
            substr($this->chipNumber2, 6, 4)
        );
    }

    public function getMapIconColor(): string
    {
        return match ($this->status) {
            VehicleStatus::TRACKING => $this->color,
            VehicleStatus::OFFLINE => 'CCCCCC',
            VehicleStatus::NO_SIGNAL => 'FF0000',
            VehicleStatus::BLOCKED => '000000',
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'imei' => $this->imei,
            'name' => $this->name,
            'identificacao' => $this->identification,
            'cliente' => $this->clientId,
            'numero_chip' => $this->chipNumber,
            'operadora_chip' => $this->carrier->value,
            'numero_chip2' => $this->chipNumber2,
            'operadora_chip2' => $this->carrier2->value,
            'cor_grafico' => $this->color,
            'activated' => $this->isActive ? 'S' : 'N',
            'modo_operacao' => $this->operatingMode->value,
            'status_sinal' => $this->status->value,
            'latitude' => $this->lastPosition?->latitude,
            'longitude' => $this->lastPosition?->longitude,
            'date' => $this->lastPositionTime?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->lastUpdate?->format('Y-m-d H:i:s'),
        ];
    }

    private function validate(): void
    {
        if (empty($this->imei) || strlen($this->imei) < 15) {
            throw ValidationException::invalidFormat('imei', '15-digit IMEI number');
        }

        if (empty($this->name)) {
            throw ValidationException::fieldRequired('name');
        }

        if ($this->clientId <= 0) {
            throw ValidationException::fieldRequired('client_id');
        }

        if (!empty($this->color) && !preg_match('/^[0-9A-Fa-f]{6}$/', $this->color)) {
            throw ValidationException::invalidFormat('color', 'valid hexadecimal color (RRGGBB)');
        }
    }
}