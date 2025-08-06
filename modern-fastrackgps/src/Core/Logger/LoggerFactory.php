<?php

declare(strict_types=1);

namespace FastrackGps\Core\Logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    public static function create(string $name = 'app', string $logPath = null): LoggerInterface
    {
        $logger = new Logger($name);

        $logPath = $logPath ?? __DIR__ . '/../../../storage/logs/app.log';

        // Production handler - rotating files
        $handler = new RotatingFileHandler($logPath, 0, Logger::INFO);
        $logger->pushHandler($handler);

        // Development handler - output to stdout for errors
        if ($_ENV['APP_DEBUG'] ?? false) {
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        }

        // Add context processors
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new WebProcessor());

        return $logger;
    }
}