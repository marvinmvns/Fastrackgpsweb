<?php

declare(strict_types=1);

namespace FastrackGps\Command\Repository;

use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use FastrackGps\Command\Entity\GpsCommand;
use FastrackGps\Command\ValueObject\CommandType;
use FastrackGps\Command\ValueObject\CommandStatus;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class MySqlGpsCommandRepository implements GpsCommandRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?GpsCommand
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('id', '=', $id)
                ->first();

            return $data ? GpsCommand::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS command by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByVehicleId(int $vehicleId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('vehicle_id', '=', $vehicleId)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS commands by vehicle ID', ['vehicle_id' => $vehicleId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByImei(string $imei): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('imei', '=', $imei)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS commands by IMEI', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByStatus(CommandStatus $status): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('status', '=', $status->value)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS commands by status', ['status' => $status->value, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByType(CommandType $type): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('type', '=', $type->value)
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS commands by type', ['type' => $type->value, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findPendingByImei(string $imei): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('imei', '=', $imei)
                ->whereIn('status', ['pending', 'sent'])
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->orderBy('created_at', 'ASC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find pending GPS commands by IMEI', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findExpiredCommands(): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->whereIn('status', ['pending', 'sent'])
                ->where('expires_at', '<', date('Y-m-d H:i:s'))
                ->orderBy('expires_at', 'ASC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find expired GPS commands', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->where('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
                ->where('created_at', '<=', $endDate->format('Y-m-d H:i:s'))
                ->orderBy('created_at', 'DESC')
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find GPS commands by date range', [
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function save(GpsCommand $command): void
    {
        try {
            if ($command->getId() > 0) {
                $this->update($command);
            } else {
                $this->insert($command);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save GPS command', ['command_id' => $command->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->queryBuilder
                ->table('comando_gps')
                ->where('id', '=', $id)
                ->delete();

            $this->logger->info('GPS command deleted successfully', ['command_id' => $id]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete GPS command', ['command_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countPendingByImei(string $imei): int
    {
        try {
            return $this->queryBuilder
                ->table('comando_gps')
                ->where('imei', '=', $imei)
                ->whereIn('status', ['pending', 'sent'])
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count pending GPS commands', ['imei' => $imei, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $data = $this->queryBuilder
                ->table('comando_gps')
                ->select([
                    'id', 'vehicle_id', 'imei', 'type', 'command', 'parameters',
                    'status', 'sent_at', 'confirmed_at', 'expires_at', 'response',
                    'error_message', 'retry_count', 'created_at', 'updated_at'
                ])
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();

            return array_map(fn(array $row) => GpsCommand::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all GPS commands', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function markExpiredAsFailed(): int
    {
        try {
            $affectedRows = $this->queryBuilder
                ->table('comando_gps')
                ->whereIn('status', ['pending', 'sent'])
                ->where('expires_at', '<', date('Y-m-d H:i:s'))
                ->update([
                    'status' => 'failed',
                    'error_message' => 'Command expired',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->info('Expired GPS commands marked as failed', ['count' => $affectedRows]);

            return $affectedRows;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to mark expired commands as failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function insert(GpsCommand $command): void
    {
        $data = $command->toArray();
        unset($data['id']);
        
        $this->queryBuilder->table('comando_gps')->insert($data);
        $this->logger->info('New GPS command created', ['imei' => $command->getImei(), 'type' => $command->getType()->value]);
    }

    private function update(GpsCommand $command): void
    {
        $data = $command->toArray();
        $id = $data['id'];
        unset($data['id']);
        
        $this->queryBuilder
            ->table('comando_gps')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->info('GPS command updated', ['command_id' => $command->getId()]);
    }
}