<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use FastrackGps\Core\Config\EnvironmentConfiguration;
use FastrackGps\Core\Database\MySqlConnection;
use FastrackGps\Core\Logger\LoggerFactory;

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Initialize configuration
$config = new EnvironmentConfiguration();

// Initialize logger
$logger = LoggerFactory::create('app');

// Initialize database connection
$database = new MySqlConnection(
    host: $config->get('database.host'),
    database: $config->get('database.name'),
    username: $config->get('database.username'),
    password: $config->get('database.password'),
    port: $config->get('database.port')
);

// Simple service container
$container = [
    'config' => $config,
    'logger' => $logger,
    'database' => $database,
];

return $container;