<?php

declare(strict_types=1);

namespace FastrackGps\Command\Entity;

use FastrackGps\Command\ValueObject\CommandType;
use FastrackGps\Command\ValueObject\CommandStatus;
use FastrackGps\Core\Exception\ValidationException;
use DateTimeImmutable;

final class GpsCommand
{
    public function __construct(
        private readonly int $id,
        private readonly string $deviceImei,
        private readonly int $userId,
        private readonly CommandType $type,
        private readonly string $command,
        private CommandStatus $status = CommandStatus::PENDING,
        private ?DateTimeImmutable $sentAt = null,
        private ?DateTimeImmutable $acknowledgedAt = null,
        private ?string $response = null,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private ?string $failureReason = null
    ) {
        $this->validate();
    }

    public static function create(
        string $deviceImei,
        int $userId,
        CommandType $type,
        array $parameters = []
    ): self {
        $command = $type->buildCommand($parameters);
        
        return new self(
            id: 0, // Will be set by repository
            deviceImei: $deviceImei,
            userId: $userId,
            type: $type,
            command: $command
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            deviceImei: $data['device_imei'],
            userId: (int) $data['user_id'],
            type: CommandType::from($data['command_type']),
            command: $data['command'],
            status: CommandStatus::from($data['status']),
            sentAt: isset($data['sent_at']) ? new DateTimeImmutable($data['sent_at']) : null,
            acknowledgedAt: isset($data['acknowledged_at']) ? new DateTimeImmutable($data['acknowledged_at']) : null,
            response: $data['response'] ?? null,
            createdAt: new DateTimeImmutable($data['created_at']),
            failureReason: $data['failure_reason'] ?? null
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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getType(): CommandType
    {
        return $this->type;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getStatus(): CommandStatus
    {
        return $this->status;
    }

    public function getSentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function getAcknowledgedAt(): ?DateTimeImmutable
    {
        return $this->acknowledgedAt;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function markAsSent(): self
    {
        $command = clone $this;
        $command->status = CommandStatus::SENT;
        $command->sentAt = new DateTimeImmutable();
        return $command;
    }

    public function markAsAcknowledged(string $response = null): self
    {
        $command = clone $this;
        $command->status = CommandStatus::ACKNOWLEDGED;
        $command->acknowledgedAt = new DateTimeImmutable();
        $command->response = $response;
        return $command;
    }

    public function markAsFailed(string $reason): self
    {
        $command = clone $this;
        $command->status = CommandStatus::FAILED;
        $command->failureReason = $reason;
        return $command;
    }

    public function markAsExpired(): self
    {
        $command = clone $this;
        $command->status = CommandStatus::EXPIRED;
        return $command;
    }

    public function isExpired(): bool
    {
        if ($this->status !== CommandStatus::PENDING && $this->status !== CommandStatus::SENT) {
            return false;
        }

        $now = new DateTimeImmutable();
        $expirationTime = $this->createdAt->modify('+15 minutes');
        
        return $now > $expirationTime;
    }

    public function requiresConfirmation(): bool
    {
        return match ($this->type) {
            CommandType::BLOCK_ENGINE_AUDIBLE,
            CommandType::BLOCK_ENGINE_SILENT,
            CommandType::ACTIVATE_SMS_MODE => true,
            default => false,
        };
    }

    public function getFormattedDuration(): ?string
    {
        if ($this->acknowledgedAt === null || $this->sentAt === null) {
            return null;
        }

        $diff = $this->acknowledgedAt->diff($this->sentAt);
        return $diff->format('%H:%I:%S');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'device_imei' => $this->deviceImei,
            'user_id' => $this->userId,
            'command_type' => $this->type->value,
            'command' => $this->command,
            'status' => $this->status->value,
            'sent_at' => $this->sentAt?->format('Y-m-d H:i:s'),
            'acknowledged_at' => $this->acknowledgedAt?->format('Y-m-d H:i:s'),
            'response' => $this->response,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'failure_reason' => $this->failureReason,
        ];
    }

    private function validate(): void
    {
        if (empty($this->deviceImei)) {
            throw ValidationException::fieldRequired('device_imei');
        }

        if ($this->userId <= 0) {
            throw ValidationException::fieldRequired('user_id');
        }

        if (empty($this->command)) {
            throw ValidationException::fieldRequired('command');
        }
    }
}