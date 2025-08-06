<?php

declare(strict_types=1);

namespace FastrackGps\Alert\Repository;

use FastrackGps\Alert\Entity\Alert;
use FastrackGps\Alert\ValueObject\AlertType;
use FastrackGps\Alert\ValueObject\AlertSeverity;
use DateTimeImmutable;

interface AlertRepositoryInterface
{
    public function findById(int $id): ?Alert;
    
    public function findByVehicleId(int $vehicleId): array;
    
    public function findByClientId(int $clientId): array;
    
    public function findByType(AlertType $type): array;
    
    public function findBySeverity(AlertSeverity $severity): array;
    
    public function findUnacknowledged(): array;
    
    public function findUnacknowledgedByClientId(int $clientId): array;
    
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array;
    
    public function findByVehicleIdAndDateRange(
        int $vehicleId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array;
    
    public function save(Alert $alert): void;
    
    public function delete(int $id): void;
    
    public function countUnacknowledgedByClientId(int $clientId): int;
    
    public function countByTypeAndDateRange(
        AlertType $type,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): int;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
    
    public function markAllAsAcknowledged(int $clientId): void;
}