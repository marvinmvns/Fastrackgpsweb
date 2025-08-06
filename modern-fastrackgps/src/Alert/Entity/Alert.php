<?php

declare(strict_types=1);

namespace FastrackGps\Alert\Entity;

use FastrackGps\Alert\ValueObject\AlertType;
use FastrackGps\Alert\ValueObject\AlertSeverity;
use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\ValueObject\Coordinates;
use DateTimeImmutable;

final class Alert
{
    public function __construct(
        private readonly int $id,
        private readonly string $deviceImei,
        private readonly string $vehicleName,
        private readonly int $clientId,
        private readonly AlertType $type,
        private readonly AlertSeverity $severity,
        private readonly string $message,
        private readonly ?Coordinates $coordinates = null,
        private readonly array $metadata = [],
        private bool $isViewed = false,
        private bool $isAcknowledged = false,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private ?DateTimeImmutable $viewedAt = null,
        private ?DateTimeImmutable $acknowledgedAt = null,
        private ?int $acknowledgedBy = null
    ) {
        $this->validate();
    }

    public static function create(
        string $deviceImei,
        string $vehicleName,
        int $clientId,
        AlertType $type,
        string $message,
        ?Coordinates $coordinates = null,
        array $metadata = []
    ): self {
        return new self(
            id: 0, // Will be set by repository
            deviceImei: $deviceImei,
            vehicleName: $vehicleName,
            clientId: $clientId,
            type: $type,
            severity: $type->getDefaultSeverity(),
            message: $message,
            coordinates: $coordinates,
            metadata: $metadata
        );
    }

    public static function fromArray(array $data): self
    {
        $coordinates = null;
        if (isset($data['latitude'], $data['longitude'])) {
            $coordinates = new Coordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            );
        }

        return new self(
            id: (int) $data['id'],
            deviceImei: $data['device_imei'],
            vehicleName: $data['vehicle_name'],
            clientId: (int) $data['client_id'],
            type: AlertType::from($data['alert_type']),
            severity: AlertSeverity::from($data['severity']),
            message: $data['message'],
            coordinates: $coordinates,
            metadata: json_decode($data['metadata'] ?? '[]', true) ?: [],
            isViewed: (bool) $data['is_viewed'],
            isAcknowledged: (bool) $data['is_acknowledged'],
            createdAt: new DateTimeImmutable($data['created_at']),
            viewedAt: isset($data['viewed_at']) ? new DateTimeImmutable($data['viewed_at']) : null,
            acknowledgedAt: isset($data['acknowledged_at']) ? new DateTimeImmutable($data['acknowledged_at']) : null,
            acknowledgedBy: $data['acknowledged_by'] ?? null
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

    public function getVehicleName(): string
    {
        return $this->vehicleName;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getType(): AlertType
    {
        return $this->type;
    }

    public function getSeverity(): AlertSeverity
    {
        return $this->severity;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCoordinates(): ?Coordinates
    {
        return $this->coordinates;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isViewed(): bool
    {
        return $this->isViewed;
    }

    public function isAcknowledged(): bool
    {
        return $this->isAcknowledged;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getViewedAt(): ?DateTimeImmutable
    {
        return $this->viewedAt;
    }

    public function getAcknowledgedAt(): ?DateTimeImmutable
    {
        return $this->acknowledgedAt;
    }

    public function getAcknowledgedBy(): ?int
    {
        return $this->acknowledgedBy;
    }

    public function markAsViewed(?DateTimeImmutable $viewedAt = null): self
    {
        $alert = clone $this;
        $alert->isViewed = true;
        $alert->viewedAt = $viewedAt ?? new DateTimeImmutable();
        return $alert;
    }

    public function acknowledge(int $userId, ?DateTimeImmutable $acknowledgedAt = null): self
    {
        $alert = clone $this;
        $alert->isAcknowledged = true;
        $alert->acknowledgedAt = $acknowledgedAt ?? new DateTimeImmutable();
        $alert->acknowledgedBy = $userId;
        
        // Mark as viewed when acknowledged
        if (!$alert->isViewed) {
            $alert->isViewed = true;
            $alert->viewedAt = $alert->acknowledgedAt;
        }
        
        return $alert;
    }

    public function getAge(): int
    {
        $now = new DateTimeImmutable();
        return $now->getTimestamp() - $this->createdAt->getTimestamp();
    }

    public function getFormattedAge(): string
    {
        $age = $this->getAge();
        
        if ($age < 60) {
            return 'há ' . $age . ' segundos';
        }
        
        if ($age < 3600) {
            return 'há ' . floor($age / 60) . ' minutos';
        }
        
        if ($age < 86400) {
            return 'há ' . floor($age / 3600) . ' horas';
        }
        
        return 'há ' . floor($age / 86400) . ' dias';
    }

    public function requiresImmediateAttention(): bool
    {
        return $this->severity === AlertSeverity::CRITICAL ||
               ($this->type === AlertType::PANIC && !$this->isAcknowledged);
    }

    public function getNotificationTitle(): string
    {
        return $this->type->getDisplayName() . ' - ' . $this->vehicleName;
    }

    public function getNotificationBody(): string
    {
        $body = $this->message;
        
        if ($this->coordinates !== null) {
            $body .= ' (Lat: ' . number_format($this->coordinates->latitude, 6) . 
                     ', Lng: ' . number_format($this->coordinates->longitude, 6) . ')';
        }
        
        return $body;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'device_imei' => $this->deviceImei,
            'vehicle_name' => $this->vehicleName,
            'client_id' => $this->clientId,
            'alert_type' => $this->type->value,
            'severity' => $this->severity->value,
            'message' => $this->message,
            'latitude' => $this->coordinates?->latitude,
            'longitude' => $this->coordinates?->longitude,
            'metadata' => json_encode($this->metadata),
            'is_viewed' => $this->isViewed,
            'is_acknowledged' => $this->isAcknowledged,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'viewed_at' => $this->viewedAt?->format('Y-m-d H:i:s'),
            'acknowledged_at' => $this->acknowledgedAt?->format('Y-m-d H:i:s'),
            'acknowledged_by' => $this->acknowledgedBy,
        ];
    }

    private function validate(): void
    {
        if (empty($this->deviceImei)) {
            throw ValidationException::fieldRequired('device_imei');
        }

        if (empty($this->vehicleName)) {
            throw ValidationException::fieldRequired('vehicle_name');
        }

        if ($this->clientId <= 0) {
            throw ValidationException::fieldRequired('client_id');
        }

        if (empty($this->message)) {
            throw ValidationException::fieldRequired('message');
        }
    }
}