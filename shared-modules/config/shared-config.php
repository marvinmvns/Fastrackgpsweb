<?php
/**
 * Configuração compartilhada central
 * Arquivo incluído por ambos os sistemas para configurações comuns
 */

// Incluir configuração de banco de dados
require_once __DIR__ . '/database.php';

// Definir caminhos base para cada sistema
define('LEGACY_BASE_PATH', dirname(__DIR__, 2) . '/legacy-fastrackgps/');
define('MODERN_BASE_PATH', dirname(__DIR__, 2) . '/modern-fastrackgps/');
define('SHARED_BASE_PATH', __DIR__ . '/../');

// Configurações globais compartilhadas
$GLOBALS['shared_config'] = [
    'app_name' => 'FastrackGPS',
    'version' => '2.0',
    'timezone' => 'America/Sao_Paulo',
    'session_lifetime' => 7200, // 2 horas
    'max_login_attempts' => 5,
    'password_min_length' => 6,
];

// Função para detectar em qual sistema estamos
function getCurrentSystem() {
    $currentPath = realpath($_SERVER['SCRIPT_FILENAME']);
    
    if (strpos($currentPath, 'legacy-fastrackgps') !== false) {
        return 'legacy';
    } elseif (strpos($currentPath, 'modern-fastrackgps') !== false) {
        return 'modern';
    }
    
    return 'unknown';
}

// Função para obter caminho relativo correto para shared-modules
function getSharedPath($subPath = '') {
    $currentSystem = getCurrentSystem();
    
    switch ($currentSystem) {
        case 'legacy':
            $basePath = '../shared-modules/';
            break;
        case 'modern':
            $basePath = '../shared-modules/';
            break;
        default:
            $basePath = './shared-modules/';
    }
    
    return $basePath . $subPath;
}

// Função para obter URL base correta para assets
function getSharedAssetUrl($assetPath = '') {
    $currentSystem = getCurrentSystem();
    
    // Verificar se estamos em um subdiretório
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $levels = substr_count($scriptDir, '/');
    
    if ($currentSystem === 'legacy') {
        // Para sistema legacy, calcular níveis baseado na estrutura
        if (strpos($_SERVER['SCRIPT_NAME'], '/administracao/') !== false ||
            strpos($_SERVER['SCRIPT_NAME'], '/pagamento/') !== false ||
            strpos($_SERVER['SCRIPT_NAME'], '/usuario/') !== false ||
            strpos($_SERVER['SCRIPT_NAME'], '/ajax/') !== false ||
            strpos($_SERVER['SCRIPT_NAME'], '/OBD/') !== false) {
            $baseUrl = '../../shared-modules/assets/';
        } else {
            $baseUrl = '../shared-modules/assets/';
        }
    } elseif ($currentSystem === 'modern') {
        $baseUrl = '../shared-modules/assets/';
    } else {
        $baseUrl = './shared-modules/assets/';
    }
    
    return $baseUrl . $assetPath;
}

// Configurar timezone
if (isset($GLOBALS['shared_config']['timezone'])) {
    date_default_timezone_set($GLOBALS['shared_config']['timezone']);
}

// Função para logging compartilhado
function writeSharedLog($message, $level = 'INFO') {
    $logFile = getSharedPath('logs/system.log');
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $system = getCurrentSystem();
    $logEntry = "[$timestamp] [$level] [$system] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Headers de segurança compartilhados
function setSharedSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    
    // Apenas HTTPS em produção
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Aplicar headers de segurança automaticamente
setSharedSecurityHeaders();

// Função de compatibilidade para mysql_* functions (sistema legacy)
if (!function_exists('mysql_connect') && getCurrentSystem() === 'legacy') {
    function mysql_connect($server, $username, $password) {
        $link = mysqli_connect($server, $username, $password);
        return $link;
    }
    
    function mysql_select_db($database, $link = null) {
        return mysqli_select_db($link ?: mysqli_connect_errno(), $database);
    }
    
    function mysql_query($query, $link = null) {
        return mysqli_query($link ?: mysqli_connect_errno(), $query);
    }
    
    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
        return mysqli_fetch_array($result, $result_type);
    }
    
    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }
    
    function mysql_error($link = null) {
        return mysqli_error($link ?: mysqli_connect_errno());
    }
    
    function mysql_real_escape_string($string, $link = null) {
        return mysqli_real_escape_string($link ?: mysqli_connect_errno(), $string);
    }
    
    function mysql_close($link = null) {
        return mysqli_close($link ?: mysqli_connect_errno());
    }
}