<?php

declare(strict_types=1);

namespace FastrackGps\Alert\Controller;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Http\JsonResponse;
use FastrackGps\Core\Http\Request;
use FastrackGps\Core\Http\Response;
use FastrackGps\Security\CsrfTokenManager;
use FastrackGps\Alert\Service\AlertService;
use FastrackGps\Alert\ValueObject\AlertType;
use FastrackGps\Alert\ValueObject\AlertSeverity;
use FastrackGps\Auth\Service\AuthenticationService;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class AlertController
{
    public function __construct(
        private readonly AlertService $alertService,
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
            $alerts = $this->alertService->getAlertsByClient($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'alerts' => array_map(fn($alert) => [
                        'id' => $alert->getId(),
                        'vehicle_id' => $alert->getVehicleId(),
                        'type' => $alert->getType()->getDisplayName(),
                        'severity' => $alert->getSeverity()->getDisplayName(),
                        'title' => $alert->getTitle(),
                        'message' => $alert->getMessage(),
                        'is_acknowledged' => $alert->isAcknowledged(),
                        'acknowledged_at' => $alert->getAcknowledgedAt()?->format('Y-m-d H:i:s'),
                        'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                        'severity_color' => $alert->getSeverity()->getColor(),
                        'type_icon' => $alert->getType()->getIcon()
                    ], $alerts)
                ]);
            }

            return Response::view('alert/index', [
                'alerts' => $alerts,
                'user' => $user,
                'unacknowledged_count' => count(array_filter($alerts, fn($alert) => !$alert->isAcknowledged()))
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to list alerts', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load alerts');
            }
            
            return Response::view('alert/index', [
                'error' => 'Erro ao carregar alertas',
                'alerts' => []
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
            $alerts = $this->alertService->getAlertsByClient($user->getId());
            $alert = null;
            
            foreach ($alerts as $a) {
                if ($a->getId() === $id) {
                    $alert = $a;
                    break;
                }
            }

            if ($alert === null) {
                if ($request->expectsJson()) {
                    return JsonResponse::error('Alert not found', [], 404);
                }
                return Response::view('error/404');
            }

            $alertData = [
                'id' => $alert->getId(),
                'vehicle_id' => $alert->getVehicleId(),
                'type' => $alert->getType()->getDisplayName(),
                'severity' => $alert->getSeverity()->getDisplayName(),
                'title' => $alert->getTitle(),
                'message' => $alert->getMessage(),
                'data' => $alert->getData(),
                'is_acknowledged' => $alert->isAcknowledged(),
                'acknowledged_at' => $alert->getAcknowledgedAt()?->format('Y-m-d H:i:s'),
                'acknowledged_by' => $alert->getAcknowledgedBy(),
                'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $alert->getUpdatedAt()->format('Y-m-d H:i:s'),
                'severity_color' => $alert->getSeverity()->getColor(),
                'type_icon' => $alert->getType()->getIcon()
            ];

            if ($request->expectsJson()) {
                return JsonResponse::success(['alert' => $alertData]);
            }

            return Response::view('alert/show', [
                'alert' => $alert,
                'alert_data' => $alertData
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to show alert', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load alert');
            }
            return Response::view('error/500');
        }
    }

    public function acknowledge(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'acknowledge_alert');

            $alert = $this->alertService->acknowledgeAlert($id, $user->getId());

            $this->logger->info('Alert acknowledged successfully', [
                'alert_id' => $id,
                'acknowledged_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'alert' => [
                        'id' => $alert->getId(),
                        'is_acknowledged' => $alert->isAcknowledged(),
                        'acknowledged_at' => $alert->getAcknowledgedAt()?->format('Y-m-d H:i:s')
                    ]
                ], 'Alert acknowledged successfully');
            }

            return Response::redirect('/alerts/' . $id);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }
            return Response::redirect('/alerts/' . $id . '?error=validation');
        } catch (\Exception $e) {
            $this->logger->error('Failed to acknowledge alert', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to acknowledge alert');
            }
            return Response::redirect('/alerts/' . $id . '?error=processing');
        }
    }

    public function acknowledgeAll(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'acknowledge_all_alerts');

            $this->alertService->markAllAsAcknowledged($user->getId());

            $this->logger->info('All alerts acknowledged successfully', [
                'acknowledged_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([], 'All alerts acknowledged successfully');
            }

            return Response::redirect('/alerts');

        } catch (\Exception $e) {
            $this->logger->error('Failed to acknowledge all alerts', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to acknowledge all alerts');
            }
            return Response::redirect('/alerts?error=processing');
        }
    }

    public function unacknowledged(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $unacknowledgedAlerts = $this->alertService->getUnacknowledgedAlerts($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'alerts' => array_map(fn($alert) => [
                        'id' => $alert->getId(),
                        'vehicle_id' => $alert->getVehicleId(),
                        'type' => $alert->getType()->getDisplayName(),
                        'severity' => $alert->getSeverity()->getDisplayName(),
                        'title' => $alert->getTitle(),
                        'message' => $alert->getMessage(),
                        'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                        'severity_color' => $alert->getSeverity()->getColor(),
                        'type_icon' => $alert->getType()->getIcon()
                    ], $unacknowledgedAlerts),
                    'count' => count($unacknowledgedAlerts)
                ]);
            }

            return Response::view('alert/unacknowledged', [
                'alerts' => $unacknowledgedAlerts,
                'count' => count($unacknowledgedAlerts)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get unacknowledged alerts', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load unacknowledged alerts');
            }
            return Response::view('error/500');
        }
    }

    public function byVehicle(Request $request, int $vehicleId): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $alerts = $this->alertService->getAlertsByVehicle($vehicleId);
            
            // Filter alerts by client ownership
            $clientAlerts = $this->alertService->getAlertsByClient($user->getId());
            $clientAlertIds = array_map(fn($alert) => $alert->getId(), $clientAlerts);
            $vehicleAlerts = array_filter($alerts, fn($alert) => in_array($alert->getId(), $clientAlertIds));

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'alerts' => array_map(fn($alert) => [
                        'id' => $alert->getId(),
                        'type' => $alert->getType()->getDisplayName(),
                        'severity' => $alert->getSeverity()->getDisplayName(),
                        'title' => $alert->getTitle(),
                        'message' => $alert->getMessage(),
                        'is_acknowledged' => $alert->isAcknowledged(),
                        'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                        'severity_color' => $alert->getSeverity()->getColor(),
                        'type_icon' => $alert->getType()->getIcon()
                    ], $vehicleAlerts)
                ]);
            }

            return Response::view('alert/by_vehicle', [
                'alerts' => $vehicleAlerts,
                'vehicle_id' => $vehicleId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get alerts by vehicle', ['vehicle_id' => $vehicleId, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load vehicle alerts');
            }
            return Response::view('error/500');
        }
    }

    public function byType(Request $request, string $type): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $alertType = AlertType::from($type);
            $typeAlerts = $this->alertService->getAlertsByType($alertType);
            
            // Filter alerts by client ownership
            $clientAlerts = $this->alertService->getAlertsByClient($user->getId());
            $clientAlertIds = array_map(fn($alert) => $alert->getId(), $clientAlerts);
            $filteredAlerts = array_filter($typeAlerts, fn($alert) => in_array($alert->getId(), $clientAlertIds));

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'alerts' => array_map(fn($alert) => [
                        'id' => $alert->getId(),
                        'vehicle_id' => $alert->getVehicleId(),
                        'severity' => $alert->getSeverity()->getDisplayName(),
                        'title' => $alert->getTitle(),
                        'message' => $alert->getMessage(),
                        'is_acknowledged' => $alert->isAcknowledged(),
                        'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                        'severity_color' => $alert->getSeverity()->getColor()
                    ], $filteredAlerts)
                ]);
            }

            return Response::view('alert/by_type', [
                'alerts' => $filteredAlerts,
                'type' => $alertType->getDisplayName()
            ]);

        } catch (\ValueError $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Invalid alert type', [], 400);
            }
            return Response::view('error/400');
        } catch (\Exception $e) {
            $this->logger->error('Failed to get alerts by type', ['type' => $type, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load alerts by type');
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
            $startDate = $request->input('start_date') ? new DateTimeImmutable($request->input('start_date')) : null;
            $endDate = $request->input('end_date') ? new DateTimeImmutable($request->input('end_date')) : null;
            
            $statistics = $this->alertService->getAlertStatistics($user->getId(), $startDate, $endDate);

            if ($request->expectsJson()) {
                return JsonResponse::success(['statistics' => $statistics]);
            }

            return Response::view('alert/statistics', [
                'statistics' => $statistics,
                'start_date' => $startDate?->format('Y-m-d') ?? (new DateTimeImmutable('-30 days'))->format('Y-m-d'),
                'end_date' => $endDate?->format('Y-m-d') ?? (new DateTimeImmutable())->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get alert statistics', ['error' => $e->getMessage()]);
            
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
            $startDate = new DateTimeImmutable($request->input('start_date', '-30 days'));
            $endDate = new DateTimeImmutable($request->input('end_date', 'now'));

            $report = $this->alertService->generateAlertReport($user->getId(), $startDate, $endDate);

            if ($request->expectsJson()) {
                return JsonResponse::success(['report' => $report]);
            }

            return Response::view('alert/report', [
                'report' => $report,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate alert report', ['error' => $e->getMessage()]);
            
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
            $this->csrfManager->requireValidToken($request->input('_token'), 'delete_alert');

            $this->alertService->deleteAlert($id);

            $this->logger->info('Alert deleted successfully', [
                'alert_id' => $id,
                'deleted_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([], 'Alert deleted successfully');
            }

            return Response::redirect('/alerts');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Alert not found', [], 404);
            }
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete alert', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to delete alert');
            }
            return Response::view('error/500');
        }
    }

    public function realtime(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $lastAlertId = (int) $request->input('last_alert_id', 0);
            $alerts = $this->alertService->getAlertsByClient($user->getId());
            
            $newAlerts = array_filter($alerts, fn($alert) => $alert->getId() > $lastAlertId && !$alert->isAcknowledged());
            $newAlerts = array_slice($newAlerts, 0, 10); // Limit to 10 most recent

            return JsonResponse::success([
                'alerts' => array_map(fn($alert) => [
                    'id' => $alert->getId(),
                    'vehicle_id' => $alert->getVehicleId(),
                    'type' => $alert->getType()->getDisplayName(),
                    'severity' => $alert->getSeverity()->getDisplayName(),
                    'title' => $alert->getTitle(),
                    'message' => $alert->getMessage(),
                    'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                    'severity_color' => $alert->getSeverity()->getColor(),
                    'type_icon' => $alert->getType()->getIcon()
                ], $newAlerts),
                'count' => count($newAlerts),
                'last_alert_id' => !empty($alerts) ? max(array_map(fn($alert) => $alert->getId(), $alerts)) : $lastAlertId
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get realtime alerts', ['error' => $e->getMessage()]);
            return JsonResponse::error('Failed to load realtime alerts');
        }
    }
}