<?php

declare(strict_types=1);

namespace FastrackGps\Command\Controller;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Http\JsonResponse;
use FastrackGps\Core\Http\Request;
use FastrackGps\Core\Http\Response;
use FastrackGps\Security\CsrfTokenManager;
use FastrackGps\Command\Service\GpsCommandService;
use FastrackGps\Command\ValueObject\CommandType;
use FastrackGps\Command\ValueObject\CommandStatus;
use FastrackGps\Vehicle\Service\VehicleService;
use FastrackGps\Auth\Service\AuthenticationService;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class GpsCommandController
{
    public function __construct(
        private readonly GpsCommandService $commandService,
        private readonly VehicleService $vehicleService,
        private readonly AuthenticationService $authService,
        private readonly CsrfTokenManager $csrfManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }

        try {
            $vehicleId = $request->input('vehicle_id');
            $imei = $request->input('imei');
            
            if ($vehicleId) {
                $commands = $this->commandService->getCommandsByVehicle((int) $vehicleId);
            } elseif ($imei) {
                $commands = $this->commandService->getCommandsByImei($imei);
            } else {
                // Get all commands for user's vehicles
                $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
                $commands = [];
                foreach ($vehicles as $vehicle) {
                    $vehicleCommands = $this->commandService->getCommandsByImei($vehicle->getImei());
                    $commands = array_merge($commands, $vehicleCommands);
                }
                // Sort by creation date
                usort($commands, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
            }

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'commands' => array_map(fn($command) => [
                        'id' => $command->getId(),
                        'vehicle_id' => $command->getVehicleId(),
                        'imei' => $command->getImei(),
                        'type' => $command->getType()->getDisplayName(),
                        'command' => $command->getCommand(),
                        'status' => $command->getStatus()->getDisplayName(),
                        'sent_at' => $command->getSentAt()?->format('Y-m-d H:i:s'),
                        'confirmed_at' => $command->getConfirmedAt()?->format('Y-m-d H:i:s'),
                        'expires_at' => $command->getExpiresAt()->format('Y-m-d H:i:s'),
                        'retry_count' => $command->getRetryCount(),
                        'created_at' => $command->getCreatedAt()->format('Y-m-d H:i:s'),
                        'status_class' => $command->getStatus()->getCssClass()
                    ], $commands)
                ]);
            }

            return Response::view('command/index', [
                'commands' => $commands,
                'user' => $user,
                'vehicle_id' => $vehicleId,
                'imei' => $imei,
                'pending_count' => count(array_filter($commands, fn($cmd) => $cmd->getStatus() === CommandStatus::PENDING))
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to list GPS commands', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load commands');
            }
            
            return Response::view('command/index', [
                'error' => 'Erro ao carregar comandos',
                'commands' => []
            ]);
        }
    }

    public function show(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $command = $this->commandService->getCommandsByVehicle(0); // Get from all vehicles
            $userCommand = null;
            
            // Find command and verify ownership
            $userVehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $userImeis = array_map(fn($vehicle) => $vehicle->getImei(), $userVehicles);
            
            foreach ($userImeis as $imei) {
                $commands = $this->commandService->getCommandsByImei($imei);
                foreach ($commands as $cmd) {
                    if ($cmd->getId() === $id) {
                        $userCommand = $cmd;
                        break 2;
                    }
                }
            }

            if ($userCommand === null) {
                if ($request->expectsJson()) {
                    return JsonResponse::error('Command not found', [], 404);
                }
                return Response::view('error/404');
            }

            $commandData = [
                'id' => $userCommand->getId(),
                'vehicle_id' => $userCommand->getVehicleId(),
                'imei' => $userCommand->getImei(),
                'type' => $userCommand->getType()->getDisplayName(),
                'command' => $userCommand->getCommand(),
                'parameters' => $userCommand->getParameters(),
                'status' => $userCommand->getStatus()->getDisplayName(),
                'sent_at' => $userCommand->getSentAt()?->format('Y-m-d H:i:s'),
                'confirmed_at' => $userCommand->getConfirmedAt()?->format('Y-m-d H:i:s'),
                'expires_at' => $userCommand->getExpiresAt()->format('Y-m-d H:i:s'),
                'response' => $userCommand->getResponse(),
                'error_message' => $userCommand->getErrorMessage(),
                'retry_count' => $userCommand->getRetryCount(),
                'created_at' => $userCommand->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $userCommand->getUpdatedAt()->format('Y-m-d H:i:s'),
                'status_class' => $userCommand->getStatus()->getCssClass()
            ];

            if ($request->expectsJson()) {
                return JsonResponse::success(['command' => $commandData]);
            }

            return Response::view('command/show', [
                'command' => $userCommand,
                'command_data' => $commandData
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to show GPS command', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load command');
            }
            return Response::view('error/500');
        }
    }

    public function create(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }

        if ($request->isPost()) {
            return $this->store($request);
        }

        try {
            $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $onlineVehicles = array_filter($vehicles, fn($vehicle) => $vehicle->isOnline());

            $csrfToken = $this->csrfManager->generateToken('create_command');

            return Response::view('command/create', [
                'csrf_token' => $csrfToken,
                'vehicles' => $onlineVehicles,
                'command_types' => CommandType::getAllOptions()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to show create command form', ['error' => $e->getMessage()]);
            return Response::view('error/500');
        }
    }

    public function store(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'create_command');

            $imei = $request->input('imei');
            $commandType = CommandType::from($request->input('type'));
            
            // Verify vehicle ownership
            $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $vehicle = null;
            foreach ($vehicles as $v) {
                if ($v->getImei() === $imei) {
                    $vehicle = $v;
                    break;
                }
            }

            if ($vehicle === null) {
                throw new ValidationException(['imei' => 'Veículo não encontrado ou sem permissão']);
            }

            $parameters = [];
            switch ($commandType) {
                case CommandType::SET_INTERVAL:
                    $parameters['interval'] = (int) $request->input('interval', 30);
                    $parameters['distance'] = (int) $request->input('distance', 100);
                    break;
                case CommandType::SET_SPEED_LIMIT:
                    $parameters['speed_limit'] = (int) $request->input('speed_limit', 80);
                    break;
                case CommandType::CUSTOM:
                    $parameters['command'] = $request->input('custom_command');
                    break;
            }

            $command = $this->commandService->sendCommand($imei, $commandType, $parameters);

            $this->logger->info('GPS command created successfully', [
                'command_id' => $command->getId(),
                'imei' => $imei,
                'type' => $commandType->value,
                'created_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'command' => [
                        'id' => $command->getId(),
                        'type' => $command->getType()->getDisplayName(),
                        'status' => $command->getStatus()->getDisplayName()
                    ]
                ], 'Command sent successfully', 201);
            }

            return Response::redirect('/commands/' . $command->getId());

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $onlineVehicles = array_filter($vehicles, fn($vehicle) => $vehicle->isOnline());

            return Response::view('command/create', [
                'errors' => $e->getErrors(),
                'old' => $request->only(['imei', 'type', 'interval', 'distance', 'speed_limit']),
                'csrf_token' => $this->csrfManager->generateToken('create_command'),
                'vehicles' => $onlineVehicles,
                'command_types' => CommandType::getAllOptions()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send GPS command', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to send command');
            }

            $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $onlineVehicles = array_filter($vehicles, fn($vehicle) => $vehicle->isOnline());

            return Response::view('command/create', [
                'error' => 'Erro ao enviar comando',
                'old' => $request->only(['imei', 'type', 'interval', 'distance', 'speed_limit']),
                'csrf_token' => $this->csrfManager->generateToken('create_command'),
                'vehicles' => $onlineVehicles,
                'command_types' => CommandType::getAllOptions()
            ]);
        }
    }

    public function retry(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'retry_command');

            // Verify command ownership
            $userVehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $userImeis = array_map(fn($vehicle) => $vehicle->getImei(), $userVehicles);
            
            $hasPermission = false;
            foreach ($userImeis as $imei) {
                $commands = $this->commandService->getCommandsByImei($imei);
                foreach ($commands as $cmd) {
                    if ($cmd->getId() === $id) {
                        $hasPermission = true;
                        break 2;
                    }
                }
            }

            if (!$hasPermission) {
                return JsonResponse::forbidden();
            }

            $command = $this->commandService->retryCommand($id);

            $this->logger->info('GPS command retried successfully', [
                'command_id' => $id,
                'retry_count' => $command->getRetryCount(),
                'retried_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'command' => [
                        'id' => $command->getId(),
                        'status' => $command->getStatus()->getDisplayName(),
                        'retry_count' => $command->getRetryCount()
                    ]
                ], 'Command retried successfully');
            }

            return Response::redirect('/commands/' . $id);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }
            return Response::redirect('/commands/' . $id . '?error=validation');
        } catch (\Exception $e) {
            $this->logger->error('Failed to retry GPS command', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to retry command');
            }
            return Response::redirect('/commands/' . $id . '?error=retry');
        }
    }

    public function pending(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $imei = $request->input('imei');
            
            if ($imei) {
                // Verify vehicle ownership
                $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
                $hasAccess = false;
                foreach ($vehicles as $vehicle) {
                    if ($vehicle->getImei() === $imei) {
                        $hasAccess = true;
                        break;
                    }
                }

                if (!$hasAccess) {
                    return JsonResponse::forbidden();
                }

                $pendingCommands = $this->commandService->getPendingCommands($imei);
            } else {
                // Get pending commands for all user vehicles
                $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
                $pendingCommands = [];
                foreach ($vehicles as $vehicle) {
                    $commands = $this->commandService->getPendingCommands($vehicle->getImei());
                    $pendingCommands = array_merge($pendingCommands, $commands);
                }
                usort($pendingCommands, fn($a, $b) => $a->getCreatedAt() <=> $b->getCreatedAt());
            }

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'commands' => array_map(fn($command) => [
                        'id' => $command->getId(),
                        'imei' => $command->getImei(),
                        'type' => $command->getType()->getDisplayName(),
                        'status' => $command->getStatus()->getDisplayName(),
                        'expires_at' => $command->getExpiresAt()->format('Y-m-d H:i:s'),
                        'retry_count' => $command->getRetryCount(),
                        'created_at' => $command->getCreatedAt()->format('Y-m-d H:i:s')
                    ], $pendingCommands),
                    'count' => count($pendingCommands)
                ]);
            }

            return Response::view('command/pending', [
                'commands' => $pendingCommands,
                'imei' => $imei,
                'count' => count($pendingCommands)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get pending commands', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load pending commands');
            }
            return Response::view('error/500');
        }
    }

    public function statistics(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $imei = $request->input('imei');
            $startDate = $request->input('start_date') ? new DateTimeImmutable($request->input('start_date')) : null;
            $endDate = $request->input('end_date') ? new DateTimeImmutable($request->input('end_date')) : null;

            if ($imei) {
                // Verify vehicle ownership
                $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
                $hasAccess = false;
                foreach ($vehicles as $vehicle) {
                    if ($vehicle->getImei() === $imei) {
                        $hasAccess = true;
                        break;
                    }
                }

                if (!$hasAccess) {
                    return JsonResponse::forbidden();
                }
            }

            $statistics = $this->commandService->getCommandStatistics($imei, $startDate, $endDate);

            if ($request->expectsJson()) {
                return JsonResponse::success(['statistics' => $statistics]);
            }

            return Response::view('command/statistics', [
                'statistics' => $statistics,
                'imei' => $imei,
                'start_date' => $startDate?->format('Y-m-d') ?? (new DateTimeImmutable('-30 days'))->format('Y-m-d'),
                'end_date' => $endDate?->format('Y-m-d') ?? (new DateTimeImmutable())->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get command statistics', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load statistics');
            }
            return Response::view('error/500');
        }
    }

    public function report(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $imei = $request->input('imei');
            
            if (!$imei) {
                if ($request->expectsJson()) {
                    return JsonResponse::error('IMEI is required for report generation', [], 400);
                }
                return Response::view('error/400');
            }

            // Verify vehicle ownership
            $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());
            $hasAccess = false;
            foreach ($vehicles as $vehicle) {
                if ($vehicle->getImei() === $imei) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                return JsonResponse::forbidden();
            }

            $startDate = new DateTimeImmutable($request->input('start_date', '-30 days'));
            $endDate = new DateTimeImmutable($request->input('end_date', 'now'));

            $report = $this->commandService->generateCommandReport($imei, $startDate, $endDate);

            if ($request->expectsJson()) {
                return JsonResponse::success(['report' => $report]);
            }

            return Response::view('command/report', [
                'report' => $report,
                'imei' => $imei,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate command report', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to generate report');
            }
            return Response::view('error/500');
        }
    }

    public function delete(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'delete_command');

            $this->commandService->deleteCommand($id);

            $this->logger->info('GPS command deleted successfully', [
                'command_id' => $id,
                'deleted_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([], 'Command deleted successfully');
            }

            return Response::redirect('/commands');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Command not found', [], 404);
            }
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete GPS command', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to delete command');
            }
            return Response::view('error/500');
        }
    }
}