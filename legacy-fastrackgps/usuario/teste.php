<?php session_start();

$nome = isset($_SESSION['logSessionName']) ? $_SESSION['logSessionName'] : "[Nome Usuário]";


echo $nome;

?>