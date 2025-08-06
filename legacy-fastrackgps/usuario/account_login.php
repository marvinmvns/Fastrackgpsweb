<?php

if ($_POST["auth_user"] != "") 
	$auth_user = $_POST["auth_user"];
else
	echo "Usuário não preenchido <br />";
	
if ($_POST["auth_pw"] != "") 
	$auth_pw = $_POST["auth_pw"];
else
	echo "Senha não preenchida <br />";

$auth = false;

$diasInativacao = "";
$flAtivo = "";
$cliente = "";
$nome = "";
$master = ""; //usuário administrador do sistema

if (isset( $auth_user ) && isset($auth_pw)) {

    include("../../shared-modules/config/google-maps.php");
    $errormsg = "Incorrect password";
    $con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASS);
    mysql_select_db($DB_NAME, $con);
	
	$auth_user = mysql_real_escape_string($auth_user);
	$auth_pw = mysql_real_escape_string($auth_pw);
	
    $sql = "SELECT 
				DATEDIFF(a.data_inativacao, NOW()) as diasInat, 
				CAST(a.id AS DECIMAL(10,0)) as idCliente,
				a.*
			FROM cliente a 
			WHERE (a.email = '$auth_user' OR a.apelido = '$auth_user') AND 
				  a.senha = '". md5($auth_pw)."' 
			LIMIT 1"; 

    $result = mysql_query( $sql ) 
        or die ( 'Unable to execute query.' ); 
		
    // Get number of rows in $result. 
    $num = mysql_numrows( $result ); 

    if ( $num != 0 ) { 
		// vars found in db, auth=true:
		$auth = true; 
		$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv("REMOTE_ADDR"));
		
		while($data = mysql_fetch_assoc($result))
		{
			$diasInativacao = $data['diasInat'];
			$flAtivo = $data['ativo'];
			$cliente = $data['idCliente'];
			$master  = $data['master'];
			$nome	 = $data['nome'];
			
			//Atualizando os STATUS dos rastreadores do cliente para desligado
			mysql_query("UPDATE bem set date = date, status_sinal = 'D' WHERE cliente = $cliente", $con);
			
			//Registrando log de acesso
			mysql_query("INSERT INTO cliente_log (id, ip) VALUES ($cliente, '$ip')", $con);			
		} 
	}
	
	mysql_close($con);
}

if ( !$auth ) {

	header("Location: /erro_login.html");
	//header("Location: http://localhost/sistema/erro_login.html");
	
    exit; 

} else {

    //$username = $auth_user;
    //$ccontent = "$auth_user:". md5($auth_pw);
    //setcookie ("authacc", "$ccontent");
	
	//Se usuário administrador, redireciona para administração
	if ($master == "S") 
	{
		session_start();
		$_SESSION['logSession'] = "true";
		$_SESSION['logSessioUser'] = $auth_user;
		$_SESSION['clienteSession'] = "master";
		$_SESSION['clienteMaster'] = "true";
		$_SESSION['logSessionName'] = $nome;
		
		$auth = true;
		header("Location: /administracao.html");
		//header("Location: http://localhost/sistema/administracao.html");
		exit;
	}
	else
	{
		session_start();
		$_SESSION['logSession'] = "true";
		$_SESSION['logSessioUser'] = $auth_user;
		$_SESSION['clienteSession'] = $cliente;
		$_SESSION['logSessionName'] = $nome;
		
		if ($diasInativacao != null) {
			$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
		}
		
		if ($flAtivo == "N") {
			$diasInativacao = "0";
			$_SESSION['logQtdeDiasDesativacao'] = $diasInativacao;
		}
	}
}

//require("account_inc.php");


if ($diasInativacao != null) {
	header("Location: /login_aviso_desativacao.php");
	//header("Location: http://localhost/sistema/login_aviso_desativacao.php");
} else {
	header("Location: /default.php");
	//header("Location: /novidade2.php");
	//header("Location: http://localhost/sistema/default.php");
	//header("Location: http://localhost/sistema/novidade.php");
}

?>
Redirecionand..
