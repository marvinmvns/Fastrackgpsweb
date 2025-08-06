<?php include('../../shared-modules/config/seguranca.php');

if ($master != "true") {
	header("Location: /logout.php");
	//header("Location: http://localhost/sistema/logout.php");
} else {
	$_SESSION['clienteSession'] = "master";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Administração - Rastreamento GPS - FastrackGPS</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow">
<meta name="robots" content="noarchive">

<?php

$acao = isset($_GET['acao']) ? $_GET['acao'] : "novoUsuario";

if (isset($_POST['acao'])) {
	$acao = $_POST['acao'];
}

	 function inverte_data($data,$separador)                    {
                     $nova_data = implode("".$separador."",array_reverse(explode("".$separador."",$data)));
                     return $nova_data;
			         }				


      function SomarData($data, $dias, $meses, $ano)
       
	          {  
                $data = explode("/", $data);
                $newData = date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses,
                $data[0] + $dias, $data[2] + $ano) );
                    return $newData;
              }		


$caracteres = array(" ", "(", ")", "-");
$chipSemMascara = str_replace($caracteres, "", $chip);

$sucesso = null;
$email = $_POST['email'];
$senha = $_POST['senha'];
$nomeCliente = $_POST['nomeCliente'];
$cpf = $_POST['cpf'];
$codigoCliente = isset($_POST['codigo']) ? $_POST['codigo'] : $_GET['codigo'];
$apelido = $_POST['apelido'];
$telefone1 = str_replace($caracteres, "", $_POST['telefone1']);
$telefone2 = str_replace($caracteres, "", $_POST['telefone2']);
$endereco = $_POST['endereco'];

//echo "<br /><br /><br /><br />";//echo "acao: " . $acao . "<br />";//echo "codigoCliente: " . $codigoCliente . "--" . $_POST['codigo'] . "<br />";

//Conectando
$cnx = mysql_connect("localhost", "admin123", "admin123")
  or die("Could not connect: " . mysql_error());
mysql_select_db("tracker2", $cnx);

$countCliente = 0;
$res = mysql_query("select count(*) as countCliente from cliente where master = 'N'");

for ($i=0; $i < 1; $i++) {
	$row = mysql_fetch_assoc($res);	
	$countCliente = (int)$row[countCliente];
}

$countIMEI = 0;
$res = mysql_query("select count(*) as countIMEI from bem");

for ($i=0; $i < 1; $i++) {
	$row = mysql_fetch_assoc($res);	
	$countIMEI = (int)$row[countIMEI];
}

/** Nao alterar ordem da acao */
if ($acao == "atualizarUsuario") {
	if ($email != null and $nomeCliente != null and $codigoCliente != null) {
		if (strpos($email, "@") === false) {
			$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Digite um e-mail.</span>";
		} else {
			if ($senha != null) 
				$sql = "UPDATE cliente set nome = '$nomeCliente', cpf = '$cpf', email = '$email', apelido = '$apelido', senha = '". md5($senha) ."', telefone1 = '$telefone1', telefone2 = '$telefone2', endereco = '$endereco' WHERE id = $codigoCliente and master = 'N'";
			else
				$sql = "UPDATE cliente set nome = '$nomeCliente', cpf = '$cpf', email = '$email', apelido = '$apelido', telefone1 = '$telefone1', telefone2 = '$telefone2', endereco = '$endereco' WHERE id = $codigoCliente and master = 'N'";		

			if (!mysql_query($sql, $cnx)) {
				// Se der erro, envia alerta que houve falha
				if (mysql_error() == "Duplicate entry '". $email ."' for key 'email_unq'" or mysql_error() == "Duplicate entry '". $apelido ."' for key 'apelido_unq'")
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Usuário já existe!</span>";
				else
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";
				//die('Error: ' . mysql_error());
			} else {
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Alterado com sucesso!</span>";
				$resCodigo = mysql_query("select id from cliente where email = '$email' and master = 'N'");
				for ($j=0; $j < mysql_num_rows($resCodigo); $j++) {
					$rowCodigo = mysql_fetch_assoc($resCodigo);
					$codigoCliente = $rowCodigo[id];
				}
				$hoje  = date('Y-m-d');
				
				  $dataini = date("d/m/Y");	        
  		          $dtvcto = SomarData($dataini, 7, 0, 0);             
                  $dtcorte = SomarData($dtvcto, 7, 0, 0); 			 
			      $dataini = inverte_data($dataini,'/');
			      $dtvcto  = inverte_data($dtvcto,'/');	
			      $dtcorte  = inverte_data($dtcorte,'/');
			 
			 		  
               
				$obs = "7 dias gratis";  
          		$descr = "7 dias gratis";  
				
				
				
				mysql_query("INSERT INTO pagtos1 (codcli, descr, data_ult_pgto, data_vcto, dtcorte, pago, obs, qtde) VALUES
						      ('$codigoCliente', '$descr',  '$dataini' , '$dtvcto', '$dtcorte' , 'N', '$obs', 1)", $cnx);
							  

				
				
				

				
				//Fim de envio e-mail*/
			}
		}
		
		$acao = "obterUsuario";
	}
}

/** Nao alterar ordem da acao */
if ($acao == "novoUsuario") {
	if ($email != null and $nomeCliente != null) {
		
		if (strpos($email, "@") === false) {
			$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Digite um e-mail.</span>";
			$acao = "novoUsuario";
		} else {
			if (!mysql_query("INSERT INTO cliente (email, nome, cpf, senha, apelido, telefone1, telefone2, endereco) VALUES ('$email', '$nomeCliente', '$cpf', '". md5($senha) ."', '$apelido', '$telefone1', '$telefone2', '$endereco')", $cnx)) {
				// Se der erro, envia alerta que houve falha
				if (mysql_error() == "Duplicate entry '". $email ."' for key 'email_unq'" or mysql_error() == "Duplicate entry '". $apelido ."' for key 'apelido_unq'") {
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Usuário já existe!</span>";
					$acao = "novoUsuario";
				} else {
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";
					//die('Error: ' . mysql_error());
				}
			} else {
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Cadastrado com sucesso!</span>";
				$acao = "atualizarUsuario";
				$resCodigo = mysql_query("select id from cliente where email = '$email' and master = 'N'");
				for ($j=0; $j < mysql_num_rows($resCodigo); $j++) {
					$rowCodigo = mysql_fetch_assoc($resCodigo);
					$codigoCliente = $rowCodigo[id];
				}
				
				//Inserindo permissões de comandos
				if (!mysql_query("INSERT INTO command_cliente (cliente) VALUES ('$codigoCliente')", $cnx)) {
					//die('Error: ' . mysql_error());
				} else {
					//Lista de comandos inserida
				}

				//ini_set("allow_url_fopen", 1);
				//ini_set("allow_url_include", 1); 
				
				/*
				//Envia e-mail para ativar a conta
				require_once('class.phpmailer.php');
				//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

				$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

				$mail->IsSMTP(); // telling the class to use SMTP

				try {
				  $mail->Host       = "mail.gmail.com"; // SMTP server
				  //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
				  $mail->SMTPAuth   = true;                  // enable SMTP authentication
				  $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
				  $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
				  $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
				  $mail->Username   = "contato@gmail.com";  // GMAIL username
				  $mail->Password   = "senha_aqui";            // GMAIL password
				  $mail->CharSet 	= "UTF-8";
				  $mail->AddReplyTo('contato@gmail.com', 'Agile GPS');
				  $mail->AddAddress($email, 'Cliente');
				  $mail->SetFrom('contato@gmail.com', 'Agile GPS');
				  $mail->AddReplyTo($email, 'Cliente');
				  $mail->Subject = 'Ativacao - Sistema de Rastreamento GPS';
				  $mail->AltBody = 'Para ver esta mensagem, por favor use um leitor de email compatível com HTML!'; // optional - MsgHTML will create an alternate automatically
				  $mail->MsgHTML(file_get_contents('modelo_email.html'));
				  $mail->AddAttachment('imagens/logo_agile.jpg');      // attachment
				  //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
				  $mail->Send();
				  //echo "Message Sent OK</p>\n";
				} catch (phpmailerException $e) {
					//echo $e->errorMessage(); //Pretty error messages from PHPMailer
				} catch (Exception $e) {
					//echo $e->getMessage(); //Boring error messages from anything else!
				}		
				
				//Fim de envio e-mail*/
				
				  $hoje  = date('Y-m-d');
				
				  $dataini = date("d/m/Y");	        
  		          $dtvcto = SomarData($dataini, 7, 0, 0);             
                  $dtcorte = SomarData($dtvcto, 7, 0, 0); 			 
			      $dataini = inverte_data($dataini,'/');
			      $dtvcto  = inverte_data($dtvcto,'/');	
			      $dtcorte  = inverte_data($dtcorte,'/');
			 
			 		  
               
				$obs = "7 dias gratis";  
          		$descr = "7 dias gratis";  
				
				
				
				mysql_query("INSERT INTO pagtos1 (codcli, descr, data_ult_pgto, data_vcto, dtcorte, pago, obs, qtde) VALUES
						      ('$codigoCliente', '$descr',  '$dataini' , '$dtvcto', '$dtcorte' , 'N', '$obs', 1)", $cnx);
				
				
			
				
			}
		}
	}
}

if ($acao == "obterUsuario") {
	if ($codigoCliente != null) {
	
		$resUsuario = mysql_query("select c.*,
									CONCAT('(', SUBSTRING(c.telefone1,1,2), ') ', SUBSTRING(c.telefone1,3,4), '-', SUBSTRING(c.telefone1,7,4)) as stelefone1,
									CONCAT('(', SUBSTRING(c.telefone2,1,2), ') ', SUBSTRING(c.telefone2,3,4), '-', SUBSTRING(c.telefone2,7,4)) as stelefone2
									from cliente c where c.id = '$codigoCliente' and c.master = 'N'");
		for ($k=0; $k < mysql_num_rows($resUsuario); $k++) {
			$rowUsuario = mysql_fetch_assoc($resUsuario);
			$codigoCliente = $rowUsuario[id];
			$nomeCliente = $rowUsuario[nome];
			$cpf = $rowUsuario[cpf];
			$email = $rowUsuario[email];
			$apelido = $rowUsuario[apelido];
			$telefone1 = $rowUsuario[stelefone1] == "() -" ? "" : $rowUsuario[stelefone1];
			$telefone2 = $rowUsuario[stelefone2] == "() -" ? "" : $rowUsuario[stelefone2];
			$endereco = $rowUsuario[endereco];
		}
		$acao = "atualizarUsuario";
	}
}


?>
<style>

body, table {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #A6A6A6;
}

.menu {
	border-color: #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #CCCCCC;
	background-color: #FFFFFF;
	border-right: 1px solid #CCCCCC;
	border-top: 1px solid #CCCCCC;
	border-bottom: 1px solid #CCCCCC;
	padding: 5px;
	cursor: hand;
}
.menu-sel {
	border-color: #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #CCCCCC;
	background-color: #F7F7F7;
	border-right: 1px solid #CCCCCC;
	border-top: 1px solid #CCCCCC;
	padding: 5px;
	cursor: hand;
}
.tb-conteudo {
	border-right: 1px solid #CCCCCC;
	border-bottom: 1px solid #CCCCCC;
	border-right-color: #CCCCCC;
	border-bottom-color: #CCCCCC;
}
.conteudo {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	font-weight: normal;
	color: #A6A6A6;
	background-color: #F7F7F7;
	padding: 5px;
/*height: 435px;*/	height: 100%;
	width: auto;
	filter:alpha(opacity=90); 
  	-moz-opacity: 0.90; 
   	opacity: 0.90; 
}

.textoEsquerda {
	text-align: right;
	padding-right:5px;
	width:25%;
}

.campoNovoVeiculo {
	border: 1px solid #C0C0C0;
}

.dicaCadastro {
	font-size:xx-small;
}

.btnAcao {
	border: 1px solid #808080;
	background-color: #E0E0E0;
}

.resumo {
	font-size:16px;
	font-weight: bold;
	color: #000000;
}

.divisorLog {
	border-right-style: solid;
	border-right-width: 1px;
	padding-right: 4px;
	border-right-color: #E2E2E2;
	text-align:center;
}

</style>

<script type="text/javascript" src="../shared-modules/assets/js/painelAdmin.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/mascaras.js"></script>

</head>

<?php 
$aba = $_GET['aba'];
$abaInicial = "'td_cadastro','div_cadastro'";

if ($aba != null && $aba == "alertas")
	$abaInicial = "'td_alertas','div_alertas'";	

?>

<body onLoad="AlternarAbas(<?php echo $abaInicial; ?>); AlternarSubAbas('td_sub_cadastro','div_sub_cadastro'); setTimeout('esconderAlerta()', 10000);  setTimeout('checarAlertasAdmin()', 10000); " 
	style=" background-position: right bottom; height:auto; border-left:thin; border-left-style: solid; 
			border-left-width: 1px; border-left-color: #CCCCCC; margin-left:0px; margin-top:-17px; 
			background-image:url("../shared-modules/assets/images/fundo_logo_webarch.png'); background-repeat: no-repeat;">

<h2 align="center" style="font-size:20px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #666666;">Administração do sistema</h2>

<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border: 1px solid #000000; border-color: #CCCCCC; background-color: #F7F7F7;" align="center">
	<tr>
		<td><a href="#" style="color:#0099FF" onclick="if (document.getElementById('divResumo').style.display=='block') { document.getElementById('divResumo').style.display='none'; } else { document.getElementById('divResumo').style.display='block'; } ">Totais</a>
		<div id="divResumo" style="display:none">
			<br/>
			<table>
				<tr>
					<td>Total de Usuários: </td>
					<td><span class="resumo"><?php echo $countCliente ?></span></td>
				</tr>
				<tr>
					<td>Total de IMEIs: </td>
					<td><span class="resumo"><?php echo $countIMEI ?></span></td>
				</tr>
			</table>
		</div>
		</td>
	</tr>
</table>
<br/>
<br/>
<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border-left: 1px solid #000000; border-left-color: #CCCCCC;" align="center">

	<tr>
		<td width="100" class="menu" id="td_cadastro"
		onclick="AlternarAbas('td_cadastro','div_cadastro')" style="height: 7px">
			Cadastro
		</td>
		<td width="100" class="menu" id="td_consulta"
		onclick="AlternarAbas('td_consulta','div_consulta')" style="height: 7px">
			Usuários
		</td>
		<td width="100" class="menu" id="td_manutencao"
		onclick="AlternarAbas('td_manutencao','div_manutencao')" style="height: 7px">
			Pagamentos
		</td>
		<td width="100" class="menu" id="td_alertas"
		onclick="AlternarAbas('td_alertas','div_alertas')" style="height: 7px">
			Alertas
		</td>		
		<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">
			&nbsp;</td>
		<td style="height: 7px"></td>
	</tr>
	<tr>
		<td class="tb-conteudo" colspan="5">
			<div id="div_cadastro" class="conteudo" style="display:block;">
				<div>
					Adicione um usuário <br />

					<form name="novoUsuario" id="novoUsuario" method="post" action="admin.php" autocomplete="off">
						<input name="acao" type="hidden" value="<?php echo $acao; ?>" />
						<table style="width: 70%" cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="2">
									<?php echo $sucesso ?><br />
									<img style="display:none" src="../shared-modules/assets/images/carregando.gif" alt="Carregando, aguarde..." title="Carregando, aguarde..." id="imgCarregando" />
									<br />
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Código:</td>
								<td><input name="codigo" id="codigoCliente" maxlength="10" size="12" type="text" value="<?php echo $codigoCliente; ?>" readonly="true" style="background-color:#E0E0E0" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Código gerado pelo sistema</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Nome do cliente:</td>
								<td><input name="nomeCliente" id="nomeCliente" maxlength="50" size="55" type="text" value="<?php echo $nomeCliente; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro"></span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">CPF/CNPJ:</td>
								<td><input name="cpf" id="cpf" maxlength="14" size="14" type="text" value="<?php echo $cpf; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro"></span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Telefones:</td>
								<td><input id='telefone1' name='telefone1' type='text' value='<?php echo $telefone1; ?>' maxlength='14' class='campoNovoVeiculo' onkeypress="return txtBoxFormat(this, '(99) 9999-9999', event);" onblur=" if (this.value != '') { return txtBoxFormat(this, '(99) 9999-9999', event);} " size='11' />
									<input id='telefone2' name='telefone2' type='text' value='<?php echo $telefone2; ?>' maxlength='14' class='campoNovoVeiculo' onkeypress="return txtBoxFormat(this, '(99) 9999-9999', event);" onblur=" if (this.value != '') { return txtBoxFormat(this, '(99) 9999-9999', event);} " size='11' />
									<span class="dicaCadastro"></span>
								</td>
							</tr>	
							<tr>
								<td class="textoEsquerda">Endereço:</td>
								<td><input name="endereco" id="endereco" maxlength="500" size="55" type="text" value="<?php echo $endereco; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro"></span>
								</td>
							</tr>							
							<tr>
								<td class="textoEsquerda">E-mail:</td>
								<td><input name="email" id="email" maxlength="45" size="25" type="text" value="<?php echo $email; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: E-mail do cliente para ativação/faturamento</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Login:</td>
								<td><input name="apelido" id="apelido" maxlength="45" size="25" type="text" value="<?php echo $apelido; ?>" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Login de acesso</span>
								</td>
							</tr>							
							<tr>
								<td class="textoEsquerda">Senha:</td>
								<td><input name="senha" id="senha" maxlength="45" size="25" type="text" value="" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Senha de acesso</span>
								</td>
							</tr>							
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td>
								<input name="btnCadastrar" type="submit" value="<?php if ($acao == "novoUsuario") { echo "Cadastrar"; } else { echo "Atualizar"; } ?>" class="btnAcao" onclick="if ((getElementById('email').value) == '' || (getElementById('nomeCliente').value) == '') { return false; } else { document.getElementById('imgCarregando').style.display='block'; } " />&nbsp;&nbsp;
								<a href="admin.php" style="color:#0099FF">Novo</a>
								</td>
								<td></td>
							</tr>
						</table>
					</form>
					<br />
					<?php if (($acao == "novoUsuario" or $acao == "atualizarUsuario") and $codigoCliente != null) { ?>
					<table width="98%" cellspacing="0" cellpadding="0" border="0" 
						style="border-left: 1px solid #000000; border-left-color: #CCCCCC; 
							   border-bottom: 1px solid #000000; border-bottom-color: #CCCCCC;"
						align="center">
					<tr>
						<td width="100" class="menu" id="td_sub_cadastro" style="height: 7px" onclick="AlternarSubAbas('td_sub_cadastro','div_sub_cadastro')">
							<span style="font-size:14px">Veículos</span>
						</td>
						<td width="100" class="menu" id="td_sub_comandos" style="height: 7px" onclick="AlternarSubAbas('td_sub_comandos','div_sub_comandos')">
							<span style="font-size:14px">Comandos</span>
						</td>						
						<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">
							&nbsp;</td>
						<td style="height: 7px"></td>						
					</tr>
					<tr>
						<td class="tb-sub-conteudo" colspan="4">
							<div id="div_sub_cadastro" class="conteudo" style="display:block; border-right: 1px solid #000000; border-right-color: #CCCCCC;">
								<div>
								
								<form name="listaBensUsuarios" method="post" action="">
								<table cellspacing="6" cellpadding="0" id="tabelaVeiculos">
										<tr>
											<td colspan="5">
												<br />
											</td>
										</tr>
									<?php 
									//Montando listagem
									$res = mysql_query("select b.*,
														CONCAT('(', SUBSTRING(b.numero_chip,1,2), ') ', SUBSTRING(b.numero_chip,3,4), '-', SUBSTRING(b.numero_chip,7,4)) as nr_chip, 
														CONCAT('(', SUBSTRING(b.numero_chip2,1,2), ') ', SUBSTRING(b.numero_chip2,3,4), '-', SUBSTRING(b.numero_chip2,7,4)) as nr_chip2
														from bem b where b.cliente = $codigoCliente 
														order by id");
									
									if (mysql_num_rows($res) == 0) {
										echo "<tr><td colspan='5'><b id='alertNenhumVeiculo'>Nenhum veículo encontrado.</b></td> </tr>";
									} else {
									
										  echo "<tr>
													<td>Número imei</td>
													<td>Nome no menu</td>
													<td>Identificação</td>
													<td>Nº Chip <span class='dicaCadastro'><br/>ex.: (11) 9876-4321</span></td>
													<td>Operadora</td>
													<td>Nº Chip2<span class='dicaCadastro'><br/>ex.: (11) 9876-4321</span></td>
													<td>Operadora2</td>											
													<td><a href='../server/list.html' style='color:#0099FF' target='_blank'>Icones</a></td>												
													<td>Ativo?</td>
													<td><img src='../shared-modules/assets/images/salvar_todos.gif' title='Salvar todos' alt='Salvar todos' onclick='salvarTodos();' /></td>
													<td>Excluir</td>
												</tr>";
									}
									
									function retornaOperadora($idOp) 
									{
										//Obtendo a operadora do chip
										GLOBAL $semOp, $tim, $claro, $vivo, $oi, $telemig, $sercomtel, $ctbc, $brasiltelecom, $amazonia;
										switch($idOp)
										{
											case "TI": $tim = "selected"; break;
											case "CL": $claro = "selected"; break;
											case "VI": $vivo = "selected"; break;
											case "OI": $oi = "selected"; break;
											case "TM": $telemig = "selected"; break;
											case "SE": $sercomtel = "selected"; break;
											case "CT": $ctbc = "selected"; break;
											case "BT": $brasiltelecom = "selected"; break;
											case "AM": $amazonia = "selected"; break;
											default : $semOp = "selected";
										}	
									
									}
									
									$semOp = $tim = $claro = $vivo = $oi = $telemig = $sercomtel = $ctbc = $brasiltelecom = $amazonia = "";
									
									$maxId = 0;
									for ($i=0; $i < mysql_num_rows($res); $i++) {
										$row = mysql_fetch_assoc($res);
										
										if ($maxId < (int)$row[id]) 
										{
											$maxId = (int)$row[id];
										}
										
										echo "<tr id='linhaBemCliente". $row[id] ."'>";
											echo "<td><input maxlength='15' size='17' id='listaImei". $row[id] ."' name='listaImei". $row[id] ."' type='text' value='". $row[imei] ."' class='campoNovoVeiculo' />
											          <input maxlength='15' size='17' id='listaImeiHidden". $row[id] ."' name='listaImeiHidden". $row[id] ."' type='hidden' value='". $row[imei] ."' />
													  <input maxlength='15' id='listaIdBemHidden". $row[id] ."' name='listaIdBemHidden". $row[id] ."' type='hidden' value='". $row[id] ."' />
											      </td>";
											echo "<td><input id='listaNome". $row[id] ."' name='listaNome". $row[id] ."' type='text' value='". $row[name] ."' class='campoNovoVeiculo' /></td>";
											echo "<td><input id='listaIdent". $row[id] ."' name='listaIdent". $row[id] ."' type='text' value='". $row[identificacao] ."' class='campoNovoVeiculo' /></td>";
											echo "<td><input id='listaChip". $row[id] ."' name='listaChip". $row[id] ."' type='text' value='". $row[nr_chip] ."' maxlength='14' class='campoNovoVeiculo' onkeypress=\"return txtBoxFormat(this, '(99) 9999-9999', event);\" onblur=\" if (this.value != '') { return txtBoxFormat(this, '(99) 9999-9999', event);} \" size='11' /></td>";
											
											retornaOperadora($row[operadora_chip]);
											
											echo "<td><select id='listaOperadora". $row[id] ."' name='listaOperadora". $row[id] ."' class='campoNovoVeiculo' >";
													echo "<option value='' ". $semOp .">--Selecione--</option>";
													echo "<option value='TI' ". $tim .">Tim</option>";
													echo "<option value='CL' ". $claro .">Claro</option>";
													echo "<option value='VI' ". $vivo .">Vivo</option>";
													echo "<option value='OI' ". $oi .">Oi</option>";
													//echo "<option value='TM' ". $telemig .">Telemig</option>";
													//echo "<option value='SE' ". $sercomtel .">Sercomtel</option>";
													echo "<option value='CT' ". $ctbc .">CTBC</option>";
													echo "<option value='BT' ". $brasiltelecom .">Brasil Telecom</option>";
													echo "<option value='AM' ". $amazonia .">Amazonia Celular</option>";
												echo "</select>";
											echo "</td>";
											
											echo "<td><input id='lista2Chip". $row[id] ."' name='lista2Chip". $row[id] ."' type='text' value='". $row[nr_chip2] ."' maxlength='14' class='campoNovoVeiculo' onkeypress=\"return txtBoxFormat(this, '(99) 9999-9999', event);\" onblur=\" if (this.value != '') { return txtBoxFormat(this, '(99) 9999-9999', event);} \" size='11' /></td>";
											
											retornaOperadora($row[operadora_chip2]);
											
											echo "<td><select id='lista2Operadora". $row[id] ."' name='lista2Operadora". $row[id] ."' class='campoNovoVeiculo' >";
													echo "<option value='' ". $semOp .">--Selecione--</option>";
													echo "<option value='TI' ". $tim .">Tim</option>";
													echo "<option value='CL' ". $claro .">Claro</option>";
													echo "<option value='VI' ". $vivo .">Vivo</option>";
													echo "<option value='OI' ". $oi .">Oi</option>";
													//echo "<option value='TM' ". $telemig .">Telemig</option>";
													//echo "<option value='SE' ". $sercomtel .">Sercomtel</option>";
													echo "<option value='CT' ". $ctbc .">CTBC</option>";
													echo "<option value='BT' ". $brasiltelecom .">Brasil Telecom</option>";
													echo "<option value='AM' ". $amazonia .">Amazonia Celular</option>";
												echo "</select>";
											echo "</td>";
											
											
											echo "<td><input id='listaCor". $row[id] ."' name='listaCor". $row[id] ."' type='text' value='". $row[cor_grafico] ."' class='campoNovoVeiculo' maxlength='6' size='6' style='background-color: #". $row[cor_grafico] ."' onblur='this.value=this.value.toUpperCase();' /></td>";
											echo "<td><select id='listaAtivo". $row[id] ."' name='listaAtivo". $row[id] ."' class='campoNovoVeiculo'>";
												if ($row[activated] == 'S') {
													echo "<option selected value='S'>Sim</option>
														  <option value='N'>Não</option>";
												} else {
													echo "<option value='S'>Sim</option>
														  <option selected value='N'>Não</option>";
												}
												echo "</select>";
											echo "</td>";
											echo "<td> <div style='width:40px'>";
													echo "<img src='../shared-modules/assets/images/salvar.png' title='Salvar alteração' alt='Salvar alteração' onclick='alterarVeiculoAdmin(". $row[id] .");' /> ";
													echo "<img id='imgExecutando". $row[id] ."' style='display:none' src='../shared-modules/assets/images/executando.gif' title='Executando...' alt='Executando...' />";
													echo "<img id='imgSucesso". $row[id] ."' style='display:none' src='../shared-modules/assets/images/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
											echo "</div></td>";
											echo "<td> <div style='width:40px'>
													<a href='javascript:void(0);'><img border=0 id='imgExcluirBem". $row[id] ."' src='../shared-modules/assets/images/lixeira.png' title='Excluir item' alt='Excluir item' onclick='excluirBemUsuario(". $row[id] .")' /><a>
																				 <span style='font-size:10px'><input name='CheckboxExcluirHist". $row[id] ."' type='checkbox' id='ckbExcluirHistorico". $row[id] ."' /><label for='ckbExcluirHistorico". $row[id] ."' style='cursor:pointer'>histórico</label></span>
																				  <img border=0 id='imgExcluindo". $row[id] ."' style='display:none' src='../shared-modules/assets/images/executando.gif' title='Executando...' alt='Executando...' />
												  </div></td>";
										echo "</tr>";
									}
									
									echo "
										<script language='JavaScript'>
											totalVeiculos = ". $maxId .";
										</script>
										";
									?>									
									</table>
									<br />
									<img src='../shared-modules/assets/images/btnNovoVeiculo.png' title='Adicionar novo veículo' alt='Adicionar novo veículo' onclick='adicionarNovaLinhaVeiculos();' /> 
									</form>
								</div>
							</div>
							
							
							<div id="div_sub_comandos" class="conteudo" style="display:block; border-right: 1px solid #000000; border-right-color: #CCCCCC;">
								<div>
								
									<form name="listaComandosUsuarios" method="post" action="admin.php">
									<table cellspacing="6" cellpadding="0" id="tabelaComandos">
										<tr>
											<td colspan="5">
												Selecione os comandos para atribuir ao usuário
												<br />
											</td>
										</tr>
									<?php 
									//Comandos por usuário
									$resCmd = mysql_query("select * from command_cliente where cliente = $codigoCliente limit 1");
									
									$comando1; $comando2; $comando3; $comando4; $comando5; $comando6; $comando7; $comando8; $comando9; $comando10; $comando11; 

									for ($i=0; $i < mysql_num_rows($resCmd); $i++) {
										$rowCmd = mysql_fetch_assoc($resCmd);
										
											$comando1 = $rowCmd[comando1];
											$comando2 = $rowCmd[comando2];
											$comando3 = $rowCmd[comando3];
											$comando4 = $rowCmd[comando4];
											$comando5 = $rowCmd[comando5];
											$comando6 = $rowCmd[comando6];
											$comando7 = $rowCmd[comando7];
											$comando8 = $rowCmd[comando8];
											$comando9 = $rowCmd[comando9];
											$comando10 = $rowCmd[comando10];
											$comando11 = $rowCmd[comando11];
									}
									
									?>
									<tr>
										<td colspan="5">
										<span style="font-size:12px; color:black;">
										<input name="ckTodosComandos" id="ckTodosComandos" type="checkbox" onclick="marcarTodosComandos();" /><label for="ckCTodos">Todos</label><br/><br/>
										
										<input name="ckComando1" id="ckComando1" type="checkbox" <?php if ($comando1==1) { echo 'checked="checked"'; } ?> /><label for="ckComando1">Ativa Bloqueio Audível</label><br/>
										<input name="ckComando2" id="ckComando2" type="checkbox" <?php if ($comando2==1) { echo 'checked="checked"'; } ?> /><label for="ckComando2">Ativa Bloqueio Silencioso</label><br/>
										<input name="ckComando3" id="ckComando3" type="checkbox" <?php if ($comando3==1) { echo 'checked="checked"'; } ?> /><label for="ckComando3">Desativa Bloqueio</label><br/>
										<input name="ckComando4" id="ckComando4" type="checkbox" <?php if ($comando4==1) { echo 'checked="checked"'; } ?> /><label for="ckComando4">Ativa Pânico (Sirene)</label><br/>
										<input name="ckComando5" id="ckComando5" type="checkbox" <?php if ($comando5==1) { echo 'checked="checked"'; } ?> /><label for="ckComando5">Ativa saída 1</label><br/>
										<input name="ckComando6" id="ckComando6" type="checkbox" <?php if ($comando6==1) { echo 'checked="checked"'; } ?> /><label for="ckComando6">Ativa saída 2</label><br/>
										<input name="ckComando7" id="ckComando7" type="checkbox" <?php if ($comando7==1) { echo 'checked="checked"'; } ?> /><label for="ckComando7">Ativa saída 3</label><br/>
										<input name="ckComando8" id="ckComando8" type="checkbox" <?php if ($comando8==1) { echo 'checked="checked"'; } ?> /><label for="ckComando8">Reset GPS</label><br/>
										<input name="ckComando9" id="ckComando9" type="checkbox" <?php if ($comando9==1) { echo 'checked="checked"'; } ?> /><label for="ckComando9">Reset GSM</label><br/>
										<input name="ckComando10" id="ckComando10" type="checkbox" <?php if ($comando10==1) { echo 'checked="checked"'; } ?> /><label for="ckComando10">Clear Memória Externa HT24</label><br/>
										<input name="ckComando11" id="ckComando11" type="checkbox" <?php if ($comando11==1) { echo 'checked="checked"'; } ?> /><label for="ckComando11">Reset Senha (Senha Fábrica 1234)</label><br/>
										</span>
										</td>
									</tr>

									</table>
									<br />
									<input name="btnCadastrar" type="submit" value="Atribuir comandos" class="btnAcao" onclick="atribuirComandos(<?php echo (int)$codigoCliente ?>); return false;" />&nbsp;&nbsp;
									<span id='imgComandosCliente' style='display:none'><img src='../shared-modules/assets/images/executando.gif' title='Executando...' alt='Executando...' />Atribuindo...</span>
									<img id='imgComandosClienteSucesso' style='display:none' src='../shared-modules/assets/images/sucesso.png' title='Comandos atribuidos' alt='Comandos atribuidos' />									
									<br />
									<span style="color:red">Os comandos só serão aplicados no próximo login do usuário</span>
									</form>
								</div>
							</div>
							
						</td>
					</tr>
					</table>
					<?php } ?>
				</div>
			</div>

			<div id="div_consulta" class="conteudo" style="display: none;">
				<div>
					Listagem e Alteração de usuários <br />
					
					<form name="listaTodosUsuarios" method="post" action="">
					<br />
					<input maxlength='20' size='20' id="campoPesquisa" name="campoPesquisa" type='text' class='campoNovoVeiculo' />
					<input name="btnPesquisar" type="button" value="Pesquisar" class="btnAcao" onclick="return false;" />
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 
						//Montando listagem
						$resUsu = mysql_query("select CAST(c.id AS DECIMAL(10,0)) as id, c.id as codCliente, c.email, c.nome, c.ativo, c.apelido, 
													(select count(*) from bem where cliente = c.id) as qtFrota 
											  from cliente c 
											  where c.master = 'N'
											  order by id");
						
						if (mysql_num_rows($resUsu) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum item encontrado.</b></td> </tr>";
						} else {
							  echo "<tr>
										<td>Código</td>
										<td>e-mail</td>
										<td>Login</td>
										<td>Nome do cliente</td>
										<td>Ativo?</td>
										<td>Acessar</td>
										<td>Frota</td>
										<td>Salvar</td>
										<td>Excluir</td>
									</tr>";
						}
						
						for ($i=0; $i < mysql_num_rows($resUsu); $i++) {
							$rowUsu = mysql_fetch_assoc($resUsu);
						
							echo "<tr id='linhaContaCliente". $rowUsu[id] ."'>";
								echo "<td><input disabled maxlength='10' size='12' id='listaCodigoCliente". $rowUsu[id] ."' name='listaCodigoCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[codCliente] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input disabled id='listaEmailCliente". $rowUsu[id] ."' name='listaEmailCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[email] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input disabled size='15' id='listaLoginCliente". $rowUsu[id] ."' name='listaLoginCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[apelido] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input size='35' id='listaNomeCliente". $rowUsu[id] ."' name='listaNomeCliente". $rowUsu[id] ."' type='text' value='". $rowUsu[nome] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><select id='listaAtivoCliente". $rowUsu[id] ."' name='listaAtivoCliente". $rowUsu[id] ."' class='campoNovoVeiculo'>";
									if ($rowUsu[ativo] == 'S') {
										echo "<option selected value='S'>Sim</option>
											  <option value='N'>Não</option>";
									} else {
										echo "<option value='S'>Sim</option>
											  <option selected value='N'>Não</option>";
									}
									echo "</select>";
								echo "</td>";
								echo "<td valign='top'> <div style='width:46px'> <a href='/default.php?user=". $rowUsu[id] ."&admin=true' target='_top'> <img border=0 src='../shared-modules/assets/images/admin.png' style='height:25px' title='Acessar conta do cliente' alt='Acessar conta do cliente'/> </a></div></td>";
								echo "<td valign='top' style='color:black;font-weight:bold'> <div style='width:46px'> <a href='javascript:void(0);'> <img border=0 src='../shared-modules/assets/images/frota.gif' style='height:25px' title='Frota do cliente' alt='Frota do cliente' onclick='abrirFrotaCliente(". $rowUsu[id] .");' /> </a> <sup>". $rowUsu[qtFrota] ."</sup></div></td>";

								echo "<td> <div style='width:40px'>";
										echo "<img src='../shared-modules/assets/images/salvar.png' title='Salvar dados' alt='Salvar dados' onclick='salvarUsuarioAdmin(". $rowUsu[id] .");' /> ";
										echo "<img id='imgExecutandoCliente". $rowUsu[id] ."' style='display:none' src='../shared-modules/assets/images/executando.gif' title='Executando...' alt='Executando...' />";
										echo "<img id='imgSucessoCliente". $rowUsu[id] ."' style='display:none' src='../shared-modules/assets/images/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
								echo "</div></td>";
								echo "<td> <div style='width:40px'>";
									echo "<a href='javascript:void(0);'><img border=0 src='../shared-modules/assets/images/lixeira.png' title='Excluir conta' alt='Excluir conta' onclick='excluirUsuarioAdmin(". $rowUsu[id] .");' /></a>";
									echo "<img id='imgExcluindoCliente". $rowUsu[id] ."' style='display:none' src='../shared-modules/assets/images/executando.gif' title='Executando...' alt='Executando...' />";
								echo "</div></td>";
							echo "</tr>";
						}
						?>
					</table>
					</form>
				</div>
			</div>
			
			<div id="div_manutencao" class="conteudo" style="display: none">
				Listagem de pagamentos <br />
				
					<form name="listaPagamentosUsuarios" method="post" action="">
					<br />
					<div>
					<input maxlength='20' size='20' id="campoPesquisa" name="campoPesquisa" type='text' class='campoNovoVeiculo' />
					<input name="btnPesquisarPgtos" type="button" value="Pesquisar" class="btnAcao" onclick="return false;" />
					</div>
					<div style="float:right; margin-right:20px">
						<table cellspacing="6" cellpadding="0" style="font-size: 12px;">
							<tr>
								<td><b>Legenda ícones de pagamento</b></td>
							</tr>
							<tr>
								<td><img alt='Sem registro de pagamento' title='Sem registro de pagamento' src='../shared-modules/assets/images/registra_pgto.gif' /> Sem registro de pagamento</td>
							</tr>
							<tr>
								<td><img alt='Pagamento confirmado' title='Pagamento confirmado' src='../shared-modules/assets/images/pagou.gif' /> Pagamento confirmado</td>
							</tr>
							<tr>
								<td><img alt='Sem pagamento' title='Sem pagamento' src='../shared-modules/assets/images/sem_pagamento.gif' /> Sem pagamento</td>
							</tr>
						</table>
					</div>
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 
						//Montando listagem
						$resPag = mysql_query("select CAST(c.id AS DECIMAL(10,0)) as id,
													  c.id as codCliente,
													  c.email,
													  c.nome,
													  c.ativo,
													  c.observacao,
													  p.*
													  from cliente c left join pagamento p on (c.id = p.cliente) 
													  where c.ativo = 'S' and c.master = 'N' and
														   (c.data_inativacao is null or c.data_inativacao >= CURDATE())
													  order by id");
						if (mysql_num_rows($resPag) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum item encontrado.</b></td> </tr>";
						} else {
							  echo "<tr>
										<td>Código</td>
										<td>e-mail</td>
										<td>Nome do cliente</td>
										<td>Ativo?</td>
										<td></td>
									</tr>";
						}
						
						/** Retorna a imagem de pagamento*/
						function obterImagemPagamento($flPagamento)
						{
							$imgPagamento = "";
							
							switch($flPagamento)
							{
								//F=falta informar; N=Nao pagou;S=pagou
								case "F": $imgPagamento = "registra_pgto.gif"; break;
								case "N": $imgPagamento = "sem_pagamento.gif"; break;
								case "S": $imgPagamento = "pagou.gif"; break;
								
								default: $imgPagamento = "registra_pgto.gif";
							}	

							return $imgPagamento;
						}
						
						for ($i=0; $i < mysql_num_rows($resPag); $i++) {
							$rowPag = mysql_fetch_assoc($resPag);
						
						  echo "<tr id='linhaContaPagtoCliente". $rowPag[id] ."'><td colspan='5'>";
						  echo "<table>";
						
							echo "<tr>";
								echo "<td><input disabled maxlength='10' size='12' id='listaCodigoClientePgto". $rowPag[id] ."' name='listaCodigoClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[codCliente] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input disabled id='listaEmailClientePgto". $rowPag[id] ."' name='listaEmailClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[email] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input size='35' id='listaNomeClientePgto". $rowPag[id] ."' name='listaNomeClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[nome] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><select id='listaAtivoClientePgto". $rowPag[id] ."' name='listaAtivoClientePgto". $rowPag[id] ."' class='campoNovoVeiculo'>";
									if ($rowPag[ativo] == 'S') {
										echo "<option selected value='S'>Sim</option>
											  <option value='N'>Não</option>";
									} else {
										echo "<option value='S'>Sim</option>
											  <option selected value='N'>Não</option>";
									}
									echo "</select>";
								echo "</td>";								
								//echo "<td><img src='../shared-modules/assets/images/frota.gif' style='height:25px' title='Frota do cliente' alt='Frota do cliente' onclick='abrirFrotaCliente(". $rowPag[id] .");' /> ";

								echo "<td> <div style='width:40px'>";
										echo "<img src='../shared-modules/assets/images/salvar.png' title='Salvar dados' alt='Salvar dados' onclick='salvarUsuarioAdminPgto(". $rowPag[id] .");' /> ";
										echo "<img id='imgExecutandoClientePgto". $rowPag[id] ."' style='display:none' src='../shared-modules/assets/images/executando.gif' title='Executando...' alt='Executando...' />";
										echo "<img id='imgSucessoClientePgto". $rowPag[id] ."' style='display:none' src='../shared-modules/assets/images/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
								echo "</div></td>";
							echo "</tr>";
							echo "<tr>";
								echo "
									<td colspan='5'>
										Obs.: <input size='82' id='listaObsClientePgto". $rowPag[id] ."' name='listaObsClientePgto". $rowPag[id] ."' type='text' value='". $rowPag[observacao] ."' class='campoNovoVeiculo' />
									</td>
								";
							echo "</tr>";
							echo "<tr>";
								echo "<td colspan='2'>
								
									<table style='width: 100%;text-align:center;font-size:xx-small'>
										<tr>
											<td>jan</td>
											<td>fev</td>
											<td>mar</td>
											<td>abr</td>
											<td>mai</td>
											<td>jun</td>
											<td>jul</td>
											<td>ago</td>
											<td>set</td>
											<td>out</td>
											<td>nov</td>
											<td>dez</td>
										</tr>
										<tr>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[jane]) ."' id='imgRegistraPagto1". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(1, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[feve]) ."' id='imgRegistraPagto2". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(2, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[marc]) ."' id='imgRegistraPagto3". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(3, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[abri]) ."' id='imgRegistraPagto4". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(4, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[maio]) ."' id='imgRegistraPagto5". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(5, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[junh]) ."' id='imgRegistraPagto6". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(6, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[julh]) ."' id='imgRegistraPagto7". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(7, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[agos]) ."' id='imgRegistraPagto8". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(8, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[sete]) ."' id='imgRegistraPagto9". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(9, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[outu]) ."' id='imgRegistraPagto10". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(10, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[nove]) ."' id='imgRegistraPagto11". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(11, ". $rowPag[id] .", this)' /></td>
											<td><img alt='Registrar pagamento' title='Registrar pagamento' src='../shared-modules/assets/images/". obterImagemPagamento($rowPag[deze]) ."' id='imgRegistraPagto12". $rowPag[id] ."' onclick='registrarPagamentoMesAdmin(12, ". $rowPag[id] .", this)' /></td>
										</tr>
									</table>
								<br />
								</td>";
							echo "<td colspan='3'><span class='dicaCadastro'>Dica: Clique no ícone referente ao mês para registrar o pagamento</span></td>";
							echo "</tr>";
							
						  echo "</table>";
						  echo "</td></tr>";							
						}
						?>
					</table>
					</form>
				
			</div>
			
			
			<div id="div_alertas" class="conteudo" style="display: none;">
				<div>
					Alertas enviandos pelo GPS  
					<span style="font-size:10px">
						<i><span id="data_checa_alerta"></span></i>
						<!--a href='admin.php?aba=alertas' style='color:#0099FF'>atualizar agora</a-->
					</span> 
					<br />
					
					
					<form name="listaAlertas" method="post" action="">
					<div id="alertas_grid">
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>
							<tr>
								<th valign="top">Tipo de alerta</th>
								<th valign="bottom" rowspan="1" class="divisorLog"></th>
								<th valign="top">Veículo <br/></th>
								<th valign="bottom" rowspan="1" class="divisorLog"></th>
								<th valign="top">Hora / Data <br/></th>
								<th valign="bottom" rowspan="1" class="divisorLog"></th>
								<th valign="top">Acesso<br/></th>
							</tr>
							
							<?php
								//Veja o arquivo checar_alertas_admin.php
								echo "<tr>";
									echo "<td colspan='7'><i>Carregando alertas, aguarde...</i></td>";
								echo "</tr>";
							?>
					</table>
					</div>
					</form>
					<br />
					<!--a href="mapa.php" style="color:#0099FF">Voltar</a-->
				</div>
			</div>

			
		</td>
	</tr>
</table>
<?php
	mysql_close($cnx);
?>
<br /><br /><br />
</body>
</html>

