<?php

declare(strict_types=1);

namespace FastrackGps\Geofence\Service;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Exception\BusinessException;
use FastrackGps\Core\ValueObject\Coordinates;
use FastrackGps\Geofence\Entity\Geofence;
use FastrackGps\Geofence\Repository\GeofenceRepositoryInterface;
use FastrackGps\Geofence\ValueObject\GeofenceType;
use FastrackGps\Vehicle\Entity\Vehicle;
use FastrackGps\Tracking\Entity\GpsPosition;
use FastrackGps\Alert\Service\AlertService;
use Psr\Log\LoggerInterface;

final class GeofenceService
{
    public function __construct(
        private readonly GeofenceRepositoryInterface $geofenceRepository,
        private readonly AlertService $alertService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createGeofence(array $data): Geofence
    {
        $this->validateGeofenceData($data);

        $existingGeofence = $this->geofenceRepository->findByName($data['name'], $data['client_id']);
        if ($existingGeofence !== null) {
            throw new ValidationException(['name' => 'Já existe uma cerca virtual com este nome']);
        }

        $coordinates = $this->parseCoordinates($data['coordinates']);

        $geofence = Geofence::create(
            clientId: $data['client_id'],
            name: $data['name'],
            description: $data['description'] ?? '',
            type: GeofenceType::from($data['type']),
            coordinates: $coordinates,
            radius: $data['radius'] ?? null,
            color: $data['color'] ?? '#FF0000',
            alertOnEnter: $data['alert_on_enter'] ?? true,
            alertOnExit: $data['alert_on_exit'] ?? true
        );

        $this->geofenceRepository->save($geofence);

        $this->logger->info('Geofence created successfully', [
            'geofence_id' => $geofence->getId(),
            'name' => $geofence->getName(),
            'client_id' => $geofence->getClientId(),
            'type' => $geofence->getType()->value
        ]);

        return $geofence;
    }

    public function updateGeofence(int $geofenceId, array $data): Geofence
    {
        $geofence = $this->geofenceRepository->findById($geofenceId);
        if ($geofence === null) {
            throw new ValidationException(['geofence' => 'Cerca virtual não encontrada']);
        }

        $this->validateUpdateData($data);

        if (isset($data['name']) && $data['name'] !== $geofence->getName()) {
            $existingGeofence = $this->geofenceRepository->findByName($data['name'], $geofence->getClientId());
            if ($existingGeofence !== null && $existingGeofence->getId() !== $geofenceId) {
                throw new ValidationException(['name' => 'Já existe uma cerca virtual com este nome']);
            }
        }

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['coordinates'])) {
            $updateData['coordinates'] = $this->parseCoordinates($data['coordinates']);
        }
        if (isset($data['radius'])) {
            $updateData['radius'] = (float) $data['radius'];
        }
        if (isset($data['color'])) {
            $updateData['color'] = $data['color'];
        }
        if (isset($data['alert_on_enter'])) {
            $updateData['alert_on_enter'] = (bool) $data['alert_on_enter'];
        }
        if (isset($data['alert_on_exit'])) {
            $updateData['alert_on_exit'] = (bool) $data['alert_on_exit'];
        }

        $geofence = $geofence->update($updateData);
        $this->geofenceRepository->save($geofence);

        $this->logger->info('Geofence updated', ['geofence_id' => $geofenceId]);

        return $geofence;
    }

    public function activateGeofence(int $geofenceId): Geofence
    {
        $geofence = $this->geofenceRepository->findById($geofenceId);
        if ($geofence === null) {
            throw new ValidationException(['geofence' => 'Cerca virtual não encontrada']);
        }

        if ($geofence->isActive()) {
            throw new BusinessException('Cerca virtual já está ativa');
        }

        $geofence = $geofence->activate();
        $this->geofenceRepository->save($geofence);

        $this->logger->info('Geofence activated', ['geofence_id' => $geofenceId]);

        return $geofence;
    }

    public function deactivateGeofence(int $geofenceId): Geofence
    {
        $geofence = $this->geofenceRepository->findById($geofenceId);
        if ($geofence === null) {
            throw new ValidationException(['geofence' => 'Cerca virtual não encontrada']);
        }

        if (!$geofence->isActive()) {
            throw new BusinessException('Cerca virtual já está inativa');
        }

        $geofence = $geofence->deactivate();
        $this->geofenceRepository->save($geofence);

        $this->logger->info('Geofence deactivated', ['geofence_id' => $geofenceId]);

        return $geofence;
    }

    public function deleteGeofence(int $geofenceId): void
    {
        $geofence = $this->geofenceRepository->findById($geofenceId);
        if ($geofence === null) {
            throw new ValidationException(['geofence' => 'Cerca virtual não encontrada']);
        }

        $this->geofenceRepository->delete($geofenceId);

        $this->logger->info('Geofence deleted', ['geofence_id' => $geofenceId]);
    }

    public function getGeofencesByClient(int $clientId): array
    {
        return $this->geofenceRepository->findByClientId($clientId);
    }

    public function getActiveGeofencesByClient(int $clientId): array
    {
        return $this->geofenceRepository->findActiveByClientId($clientId);
    }

    public function getGeofencesContainingPoint(Coordinates $coordinates, int $clientId = null): array
    {
        return $this->geofenceRepository->findContainingPoint($coordinates, $clientId);
    }

    public function checkViolations(Vehicle $vehicle, GpsPosition $position): void
    {
        $clientId = $vehicle->getClientId();
        $coordinates = $position->getCoordinates();
        
        $activeGeofences = $this->geofenceRepository->findActiveByClientId($clientId);
        $containingGeofences = [];

        foreach ($activeGeofences as $geofence) {
            if ($geofence->containsPoint($coordinates)) {
                $containingGeofences[] = $geofence;
            }
        }

        $previousPosition = $this->getPreviousPosition($vehicle);
        $previousContainingGeofences = [];

        if ($previousPosition !== null) {
            foreach ($activeGeofences as $geofence) {
                if ($geofence->containsPoint($previousPosition->getCoordinates())) {
                    $previousContainingGeofences[] = $geofence;
                }
            }
        }

        $this->detectGeofenceEvents($vehicle, $position, $containingGeofences, $previousContainingGeofences);
    }

    public function getGeofenceStatistics(int $clientId): array
    {
        $geofences = $this->geofenceRepository->findByClientId($clientId);

        $statistics = [
            'total_geofences' => count($geofences),
            'active_geofences' => 0,
            'by_type' => [],
            'with_enter_alert' => 0,
            'with_exit_alert' => 0
        ];

        foreach ($geofences as $geofence) {
            if ($geofence->isActive()) {
                $statistics['active_geofences']++;
            }

            $type = $geofence->getType()->value;
            $statistics['by_type'][$type] = ($statistics['by_type'][$type] ?? 0) + 1;

            if ($geofence->alertOnEnter()) {
                $statistics['with_enter_alert']++;
            }

            if ($geofence->alertOnExit()) {
                $statistics['with_exit_alert']++;
            }
        }

        return $statistics;
    }

    public function generateGeofenceReport(int $clientId): array
    {
        $geofences = $this->geofenceRepository->findByClientId($clientId);

        $report = [
            'client_id' => $clientId,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => [
                'total' => count($geofences),
                'active' => 0,
                'inactive' => 0
            ],
            'geofences' => []
        ];

        foreach ($geofences as $geofence) {
            if ($geofence->isActive()) {
                $report['summary']['active']++;
            } else {
                $report['summary']['inactive']++;
            }

            $report['geofences'][] = [
                'id' => $geofence->getId(),
                'name' => $geofence->getName(),
                'description' => $geofence->getDescription(),
                'type' => $geofence->getType()->getDisplayName(),
                'is_active' => $geofence->isActive(),
                'alert_on_enter' => $geofence->alertOnEnter(),
                'alert_on_exit' => $geofence->alertOnExit(),
                'coordinates_count' => count($geofence->getCoordinates()),
                'radius' => $geofence->getRadius(),
                'created_at' => $geofence->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $report;
    }

    private function validateGeofenceData(array $data): void
    {
        $errors = [];

        if (empty($data['client_id']) || !is_numeric($data['client_id'])) {
            $errors['client_id'] = 'ID do cliente é obrigatório';
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Nome é obrigatório';
        }

        if (empty($data['type'])) {
            $errors['type'] = 'Tipo é obrigatório';
        } else {
            try {
                GeofenceType::from($data['type']);
            } catch (\ValueError $e) {
                $errors['type'] = 'Tipo inválido';
            }
        }

        if (empty($data['coordinates']) || !is_array($data['coordinates'])) {
            $errors['coordinates'] = 'Coordenadas são obrigatórias';
        } else {
            $this->validateCoordinates($data['coordinates'], $errors);
        }

        if (isset($data['radius']) && (!is_numeric($data['radius']) || (float) $data['radius'] <= 0)) {
            $errors['radius'] = 'Raio deve ser maior que zero';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private function validateUpdateData(array $data): void
    {
        $errors = [];

        if (isset($data['name']) && empty($data['name'])) {
            $errors['name'] = 'Nome não pode ser vazio';
        }

        if (isset($data['coordinates']) && (!is_array($data['coordinates']) || empty($data['coordinates']))) {
            $errors['coordinates'] = 'Coordenadas inválidas';
        } elseif (isset($data['coordinates'])) {
            $this->validateCoordinates($data['coordinates'], $errors);
        }

        if (isset($data['radius']) && (!is_numeric($data['radius']) || (float) $data['radius'] <= 0)) {
            $errors['radius'] = 'Raio deve ser maior que zero';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private function validateCoordinates(array $coordinates, array &$errors): void
    {
        if (empty($coordinates)) {
            $errors['coordinates'] = 'Pelo menos uma coordenada é necessária';
            return;
        }

        foreach ($coordinates as $index => $coordinate) {
            if (!isset($coordinate['latitude']) || !isset($coordinate['longitude'])) {
                $errors['coordinates'] = "Coordenada {$index} deve ter latitude e longitude";
                return;
            }

            $lat = (float) $coordinate['latitude'];
            $lng = (float) $coordinate['longitude'];

            if ($lat < -90 || $lat > 90) {
                $errors['coordinates'] = "Latitude da coordenada {$index} inválida";
                return;
            }

            if ($lng < -180 || $lng > 180) {
                $errors['coordinates'] = "Longitude da coordenada {$index} inválida";
                return;
            }
        }
    }

    private function parseCoordinates(array $coordinatesData): array
    {
        $coordinates = [];
        foreach ($coordinatesData as $coordinate) {
            $coordinates[] = new Coordinates(
                (float) $coordinate['latitude'],
                (float) $coordinate['longitude']
            );
        }
        return $coordinates;
    }

    private function detectGeofenceEvents(
        Vehicle $vehicle,
        GpsPosition $position,
        array $currentGeofences,
        array $previousGeofences
    ): void {
        $currentIds = array_map(fn(Geofence $g) => $g->getId(), $currentGeofences);
        $previousIds = array_map(fn(Geofence $g) => $g->getId(), $previousGeofences);

        $enteredIds = array_diff($currentIds, $previousIds);
        $exitedIds = array_diff($previousIds, $currentIds);

        foreach ($enteredIds as $geofenceId) {
            $geofence = $this->findGeofenceById($currentGeofences, $geofenceId);
            if ($geofence && $geofence->alertOnEnter()) {
                $this->alertService->createGeofenceAlert($vehicle, $position, $geofence->getName(), true);
            }
        }

        foreach ($exitedIds as $geofenceId) {
            $geofence = $this->findGeofenceById($previousGeofences, $geofenceId);
            if ($geofence && $geofence->alertOnExit()) {
                $this->alertService->createGeofenceAlert($vehicle, $position, $geofence->getName(), false);
            }
        }
    }

    private function findGeofenceById(array $geofences, int $id): ?Geofence
    {
        foreach ($geofences as $geofence) {
            if ($geofence->getId() === $id) {
                return $geofence;
            }
        }
        return null;
    }

    private function getPreviousPosition(Vehicle $vehicle): ?GpsPosition
    {
        return null;
    }
}