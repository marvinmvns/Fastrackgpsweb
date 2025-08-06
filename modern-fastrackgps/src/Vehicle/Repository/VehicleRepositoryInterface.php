<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\Repository;

use FastrackGps\Vehicle\Entity\Vehicle;

interface VehicleRepositoryInterface
{
    public function findById(int $id): ?Vehicle;
    
    public function findByImei(string $imei): ?Vehicle;
    
    public function findByClientId(int $clientId): array;
    
    public function findActiveByClientId(int $clientId): array;
    
    public function save(Vehicle $vehicle): void;
    
    public function delete(int $id): void;
    
    public function countByClientId(int $clientId): int;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
    
    public function updateLastPosition(string $imei, float $latitude, float $longitude): void;
    
    public function updateStatus(string $imei, string $status): void;
}