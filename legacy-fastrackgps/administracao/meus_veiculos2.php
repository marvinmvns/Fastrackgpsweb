<?php include('../../shared-modules/config/seguranca.php'); 
  

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Alteração de Bens</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

$sucesso = null;
$imei = $_POST['imei'];
$nomeGps = $_POST['nomeGps'];
$identificacao = $_POST['identificacao'];

//Conectando


if ($imei != null and $nomeGps != null) {

	if (!preg_match("/^([0-9]+)$/", $imei)) 
	{
		$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Digite apenas números para o imei.</span>";
	}
	else
	{
		if (strlen($imei) < 15) 
		{
			$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Número imei deve ter 15 números.</span>";
		}
		else 
		{	
			if (!mysql_query("INSERT INTO bem (imei, name, identificacao, cliente) VALUES ('$imei', '$nomeGps', '$identificacao', $cliente)", $cnx))
			{
				// Se der erro, envia alerta que houve falha
				if (mysql_error() == "Duplicate entry '". $imei ."' for key 'imei'")
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Número imei já existe!</span>";
				else
					$sucesso = "<span id=\"alertaCadastro\" style=\"color:red\">Falha no cadastro.</span>";			
				//die('Error: ' . mysql_error());
			}
			else
			{			
				$sucesso = "<span id=\"alertaCadastro\" style=\"color:black\">Cadastrado com sucesso!</span>";
				echo "<script language='JavaScript'>
							//Adicionando o novo item cadastrado ao menu
							var comboMenuVeiculos = parent.contents.document.getElementById(\"bens\");
							var oOption = document.createElement('option');
							oOption.text='$nomeGps';
							oOption.value=$imei;
							try {
								comboMenuVeiculos.options.add(oOption);
							} catch(err) {
								//Se nao tiver combo, nao existe bens ou estão inativados, reload no menu.
								parent.contents.window.location.href=parent.contents.window.location.href;
							}
					  </script>";
			}
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
<script type="text/javascript" src="../shared-modules/assets/js/alterarVeiculo.js"></script>
<script language="JavaScript">

	function stAba(menu,conteudo)
	{
		this.menu = menu;
		this.conteudo = conteudo;
	}

	var arAbas = new Array();
	arAbas[0] = new stAba('td_cadastro','div_cadastro');
	arAbas[1] = new stAba('td_consulta','div_consulta');
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
			background-image:url("../shared-modules/assets/images/fundo_logo_webarch.png'); background-repeat: no-repeat;">

<h2 align="center" style="font-size:20px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #666666;">Seus bens</h2>
<table width="80%" height="80%" cellspacing="0" cellpadding="0" border="0" style="border-left: 1px solid #000000; border-left-color: #CCCCCC;" align="center">

	<tr>
		<td width="100" class="menu" id="td_cadastro"
		onclick="AlternarAbas('td_cadastro','div_cadastro')" style="height: 7px">
			Cadastro
		</td>
		<td width="100" class="menu" id="td_consulta"
		onclick="AlternarAbas('td_consulta','div_consulta')" style="height: 7px">
			Consulta
		</td>
		<!--td width="100" class="menu" id="td_manutencao"
		onclick="AlternarAbas('td_manutencao','div_manutencao')" style="height: 7px">
			Manutenção
		</td-->
		<td style="border-bottom: 1px solid #CCCCCC; height: 7px;">&nbsp;
			</td>
		<td style="height: 7px"></td>
	</tr>
	<tr>
		<td class="tb-conteudo" colspan="4">
			<div id="div_cadastro" class="conteudo" style="display:block;">
				<div>
					Adicione um novo bem <br />
					
							<br />

						<!--span style="font-size:11px">Não lembra os comandos? Use o <a href="configuracao/" target="_top" style="color:#0099FF">Configurador</a></span> <img src="../shared-modules/assets/images/novidade.png" /> <br /!-->


					<form name="novoImei" method="post" action="menu_novo_veiculo.php" autocomplete="off">
						<table style="width: 70%" cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="2">
									<?php echo $sucesso ?>
									<br />
								</td>
							</tr>						
							<tr>
								<td class="textoEsquerda">Número imei:</td>
								<td><input name="imei" maxlength="15" size="17" type="text" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Número único de identificação do chip</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Nome:</td>
								<td><input name="nomeGps" maxlength="45" size="25" type="text" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Ford/Ka, Caminhão baú, João, etc</span>
								</td>
							</tr>
							<tr>
								<td class="textoEsquerda">Identificação:</td>
								<td><input name="identificacao" maxlength="20" size="25" type="text" class="campoNovoVeiculo" />
									<span class="dicaCadastro">Dica: Placa de carro, Cor, etc</span>
								</td>
							</tr>
							<tr>
								<td><br/></td>
								<td><br/></td>
							</tr>
							<tr>
								<td>
								<input name="btnCadastrar" type="submit" value="Cadastrar" class="btnAcao" /></td>
								<td><a href="mapa.php" style="color:#0099FF">Cancelar</a></td>
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
						
						include('../../shared-modules/config/seguranca.php');
						//Montando listagem - $cliente está na sessão
						$cnx = mysql_connect("localhost", "admin123", "admin123")
						or die("Could not connect: " . mysql_error());
						mysql_select_db("tracker2", $cnx);
															$res = mysql_query("select b.*,
														CONCAT('(', SUBSTRING(b.numero_chip,1,2), ') ', SUBSTRING(b.numero_chip,3,4), '-', SUBSTRING(b.numero_chip,7,4)) as nr_chip, 
														CONCAT('(', SUBSTRING(b.numero_chip2,1,2), ') ', SUBSTRING(b.numero_chip2,3,4), '-', SUBSTRING(b.numero_chip2,7,4)) as nr_chip2
														from bem b where b.cliente = $cliente 
														order by name");
									
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
													<td><a href='http://www.mxstudio.com.br/Conteudos/Dreamweaver/Cores.htm' style='color:#0099FF' target='_blank'>Gráfico</a></td>
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
													echo "<img src='../shared-modules/assets/images/salvar.png' title='Salvar alteração' alt='Salvar alteração' onclick='alterarVeiculoPainel(". $row[id] .");' /> ";
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
					</form>
					<br />
					<a href="mapa.php" style="color:#0099FF">Cancelar</a>
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