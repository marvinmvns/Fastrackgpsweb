<?php
//Sessão iniciada em default.php
session_start();

$cliente = isset($_SESSION['clienteSession']) ? $_SESSION['clienteSession'] : "";
$nmCliente = isset($_SESSION['logSessioUser']) ? $_SESSION['logSessioUser'] : "";
$token = isset($_SESSION['tokenSession']) ? $_SESSION['tokenSession'] : "";
$master = isset($_SESSION['clienteMaster']) ? $_SESSION['clienteMaster'] : "";

if ($cliente == "") {
	//redirect para login
	die('Sessão encerrada! Faça login.');
}

/*if ($token == "") {
	//redirect para login
	die('Error token!');
}*/
?>