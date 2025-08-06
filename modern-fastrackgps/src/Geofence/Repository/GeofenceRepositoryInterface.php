<?php

declare(strict_types=1);

namespace FastrackGps\Geofence\Repository;

use FastrackGps\Geofence\Entity\Geofence;
use FastrackGps\Core\ValueObject\Coordinates;

interface GeofenceRepositoryInterface
{
    public function findById(int $id): ?Geofence;
    
    public function findByClientId(int $clientId): array;
    
    public function findActiveByClientId(int $clientId): array;
    
    public function findByName(string $name, int $clientId): ?Geofence;
    
    public function findContainingPoint(Coordinates $coordinates, int $clientId = null): array;
    
    public function save(Geofence $geofence): void;
    
    public function delete(int $id): void;
    
    public function countByClientId(int $clientId): int;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
    
    public function findByVehicleAssignment(int $vehicleId): array;
}