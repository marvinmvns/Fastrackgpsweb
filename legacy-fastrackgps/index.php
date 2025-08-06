<?php

declare(strict_types=1);

/**
 * FastrackGPS - Modern GPS Tracking System
 * Entry Point
 * 
 * This is the modern, refactored version of the legacy FastrackGPS system.
 * 
 * Features:
 * - PHP 8+ with strict types
 * - Modern architecture with SOLID principles
 * - Secure coding practices
 * - Twig templating engine
 * - Comprehensive error handling
 * - Real-time GPS tracking
 * - Advanced alert system
 * - Geofencing capabilities
 * - Vehicle command system
 * - Payment management
 */

// Bootstrap the application
$app = require_once __DIR__ . '/bootstrap.php';

// Get HTTP method and path
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Remove query string and normalize path
$path = rtrim($path, '/') ?: '/';

// Handle the request
try {
    $app['router']->dispatch($method, $path);
} catch (Exception $e) {
    $app['logger']->error('Request handling failed', [
        'method' => $method,
        'path' => $path,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Show error page
    if ($app['config']['app']['debug']) {
        echo "<h1>Request Error</h1>";
        echo "<p><strong>Path:</strong> $path</p>";
        echo "<p><strong>Method:</strong> $method</p>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "An error occurred while processing your request.";
    }
}