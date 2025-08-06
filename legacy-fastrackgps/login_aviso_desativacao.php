<?php session_start(); ?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow">
<meta name="robots" content="noarchive">

<?php


$qtdeDiasDesativacao = isset($_SESSION['logQtdeDiasDesativacao']) ? $_SESSION['logQtdeDiasDesativacao'] : "180";

?>

<title>Aviso - Rastreamento GPS - FastrackGps</title>
<style type="text/css" media="all">
html, body {
   height :100%;
}
body {
	margin:0; 
	padding:0;
	font:75%/1.4 Verdana, Arial, Helvetica, sans-serif;
	text-align:center;
}
#tudo { 
	width:100%;
	position:relative;  /*Contexto de posicionamento */
	margin:0 auto;
	text-align:left;
	min-height:100%;/**/
	}
 * html #tudo {height: 100%;}

#conteudo {
	padding-bottom:30px;
}
#topo {
	background-position: center center;
	width: 100%;
	height: 100px;
	text-align: center;
	padding-top: 6px;
	background-image: url('imagens/topo_login.png');
	background-repeat: no-repeat;
	border-bottom-style: solid;
	border-bottom-width: 1px;
	border-bottom-color: #C0C0C0;
}
#topo2 {
	background-position: center top;
	width: 100%;
	height: 25px;
	text-align: center;
	padding-top: 6px;
	background-image: url('imagens/topo_login2.png');
	background-repeat: repeat-x;
}
#principal {
	background-position: center top;
	width: 100%;
	padding-left: 0px;
	float: left;
	padding-top: 5px;
	text-align: center;
	vertical-align: middle;
	background-image: url('imagens/sombra_topo.png');
	background-repeat: repeat-x;
}
#rodape {
	background-position: center top;
	width: 100%;
	height: 68px;
	position: absolute;
	bottom: 0;
	text-align: center;
	font-family: Segoe UI,Arial,Verdana,Helvetica,sans-serif;
	vertical-align: top;
	background-repeat: no-repeat;
}
</style>
</head>
<body>
	<div id="tudo">
	
		<div id="conteudo">
			<div id="topo"></div>
			<div id="topo2"></div>
		
			<div id="principal" align="center">
			
			<p style="height: 60px"></p>

			<?php 
				$continua = true;
				
				if ((int)$qtdeDiasDesativacao > 0) {
					echo "Você tem <span style='font-size: large; font-weight:bold'>". $qtdeDiasDesativacao ." dia(s) de monitoramento gratuito</span>.";
				} else {
					if ((int)$qtdeDiasDesativacao <= 0) {
						$continua = true;

						$_SESSION['logSession'] = "false";
						$_SESSION['clienteSession'] = "";
						echo "Sua conta está desativada.";
						
					}
				}
			?>
			<br>
			<br />
			Para continuar com o serviço de Rastreamento, efetue o pagamento por depósito em conta no banco:  <br />	
			Itau Agencia: 0933 C/C:01284-1
			<br>
			<br>
			Não esqueça de enviar o comprovante em anexo ao seu e-mail para <a href="mailto:marcus@segundo.me" style="color: #196297; text-decoration:none; font-size:small;font-weight:bold">marcus@segundo.me</a>
			<br>
			<br />
			Caso opte a pagar por Pagseguro <a href=http://fastrackgps.net/pagamento/consulta_pgto.php style="color: #196297; text-decoration:none; font-size:small;font-weight:bold">Clique Aqui</a>.
			<br>		
			<br />
			
			
			IMPORTANTE: Efetue o pagamento até o ultimo dia gratuito, evitando assim, que sua conta seja automaticamente desativada.
			<br />
			<br>		
			<br />
		
			
			<?php  if ($continua) { 
				echo "<input name='btnContinuar' type='button' value='Continuar' onclick=window.location='default.php' />";
			}
			?>
			</div>
		</div>
		
		<div id="rodape"> 
			<img src="imagens/divider.jpg" alt="" title="" />
			<br />
    © 2014 FastrackGPS &nbsp;&nbsp;&nbsp; Todos os Direitos Reservados &nbsp;&nbsp;&nbsp; 
    &nbsp;&nbsp;&nbsp; <a style="text-decoration:none;color:black" href="mailto:marcus@segundo.me">marcus@segundo.me</a> 
    &nbsp;&nbsp;&nbsp; </a> 
  </div>
	</div>
</body>
</html>
