<?php

declare(strict_types=1);

/**
 * FastrackGPS GPS Data Processor
 * Processes incoming GPS data and handles real-time updates
 */

use FastrackGps\Core\Config\EnvironmentConfiguration;
use FastrackGps\Core\Database\MySqlConnection;
use FastrackGps\Core\Logger\LoggerFactory;
use FastrackGps\Tracking\Repository\MySqlGpsPositionRepository;
use FastrackGps\Vehicle\Repository\MySqlVehicleRepository;
use FastrackGps\Alert\Repository\MySqlAlertRepository;
use FastrackGps\Tracking\Service\TrackingService;
use FastrackGps\Alert\Service\AlertService;
use Monolog\Logger;

require_once __DIR__ . '/../bootstrap.php';

final class GpsProcessor
{
    private Logger $logger;
    private array $config;
    private bool $running = false;
    private TrackingService $trackingService;
    private AlertService $alertService;
    private $socket;

    public function __construct(
        array $config,
        Logger $logger,
        TrackingService $trackingService,
        AlertService $alertService
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->trackingService = $trackingService;
        $this->alertService = $alertService;
        
        // Handle shutdown signals
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }

    public function start(): void
    {
        $host = $this->config['gps']['host'] ?? '0.0.0.0';
        $port = $this->config['gps']['port'] ?? 8090;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            throw new RuntimeException('Failed to create GPS socket: ' . socket_strerror(socket_last_error()));
        }

        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        if (!socket_bind($this->socket, $host, $port)) {
            throw new RuntimeException('Failed to bind GPS socket: ' . socket_strerror(socket_last_error($this->socket)));
        }

        if (!socket_listen($this->socket, 20)) {
            throw new RuntimeException('Failed to listen on GPS socket: ' . socket_strerror(socket_last_error($this->socket)));
        }

        $this->logger->info("GPS processor started on {$host}:{$port}");
        $this->running = true;

        while ($this->running) {
            pcntl_signal_dispatch();
            
            $read = [$this->socket];
            $write = null;
            $except = null;

            if (socket_select($read, $write, $except, 1) < 1) {
                continue;
            }

            if (in_array($this->socket, $read)) {
                $clientSocket = socket_accept($this->socket);
                if ($clientSocket !== false) {
                    $this->handleGpsConnection($clientSocket);
                }
            }
        }

        $this->cleanup();
    }

    private function handleGpsConnection($socket): void
    {
        $clientAddress = '';
        socket_getpeername($socket, $clientAddress);
        $this->logger->info("GPS device connected from: {$clientAddress}");

        $buffer = '';
        $lastActivity = time();
        $timeout = $this->config['gps']['timeout'] ?? 300;

        while ($this->running) {
            $data = socket_read($socket, 1024);
            
            if ($data === false || $data === '') {
                break;
            }

            $buffer .= $data;
            $lastActivity = time();

            // Process complete messages
            while (($pos = strpos($buffer, "\n")) !== false) {
                $message = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                
                if (!empty(trim($message))) {
                    $this->processGpsMessage(trim($message), $clientAddress);
                }
            }

            // Check for timeout
            if ((time() - $lastActivity) > $timeout) {
                $this->logger->warning("GPS connection timeout for {$clientAddress}");
                break;
            }
        }

        socket_close($socket);
        $this->logger->info("GPS device disconnected: {$clientAddress}");
    }

    private function processGpsMessage(string $message, string $clientAddress): void
    {
        try {
            $this->logger->debug("GPS message from {$clientAddress}: {$message}");

            // Parse different GPS protocols
            $gpsData = $this->parseGpsMessage($message);
            
            if (!$gpsData) {
                $this->logger->warning("Failed to parse GPS message: {$message}");
                return;
            }

            // Store position data
            $position = $this->trackingService->savePosition($gpsData);
            
            if ($position) {
                $this->logger->info("Position saved for IMEI {$gpsData['imei']}: {$gpsData['latitude']}, {$gpsData['longitude']}");
                
                // Check for alerts
                $this->checkAndCreateAlerts($position);
                
                // Notify WebSocket clients
                $this->notifyWebSocketClients($position);
            }

        } catch (Exception $e) {
            $this->logger->error("Error processing GPS message: " . $e->getMessage(), [
                'message' => $message,
                'client' => $clientAddress,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function parseGpsMessage(string $message): ?array
    {
        // Support multiple GPS protocols
        
        // GT06 Protocol
        if ($this->isGt06Protocol($message)) {
            return $this->parseGt06Message($message);
        }
        
        // TK103 Protocol
        if ($this->isTk103Protocol($message)) {
            return $this->parseTk103Message($message);
        }
        
        // Generic NMEA
        if ($this->isNmeaProtocol($message)) {
            return $this->parseNmeaMessage($message);
        }

        return null;
    }

    private function isGt06Protocol(string $message): bool
    {
        return strpos($message, '(') === 0 && strpos($message, ')') !== false;
    }

    private function parseGt06Message(string $message): ?array
    {
        // Example: (355488020899095BP05170209V-2345.3456S-04629.8765W000.0000000000.00h)
        if (!preg_match('/\((\d+)([A-Z]{2})(\d{2})(\d{2})(\d{2})(\d{2})([AV])([-+]?\d+\.\d+)([NS])([-+]?\d+\.\d+)([EW])(\d+\.\d+)(\d+)(\d+\.\d+)([h\)]?)/', $message, $matches)) {
            return null;
        }

        $imei = $matches[1];
        $valid = $matches[7] === 'A';
        $latitude = $this->convertCoordinate($matches[8], $matches[9]);
        $longitude = $this->convertCoordinate($matches[10], $matches[11]);
        $speed = (float) $matches[12];
        $course = (int) $matches[13];

        // Parse timestamp
        $date = sprintf('20%02d-%02d-%02d', (int) $matches[3], (int) $matches[4], (int) $matches[5]);
        $time = sprintf('%02d:00:00', (int) $matches[6]); // Simplified time parsing
        $timestamp = $date . ' ' . $time;

        return [
            'imei' => $imei,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'course' => $course,
            'timestamp' => $timestamp,
            'valid' => $valid,
            'raw_message' => $message
        ];
    }

    private function isTk103Protocol(string $message): bool
    {
        return strpos($message, 'imei:') === 0 || preg_match('/^\d+,/', $message);
    }

    private function parseTk103Message(string $message): ?array
    {
        // Example: 123456789012345,tracker,150617080629,A,2234.5678,N,11355.1234,E,0.00,0,,,F,hhmm.m,imei:123456789012345,
        $parts = explode(',', $message);
        
        if (count($parts) < 15) {
            return null;
        }

        $imei = $parts[0];
        $valid = $parts[3] === 'A';
        $latitude = $this->convertGpsCoordinate($parts[4], $parts[5]);
        $longitude = $this->convertGpsCoordinate($parts[6], $parts[7]);
        $speed = (float) $parts[8];
        $course = (float) $parts[9];

        // Parse timestamp YYMMDDHHMMSS
        $dateTime = $parts[2];
        $timestamp = sprintf('20%02d-%02d-%02d %02d:%02d:%02d',
            (int) substr($dateTime, 0, 2),
            (int) substr($dateTime, 2, 2),
            (int) substr($dateTime, 4, 2),
            (int) substr($dateTime, 6, 2),
            (int) substr($dateTime, 8, 2),
            (int) substr($dateTime, 10, 2)
        );

        return [
            'imei' => $imei,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'course' => $course,
            'timestamp' => $timestamp,
            'valid' => $valid,
            'raw_message' => $message
        ];
    }

    private function isNmeaProtocol(string $message): bool
    {
        return strpos($message, '$GPRMC') === 0 || strpos($message, '$GNRMC') === 0;
    }

    private function parseNmeaMessage(string $message): ?array
    {
        // Example: $GPRMC,123519,A,4807.038,N,01131.000,E,022.4,084.4,230394,003.1,W*6A
        $parts = explode(',', $message);
        
        if (count($parts) < 12 || $parts[2] !== 'A') {
            return null;
        }

        $latitude = $this->convertGpsCoordinate($parts[3], $parts[4]);
        $longitude = $this->convertGpsCoordinate($parts[5], $parts[6]);
        $speed = (float) $parts[7] * 1.852; // Convert knots to km/h
        $course = (float) $parts[8];

        // Parse timestamp
        $time = $parts[1];
        $date = $parts[9];
        $timestamp = sprintf('20%02d-%02d-%02d %02d:%02d:%02d',
            (int) substr($date, 4, 2),
            (int) substr($date, 2, 2),
            (int) substr($date, 0, 2),
            (int) substr($time, 0, 2),
            (int) substr($time, 2, 2),
            (int) substr($time, 4, 2)
        );

        return [
            'imei' => 'unknown', // NMEA doesn't include IMEI
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'course' => $course,
            'timestamp' => $timestamp,
            'valid' => true,
            'raw_message' => $message
        ];
    }

    private function convertCoordinate(string $coord, string $direction): float
    {
        $value = (float) $coord;
        
        if (in_array($direction, ['S', 'W'])) {
            $value = -$value;
        }
        
        return $value;
    }

    private function convertGpsCoordinate(string $coord, string $direction): float
    {
        if (empty($coord)) {
            return 0.0;
        }

        // Convert DDMM.MMMM to decimal degrees
        $degrees = (int) ($coord / 100);
        $minutes = $coord - ($degrees * 100);
        $decimal = $degrees + ($minutes / 60);
        
        if (in_array($direction, ['S', 'W'])) {
            $decimal = -$decimal;
        }
        
        return $decimal;
    }

    private function checkAndCreateAlerts($position): void
    {
        try {
            // Check speed alerts
            if ($position->getSpeed() > 100) { // Example threshold
                $this->alertService->createSpeedAlert($position);
            }

            // Check geofence alerts
            $this->alertService->checkGeofenceAlerts($position);

            // Check offline alerts
            $this->alertService->checkOfflineAlerts($position->getVehicleId());

        } catch (Exception $e) {
            $this->logger->error("Error checking alerts: " . $e->getMessage());
        }
    }

    private function notifyWebSocketClients($position): void
    {
        // Send position update to WebSocket server
        // This would typically use a message queue or direct socket communication
        try {
            $wsSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($wsSocket && socket_connect($wsSocket, '127.0.0.1', 8080)) {
                $message = json_encode([
                    'type' => 'position_update',
                    'vehicle_id' => $position->getVehicleId(),
                    'latitude' => $position->getLatitude(),
                    'longitude' => $position->getLongitude(),
                    'speed' => $position->getSpeed(),
                    'timestamp' => $position->getTimestamp()->format('Y-m-d H:i:s')
                ]);
                
                socket_write($wsSocket, $message);
                socket_close($wsSocket);
            }
        } catch (Exception $e) {
            $this->logger->debug("Failed to notify WebSocket clients: " . $e->getMessage());
        }
    }

    public function shutdown(): void
    {
        $this->logger->info("GPS processor shutting down...");
        $this->running = false;
    }

    private function cleanup(): void
    {
        if ($this->socket) {
            socket_close($this->socket);
        }
        $this->logger->info("GPS processor stopped");
    }
}

// Start the GPS processor
try {
    $config = new EnvironmentConfiguration();
    $logger = LoggerFactory::create('gps-processor');
    
    // Initialize dependencies
    $dbConnection = new MySqlConnection($config);
    $gpsPositionRepo = new MySqlGpsPositionRepository($dbConnection);
    $vehicleRepo = new MySqlVehicleRepository($dbConnection);
    $alertRepo = new MySqlAlertRepository($dbConnection);
    
    $trackingService = new TrackingService($gpsPositionRepo, $vehicleRepo);
    $alertService = new AlertService($alertRepo, $vehicleRepo);
    
    $processor = new GpsProcessor(
        $config->getAll(),
        $logger,
        $trackingService,
        $alertService
    );
    
    $processor->start();
    
} catch (Exception $e) {
    $logger->error('GPS processor error: ' . $e->getMessage());
    exit(1);
}