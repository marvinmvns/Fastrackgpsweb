<?php

declare(strict_types=1);

namespace FastrackGps\Tracking\Service;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\ValueObject\Coordinates;
use FastrackGps\Tracking\Entity\GpsPosition;
use FastrackGps\Tracking\Repository\GpsPositionRepositoryInterface;
use FastrackGps\Vehicle\Repository\VehicleRepositoryInterface;
use FastrackGps\Alert\Service\AlertService;
use FastrackGps\Geofence\Service\GeofenceService;
use FastrackGps\Vehicle\Entity\Vehicle;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class TrackingService
{
    public function __construct(
        private readonly GpsPositionRepositoryInterface $positionRepository,
        private readonly VehicleRepositoryInterface $vehicleRepository,
        private readonly AlertService $alertService,
        private readonly GeofenceService $geofenceService,
        private readonly GeocodingServiceInterface $geocodingService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function processGpsData(string $rawData): void
    {
        try {
            $parsedData = $this->parseGpsData($rawData);
            
            if (!$this->isValidGpsData($parsedData)) {
                throw new ValidationException(['gps_data' => 'Invalid GPS data format']);
            }

            $vehicle = $this->vehicleRepository->findByImei($parsedData['imei']);
            if ($vehicle === null) {
                $this->logger->warning('Received GPS data for unknown vehicle', ['imei' => $parsedData['imei']]);
                return;
            }

            // Create GPS position
            $position = GpsPosition::fromGprmcData($parsedData);

            // Geocode address if not present
            if ($position->getAddress() === null) {
                $address = $this->geocodingService->reverseGeocode($position->getCoordinates());
                $position = $position->withAddress($address ?? '');
            }

            // Save position
            $this->positionRepository->save($position);

            // Update vehicle position and status
            $vehicle = $vehicle->updatePosition($position->getCoordinates(), $position->getTimestamp());
            $this->vehicleRepository->save($vehicle);

            // Check for alerts
            $this->checkAlerts($vehicle, $position);

            // Check geofences
            $this->geofenceService->checkViolations($vehicle, $position);

            $this->logger->info('GPS data processed successfully', [
                'imei' => $parsedData['imei'],
                'latitude' => $position->getCoordinates()->latitude,
                'longitude' => $position->getCoordinates()->longitude,
                'speed' => $position->getSpeed()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to process GPS data', [
                'raw_data' => $rawData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getVehicleHistory(
        string $imei,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        int $limit = 1000
    ): array {
        $vehicle = $this->vehicleRepository->findByImei($imei);
        if ($vehicle === null) {
            throw new ValidationException(['imei' => 'Vehicle not found']);
        }

        return $this->positionRepository->findByImeiAndDateRange($imei, $startDate, $endDate, $limit);
    }

    public function getVehicleCurrentPosition(string $imei): ?GpsPosition
    {
        return $this->positionRepository->findLatestByImei($imei);
    }

    public function getVehicleRoute(
        string $imei,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        $positions = $this->positionRepository->findByImeiAndDateRange($imei, $startDate, $endDate);
        
        return $this->buildRouteFromPositions($positions);
    }

    public function calculateDistance(string $imei, DateTimeImmutable $date): float
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);
        
        $positions = $this->positionRepository->findByImeiAndDateRange($imei, $startOfDay, $endOfDay);
        
        if (count($positions) < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;
        for ($i = 1; $i < count($positions); $i++) {
            $distance = $positions[$i - 1]->getCoordinates()->distanceTo($positions[$i]->getCoordinates());
            $totalDistance += $distance;
        }

        return $totalDistance / 1000; // Convert to kilometers
    }

    public function generateKmlRoute(
        string $imei,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): string {
        $positions = $this->positionRepository->findByImeiAndDateRange($imei, $startDate, $endDate);
        
        return $this->buildKmlFromPositions($positions);
    }

    public function parseGpsData(string $rawData): array
    {
        // Parse different GPS protocols
        if (str_contains($rawData, 'GPRMC')) {
            return $this->parseGprmcProtocol($rawData);
        }
        
        if (str_contains($rawData, 'GT')) {
            return $this->parseGt06Protocol($rawData);
        }

        throw new ValidationException(['gps_data' => 'Unsupported GPS protocol']);
    }

    public function isValidGpsData(array $data): bool
    {
        $required = ['imei', 'latitude', 'longitude', 'date'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        // Validate coordinates
        if ($data['latitude'] < -90 || $data['latitude'] > 90) {
            return false;
        }

        if ($data['longitude'] < -180 || $data['longitude'] > 180) {
            return false;
        }

        return true;
    }

    private function checkAlerts(Vehicle $vehicle, GpsPosition $position): void
    {
        // Speed alert
        if ($position->getSpeed() > 80) {
            $this->alertService->createSpeedingAlert($vehicle, $position);
        }

        // Low battery alert
        if ($position->hasLowBattery()) {
            $this->alertService->createLowBatteryAlert($vehicle, $position);
        }

        // Panic button alert
        $extendedInfo = $position->getExtendedInfo();
        if (isset($extendedInfo['panic']) && $extendedInfo['panic']) {
            $this->alertService->createPanicAlert($vehicle, $position);
        }
    }

    private function parseGprmcProtocol(string $data): array
    {
        // Example: $GPRMC,123519,A,4807.038,N,01131.000,E,022.4,084.4,230394,003.1,W*6A
        $parts = explode(',', $data);
        
        if (count($parts) < 12) {
            throw new ValidationException(['gps_data' => 'Invalid GPRMC format']);
        }

        return [
            'imei' => $this->extractImeiFromData($data),
            'latitude' => $this->convertCoordinate($parts[3], $parts[4]),
            'longitude' => $this->convertCoordinate($parts[5], $parts[6]),
            'speed' => (float) $parts[7],
            'course' => (float) $parts[8],
            'date' => $this->convertGpsDate($parts[9], $parts[1]),
            'gpsSignalIndicator' => $parts[2]
        ];
    }

    private function parseGt06Protocol(string $data): array
    {
        // Implementation for GT06 protocol
        // This would parse the specific format used by GT06 devices
        throw new ValidationException(['gps_data' => 'GT06 protocol parser not implemented']);
    }

    private function convertCoordinate(string $coordinate, string $direction): float
    {
        $degrees = (int) ($coordinate / 100);
        $minutes = $coordinate - ($degrees * 100);
        $decimal = $degrees + ($minutes / 60);
        
        if ($direction === 'S' || $direction === 'W') {
            $decimal = -$decimal;
        }
        
        return $decimal;
    }

    private function convertGpsDate(string $date, string $time): string
    {
        // Convert DDMMYY and HHMMSS to proper datetime
        $day = substr($date, 0, 2);
        $month = substr($date, 2, 2);
        $year = '20' . substr($date, 4, 2);
        
        $hour = substr($time, 0, 2);
        $minute = substr($time, 2, 2);
        $second = substr($time, 4, 2);
        
        return "{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}";
    }

    private function extractImeiFromData(string $data): string
    {
        // Extract IMEI from GPS data - implementation depends on protocol
        // This is a simplified version
        preg_match('/(\d{15})/', $data, $matches);
        return $matches[1] ?? '';
    }

    private function buildRouteFromPositions(array $positions): array
    {
        $route = [
            'coordinates' => [],
            'total_distance' => 0.0,
            'total_time' => 0,
            'max_speed' => 0.0,
            'avg_speed' => 0.0
        ];

        if (empty($positions)) {
            return $route;
        }

        $totalDistance = 0.0;
        $totalSpeed = 0.0;
        $maxSpeed = 0.0;

        foreach ($positions as $i => $position) {
            $route['coordinates'][] = [
                'latitude' => $position->getCoordinates()->latitude,
                'longitude' => $position->getCoordinates()->longitude,
                'timestamp' => $position->getTimestamp()->format('c'),
                'speed' => $position->getSpeed()
            ];

            if ($i > 0) {
                $distance = $positions[$i - 1]->getCoordinates()->distanceTo($position->getCoordinates());
                $totalDistance += $distance;
            }

            $speed = $position->getSpeed();
            $totalSpeed += $speed;
            $maxSpeed = max($maxSpeed, $speed);
        }

        $route['total_distance'] = $totalDistance / 1000; // km
        $route['max_speed'] = $maxSpeed;
        $route['avg_speed'] = $totalSpeed / count($positions);
        
        if (count($positions) > 1) {
            $startTime = $positions[0]->getTimestamp();
            $endTime = end($positions)->getTimestamp();
            $route['total_time'] = $endTime->getTimestamp() - $startTime->getTimestamp();
        }

        return $route;
    }

    private function buildKmlFromPositions(array $positions): string
    {
        $kml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $kml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $kml .= '<Document>' . "\n";
        $kml .= '<Placemark>' . "\n";
        $kml .= '<LineString>' . "\n";
        $kml .= '<coordinates>' . "\n";

        foreach ($positions as $position) {
            $kml .= $position->getCoordinates()->longitude . ',' . 
                   $position->getCoordinates()->latitude . ',0' . "\n";
        }

        $kml .= '</coordinates>' . "\n";
        $kml .= '</LineString>' . "\n";
        $kml .= '</Placemark>' . "\n";
        $kml .= '</Document>' . "\n";
        $kml .= '</kml>' . "\n";

        return $kml;
    }
}