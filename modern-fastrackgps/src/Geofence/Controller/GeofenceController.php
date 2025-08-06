<?php

declare(strict_types=1);

namespace FastrackGps\Geofence\Controller;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Http\JsonResponse;
use FastrackGps\Core\Http\Request;
use FastrackGps\Core\Http\Response;
use FastrackGps\Security\CsrfTokenManager;
use FastrackGps\Geofence\Service\GeofenceService;
use FastrackGps\Geofence\ValueObject\GeofenceType;
use FastrackGps\Auth\Service\AuthenticationService;
use Psr\Log\LoggerInterface;

final class GeofenceController
{
    public function __construct(
        private readonly GeofenceService $geofenceService,
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
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'geofences' => array_map(fn($geofence) => [
                        'id' => $geofence->getId(),
                        'name' => $geofence->getName(),
                        'description' => $geofence->getDescription(),
                        'type' => $geofence->getType()->getDisplayName(),
                        'color' => $geofence->getColor(),
                        'is_active' => $geofence->isActive(),
                        'alert_on_enter' => $geofence->alertOnEnter(),
                        'alert_on_exit' => $geofence->alertOnExit(),
                        'coordinates' => array_map(fn($coord) => [
                            'latitude' => $coord->latitude,
                            'longitude' => $coord->longitude
                        ], $geofence->getCoordinates()),
                        'radius' => $geofence->getRadius(),
                        'created_at' => $geofence->getCreatedAt()->format('Y-m-d H:i:s')
                    ], $geofences)
                ]);
            }

            return Response::view('geofence/index', [
                'geofences' => $geofences,
                'user' => $user,
                'active_count' => count(array_filter($geofences, fn($g) => $g->isActive()))
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to list geofences', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load geofences');
            }
            
            return Response::view('geofence/index', [
                'error' => 'Erro ao carregar cercas virtuais',
                'geofences' => []
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
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = null;
            
            foreach ($geofences as $g) {
                if ($g->getId() === $id) {
                    $geofence = $g;
                    break;
                }
            }

            if ($geofence === null) {
                if ($request->expectsJson()) {
                    return JsonResponse::error('Geofence not found', [], 404);
                }
                return Response::view('error/404');
            }

            $geofenceData = [
                'id' => $geofence->getId(),
                'client_id' => $geofence->getClientId(),
                'name' => $geofence->getName(),
                'description' => $geofence->getDescription(),
                'type' => $geofence->getType()->getDisplayName(),
                'coordinates' => array_map(fn($coord) => [
                    'latitude' => $coord->latitude,
                    'longitude' => $coord->longitude
                ], $geofence->getCoordinates()),
                'radius' => $geofence->getRadius(),
                'color' => $geofence->getColor(),
                'is_active' => $geofence->isActive(),
                'alert_on_enter' => $geofence->alertOnEnter(),
                'alert_on_exit' => $geofence->alertOnExit(),
                'created_at' => $geofence->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $geofence->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            if ($request->expectsJson()) {
                return JsonResponse::success(['geofence' => $geofenceData]);
            }

            return Response::view('geofence/show', [
                'geofence' => $geofence,
                'geofence_data' => $geofenceData
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to show geofence', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load geofence');
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

        $csrfToken = $this->csrfManager->generateToken('create_geofence');

        return Response::view('geofence/create', [
            'csrf_token' => $csrfToken,
            'geofence_types' => GeofenceType::getAllOptions()
        ]);
    }

    public function store(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'create_geofence');

            $geofenceData = [
                'client_id' => $user->getId(),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'type' => $request->input('type'),
                'coordinates' => json_decode($request->input('coordinates'), true),
                'radius' => $request->input('radius') ? (float) $request->input('radius') : null,
                'color' => $request->input('color', '#FF0000'),
                'alert_on_enter' => (bool) $request->input('alert_on_enter', true),
                'alert_on_exit' => (bool) $request->input('alert_on_exit', true)
            ];

            $geofence = $this->geofenceService->createGeofence($geofenceData);

            $this->logger->info('Geofence created successfully', [
                'geofence_id' => $geofence->getId(),
                'name' => $geofence->getName(),
                'client_id' => $geofence->getClientId(),
                'created_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'geofence' => [
                        'id' => $geofence->getId(),
                        'name' => $geofence->getName(),
                        'type' => $geofence->getType()->getDisplayName()
                    ]
                ], 'Geofence created successfully', 201);
            }

            return Response::redirect('/geofences/' . $geofence->getId());

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            return Response::view('geofence/create', [
                'errors' => $e->getErrors(),
                'old' => $request->only(['name', 'description', 'type', 'color']),
                'csrf_token' => $this->csrfManager->generateToken('create_geofence'),
                'geofence_types' => GeofenceType::getAllOptions()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create geofence', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to create geofence');
            }

            return Response::view('geofence/create', [
                'error' => 'Erro ao criar cerca virtual',
                'old' => $request->only(['name', 'description', 'type', 'color']),
                'csrf_token' => $this->csrfManager->generateToken('create_geofence'),
                'geofence_types' => GeofenceType::getAllOptions()
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
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = null;
            
            foreach ($geofences as $g) {
                if ($g->getId() === $id) {
                    $geofence = $g;
                    break;
                }
            }

            if ($geofence === null) {
                return Response::view('error/404');
            }

            $csrfToken = $this->csrfManager->generateToken('edit_geofence');

            return Response::view('geofence/edit', [
                'geofence' => $geofence,
                'csrf_token' => $csrfToken,
                'geofence_types' => GeofenceType::getAllOptions()
            ]);

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
            $this->csrfManager->requireValidToken($request->input('_token'), 'edit_geofence');

            // Verify ownership
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = null;
            foreach ($geofences as $g) {
                if ($g->getId() === $id) {
                    $geofence = $g;
                    break;
                }
            }

            if ($geofence === null) {
                return JsonResponse::forbidden();
            }

            $updateData = [];
            if ($request->input('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->input('description') !== null) {
                $updateData['description'] = $request->input('description');
            }
            if ($request->input('coordinates')) {
                $updateData['coordinates'] = json_decode($request->input('coordinates'), true);
            }
            if ($request->input('radius')) {
                $updateData['radius'] = (float) $request->input('radius');
            }
            if ($request->input('color')) {
                $updateData['color'] = $request->input('color');
            }
            if ($request->input('alert_on_enter') !== null) {
                $updateData['alert_on_enter'] = (bool) $request->input('alert_on_enter');
            }
            if ($request->input('alert_on_exit') !== null) {
                $updateData['alert_on_exit'] = (bool) $request->input('alert_on_exit');
            }

            $updatedGeofence = $this->geofenceService->updateGeofence($id, $updateData);

            $this->logger->info('Geofence updated successfully', [
                'geofence_id' => $id,
                'updated_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'geofence' => [
                        'id' => $updatedGeofence->getId(),
                        'name' => $updatedGeofence->getName(),
                        'description' => $updatedGeofence->getDescription()
                    ]
                ], 'Geofence updated successfully');
            }

            return Response::redirect('/geofences/' . $id);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            $geofence = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = array_filter($geofence, fn($g) => $g->getId() === $id)[0] ?? null;
            
            return Response::view('geofence/edit', [
                'geofence' => $geofence,
                'errors' => $e->getErrors(),
                'old' => $request->only(['name', 'description', 'color']),
                'csrf_token' => $this->csrfManager->generateToken('edit_geofence'),
                'geofence_types' => GeofenceType::getAllOptions()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to update geofence', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to update geofence');
            }

            return Response::view('error/500');
        }
    }

    public function activate(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'activate_geofence');

            // Verify ownership
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = array_filter($geofences, fn($g) => $g->getId() === $id)[0] ?? null;

            if ($geofence === null) {
                return JsonResponse::forbidden();
            }

            $updatedGeofence = $this->geofenceService->activateGeofence($id);

            $this->logger->info('Geofence activated successfully', [
                'geofence_id' => $id,
                'activated_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'geofence' => [
                        'id' => $updatedGeofence->getId(),
                        'is_active' => $updatedGeofence->isActive()
                    ]
                ], 'Geofence activated successfully');
            }

            return Response::redirect('/geofences/' . $id);

        } catch (\Exception $e) {
            $this->logger->error('Failed to activate geofence', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to activate geofence');
            }
            return Response::redirect('/geofences/' . $id . '?error=activation');
        }
    }

    public function deactivate(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'deactivate_geofence');

            // Verify ownership
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = array_filter($geofences, fn($g) => $g->getId() === $id)[0] ?? null;

            if ($geofence === null) {
                return JsonResponse::forbidden();
            }

            $updatedGeofence = $this->geofenceService->deactivateGeofence($id);

            $this->logger->info('Geofence deactivated successfully', [
                'geofence_id' => $id,
                'deactivated_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'geofence' => [
                        'id' => $updatedGeofence->getId(),
                        'is_active' => $updatedGeofence->isActive()
                    ]
                ], 'Geofence deactivated successfully');
            }

            return Response::redirect('/geofences/' . $id);

        } catch (\Exception $e) {
            $this->logger->error('Failed to deactivate geofence', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to deactivate geofence');
            }
            return Response::redirect('/geofences/' . $id . '?error=deactivation');
        }
    }

    public function statistics(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $statistics = $this->geofenceService->getGeofenceStatistics($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success(['statistics' => $statistics]);
            }

            return Response::view('geofence/statistics', [
                'statistics' => $statistics
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get geofence statistics', ['error' => $e->getMessage()]);
            
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
            $report = $this->geofenceService->generateGeofenceReport($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success(['report' => $report]);
            }

            return Response::view('geofence/report', [
                'report' => $report
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate geofence report', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to generate report');
            }
            return Response::view('error/500');
        }
    }

    public function delete(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'delete_geofence');

            // Verify ownership
            $geofences = $this->geofenceService->getGeofencesByClient($user->getId());
            $geofence = array_filter($geofences, fn($g) => $g->getId() === $id)[0] ?? null;

            if ($geofence === null) {
                return JsonResponse::forbidden();
            }

            $this->geofenceService->deleteGeofence($id);

            $this->logger->info('Geofence deleted successfully', [
                'geofence_id' => $id,
                'deleted_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([], 'Geofence deleted successfully');
            }

            return Response::redirect('/geofences');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Geofence not found', [], 404);
            }
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete geofence', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to delete geofence');
            }
            return Response::view('error/500');
        }
    }
}