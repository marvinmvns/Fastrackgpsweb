<?php

declare(strict_types=1);

namespace FastrackGps\Core\Config;

interface ConfigurationInterface
{
    public function get(string $key, mixed $default = null): mixed;
    
    public function set(string $key, mixed $value): void;
    
    public function has(string $key): bool;
    
    public function all(): array;
}