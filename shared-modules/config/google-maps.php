<?php
/**
 * Configuração do Google Maps API
 * Compartilhada entre sistemas legacy e moderno
 */

// Carregar configuração compartilhada se disponível
if (file_exists(__DIR__ . '/.env.shared')) {
    $envFile = file_get_contents(__DIR__ . '/.env.shared');
    foreach (explode("\n", $envFile) as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Chave do Google Maps (prioritiza .env.shared, senão usa valor padrão)
$google_maps_key = $_ENV['GOOGLE_MAPS_API_KEY'] ?? "AIzaSyAxJqx0ykYIyiq6otbduy0QQM7K_ksd96c";

// Para compatibilidade com código legacy
$GLOBALS['google_maps_key'] = $google_maps_key;

// Função para obter a chave da API
function getGoogleMapsApiKey() {
    global $google_maps_key;
    return $google_maps_key;
}

// Função para validar se a chave está configurada
function isGoogleMapsConfigured() {
    global $google_maps_key;
    return !empty($google_maps_key) && $google_maps_key !== 'your_api_key_here';
}

// Log se chave não estiver configurada
if (!isGoogleMapsConfigured() && function_exists('writeSharedLog')) {
    writeSharedLog("Google Maps API key not properly configured", "WARNING");
}
?>