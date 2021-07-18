<?php
session_start();

//Token vem da geracao do MD5 no momento do login no membership, via post
$token = $_POST['token'];
	
$auth_user = isset($_SESSION['logSessioUser']) ? $_SESSION['logSessioUser'] : "false";

$logado = isset($_SESSION['logSession']) ? $_SESSION['logSession'] : "false";

//if (!isset($_COOKIE["authacc"])) {
if ($logado == "false") {
	//Nao logou, volta
	header("Location: index.html");
}

$cliente = isset($_SESSION['clienteSession']) ? $_SESSION['clienteSession'] : "";
$admin = isset($_GET['admin']) ? $_GET['admin'] : "";
$master = isset($_SESSION['clienteMaster']) ? $_SESSION['clienteMaster'] : "";

//Logar como usuário
if ($admin == "true" and $master == "true") {
	$user = isset($_GET['user']) ? $_GET['user'] : "";
	$_SESSION['clienteSession'] = $user;
}

if ($master == "true") {
	if ($admin == "")
		header("Location: /administracao.html");

	//header("Location: http://localhost/php/sistema/administracao.html");
}

//Comparando os tokens
//TODO: comparar os tokens do post e do php+banco, se for diferente, madeira!
//echo "Falha no login!";


//Se estiver ok, coloca na nessao, e checa sempre na segurança
$_SESSION['tokenSession'] = $token;

/*
//Imprimir os posts
foreach ($_POST as $var => $value) {
	echo "$var = $value<br />";
}*/
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Rastreamento GPS - FastrackGPS</title>
</head>

<frameset id="mainFrame" rows="90,*,159" border="0" frameborder="0" framespacing="0">
	<frame id="topoFrame" name="top" scrolling="no" noresize="noresize" target="contents" src="<?php if ($master == "true") { echo "topo_master.htm"; } else { echo "topo.php"; } ?>">
	<frameset cols="285,*" frameborder="no">
		<frame id="menuFrame" name="contents" target="main" src="menu.php">
		<frame id="mapaFrame" name="main" src="mapa.php">
	</frameset>
	<frame id="listaFrame" name="bottom" scrolling="auto" target="contents" src="listagem.html">	
	
	<noframes>
	<body>
	
	
		<p>This page uses frames, but your browser doesn&#39;t support them.</p>
	

	</body>
	</noframes>
</frameset>

</html>
