<?php

//conexão com o servidor
$cnx = mysql_connect("localhost", "root", "suasenha");
$con = mysql_connect("localhost", "root", "suasenha");

// Caso a conexão seja reprovada, exibe na tela uma mensagem de erro
if (!$cnx) die ("<h1>Falha na coneco com o Banco de Dados!</h1>");

// Caso a conexão seja aprovada, então conecta o Banco de Dados.	
$db = mysql_select_db('tracker2');

/*Configurando este arquivo, depois é só você dar um include em suas paginas php, isto facilita muito, pois caso haja necessidade de mudar seu Banco de Dados
você altera somente um arquivo*/
?>