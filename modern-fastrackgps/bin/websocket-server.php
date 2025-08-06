<?php

declare(strict_types=1);

/**
 * FastrackGPS WebSocket Server
 * Handles real-time communication for GPS tracking updates
 */

use FastrackGps\Core\Config\EnvironmentConfiguration;
use FastrackGps\Core\Logger\LoggerFactory;
use Monolog\Logger;

require_once __DIR__ . '/../bootstrap.php';

final class WebSocketServer
{
    private array $clients = [];
    private $socket;
    private Logger $logger;
    private array $config;
    private bool $running = false;

    public function __construct(array $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        
        // Handle shutdown signals
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }

    public function start(): void
    {
        $host = $this->config['websocket']['host'] ?? '127.0.0.1';
        $port = $this->config['websocket']['port'] ?? 8080;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            throw new RuntimeException('Failed to create socket: ' . socket_strerror(socket_last_error()));
        }

        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        if (!socket_bind($this->socket, $host, $port)) {
            throw new RuntimeException('Failed to bind socket: ' . socket_strerror(socket_last_error($this->socket)));
        }

        if (!socket_listen($this->socket, 10)) {
            throw new RuntimeException('Failed to listen on socket: ' . socket_strerror(socket_last_error($this->socket)));
        }

        $this->logger->info("WebSocket server started on {$host}:{$port}");
        $this->running = true;

        while ($this->running) {
            pcntl_signal_dispatch();
            
            $read = [$this->socket];
            foreach ($this->clients as $client) {
                $read[] = $client['socket'];
            }

            $write = null;
            $except = null;

            if (socket_select($read, $write, $except, 1) < 1) {
                continue;
            }

            // Handle new connections
            if (in_array($this->socket, $read)) {
                $newSocket = socket_accept($this->socket);
                if ($newSocket !== false) {
                    $this->handleNewConnection($newSocket);
                }
                $key = array_search($this->socket, $read);
                unset($read[$key]);
            }

            // Handle client messages
            foreach ($read as $clientSocket) {
                $this->handleClientMessage($clientSocket);
            }
        }

        $this->cleanup();
    }

    private function handleNewConnection($socket): void
    {
        $headers = $this->readHeaders($socket);
        
        if (!isset($headers['Sec-WebSocket-Key'])) {
            socket_close($socket);
            return;
        }

        $acceptKey = base64_encode(pack('H*', sha1($headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";

        socket_write($socket, $response);

        $clientId = uniqid();
        $this->clients[$clientId] = [
            'socket' => $socket,
            'headers' => $headers,
            'authenticated' => false,
            'user_id' => null,
            'subscriptions' => []
        ];

        $this->logger->info("New WebSocket client connected: {$clientId}");
    }

    private function handleClientMessage($socket): void
    {
        $data = socket_read($socket, 2048, PHP_BINARY_READ);
        
        if ($data === false || $data === '') {
            $this->removeClient($socket);
            return;
        }

        $frame = $this->decodeFrame($data);
        if (!$frame) {
            return;
        }

        $clientId = $this->getClientId($socket);
        if (!$clientId) {
            return;
        }

        try {
            $message = json_decode($frame['payload'], true, 512, JSON_THROW_ON_ERROR);
            $this->processMessage($clientId, $message);
        } catch (JsonException $e) {
            $this->logger->warning("Invalid JSON from client {$clientId}: " . $e->getMessage());
        }
    }

    private function processMessage(string $clientId, array $message): void
    {
        $type = $message['type'] ?? null;
        
        switch ($type) {
            case 'auth':
                $this->handleAuth($clientId, $message);
                break;
                
            case 'subscribe':
                $this->handleSubscribe($clientId, $message);
                break;
                
            case 'unsubscribe':
                $this->handleUnsubscribe($clientId, $message);
                break;
                
            case 'ping':
                $this->sendToClient($clientId, ['type' => 'pong']);
                break;
                
            default:
                $this->logger->warning("Unknown message type from client {$clientId}: {$type}");
        }
    }

    private function handleAuth(string $clientId, array $message): void
    {
        $token = $message['token'] ?? null;
        if (!$token) {
            $this->sendToClient($clientId, [
                'type' => 'auth_error',
                'message' => 'Token required'
            ]);
            return;
        }

        // Validate token and get user (simplified for example)
        $userId = $this->validateToken($token);
        if (!$userId) {
            $this->sendToClient($clientId, [
                'type' => 'auth_error',
                'message' => 'Invalid token'
            ]);
            return;
        }

        $this->clients[$clientId]['authenticated'] = true;
        $this->clients[$clientId]['user_id'] = $userId;

        $this->sendToClient($clientId, [
            'type' => 'auth_success',
            'user_id' => $userId
        ]);

        $this->logger->info("Client {$clientId} authenticated as user {$userId}");
    }

    private function handleSubscribe(string $clientId, array $message): void
    {
        if (!$this->clients[$clientId]['authenticated']) {
            $this->sendToClient($clientId, [
                'type' => 'error',
                'message' => 'Authentication required'
            ]);
            return;
        }

        $channel = $message['channel'] ?? null;
        if (!$channel) {
            return;
        }

        $this->clients[$clientId]['subscriptions'][] = $channel;
        
        $this->sendToClient($clientId, [
            'type' => 'subscribed',
            'channel' => $channel
        ]);

        $this->logger->info("Client {$clientId} subscribed to channel: {$channel}");
    }

    private function handleUnsubscribe(string $clientId, array $message): void
    {
        $channel = $message['channel'] ?? null;
        if (!$channel) {
            return;
        }

        $key = array_search($channel, $this->clients[$clientId]['subscriptions']);
        if ($key !== false) {
            unset($this->clients[$clientId]['subscriptions'][$key]);
        }

        $this->sendToClient($clientId, [
            'type' => 'unsubscribed',
            'channel' => $channel
        ]);
    }

    public function broadcast(string $channel, array $data): void
    {
        $message = [
            'type' => 'broadcast',
            'channel' => $channel,
            'data' => $data
        ];

        foreach ($this->clients as $clientId => $client) {
            if (in_array($channel, $client['subscriptions'])) {
                $this->sendToClient($clientId, $message);
            }
        }
    }

    private function sendToClient(string $clientId, array $message): void
    {
        if (!isset($this->clients[$clientId])) {
            return;
        }

        $socket = $this->clients[$clientId]['socket'];
        $frame = $this->encodeFrame(json_encode($message));
        
        if (socket_write($socket, $frame) === false) {
            $this->removeClient($socket);
        }
    }

    private function validateToken(string $token): ?int
    {
        // Simplified token validation - implement proper JWT validation
        // For now, just return a mock user ID
        return 1;
    }

    private function readHeaders($socket): array
    {
        $headers = [];
        $request = '';
        
        while (($line = socket_read($socket, 1024)) !== false) {
            $request .= $line;
            if (strpos($request, "\r\n\r\n") !== false) {
                break;
            }
        }

        $lines = explode("\r\n", $request);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }

    private function decodeFrame(string $data): ?array
    {
        if (strlen($data) < 2) {
            return null;
        }

        $firstByte = ord($data[0]);
        $secondByte = ord($data[1]);

        $opcode = $firstByte & 0x0F;
        $masked = ($secondByte >> 7) & 0x01;
        $payloadLength = $secondByte & 0x7F;

        $offset = 2;

        if ($payloadLength === 126) {
            $payloadLength = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
        } elseif ($payloadLength === 127) {
            $payloadLength = unpack('J', substr($data, $offset, 8))[1];
            $offset += 8;
        }

        if ($masked) {
            $maskingKey = substr($data, $offset, 4);
            $offset += 4;
        }

        $payload = substr($data, $offset, $payloadLength);

        if ($masked) {
            for ($i = 0; $i < $payloadLength; $i++) {
                $payload[$i] = chr(ord($payload[$i]) ^ ord($maskingKey[$i % 4]));
            }
        }

        return [
            'opcode' => $opcode,
            'payload' => $payload
        ];
    }

    private function encodeFrame(string $payload): string
    {
        $payloadLength = strlen($payload);
        $frame = chr(0x81); // Text frame

        if ($payloadLength < 126) {
            $frame .= chr($payloadLength);
        } elseif ($payloadLength < 65536) {
            $frame .= chr(126) . pack('n', $payloadLength);
        } else {
            $frame .= chr(127) . pack('J', $payloadLength);
        }

        return $frame . $payload;
    }

    private function getClientId($socket): ?string
    {
        foreach ($this->clients as $clientId => $client) {
            if ($client['socket'] === $socket) {
                return $clientId;
            }
        }
        return null;
    }

    private function removeClient($socket): void
    {
        $clientId = $this->getClientId($socket);
        if ($clientId) {
            unset($this->clients[$clientId]);
            socket_close($socket);
            $this->logger->info("Client {$clientId} disconnected");
        }
    }

    public function shutdown(): void
    {
        $this->logger->info("WebSocket server shutting down...");
        $this->running = false;
    }

    private function cleanup(): void
    {
        foreach ($this->clients as $client) {
            socket_close($client['socket']);
        }
        socket_close($this->socket);
        $this->logger->info("WebSocket server stopped");
    }
}

// Start the server
try {
    $config = new EnvironmentConfiguration();
    $logger = LoggerFactory::create('websocket');
    
    $server = new WebSocketServer($config->getAll(), $logger);
    $server->start();
} catch (Exception $e) {
    $logger->error('WebSocket server error: ' . $e->getMessage());
    exit(1);
}