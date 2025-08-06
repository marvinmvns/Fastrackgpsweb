<?php /*include('../../shared-modules/config/seguranca.php');*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Cadastro de Usuários</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

$sucesso = null;
$email = $_POST['email'];
$senha = $_POST['senha'];

//Conectando
$cnx = mysql_connect("localhost", "admin123", "admin123")
  or die("Could not connect: " . mysql_error());
mysql_select_db(tracker, $cnx);

if ($email != null and $senha != null) {
	
	if (strpos($email, "@") === false) 
	{
		$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Digite um e-mail.</span>";
	}
	else 
	{	
		if (!mysql_query("INSERT INTO alerts (imei, responsible, password) VALUES ('000000000000000', '$email', '". md5($senha) ."')", $cnx))
		{
			// Se der erro, envia alerta que houve falha
			if (mysql_error() == "Duplicate entry '". $email ."' for key 'imei'")
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Usuário já existe!</span>";
			else
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";			
			//die('Error: ' . mysql_error());
		}
		else
		{
			$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Cadastrado com sucesso!</span>";
		}
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
	width:10%;
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

</style>
<script type="text/javascript">

	function stAba(menu,conteudo)
	{
		this.menu = menu;
		this.conteudo = conteudo;
	}

	var arAbas = new Array();
	arAbas[0] = new stAba('td_cadastro','div_cadastro');
	//arAbas[1] = new stAba('td_consulta','div_consulta');
	//arAbas[2] = new stAba('td_manutencao','div_manutencao');

	function AlternarAbas(menu,conteudo)
	{
		for (i=0;i<arAbas.length;i++)
		{
			m = document.getElementById(arAbas[i].menu);
			m.className = 'menu';
			c = document.getElementById(arAbas[i].conteudo)
			c.style.display = 'none';
		}
		m = document.getElementById(menu)
		m.className = 'menu-sel';
		
		c = document.getElementById(conteudo)
		c.style.display = '';
		if (conteudo == 'div_cadastro')
			c.style.height = document.body.parentNode.clientHeight - 145 + "px";
	}
	
	function esconderAlerta() {
		try
		  {
		  	var existeSpan = document.getElementById('alertaCadastro');
		  	existeSpan.style.display='none';
		  }
		catch(err)
		  {
			  //Abafo se o campo não existir
		  }
  	}
  	
</script>

</head>

<body onLoad="AlternarAbas('td_cadastro','div_cadastro'); setTimeout('esconderAlerta()', 10000); " 
	style=" background-position: right bottom; height:auto; border-left:thin; border-left-style: solid; 
			border-left-width: 1px; border-left-color: #CCCCCC; margin-left:0px; margin-top:-17px; 
			background-image:url('imagens/fundo_logo_webarch.png'); background-repeat: no-repeat;">

<h2 align="center" style="font-size:20px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #666666;">Usuário do Sistema</h2>
<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border-left: 1px solid #000000; border-left-color: #CCCCCC;" align="center">

	<tr>
		<td width="100" class="menu" id="td_cadastro"
		onclick="AlternarAbas('td_cadastro','div_cadastro')" style="height: 7px">
			Cadastro
		</td>
		<!--td width="100" class="menu" id="td_consulta"
		onclick="AlternarAbas('td_consulta','div_consulta')" style="height: 7px">
			Consulta
		</td-->
		<!--td width="100" class="menu" id="td_manutencao"
		onclick="AlternarAbas('td_manutencao','div_manutencao')" style="height: 7px">
			Manutenção
		</td-->
		<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">
			&nbsp;</td>
		<td style="height: 7px"></td>
	</tr>
	<tr>
		<td class="tb-conteudo" colspan="4">
			<div id="div_cadastro" class="conteudo" style="display:block;">
				<div>
					Adicione um usuário <br />

					<form name="novoUsuario" method="post" action="usuarios.php" autocomplete="off">
						<table style="width: 70%" cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="2">
									<?php echo $sucesso ?>
									<br />
								</td>
							</tr>						
							<tr>
								<td class="textoEsquerda">E-mail:</td>
								<td><input name="email" maxlength="45" size="25" type="text" class="campoNovoVeiculo" />
									<span class="dicaCadastro"></span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Senha:</td>
								<td><input name="senha" maxlength="20" size="25" type="password" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Senha para entrar no sistema de rastreamento</span>
								</td>
							</tr>
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td>
								<input name="btnCadastrar" type="submit" value="Cadastrar" class="btnAcao" /></td>
								<td><a href="http://gps.empresa.com.br" style="color:#0099FF">Cancelar</a></td>
							</tr>
						</table>
					</form>
					
				</div>
			</div>

			<div id="div_consulta" class="conteudo" style="display: none;">
				<div>
					Listagem e Alteração de bens <br />
					
					<form name="listaBens" method="post" action="menu_novo_veiculo.php">
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 
						//Montando listagem - $cliente está na sessão
						$res = mysql_query("select * from bem where cliente = " . trim($cliente) . " order by name");
						
						if (mysql_num_rows($res) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum item encontrado.</b></td> </tr>";
						} else {
							  echo "<tr>
										<td>Número imei</td>
										<td>Nome</td>
										<td>Identificação</td>
										<td>Ativo?</td>
										<td></td>
										<td>Modo</td>
									</tr>";
						}
						
						for ($i=0; $i < mysql_num_rows($res); $i++) {
							$row = mysql_fetch_assoc($res);
						
							echo "<tr>";
								echo "<td><input maxlength='15' size='17' id='listaImei". $row[id] ."' name='listaImei". $row[id] ."' type='text' value='". $row[imei] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input id='listaNome". $row[id] ."' name='listaNome". $row[id] ."' type='text' value='". $row[name] ."' class='campoNovoVeiculo' /></td>";
								echo "<td><input id='listaIdent". $row[id] ."' name='listaIdent". $row[id] ."' type='text' value='". $row[identificacao] ."' class='campoNovoVeiculo' /></td>";
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
										echo "<img src='imagens/salvar.png' title='Salvar alteração' alt='Salvar alteração' onclick='alterarVeiculoPainel(". $row[id] .");' /> ";
										echo "<img id='imgSucesso". $row[id] ."' style='display:none' src='imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
								echo "</div></td>";
								echo "<td>";
									if ($row[modo_operacao] == 'SMS')
										echo "<span style='font-size:8px;color:black'> ". $row[modo_operacao] ." </span>";
									else
										echo "<span style='font-size:8px'> ". $row[modo_operacao] ." </span>";
								echo "<td>";
							echo "</tr>";
						}
						?>
					</table>
					</form>
				</div>
			</div>
			<!--div id="div_manutencao" class="conteudo" style="display: none">
				MANUTENÇÃO
			</div-->
		</td>
	</tr>
</table>
<?php
	mysql_close($cnx);
?>
<br /><br /><br />
</body>
</html>

