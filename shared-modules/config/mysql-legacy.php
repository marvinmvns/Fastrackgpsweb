<?php
/**
 * Configuração MySQL Legacy - Migrada do sistema antigo
 * Inclui configuração compartilhada do banco de dados
 */

// Incluir configuração compartilhada
require_once __DIR__ . '/database.php';

// Obter configurações do banco
$dbConfig = $GLOBALS['sharedDbConfig'];

// Conexão com o servidor usando as configurações compartilhadas
$cnx = mysql_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password']);
$con = mysql_connect($dbConfig['host'], $dbConfig['username'], $dbConfig['password']);

// Caso a conexão seja reprovada, exibe mensagem de erro
if (!$cnx) die ("<h1>Falha na conexão com o Banco de Dados!</h1>");

// Conectar ao banco de dados
$db = mysql_select_db($dbConfig['database']);

// Log da conexão
if (function_exists('writeSharedLog')) {
    writeSharedLog("Database connection established - Legacy MySQL");
}
?>