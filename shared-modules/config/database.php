<?php
/**
 * Configuração de banco de dados compartilhada
 * Usado tanto pelo sistema legacy quanto pelo moderno
 */

// Carrega variáveis de ambiente se disponível
if (file_exists(__DIR__ . '/.env.shared')) {
    $envFile = file_get_contents(__DIR__ . '/.env.shared');
    foreach (explode("\n", $envFile) as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Configurações padrão do banco de dados
$sharedDbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'tracker2',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// Para uso no sistema legacy
function getSharedDbConnection() {
    global $sharedDbConfig;
    
    $dsn = "mysql:host={$sharedDbConfig['host']};dbname={$sharedDbConfig['database']};port={$sharedDbConfig['port']};charset={$sharedDbConfig['charset']}";
    
    return new PDO(
        $dsn,
        $sharedDbConfig['username'],
        $sharedDbConfig['password'],
        $sharedDbConfig['options']
    );
}

// Para uso no sistema moderno (compatibilidade)
class SharedDatabaseConfig {
    public static function getConfig(): array {
        global $sharedDbConfig;
        return $sharedDbConfig;
    }
    
    public static function getDsn(): string {
        $config = self::getConfig();
        return "mysql:host={$config['host']};dbname={$config['database']};port={$config['port']};charset={$config['charset']}";
    }
}