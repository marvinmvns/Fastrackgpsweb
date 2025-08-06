<?php

declare(strict_types=1);

namespace FastrackGps\Command\Service;

use FastrackGps\Command\Entity\GpsCommand;
use FastrackGps\Command\Repository\GpsCommandRepositoryInterface;
use FastrackGps\Command\ValueObject\CommandType;
use FastrackGps\Command\ValueObject\CommandStatus;
use FastrackGps\Vehicle\Repository\VehicleRepositoryInterface;
use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Exception\BusinessException;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class GpsCommandService
{
    private const MAX_RETRY_COUNT = 3;
    private const COMMAND_TIMEOUT_MINUTES = 30;

    public function __construct(
        private readonly GpsCommandRepositoryInterface $commandRepository,
        private readonly VehicleRepositoryInterface $vehicleRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendCommand(string $imei, CommandType $type, array $parameters = []): GpsCommand
    {
        $vehicle = $this->vehicleRepository->findByImei($imei);
        if ($vehicle === null) {
            throw new ValidationException(['imei' => 'Veículo não encontrado']);
        }

        if (!$vehicle->isOnline()) {
            throw new BusinessException('Veículo não está online');
        }

        $pendingCount = $this->commandRepository->countPendingByImei($imei);
        if ($pendingCount >= 5) {
            throw new BusinessException('Muitos comandos pendentes para este veículo');
        }

        $commandString = $this->buildCommandString($type, $parameters);
        $expiresAt = new DateTimeImmutable('+' . self::COMMAND_TIMEOUT_MINUTES . ' minutes');

        $command = GpsCommand::create(
            vehicleId: $vehicle->getId(),
            imei: $imei,
            type: $type,
            command: $commandString,
            parameters: $parameters,
            expiresAt: $expiresAt
        );

        $this->commandRepository->save($command);

        $this->logger->info('GPS command created', [
            'command_id' => $command->getId(),
            'imei' => $imei,
            'type' => $type->value,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);

        return $command;
    }

    public function markCommandAsSent(int $commandId): GpsCommand
    {
        $command = $this->commandRepository->findById($commandId);
        if ($command === null) {
            throw new ValidationException(['command' => 'Comando não encontrado']);
        }

        if ($command->getStatus() !== CommandStatus::PENDING) {
            throw new BusinessException('Comando não está pendente');
        }

        $command = $command->markAsSent();
        $this->commandRepository->save($command);

        $this->logger->info('GPS command marked as sent', ['command_id' => $commandId]);

        return $command;
    }

    public function confirmCommand(int $commandId, string $response = null): GpsCommand
    {
        $command = $this->commandRepository->findById($commandId);
        if ($command === null) {
            throw new ValidationException(['command' => 'Comando não encontrado']);
        }

        if (!in_array($command->getStatus(), [CommandStatus::PENDING, CommandStatus::SENT])) {
            throw new BusinessException('Comando não pode ser confirmado');
        }

        $command = $command->confirm($response);
        $this->commandRepository->save($command);

        $this->logger->info('GPS command confirmed', [
            'command_id' => $commandId,
            'response' => $response
        ]);

        return $command;
    }

    public function failCommand(int $commandId, string $errorMessage = null): GpsCommand
    {
        $command = $this->commandRepository->findById($commandId);
        if ($command === null) {
            throw new ValidationException(['command' => 'Comando não encontrado']);
        }

        if ($command->getStatus() === CommandStatus::CONFIRMED) {
            throw new BusinessException('Comando já foi confirmado');
        }

        $command = $command->fail($errorMessage);
        $this->commandRepository->save($command);

        $this->logger->warning('GPS command failed', [
            'command_id' => $commandId,
            'error' => $errorMessage
        ]);

        return $command;
    }

    public function retryCommand(int $commandId): GpsCommand
    {
        $command = $this->commandRepository->findById($commandId);
        if ($command === null) {
            throw new ValidationException(['command' => 'Comando não encontrado']);
        }

        if ($command->getStatus() !== CommandStatus::FAILED) {
            throw new BusinessException('Apenas comandos falhados podem ser repetidos');
        }

        if ($command->getRetryCount() >= self::MAX_RETRY_COUNT) {
            throw new BusinessException('Limite de tentativas excedido');
        }

        $expiresAt = new DateTimeImmutable('+' . self::COMMAND_TIMEOUT_MINUTES . ' minutes');
        $command = $command->retry($expiresAt);
        $this->commandRepository->save($command);

        $this->logger->info('GPS command retried', [
            'command_id' => $commandId,
            'retry_count' => $command->getRetryCount()
        ]);

        return $command;
    }

    public function getPendingCommands(string $imei): array
    {
        return $this->commandRepository->findPendingByImei($imei);
    }

    public function getCommandsByVehicle(int $vehicleId): array
    {
        return $this->commandRepository->findByVehicleId($vehicleId);
    }

    public function getCommandsByImei(string $imei): array
    {
        return $this->commandRepository->findByImei($imei);
    }

    public function getCommandsByStatus(CommandStatus $status): array
    {
        return $this->commandRepository->findByStatus($status);
    }

    public function getCommandsByType(CommandType $type): array
    {
        return $this->commandRepository->findByType($type);
    }

    public function getCommandsByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        return $this->commandRepository->findByDateRange($startDate, $endDate);
    }

    public function processExpiredCommands(): int
    {
        return $this->commandRepository->markExpiredAsFailed();
    }

    public function deleteCommand(int $commandId): void
    {
        $command = $this->commandRepository->findById($commandId);
        if ($command === null) {
            throw new ValidationException(['command' => 'Comando não encontrado']);
        }

        if (in_array($command->getStatus(), [CommandStatus::PENDING, CommandStatus::SENT])) {
            throw new BusinessException('Não é possível excluir comandos pendentes ou enviados');
        }

        $this->commandRepository->delete($commandId);

        $this->logger->info('GPS command deleted', ['command_id' => $commandId]);
    }

    public function getCommandStatistics(string $imei = null, DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): array
    {
        $startDate = $startDate ?? new DateTimeImmutable('-30 days');
        $endDate = $endDate ?? new DateTimeImmutable();

        $commands = $this->commandRepository->findByDateRange($startDate, $endDate);

        if ($imei !== null) {
            $commands = array_filter($commands, fn(GpsCommand $cmd) => $cmd->getImei() === $imei);
        }

        $statistics = [
            'total_commands' => count($commands),
            'by_status' => [],
            'by_type' => [],
            'success_rate' => 0.0,
            'average_response_time' => 0.0,
            'retry_rate' => 0.0
        ];

        $totalResponseTime = 0;
        $confirmedCount = 0;
        $retriedCount = 0;

        foreach ($commands as $command) {
            $status = $command->getStatus()->value;
            $type = $command->getType()->value;

            $statistics['by_status'][$status] = ($statistics['by_status'][$status] ?? 0) + 1;
            $statistics['by_type'][$type] = ($statistics['by_type'][$type] ?? 0) + 1;

            if ($command->getStatus() === CommandStatus::CONFIRMED) {
                $confirmedCount++;
                
                if ($command->getSentAt() && $command->getConfirmedAt()) {
                    $responseTime = $command->getConfirmedAt()->getTimestamp() - $command->getSentAt()->getTimestamp();
                    $totalResponseTime += $responseTime;
                }
            }

            if ($command->getRetryCount() > 0) {
                $retriedCount++;
            }
        }

        if (count($commands) > 0) {
            $statistics['success_rate'] = ($confirmedCount / count($commands)) * 100;
            $statistics['retry_rate'] = ($retriedCount / count($commands)) * 100;
        }

        if ($confirmedCount > 0) {
            $statistics['average_response_time'] = $totalResponseTime / $confirmedCount;
        }

        return $statistics;
    }

    public function generateCommandReport(
        string $imei,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        $commands = $this->commandRepository->findByImei($imei);
        $periodCommands = array_filter($commands, function (GpsCommand $command) use ($startDate, $endDate) {
            $commandDate = $command->getCreatedAt();
            return $commandDate >= $startDate && $commandDate <= $endDate;
        });

        $report = [
            'imei' => $imei,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_commands' => count($periodCommands),
                'confirmed' => 0,
                'failed' => 0,
                'pending' => 0,
                'sent' => 0
            ],
            'by_type' => [],
            'commands' => []
        ];

        foreach ($periodCommands as $command) {
            $status = $command->getStatus()->value;
            $report['summary'][$status]++;

            $type = $command->getType()->getDisplayName();
            if (!isset($report['by_type'][$type])) {
                $report['by_type'][$type] = 0;
            }
            $report['by_type'][$type]++;

            $report['commands'][] = [
                'id' => $command->getId(),
                'type' => $command->getType()->getDisplayName(),
                'command' => $command->getCommand(),
                'status' => $command->getStatus()->getDisplayName(),
                'sent_at' => $command->getSentAt()?->format('Y-m-d H:i:s'),
                'confirmed_at' => $command->getConfirmedAt()?->format('Y-m-d H:i:s'),
                'expires_at' => $command->getExpiresAt()->format('Y-m-d H:i:s'),
                'response' => $command->getResponse(),
                'error_message' => $command->getErrorMessage(),
                'retry_count' => $command->getRetryCount(),
                'created_at' => $command->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $report;
    }

    private function buildCommandString(CommandType $type, array $parameters): string
    {
        return match ($type) {
            CommandType::REBOOT => 'AT+GTRTO=gv300,0,,,,FFFF$',
            CommandType::RESET => 'AT+GTRESET=gv300,0,,,,FFFF$',
            CommandType::STOP_ENGINE => 'AT+GTOUT=gv300,1,0,0,0,0,0,0,0,0,FFFF$',
            CommandType::START_ENGINE => 'AT+GTOUT=gv300,0,0,0,0,0,0,0,0,0,FFFF$',
            CommandType::SET_INTERVAL => sprintf('AT+GTSRI=gv300,1,0,0,%d,,%d,FFFF$', 
                $parameters['interval'] ?? 30, 
                $parameters['distance'] ?? 100
            ),
            CommandType::REQUEST_POSITION => 'AT+GTQSS=gv300,0,FFFF$',
            CommandType::SET_SPEED_LIMIT => sprintf('AT+GTSPD=gv300,0,0,0,0,%d,3,5,3600,180,30,FFFF$',
                $parameters['speed_limit'] ?? 80
            ),
            CommandType::CUSTOM => $parameters['command'] ?? '',
        };
    }
}