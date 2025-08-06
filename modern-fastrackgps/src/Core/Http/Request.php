<?php

declare(strict_types=1);

namespace FastrackGps\Core\Http;

final class Request
{
    private array $query;
    private array $request;
    private array $server;
    private array $files;

    public function __construct()
    {
        $this->query = $_GET;
        $this->request = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
    }

    public static function createFromGlobals(): self
    {
        return new self();
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->input($key);
            }
        }
        return $result;
    }

    public function has(string $key): bool
    {
        return isset($this->request[$key]) || isset($this->query[$key]);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function expectsJson(): bool
    {
        $contentType = $this->server['HTTP_CONTENT_TYPE'] ?? '';
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        
        return str_contains($contentType, 'application/json') || 
               str_contains($accept, 'application/json');
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? 
               $this->server['HTTP_X_REAL_IP'] ?? 
               $this->server['REMOTE_ADDR'] ?? 
               '0.0.0.0';
    }
}