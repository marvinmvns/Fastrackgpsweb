<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\Repository;

use FastrackGps\Tracking\Entity\GpsPosition;
use DateTimeImmutable;

interface GpsPositionRepositoryInterface
{
    public function findById(int $id): ?GpsPosition;
    
    public function findByImei(string $imei, int $limit = 100): array;
    
    public function findLatestByImei(string $imei): ?GpsPosition;
    
    public function findByImeiAndDateRange(
        string $imei,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $limit = 1000
    ): array;
    
    public function findByVehicleId(int $vehicleId, int $limit = 100): array;
    
    public function save(GpsPosition $position): void;
    
    public function delete(int $id): void;
    
    public function deleteOldPositions(DateTimeImmutable $beforeDate): int;
    
    public function countByImei(string $imei): int;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
}