<?php

declare(strict_types=1);

namespace FastrackGps\Alert\Repository;

use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use FastrackGps\Alert\Entity\Alert;
use FastrackGps\Alert\ValueObject\AlertType;
use FastrackGps\Alert\ValueObject\AlertSeverity;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class MySqlAlertRepository implements AlertRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?Alert
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('id', '=', $id)
                ->first();

            return $data ? Alert::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alert by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByVehicleId(int $vehicleId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('vehicle_id', '=', $vehicleId)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alerts by vehicle ID', ['vehicle_id' => $vehicleId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta a')
                ->join('bem v', 'a.vehicle_id', '=', 'v.id')
                ->select([
                    'a.id', 'a.vehicle_id', 'a.type', 'a.severity', 'a.title', 'a.message',
                    'a.data', 'a.is_acknowledged', 'a.acknowledged_at', 'a.acknowledged_by',
                    'a.created_at', 'a.updated_at'
                ])
                ->where('v.cliente', '=', $clientId)
                ->orderBy('a.created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alerts by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByType(AlertType $type): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('type', '=', $type->value)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alerts by type', ['type' => $type->value, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findBySeverity(AlertSeverity $severity): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('severity', '=', $severity->value)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alerts by severity', ['severity' => $severity->value, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findUnacknowledged(): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('is_acknowledged', '=', false)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find unacknowledged alerts', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findUnacknowledgedByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta a')
                ->join('bem v', 'a.vehicle_id', '=', 'v.id')
                ->select([
                    'a.id', 'a.vehicle_id', 'a.type', 'a.severity', 'a.title', 'a.message',
                    'a.data', 'a.is_acknowledged', 'a.acknowledged_at', 'a.acknowledged_by',
                    'a.created_at', 'a.updated_at'
                ])
                ->where('v.cliente', '=', $clientId)
                ->where('a.is_acknowledged', '=', false)
                ->orderBy('a.created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find unacknowledged alerts by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
                ->where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alerts by date range', [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function findByVehicleIdAndDateRange(
        int $vehicleId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->where('vehicle_id', '=', $vehicleId)
                ->where('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
                ->where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find alerts by vehicle and date range', [
                'vehicle_id' => $vehicleId,
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function save(Alert $alert): void
    {
        try {
            if ($alert->getId() > 0) {
                $this->update($alert);
            } else {
                $this->insert($alert);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save alert', ['alert_id' => $alert->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->queryBuilder
                ->table('alerta')
                ->where('id', '=', $id)
                ->delete();

            $this->logger->info('Alert deleted successfully', ['alert_id' => $id]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete alert', ['alert_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countUnacknowledgedByClientId(int $clientId): int
    {
        try {
            return $this->queryBuilder
                ->table('alerta a')
                ->join('bem v', 'a.vehicle_id', '=', 'v.id')
                ->where('v.cliente', '=', $clientId)
                ->where('a.is_acknowledged', '=', false)
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count unacknowledged alerts', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countByTypeAndDateRange(
        AlertType $type,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): int {
        try {
            return $this->queryBuilder
                ->table('alerta')
                ->where('type', '=', $type->value)
                ->where('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
                ->where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count alerts by type and date range', [
                'type' => $type->value,
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $data = $this->queryBuilder
                ->table('alerta')
                ->select([
                    'id', 'vehicle_id', 'type', 'severity', 'title', 'message',
                    'data', 'is_acknowledged', 'acknowledged_at', 'acknowledged_by',
                    'created_at', 'updated_at'
                ])
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();

            return array_map(fn(array $row) => Alert::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all alerts', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function markAllAsAcknowledged(int $clientId): void
    {
        try {
            $this->queryBuilder
                ->table('alerta a')
                ->join('bem v', 'a.vehicle_id', '=', 'v.id')
                ->where('v.cliente', '=', $clientId)
                ->where('a.is_acknowledged', '=', false)
                ->update([
                    'is_acknowledged' => true,
                    'acknowledged_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->info('All alerts marked as acknowledged', ['client_id' => $clientId]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to mark all alerts as acknowledged', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function insert(Alert $alert): void
    {
        $data = $alert->toArray();
        unset($data['id']);
        
        $this->queryBuilder->table('alerta')->insert($data);
        $this->logger->info('New alert created', ['vehicle_id' => $alert->getVehicleId(), 'type' => $alert->getType()->value]);
    }

    private function update(Alert $alert): void
    {
        $data = $alert->toArray();
        $id = $data['id'];
        unset($data['id']);
        
        $this->queryBuilder
            ->table('alerta')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->info('Alert updated', ['alert_id' => $alert->getId()]);
    }
}