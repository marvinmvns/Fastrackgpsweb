<?php

declare(strict_types=1);

/**
 * FastrackGPS Application Bootstrap
 * Modern PHP 8+ GPS Tracking System
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('America/Sao_Paulo');

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables if available
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

use FastrackGps\Core\Database\MySqlConnection;
use FastrackGps\Core\View\TwigRenderer;
use FastrackGps\Core\Http\Response;
use FastrackGps\Auth\Service\AuthenticationService;
use FastrackGps\Security\CsrfTokenManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

// Configuration
$config = [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
        'database' => $_ENV['DB_NAME'] ?? 'fastrackgps',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'FastrackGPS',
        'version' => '2.0.0',
        'debug' => (bool) ($_ENV['APP_DEBUG'] ?? false),
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'secret_key' => $_ENV['APP_SECRET'] ?? 'your-secret-key-change-this'
    ],
    'paths' => [
        'templates' => __DIR__ . '/templates',
        'public' => __DIR__ . '/public',
        'logs' => __DIR__ . '/storage/logs',
        'cache' => __DIR__ . '/storage/cache'
    ]
];

// Create necessary directories
$directories = [
    $config['paths']['logs'],
    $config['paths']['cache'],
    __DIR__ . '/storage/sessions'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Setup Logger
$logger = new Logger('fastrackgps');
$logger->pushHandler(new RotatingFileHandler($config['paths']['logs'] . '/app.log', 30, Logger::INFO));

if ($config['app']['debug']) {
    $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
}

// Setup Database Connection
try {
    $database = new MySqlConnection(
        $config['database']['host'],
        $config['database']['port'],
        $config['database']['database'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );
    
    $logger->info('Database connection established');
} catch (Exception $e) {
    $logger->error('Database connection failed: ' . $e->getMessage());
    
    if ($config['app']['debug']) {
        throw $e;
    }
    
    http_response_code(500);
    echo "Database connection error. Please check your configuration.";
    exit;
}

// Setup Twig Template Engine
try {
    $twig = new TwigRenderer($config['paths']['templates'], $config['app']['debug']);
    Response::setRenderer($twig);
    
    $logger->info('Template engine initialized');
} catch (Exception $e) {
    $logger->error('Template engine initialization failed: ' . $e->getMessage());
    
    if ($config['app']['debug']) {
        throw $e;
    }
}

// Setup Session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', (bool) $_SERVER['HTTPS'] ?? false ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// Setup CSRF Protection
$csrfTokenManager = new CsrfTokenManager($_SESSION, $config['app']['secret_key']);

// Setup Authentication Service
$authService = new AuthenticationService(
    // Will be properly injected with repositories in production
    null, // UserRepository
    $logger
);

// Error Handler
set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $logger->error("PHP Error: $message", [
        'file' => $file,
        'line' => $line,
        'severity' => $severity
    ]);
    
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Exception Handler
set_exception_handler(function ($exception) use ($logger, $config) {
    $logger->error('Uncaught exception: ' . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if ($config['app']['debug']) {
        echo "<h1>Uncaught Exception</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        if (class_exists('FastrackGps\Core\Http\Response')) {
            try {
                $response = Response::view('error/500', ['error' => $exception->getMessage()]);
                $response->send();
            } catch (Exception $e) {
                echo "An error occurred. Please try again later.";
            }
        } else {
            echo "An error occurred. Please try again later.";
        }
    }
});

// Simple Router Class
class Router
{
    private array $routes = [];
    
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }
    
    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }
    
    public function put(string $path, callable $handler): void
    {
        $this->routes['PUT'][$path] = $handler;
    }
    
    public function delete(string $path, callable $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }
    
    public function dispatch(string $method, string $path): void
    {
        // Simple pattern matching - in production use a proper router
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            if ($this->matchRoute($route, $path)) {
                $params = $this->extractParams($route, $path);
                call_user_func($handler, ...$params);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        $response = Response::view('error/404');
        $response->send();
    }
    
    private function matchRoute(string $route, string $path): bool
    {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        return preg_match($pattern, $path);
    }
    
    private function extractParams(string $route, string $path): array
    {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        preg_match($pattern, $path, $matches);
        array_shift($matches);
        return $matches;
    }
}

// Initialize Router
$router = new Router();

// Basic Routes
$router->get('/', function() {
    $response = Response::redirect('/dashboard');
    $response->send();
});

$router->get('/dashboard', function() use ($logger) {
    try {
        // Mock data for now - will be replaced with real services
        $statistics = [
            'total_vehicles' => 25,
            'online_vehicles' => 18,
            'pending_alerts' => 3,
            'active_geofences' => 12
        ];
        
        $response = Response::view('dashboard/index', [
            'statistics' => $statistics,
            'user' => (object) ['name' => 'Admin User', 'id' => 1]
        ]);
        $response->send();
    } catch (Exception $e) {
        $logger->error('Dashboard error: ' . $e->getMessage());
        $response = Response::view('error/500', ['error' => $e->getMessage()]);
        $response->send();
    }
});

$router->get('/login', function() {
    $response = Response::view('auth/login', [
        'csrf_token' => 'demo-token' // Will be replaced with real CSRF token
    ]);
    $response->send();
});

$router->get('/vehicles', function() {
    // Mock data
    $vehicles = [
        (object) [
            'id' => 1,
            'name' => 'Veículo Teste 1',
            'imei' => '123456789012345',
            'identification' => 'ABC-1234',
            'is_online' => true,
            'is_active' => true,
            'status' => (object) ['name' => 'Ativo', 'color' => '#28a745'],
            'operating_mode' => (object) ['name' => 'Normal'],
            'last_position' => (object) [
                'latitude' => -23.5505,
                'longitude' => -46.6333,
                'timestamp' => new DateTime()
            ],
            'formatted_chip' => '11999887766',
            'carrier' => (object) ['name' => 'Vivo']
        ]
    ];
    
    $response = Response::view('vehicle/index', [
        'vehicles' => $vehicles,
        'user' => (object) ['name' => 'Admin User', 'id' => 1, 'isAdmin' => true]
    ]);
    $response->send();
});

$router->get('/alerts', function() {
    $response = Response::view('alert/index', [
        'alerts' => [],
        'unacknowledged_count' => 0,
        'user' => (object) ['name' => 'Admin User', 'id' => 1]
    ]);
    $response->send();
});

// Make global objects available
$GLOBALS['config'] = $config;
$GLOBALS['logger'] = $logger;
$GLOBALS['database'] = $database;
$GLOBALS['router'] = $router;
$GLOBALS['csrfTokenManager'] = $csrfTokenManager;
$GLOBALS['authService'] = $authService;

$logger->info('Application bootstrapped successfully', [
    'version' => $config['app']['version'],
    'environment' => $config['app']['env']
]);

return [
    'config' => $config,
    'logger' => $logger,
    'database' => $database,
    'router' => $router,
    'csrf' => $csrfTokenManager,
    'auth' => $authService
];