<?php

declare(strict_types=1);

namespace FastrackGps\Geofence\Repository;

use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use FastrackGps\Core\ValueObject\Coordinates;
use FastrackGps\Geofence\Entity\Geofence;
use Psr\Log\LoggerInterface;

final class MySqlGeofenceRepository implements GeofenceRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?Geofence
    {
        try {
            $data = $this->queryBuilder
                ->table('cerca_virtual')
                ->select([
                    'id', 'client_id', 'name', 'description', 'type', 'coordinates',
                    'radius', 'color', 'is_active', 'alert_on_enter', 'alert_on_exit',
                    'created_at', 'updated_at'
                ])
                ->where('id', '=', $id)
                ->first();

            return $data ? Geofence::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find geofence by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('cerca_virtual')
                ->select([
                    'id', 'client_id', 'name', 'description', 'type', 'coordinates',
                    'radius', 'color', 'is_active', 'alert_on_enter', 'alert_on_exit',
                    'created_at', 'updated_at'
                ])
                ->where('client_id', '=', $clientId)
                ->orderBy('name')
                ->get();

            return array_map(fn(array $row) => Geofence::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find geofences by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findActiveByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('cerca_virtual')
                ->select([
                    'id', 'client_id', 'name', 'description', 'type', 'coordinates',
                    'radius', 'color', 'is_active', 'alert_on_enter', 'alert_on_exit',
                    'created_at', 'updated_at'
                ])
                ->where('client_id', '=', $clientId)
                ->where('is_active', '=', true)
                ->orderBy('name')
                ->get();

            return array_map(fn(array $row) => Geofence::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find active geofences by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByName(string $name, int $clientId): ?Geofence
    {
        try {
            $data = $this->queryBuilder
                ->table('cerca_virtual')
                ->select([
                    'id', 'client_id', 'name', 'description', 'type', 'coordinates',
                    'radius', 'color', 'is_active', 'alert_on_enter', 'alert_on_exit',
                    'created_at', 'updated_at'
                ])
                ->where('name', '=', $name)
                ->where('client_id', '=', $clientId)
                ->first();

            return $data ? Geofence::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find geofence by name', ['name' => $name, 'client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findContainingPoint(Coordinates $coordinates, int $clientId = null): array
    {
        try {
            $query = $this->queryBuilder
                ->table('cerca_virtual')
                ->select([
                    'id', 'client_id', 'name', 'description', 'type', 'coordinates',
                    'radius', 'color', 'is_active', 'alert_on_enter', 'alert_on_exit',
                    'created_at', 'updated_at'
                ])
                ->where('is_active', '=', true);

            if ($clientId !== null) {
                $query->where('client_id', '=', $clientId);
            }

            $geofences = $query->get();
            $containingGeofences = [];

            foreach ($geofences as $data) {
                $geofence = Geofence::fromArray($data);
                if ($geofence->containsPoint($coordinates)) {
                    $containingGeofences[] = $geofence;
                }
            }

            return $containingGeofences;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find geofences containing point', [
                'coordinates' => ['lat' => $coordinates->latitude, 'lng' => $coordinates->longitude],
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function save(Geofence $geofence): void
    {
        try {
            if ($geofence->getId() > 0) {
                $this->update($geofence);
            } else {
                $this->insert($geofence);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save geofence', ['geofence_id' => $geofence->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->queryBuilder
                ->table('cerca_virtual')
                ->where('id', '=', $id)
                ->delete();

            $this->logger->info('Geofence deleted successfully', ['geofence_id' => $id]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete geofence', ['geofence_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countByClientId(int $clientId): int
    {
        try {
            return $this->queryBuilder
                ->table('cerca_virtual')
                ->where('client_id', '=', $clientId)
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count geofences by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $data = $this->queryBuilder
                ->table('cerca_virtual')
                ->select([
                    'id', 'client_id', 'name', 'description', 'type', 'coordinates',
                    'radius', 'color', 'is_active', 'alert_on_enter', 'alert_on_exit',
                    'created_at', 'updated_at'
                ])
                ->orderBy('name')
                ->limit($limit)
                ->offset($offset)
                ->get();

            return array_map(fn(array $row) => Geofence::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all geofences', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByVehicleAssignment(int $vehicleId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('cerca_virtual g')
                ->join('veiculo_cerca_virtual vcv', 'g.id', '=', 'vcv.geofence_id')
                ->select([
                    'g.id', 'g.client_id', 'g.name', 'g.description', 'g.type', 'g.coordinates',
                    'g.radius', 'g.color', 'g.is_active', 'g.alert_on_enter', 'g.alert_on_exit',
                    'g.created_at', 'g.updated_at'
                ])
                ->where('vcv.vehicle_id', '=', $vehicleId)
                ->where('g.is_active', '=', true)
                ->orderBy('g.name')
                ->get();

            return array_map(fn(array $row) => Geofence::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find geofences by vehicle assignment', ['vehicle_id' => $vehicleId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function insert(Geofence $geofence): void
    {
        $data = $geofence->toArray();
        unset($data['id']);
        
        $this->queryBuilder->table('cerca_virtual')->insert($data);
        $this->logger->info('New geofence created', ['name' => $geofence->getName(), 'client_id' => $geofence->getClientId()]);
    }

    private function update(Geofence $geofence): void
    {
        $data = $geofence->toArray();
        $id = $data['id'];
        unset($data['id']);
        
        $this->queryBuilder
            ->table('cerca_virtual')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->info('Geofence updated', ['geofence_id' => $geofence->getId()]);
    }
}