<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\Repository;

use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use FastrackGps\Vehicle\Entity\Vehicle;
use Psr\Log\LoggerInterface;

final class MySqlVehicleRepository implements VehicleRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?Vehicle
    {
        try {
            $data = $this->queryBuilder
                ->table('bem')
                ->select([
                    'id', 'imei', 'name', 'identificacao', 'cliente',
                    'numero_chip', 'operadora_chip', 'numero_chip2', 'operadora_chip2',
                    'cor_grafico', 'activated', 'modo_operacao', 'status_sinal',
                    'created_at', 'updated_at'
                ])
                ->where('id', '=', $id)
                ->first();

            return $data ? Vehicle::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find vehicle by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByImei(string $imei): ?Vehicle
    {
        try {
            $data = $this->queryBuilder
                ->table('bem')
                ->select([
                    'id', 'imei', 'name', 'identificacao', 'cliente',
                    'numero_chip', 'operadora_chip', 'numero_chip2', 'operadora_chip2',
                    'cor_grafico', 'activated', 'modo_operacao', 'status_sinal',
                    'created_at', 'updated_at'
                ])
                ->where('imei', '=', $imei)
                ->first();

            return $data ? Vehicle::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find vehicle by IMEI', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('bem')
                ->select([
                    'id', 'imei', 'name', 'identificacao', 'cliente',
                    'numero_chip', 'operadora_chip', 'numero_chip2', 'operadora_chip2',
                    'cor_grafico', 'activated', 'modo_operacao', 'status_sinal',
                    'created_at', 'updated_at'
                ])
                ->where('cliente', '=', $clientId)
                ->orderBy('name')
                ->get();

            return array_map(fn(array $row) => Vehicle::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find vehicles by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findActiveByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('bem')
                ->select([
                    'id', 'imei', 'name', 'identificacao', 'cliente',
                    'numero_chip', 'operadora_chip', 'numero_chip2', 'operadora_chip2',
                    'cor_grafico', 'activated', 'modo_operacao', 'status_sinal',
                    'created_at', 'updated_at'
                ])
                ->where('cliente', '=', $clientId)
                ->where('activated', '=', 'S')
                ->orderBy('name')
                ->get();

            return array_map(fn(array $row) => Vehicle::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find active vehicles by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function save(Vehicle $vehicle): void
    {
        try {
            if ($vehicle->getId() > 0) {
                $this->update($vehicle);
            } else {
                $this->insert($vehicle);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save vehicle', ['vehicle_id' => $vehicle->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->queryBuilder
                ->table('bem')
                ->where('id', '=', $id)
                ->delete();

            $this->logger->info('Vehicle deleted successfully', ['vehicle_id' => $id]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete vehicle', ['vehicle_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countByClientId(int $clientId): int
    {
        try {
            return $this->queryBuilder
                ->table('bem')
                ->where('cliente', '=', $clientId)
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count vehicles by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $data = $this->queryBuilder
                ->table('bem')
                ->select([
                    'id', 'imei', 'name', 'identificacao', 'cliente',
                    'numero_chip', 'operadora_chip', 'numero_chip2', 'operadora_chip2',
                    'cor_grafico', 'activated', 'modo_operacao', 'status_sinal',
                    'created_at', 'updated_at'
                ])
                ->orderBy('name')
                ->limit($limit)
                ->get();

            return array_map(fn(array $row) => Vehicle::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all vehicles', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateLastPosition(string $imei, float $latitude, float $longitude): void
    {
        try {
            $this->queryBuilder
                ->table('bem')
                ->where('imei', '=', $imei)
                ->update([
                    'last_latitude' => $latitude,
                    'last_longitude' => $longitude,
                    'last_position_time' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->debug('Vehicle position updated', ['imei' => $imei, 'lat' => $latitude, 'lng' => $longitude]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to update vehicle position', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateStatus(string $imei, string $status): void
    {
        try {
            $this->queryBuilder
                ->table('bem')
                ->where('imei', '=', $imei)
                ->update([
                    'status_sinal' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->debug('Vehicle status updated', ['imei' => $imei, 'status' => $status]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to update vehicle status', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function insert(Vehicle $vehicle): void
    {
        $data = $vehicle->toArray();
        unset($data['id']);
        
        $this->queryBuilder->table('bem')->insert($data);
        $this->logger->info('New vehicle created', ['imei' => $vehicle->getImei()]);
    }

    private function update(Vehicle $vehicle): void
    {
        $data = $vehicle->toArray();
        $id = $data['id'];
        unset($data['id']);
        
        $this->queryBuilder
            ->table('bem')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->info('Vehicle updated', ['vehicle_id' => $vehicle->getId()]);
    }
}