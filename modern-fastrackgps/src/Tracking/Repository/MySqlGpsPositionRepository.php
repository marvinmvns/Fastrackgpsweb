<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\Repository;

use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use FastrackGps\Tracking\Entity\GpsPosition;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class MySqlGpsPositionRepository implements GpsPositionRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?GpsPosition
    {
        try {
            $data = $this->queryBuilder
                ->table('posicao_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'latitude', 'longitude', 'altitude',
                    'speed', 'course', 'satellites', 'gps_signal_indicator', 'address',
                    'battery_level', 'gsm_signal', 'extended_info', 'timestamp',
                    'created_at', 'updated_at'
                ])
                ->where('id', '=', $id)
                ->first();

            return $data ? GpsPosition::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS position by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByImei(string $imei, int $limit = 100): array
    {
        try {
            $data = $this->queryBuilder
                ->table('posicao_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'latitude', 'longitude', 'altitude',
                    'speed', 'course', 'satellites', 'gps_signal_indicator', 'address',
                    'battery_level', 'gsm_signal', 'extended_info', 'timestamp',
                    'created_at', 'updated_at'
                ])
                ->where('imei', '=', $imei)
                ->orderBy('timestamp', 'DESC')
                ->limit($limit)
                ->get();

            return array_map(fn(array $row) => GpsPosition::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS positions by IMEI', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findLatestByImei(string $imei): ?GpsPosition
    {
        try {
            $data = $this->queryBuilder
                ->table('posicao_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'latitude', 'longitude', 'altitude',
                    'speed', 'course', 'satellites', 'gps_signal_indicator', 'address',
                    'battery_level', 'gsm_signal', 'extended_info', 'timestamp',
                    'created_at', 'updated_at'
                ])
                ->where('imei', '=', $imei)
                ->orderBy('timestamp', 'DESC')
                ->first();

            return $data ? GpsPosition::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find latest GPS position by IMEI', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByImeiAndDateRange(
        string $imei,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $limit = 1000
    ): array {
        try {
            $data = $this->queryBuilder
                ->table('posicao_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'latitude', 'longitude', 'altitude',
                    'speed', 'course', 'satellites', 'gps_signal_indicator', 'address',
                    'battery_level', 'gsm_signal', 'extended_info', 'timestamp',
                    'created_at', 'updated_at'
                ])
                ->where('imei', '=', $imei)
                ->where('timestamp', '>=', $startDate->format('Y-m-d H:i:s'))
                ->where('timestamp', '<=', $endDate->format('Y-m-d H:i:s'))
                ->orderBy('timestamp', 'ASC')
                ->limit($limit)
                ->get();

            return array_map(fn(array $row) => GpsPosition::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS positions by date range', [
                'imei' => $imei,
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function findByVehicleId(int $vehicleId, int $limit = 100): array
    {
        try {
            $data = $this->queryBuilder
                ->table('posicao_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'latitude', 'longitude', 'altitude',
                    'speed', 'course', 'satellites', 'gps_signal_indicator', 'address',
                    'battery_level', 'gsm_signal', 'extended_info', 'timestamp',
                    'created_at', 'updated_at'
                ])
                ->where('vehicle_id', '=', $vehicleId)
                ->orderBy('timestamp', 'DESC')
                ->limit($limit)
                ->get();

            return array_map(fn(array $row) => GpsPosition::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS positions by vehicle ID', ['vehicle_id' => $vehicleId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function save(GpsPosition $position): void
    {
        try {
            if ($position->getId() > 0) {
                $this->update($position);
            } else {
                $this->insert($position);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save GPS position', ['position_id' => $position->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->queryBuilder
                ->table('posicao_gps')
                ->where('id', '=', $id)
                ->delete();

            $this->logger->info('GPS position deleted successfully', ['position_id' => $id]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete GPS position', ['position_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteOldPositions(DateTimeImmutable $beforeDate): int
    {
        try {
            $affectedRows = $this->queryBuilder
                ->table('posicao_gps')
                ->where('created_at', '<', $beforeDate->format('Y-m-d H:i:s'))
                ->delete();

            $this->logger->info('Old GPS positions deleted', [
                'before_date' => $beforeDate->format('Y-m-d H:i:s'),
                'count' => $affectedRows
            ]);

            return $affectedRows;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete old GPS positions', [
                'before_date' => $beforeDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function countByImei(string $imei): int
    {
        try {
            return $this->queryBuilder
                ->table('posicao_gps')
                ->where('imei', '=', $imei)
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count GPS positions by IMEI', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $data = $this->queryBuilder
                ->table('posicao_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'latitude', 'longitude', 'altitude',
                    'speed', 'course', 'satellites', 'gps_signal_indicator', 'address',
                    'battery_level', 'gsm_signal', 'extended_info', 'timestamp',
                    'created_at', 'updated_at'
                ])
                ->orderBy('timestamp', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();

            return array_map(fn(array $row) => GpsPosition::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all GPS positions', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function insert(GpsPosition $position): void
    {
        $data = $position->toArray();
        unset($data['id']);
        
        $this->queryBuilder->table('posicao_gps')->insert($data);
        $this->logger->debug('New GPS position saved', ['imei' => $position->getImei()]);
    }

    private function update(GpsPosition $position): void
    {
        $data = $position->toArray();
        $id = $data['id'];
        unset($data['id']);
        
        $this->queryBuilder
            ->table('posicao_gps')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->debug('GPS position updated', ['position_id' => $position->getId()]);
    }
}