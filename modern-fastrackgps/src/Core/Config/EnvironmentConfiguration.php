<?php

declare(strict_types=1);

namespace FastrackGps\Core\Config;

final class EnvironmentConfiguration implements ConfigurationInterface
{
    private array $config = [];

    public function __construct()
    {
        $this->loadEnvironmentVariables();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    public function all(): array
    {
        return $this->config;
    }

    private function loadEnvironmentVariables(): void
    {
        $this->config = [
            'database.host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database.port' => (int) ($_ENV['DB_PORT'] ?? 3306),
            'database.name' => $_ENV['DB_DATABASE'] ?? 'tracker2',
            'database.username' => $_ENV['DB_USERNAME'] ?? 'root',
            'database.password' => $_ENV['DB_PASSWORD'] ?? '',
            'google.maps.key' => $_ENV['GOOGLE_MAPS_API_KEY'] ?? '',
            'app.env' => $_ENV['APP_ENV'] ?? 'production',
            'app.debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'session.lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
        ];
    }
}