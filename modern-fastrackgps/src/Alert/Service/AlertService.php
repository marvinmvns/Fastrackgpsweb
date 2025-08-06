<?php

declare(strict_types=1);

namespace FastrackGps\Alert\Service;

use FastrackGps\Alert\Entity\Alert;
use FastrackGps\Alert\Repository\AlertRepositoryInterface;
use FastrackGps\Alert\ValueObject\AlertType;
use FastrackGps\Alert\ValueObject\AlertSeverity;
use FastrackGps\Vehicle\Entity\Vehicle;
use FastrackGps\Tracking\Entity\GpsPosition;
use FastrackGps\Core\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class AlertService
{
    public function __construct(
        private readonly AlertRepositoryInterface $alertRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createSpeedingAlert(Vehicle $vehicle, GpsPosition $position): Alert
    {
        $alert = Alert::create(
            vehicleId: $vehicle->getId(),
            type: AlertType::SPEEDING,
            severity: AlertSeverity::HIGH,
            title: 'Excesso de Velocidade',
            message: sprintf(
                'Veículo "%s" excedeu limite de velocidade: %.1f km/h',
                $vehicle->getName(),
                $position->getSpeed()
            ),
            data: [
                'speed' => $position->getSpeed(),
                'coordinates' => [
                    'latitude' => $position->getCoordinates()->latitude,
                    'longitude' => $position->getCoordinates()->longitude
                ],
                'timestamp' => $position->getTimestamp()->format('Y-m-d H:i:s'),
                'address' => $position->getAddress()
            ]
        );

        $this->alertRepository->save($alert);

        $this->logger->warning('Speeding alert created', [
            'vehicle_id' => $vehicle->getId(),
            'vehicle_name' => $vehicle->getName(),
            'speed' => $position->getSpeed(),
            'alert_id' => $alert->getId()
        ]);

        return $alert;
    }

    public function createGeofenceAlert(Vehicle $vehicle, GpsPosition $position, string $geofenceName, bool $entering): Alert
    {
        $type = $entering ? AlertType::GEOFENCE_ENTER : AlertType::GEOFENCE_EXIT;
        $action = $entering ? 'entrou' : 'saiu';

        $alert = Alert::create(
            vehicleId: $vehicle->getId(),
            type: $type,
            severity: AlertSeverity::MEDIUM,
            title: 'Alerta de Cerca Virtual',
            message: sprintf(
                'Veículo "%s" %s da cerca virtual "%s"',
                $vehicle->getName(),
                $action,
                $geofenceName
            ),
            data: [
                'geofence_name' => $geofenceName,
                'entering' => $entering,
                'coordinates' => [
                    'latitude' => $position->getCoordinates()->latitude,
                    'longitude' => $position->getCoordinates()->longitude
                ],
                'timestamp' => $position->getTimestamp()->format('Y-m-d H:i:s'),
                'address' => $position->getAddress()
            ]
        );

        $this->alertRepository->save($alert);

        $this->logger->info('Geofence alert created', [
            'vehicle_id' => $vehicle->getId(),
            'geofence' => $geofenceName,
            'entering' => $entering,
            'alert_id' => $alert->getId()
        ]);

        return $alert;
    }

    public function createPanicAlert(Vehicle $vehicle, GpsPosition $position): Alert
    {
        $alert = Alert::create(
            vehicleId: $vehicle->getId(),
            type: AlertType::PANIC_BUTTON,
            severity: AlertSeverity::CRITICAL,
            title: 'Botão de Pânico Acionado',
            message: sprintf(
                'Botão de pânico foi acionado no veículo "%s"',
                $vehicle->getName()
            ),
            data: [
                'coordinates' => [
                    'latitude' => $position->getCoordinates()->latitude,
                    'longitude' => $position->getCoordinates()->longitude
                ],
                'timestamp' => $position->getTimestamp()->format('Y-m-d H:i:s'),
                'address' => $position->getAddress(),
                'speed' => $position->getSpeed()
            ]
        );

        $this->alertRepository->save($alert);

        $this->logger->critical('Panic button alert created', [
            'vehicle_id' => $vehicle->getId(),
            'vehicle_name' => $vehicle->getName(),
            'coordinates' => [
                'lat' => $position->getCoordinates()->latitude,
                'lng' => $position->getCoordinates()->longitude
            ],
            'alert_id' => $alert->getId()
        ]);

        return $alert;
    }

    public function createLowBatteryAlert(Vehicle $vehicle, GpsPosition $position): Alert
    {
        $alert = Alert::create(
            vehicleId: $vehicle->getId(),
            type: AlertType::LOW_BATTERY,
            severity: AlertSeverity::MEDIUM,
            title: 'Bateria Baixa',
            message: sprintf(
                'Bateria baixa detectada no veículo "%s"',
                $vehicle->getName()
            ),
            data: [
                'battery_level' => $position->getBatteryLevel(),
                'coordinates' => [
                    'latitude' => $position->getCoordinates()->latitude,
                    'longitude' => $position->getCoordinates()->longitude
                ],
                'timestamp' => $position->getTimestamp()->format('Y-m-d H:i:s'),
                'address' => $position->getAddress()
            ]
        );

        $this->alertRepository->save($alert);

        $this->logger->warning('Low battery alert created', [
            'vehicle_id' => $vehicle->getId(),
            'battery_level' => $position->getBatteryLevel(),
            'alert_id' => $alert->getId()
        ]);

        return $alert;
    }

    public function createOfflineAlert(Vehicle $vehicle): Alert
    {
        $alert = Alert::create(
            vehicleId: $vehicle->getId(),
            type: AlertType::OFFLINE,
            severity: AlertSeverity::HIGH,
            title: 'Veículo Offline',
            message: sprintf(
                'Veículo "%s" está offline há mais de 30 minutos',
                $vehicle->getName()
            ),
            data: [
                'last_position_time' => $vehicle->getLastPositionTime()?->format('Y-m-d H:i:s'),
                'offline_since' => new DateTimeImmutable()
            ]
        );

        $this->alertRepository->save($alert);

        $this->logger->warning('Offline alert created', [
            'vehicle_id' => $vehicle->getId(),
            'vehicle_name' => $vehicle->getName(),
            'alert_id' => $alert->getId()
        ]);

        return $alert;
    }

    public function createMaintenanceAlert(Vehicle $vehicle, string $maintenanceType, array $details = []): Alert
    {
        $alert = Alert::create(
            vehicleId: $vehicle->getId(),
            type: AlertType::MAINTENANCE,
            severity: AlertSeverity::LOW,
            title: 'Alerta de Manutenção',
            message: sprintf(
                'Manutenção necessária no veículo "%s": %s',
                $vehicle->getName(),
                $maintenanceType
            ),
            data: array_merge([
                'maintenance_type' => $maintenanceType,
                'vehicle_mileage' => $details['mileage'] ?? null
            ], $details)
        );

        $this->alertRepository->save($alert);

        $this->logger->info('Maintenance alert created', [
            'vehicle_id' => $vehicle->getId(),
            'maintenance_type' => $maintenanceType,
            'alert_id' => $alert->getId()
        ]);

        return $alert;
    }

    public function acknowledgeAlert(int $alertId, int $userId): Alert
    {
        $alert = $this->alertRepository->findById($alertId);
        if ($alert === null) {
            throw new ValidationException(['alert' => 'Alerta não encontrado']);
        }

        if ($alert->isAcknowledged()) {
            throw new ValidationException(['alert' => 'Alerta já foi reconhecido']);
        }

        $alert = $alert->acknowledge($userId);
        $this->alertRepository->save($alert);

        $this->logger->info('Alert acknowledged', [
            'alert_id' => $alertId,
            'acknowledged_by' => $userId
        ]);

        return $alert;
    }

    public function getAlertsByVehicle(int $vehicleId): array
    {
        return $this->alertRepository->findByVehicleId($vehicleId);
    }

    public function getAlertsByClient(int $clientId): array
    {
        return $this->alertRepository->findByClientId($clientId);
    }

    public function getUnacknowledgedAlerts(int $clientId = null): array
    {
        if ($clientId !== null) {
            return $this->alertRepository->findUnacknowledgedByClientId($clientId);
        }

        return $this->alertRepository->findUnacknowledged();
    }

    public function getAlertsByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $vehicleId = null
    ): array {
        if ($vehicleId !== null) {
            return $this->alertRepository->findByVehicleIdAndDateRange($vehicleId, $startDate, $endDate);
        }

        return $this->alertRepository->findByDateRange($startDate, $endDate);
    }

    public function getAlertsByType(AlertType $type): array
    {
        return $this->alertRepository->findByType($type);
    }

    public function getAlertsBySeverity(AlertSeverity $severity): array
    {
        return $this->alertRepository->findBySeverity($severity);
    }

    public function deleteAlert(int $alertId): void
    {
        $alert = $this->alertRepository->findById($alertId);
        if ($alert === null) {
            throw new ValidationException(['alert' => 'Alerta não encontrado']);
        }

        $this->alertRepository->delete($alertId);

        $this->logger->info('Alert deleted', ['alert_id' => $alertId]);
    }

    public function markAllAsAcknowledged(int $clientId): void
    {
        $this->alertRepository->markAllAsAcknowledged($clientId);

        $this->logger->info('All alerts marked as acknowledged', ['client_id' => $clientId]);
    }

    public function getAlertStatistics(int $clientId, DateTimeImmutable $startDate = null, DateTimeImmutable $endDate = null): array
    {
        $startDate = $startDate ?? new DateTimeImmutable('-30 days');
        $endDate = $endDate ?? new DateTimeImmutable();

        $alerts = $this->alertRepository->findByClientId($clientId);
        $periodAlerts = array_filter($alerts, function (Alert $alert) use ($startDate, $endDate) {
            $alertDate = $alert->getCreatedAt();
            return $alertDate >= $startDate && $alertDate <= $endDate;
        });

        $statistics = [
            'total_alerts' => count($alerts),
            'period_alerts' => count($periodAlerts),
            'unacknowledged_count' => $this->alertRepository->countUnacknowledgedByClientId($clientId),
            'by_type' => [],
            'by_severity' => [],
            'by_day' => []
        ];

        foreach ($periodAlerts as $alert) {
            $type = $alert->getType()->value;
            $severity = $alert->getSeverity()->value;
            $day = $alert->getCreatedAt()->format('Y-m-d');

            $statistics['by_type'][$type] = ($statistics['by_type'][$type] ?? 0) + 1;
            $statistics['by_severity'][$severity] = ($statistics['by_severity'][$severity] ?? 0) + 1;
            $statistics['by_day'][$day] = ($statistics['by_day'][$day] ?? 0) + 1;
        }

        return $statistics;
    }

    public function generateAlertReport(
        int $clientId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        $alerts = $this->alertRepository->findByClientId($clientId);
        $periodAlerts = array_filter($alerts, function (Alert $alert) use ($startDate, $endDate) {
            $alertDate = $alert->getCreatedAt();
            return $alertDate >= $startDate && $alertDate <= $endDate;
        });

        $report = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_alerts' => count($periodAlerts),
                'acknowledged' => 0,
                'unacknowledged' => 0,
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ],
            'by_type' => [],
            'by_vehicle' => [],
            'alerts' => []
        ];

        foreach ($periodAlerts as $alert) {
            if ($alert->isAcknowledged()) {
                $report['summary']['acknowledged']++;
            } else {
                $report['summary']['unacknowledged']++;
            }

            $severity = $alert->getSeverity()->value;
            $report['summary'][$severity]++;

            $type = $alert->getType()->getDisplayName();
            if (!isset($report['by_type'][$type])) {
                $report['by_type'][$type] = 0;
            }
            $report['by_type'][$type]++;

            $vehicleId = $alert->getVehicleId();
            if (!isset($report['by_vehicle'][$vehicleId])) {
                $report['by_vehicle'][$vehicleId] = 0;
            }
            $report['by_vehicle'][$vehicleId]++;

            $report['alerts'][] = [
                'id' => $alert->getId(),
                'vehicle_id' => $alert->getVehicleId(),
                'type' => $alert->getType()->getDisplayName(),
                'severity' => $alert->getSeverity()->getDisplayName(),
                'title' => $alert->getTitle(),
                'message' => $alert->getMessage(),
                'is_acknowledged' => $alert->isAcknowledged(),
                'acknowledged_at' => $alert->getAcknowledgedAt()?->format('Y-m-d H:i:s'),
                'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s'),
                'data' => $alert->getData()
            ];
        }

        return $report;
    }
}