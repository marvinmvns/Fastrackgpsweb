<?php

declare(strict_types=1);

namespace FastrackGps\Core\Database;

use PDO;

interface DatabaseConnectionInterface
{
    public function getConnection(): PDO;
    
    public function beginTransaction(): bool;
    
    public function commit(): bool;
    
    public function rollback(): bool;
    
    public function isConnected(): bool;
}