<?php

declare(strict_types=1);

namespace FastrackGps\Vehicle\Controller;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Http\JsonResponse;
use FastrackGps\Core\Http\Request;
use FastrackGps\Core\Http\Response;
use FastrackGps\Security\CsrfTokenManager;
use FastrackGps\Vehicle\Service\VehicleService;
use FastrackGps\Auth\Service\AuthenticationService;
use Psr\Log\LoggerInterface;

final class VehicleController
{
    public function __construct(
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
            $vehicles = $this->vehicleService->getVehiclesByClientId($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'vehicles' => array_map(fn($vehicle) => [
                        'id' => $vehicle->getId(),
                        'imei' => $vehicle->getImei(),
                        'name' => $vehicle->getName(),
                        'identification' => $vehicle->getIdentification(),
                        'status' => $vehicle->getStatus()->value,
                        'is_online' => $vehicle->isOnline(),
                        'last_position' => $vehicle->getLastPosition()?->toArray(),
                        'formatted_chip' => $vehicle->getFormattedChipNumber()
                    ], $vehicles)
                ]);
            }

            return Response::view('vehicle/index', [
                'vehicles' => $vehicles,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to list vehicles', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load vehicles');
            }
            
            return Response::view('vehicle/index', [
                'error' => 'Erro ao carregar veículos',
                'vehicles' => []
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
            $vehicle = $this->vehicleService->getVehicleById($id);
            
            // Check if user owns this vehicle
            if (!$user->isAdmin() && $vehicle->getClientId() !== $user->getId()) {
                return JsonResponse::forbidden('Access denied to this vehicle');
            }

            $vehicleData = [
                'id' => $vehicle->getId(),
                'imei' => $vehicle->getImei(),
                'name' => $vehicle->getName(),
                'identification' => $vehicle->getIdentification(),
                'client_id' => $vehicle->getClientId(),
                'chip_number' => $vehicle->getFormattedChipNumber(),
                'carrier' => $vehicle->getCarrier()->getDisplayName(),
                'chip_number2' => $vehicle->getFormattedChipNumber2(),
                'carrier2' => $vehicle->getCarrier2()->getDisplayName(),
                'color' => $vehicle->getColor(),
                'is_active' => $vehicle->isActive(),
                'operating_mode' => $vehicle->getOperatingMode()->getDisplayName(),
                'status' => [
                    'code' => $vehicle->getStatus()->value,
                    'name' => $vehicle->getStatus()->getDisplayName(),
                    'color' => $vehicle->getStatus()->getColor(),
                    'icon' => $vehicle->getStatus()->getIcon()
                ],
                'is_online' => $vehicle->isOnline(),
                'last_position' => $vehicle->getLastPosition()?->toArray(),
                'last_position_time' => $vehicle->getLastPositionTime()?->format('c')
            ];

            if ($request->expectsJson()) {
                return JsonResponse::success(['vehicle' => $vehicleData]);
            }

            return Response::view('vehicle/show', [
                'vehicle' => $vehicle,
                'vehicle_data' => $vehicleData
            ]);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Vehicle not found', [], 404);
            }
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to show vehicle', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load vehicle');
            }
            return Response::view('error/500');
        }
    }

    public function create(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        if ($request->isPost()) {
            return $this->store($request);
        }

        $csrfToken = $this->csrfManager->generateToken('create_vehicle');

        return Response::view('vehicle/create', [
            'csrf_token' => $csrfToken,
            'carriers' => \FastrackGps\Vehicle\ValueObject\Carrier::getAllOptions()
        ]);
    }

    public function store(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'create_vehicle');

            $vehicleData = [
                'imei' => $request->input('imei'),
                'name' => $request->input('name'),
                'identification' => $request->input('identification'),
                'client_id' => (int) $request->input('client_id'),
                'chip_number' => $request->input('chip_number'),
                'carrier' => $request->input('carrier'),
                'chip_number2' => $request->input('chip_number2'),
                'carrier2' => $request->input('carrier2'),
                'color' => $request->input('color', 'FF0000')
            ];

            $vehicle = $this->vehicleService->createVehicle($vehicleData);

            $this->logger->info('Vehicle created successfully', [
                'vehicle_id' => $vehicle->getId(),
                'imei' => $vehicle->getImei(),
                'created_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'vehicle' => [
                        'id' => $vehicle->getId(),
                        'imei' => $vehicle->getImei(),
                        'name' => $vehicle->getName()
                    ]
                ], 'Vehicle created successfully', 201);
            }

            return Response::redirect('/vehicles/' . $vehicle->getId());

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            return Response::view('vehicle/create', [
                'errors' => $e->getErrors(),
                'old' => $request->only(['imei', 'name', 'identification', 'client_id']),
                'csrf_token' => $this->csrfManager->generateToken('create_vehicle'),
                'carriers' => \FastrackGps\Vehicle\ValueObject\Carrier::getAllOptions()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create vehicle', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to create vehicle');
            }

            return Response::view('vehicle/create', [
                'error' => 'Erro ao criar veículo',
                'old' => $request->only(['imei', 'name', 'identification', 'client_id']),
                'csrf_token' => $this->csrfManager->generateToken('create_vehicle'),
                'carriers' => \FastrackGps\Vehicle\ValueObject\Carrier::getAllOptions()
            ]);
        }
    }

    public function edit(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }

        try {
            $vehicle = $this->vehicleService->getVehicleById($id);
            
            // Check permissions
            if (!$user->isAdmin() && $vehicle->getClientId() !== $user->getId()) {
                return JsonResponse::forbidden();
            }

            $csrfToken = $this->csrfManager->generateToken('edit_vehicle');

            return Response::view('vehicle/edit', [
                'vehicle' => $vehicle,
                'csrf_token' => $csrfToken,
                'carriers' => \FastrackGps\Vehicle\ValueObject\Carrier::getAllOptions()
            ]);

        } catch (ValidationException $e) {
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to show edit form', ['id' => $id, 'error' => $e->getMessage()]);
            return Response::view('error/500');
        }
    }

    public function update(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'edit_vehicle');

            $vehicle = $this->vehicleService->getVehicleById($id);
            
            // Check permissions
            if (!$user->isAdmin() && $vehicle->getClientId() !== $user->getId()) {
                return JsonResponse::forbidden();
            }

            $updateData = [
                'name' => $request->input('name'),
                'identification' => $request->input('identification'),
                'chip_number' => $request->input('chip_number'),
                'carrier' => $request->input('carrier'),
                'chip_number2' => $request->input('chip_number2'),
                'carrier2' => $request->input('carrier2'),
                'color' => $request->input('color')
            ];

            $updatedVehicle = $this->vehicleService->updateVehicle($id, $updateData);

            $this->logger->info('Vehicle updated successfully', [
                'vehicle_id' => $id,
                'updated_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'vehicle' => [
                        'id' => $updatedVehicle->getId(),
                        'name' => $updatedVehicle->getName(),
                        'identification' => $updatedVehicle->getIdentification()
                    ]
                ], 'Vehicle updated successfully');
            }

            return Response::redirect('/vehicles/' . $id);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            $vehicle = $this->vehicleService->getVehicleById($id);
            return Response::view('vehicle/edit', [
                'vehicle' => $vehicle,
                'errors' => $e->getErrors(),
                'old' => $request->only(['name', 'identification', 'chip_number', 'carrier']),
                'csrf_token' => $this->csrfManager->generateToken('edit_vehicle'),
                'carriers' => \FastrackGps\Vehicle\ValueObject\Carrier::getAllOptions()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update vehicle', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to update vehicle');
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
            $this->csrfManager->requireValidToken($request->input('_token'), 'delete_vehicle');

            $vehicle = $this->vehicleService->getVehicleById($id);
            $this->vehicleService->deleteVehicle($id);

            $this->logger->info('Vehicle deleted successfully', [
                'vehicle_id' => $id,
                'imei' => $vehicle->getImei(),
                'deleted_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([], 'Vehicle deleted successfully');
            }

            return Response::redirect('/vehicles');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Vehicle not found', [], 404);
            }
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete vehicle', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to delete vehicle');
            }
            return Response::view('error/500');
        }
    }
}