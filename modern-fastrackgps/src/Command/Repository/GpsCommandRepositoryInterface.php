<?php

declare(strict_types=1);

namespace FastrackGps\Command\Repository;

use FastrackGps\Command\Entity\GpsCommand;
use FastrackGps\Command\ValueObject\CommandType;
use FastrackGps\Command\ValueObject\CommandStatus;
use DateTimeImmutable;

interface GpsCommandRepositoryInterface
{
    public function findById(int $id): ?GpsCommand;
    
    public function findByVehicleId(int $vehicleId): array;
    
    public function findByImei(string $imei): array;
    
    public function findByStatus(CommandStatus $status): array;
    
    public function findByType(CommandType $type): array;
    
    public function findPendingByImei(string $imei): array;
    
    public function findExpiredCommands(): array;
    
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array;
    
    public function save(GpsCommand $command): void;
    
    public function delete(int $id): void;
    
    public function countPendingByImei(string $imei): int;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
    
    public function markExpiredAsFailed(): int;
}