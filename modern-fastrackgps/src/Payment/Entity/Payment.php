<?php

declare(strict_types=1);

namespace FastrackGps\Payment\Entity;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Payment\ValueObject\PaymentStatus;
use FastrackGps\Payment\ValueObject\PaymentMethod;
use FastrackGps\Security\InputSanitizer;
use DateTimeImmutable;

final class Payment
{
    public function __construct(
        private readonly int $id,
        private readonly int $clientId,
        private readonly string $description,
        private readonly float $amount,
        private readonly DateTimeImmutable $dueDate,
        private PaymentStatus $status = PaymentStatus::PENDING,
        private ?PaymentMethod $method = null,
        private ?DateTimeImmutable $paidAt = null,
        private ?string $transactionId = null,
        private ?string $observations = null,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private ?DateTimeImmutable $updatedAt = null
    ) {
        $this->validate();
    }

    public static function create(
        int $clientId,
        string $description,
        float $amount,
        DateTimeImmutable $dueDate,
        ?string $observations = null
    ): self {
        return new self(
            id: 0,
            clientId: $clientId,
            description: InputSanitizer::sanitizeString($description),
            amount: $amount,
            dueDate: $dueDate,
            observations: $observations ? InputSanitizer::sanitizeString($observations) : null
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            clientId: (int) $data['client_id'],
            description: $data['description'],
            amount: (float) $data['amount'],
            dueDate: new DateTimeImmutable($data['due_date']),
            status: PaymentStatus::from($data['status']),
            method: isset($data['payment_method']) ? PaymentMethod::from($data['payment_method']) : null,
            paidAt: isset($data['paid_at']) ? new DateTimeImmutable($data['paid_at']) : null,
            transactionId: $data['transaction_id'] ?? null,
            observations: $data['observations'] ?? null,
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDueDate(): DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function getMethod(): ?PaymentMethod
    {
        return $this->method;
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markAsPaid(
        PaymentMethod $method,
        ?string $transactionId = null,
        ?DateTimeImmutable $paidAt = null
    ): self {
        $payment = clone $this;
        $payment->status = PaymentStatus::PAID;
        $payment->method = $method;
        $payment->transactionId = $transactionId;
        $payment->paidAt = $paidAt ?? new DateTimeImmutable();
        $payment->updatedAt = new DateTimeImmutable();
        
        return $payment;
    }

    public function markAsPending(): self
    {
        $payment = clone $this;
        $payment->status = PaymentStatus::PENDING;
        $payment->method = null;
        $payment->transactionId = null;
        $payment->paidAt = null;
        $payment->updatedAt = new DateTimeImmutable();
        
        return $payment;
    }

    public function markAsOverdue(): self
    {
        $payment = clone $this;
        $payment->status = PaymentStatus::OVERDUE;
        $payment->updatedAt = new DateTimeImmutable();
        
        return $payment;
    }

    public function cancel(string $reason = null): self
    {
        $payment = clone $this;
        $payment->status = PaymentStatus::CANCELLED;
        $payment->observations = $reason ? 
            ($this->observations . "\nCancelado: " . $reason) : 
            $this->observations;
        $payment->updatedAt = new DateTimeImmutable();
        
        return $payment;
    }

    public function updateObservations(string $observations): self
    {
        $payment = clone $this;
        $payment->observations = InputSanitizer::sanitizeString($observations);
        $payment->updatedAt = new DateTimeImmutable();
        
        return $payment;
    }

    public function isOverdue(): bool
    {
        if ($this->status === PaymentStatus::PAID || $this->status === PaymentStatus::CANCELLED) {
            return false;
        }

        return new DateTimeImmutable() > $this->dueDate;
    }

    public function getDaysUntilDue(): int
    {
        $now = new DateTimeImmutable();
        $diff = $this->dueDate->diff($now);
        
        return $diff->invert ? $diff->days : -$diff->days;
    }

    public function getFormattedAmount(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getFormattedDueDate(): string
    {
        return $this->dueDate->format('d/m/Y');
    }

    public function getFormattedPaidDate(): string
    {
        return $this->paidAt?->format('d/m/Y H:i') ?? '-';
    }

    public function canBePaid(): bool
    {
        return $this->status === PaymentStatus::PENDING || $this->status === PaymentStatus::OVERDUE;
    }

    public function canBeCancelled(): bool
    {
        return $this->status !== PaymentStatus::PAID && $this->status !== PaymentStatus::CANCELLED;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'description' => $this->description,
            'amount' => $this->amount,
            'due_date' => $this->dueDate->format('Y-m-d'),
            'status' => $this->status->value,
            'payment_method' => $this->method?->value,
            'paid_at' => $this->paidAt?->format('Y-m-d H:i:s'),
            'transaction_id' => $this->transactionId,
            'observations' => $this->observations,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    private function validate(): void
    {
        if ($this->clientId <= 0) {
            throw ValidationException::fieldRequired('client_id');
        }

        if (empty($this->description)) {
            throw ValidationException::fieldRequired('description');
        }

        if ($this->amount <= 0) {
            throw ValidationException::invalidFormat('amount', 'positive value');
        }
    }
}