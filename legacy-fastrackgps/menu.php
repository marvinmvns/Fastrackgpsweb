<?php include('../shared-modules/config/seguranca.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Menu GPS</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="../shared-modules/assets/js/prototype.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/effects.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/accordion.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/brwsniff.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/checarAlertas.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/cancelarComando.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/alterarVeiculo2.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/enviarComando.js"></script>
<script type="text/javascript" src="../shared-modules/assets/js/verificarStatusSinalGPS.js"></script>
<link rel="stylesheet" type="text/css" href="../shared-modules/assets/css/menu.css" />
<script type="text/javascript">

		// Detectando browser usado e alterando tamanho do frame main
		var br=new Array(4); br=getBrowser(); //alert(br[0]);
		var frameset = parent.document.getElementById("mainFrame");
		if (br[0] == 'firefox') {
			frameset.rows = "89,*,153";
		} else {
			if (br[0] == 'msie') {
				frameset.rows = "88,*,153";
			}
		}
			
		//  In my case I want to load them onload, this is how you do it!
		Event.observe(window, 'load', loadAccordions, false);
	
		//	Set up all accordions
		function loadAccordions() {
			
			var bottomAccordion = new accordion('vertical_container');
			
			// Open first one
			bottomAccordion.activate($$('#vertical_container .accordion_toggle')[0]);
		}
		
		function formataData(Campo, teclapres)
		{
			var tecla = teclapres.keyCode;
			var vr = new String(Campo.value);
			vr = vr.replace("/", "");
			vr = vr.replace("/", "");
			vr = vr.replace("/", "");
			tam = vr.length + 1;
			if (tecla != 8 && tecla != 8)
			{
				if (tam > 0 && tam < 2)
					Campo.value = vr.substr(0, 2) ;
				if (tam > 2 && tam < 4)
					Campo.value = vr.substr(0, 2) + '/' + vr.substr(2, 2);
				if (tam > 4 && tam < 7)
					Campo.value = vr.substr(0, 2) + '/' + vr.substr(2, 2) + '/' + vr.substr(4, 7);
			}
		}
		
</script>
<style type="text/css">
.accordion_toggle {
	border-left: 1px solid #CCCCCC;
	border-right: 1px solid #CCCCCC;
	border-bottom: 1px solid #CCCCCC;
	display: block;
	height: 30px;
	width: 260px;
	background: url('imagens/seta.gif') no-repeat 12px;
	padding: 0 0 0 20px;
	line-height: 30px;
	color: #ffffff;
	font-weight: bold;
	text-decoration: none;
	outline: none;
	font-size: 20px;
	color: #CCCCCC;
	cursor: pointer;
	margin: 0 0 0 0;
	font-family: Arial, Helvetica, sans-serif;
}
.accordion_toggle:hover {
	background-color: #F7F7F7;
}
.accordion_toggle_active {
	background: url('imagens/seta-on.gif') no-repeat 12px;
	color: #000000;
	border-bottom-style: none;
	border-bottom-width: 0px;
}
.accordion_toggle_red_active {
	border-color: #D8000C;
	border-style: solid;
	border-width: 1px;
	background: url('imagens/icon_alert.gif') no-repeat 12px;
	color: #D8000C;
	background-color: #FFBABA;
}
.accordion_toggle_active_yellow {
	background: url('imagens/seta-on.gif') no-repeat 12px;
	color: #000000;
	border-bottom-style: none;
	border-bottom-width: 0px;
}
.accordion_toggle_active:hover {
	background-color: #FFFFFF;
}
.accordion_content {
	background-color: #ffffff;
	color: #444444;
	overflow: hidden;
	margin-right: -2px;
	margin-left: 0px;
	border-right-style: solid;
	border-bottom-style: solid;
	border-left-style: solid;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-right-color: #CCCCCC;
	border-bottom-color: #CCCCCC;
	border-left-color: #CCCCCC;
	padding-left: 32px;
}
.accordion_content h2 {
	margin: 15px 0 5px 10px;
	color: #0099FF;
}
.accordion_content p {
	line-height: 150%;
	padding: 5px 10px 15px 10px;
}
.botaoBranco {
	background-color:#FFFFFF;
	border:1px solid #999999;
	color:#333333;
	font-family:Verdana;
	font-size:7pt;
	font-style:normal;
	font-weight:normal;
	text-decoration:none;
}
.spanComentarios {
	font-size: 9px;
	color: #6A6A6A;
}


.styled-select select {
   background: transparent;
   width: 268px;
   padding: 5px;
   font-size: 16px;
   line-height: 1;
   border: 0;
   border-radius: 0;
   height: 34px;
   -webkit-appearance: none;
   }


</style>
<base target="main" />
</head>

<body onload="verificarAlertas(); verificarStatusSinalGPS();">

<div id="imagens_status_veiculos" style="display:none">

<?php

$cnx = mysql_connect("localhost", "root", "suasenha") 
	  or die("Could not connect: " . mysql_error());
mysql_select_db('tracker2', $cnx);

$res = mysql_query("select imei, status_sinal from bem where cliente = $cliente");

	for($i=0; $i < mysql_num_rows($res); $i++) {
		$row = mysql_fetch_assoc($res);
		echo "<img id='img_status_sinal". $row[imei] ."' src='imagens/". imagenStatusSinal($row[status_sinal]) ."' border='0' />";
	}
	
/** Retorna a imagem do status do sinal */
function imagenStatusSinal($sgSinal)
{
	$imgSinal;
	
	switch($sgSinal)
	{
		case "R": $imgSinal = "status_rastreando.png"; break;
		case "S": $imgSinal = "status_sem_sinal.png"; break;
		case "D": $imgSinal = "status_desligado.png"; break;
	}

	return $imgSinal;
}	
		
mysql_close($cnx);
?>
</div>

<div id="container">
	<div id="vertical_container">
		<h1 class="accordion_toggle">&nbsp; Veículos <span id="spanCarroSelecionado" class="spanComentarios" style="color:#CCCCCC;"></span>
				<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/icon_menu_lista.png" border="0"></div>
			<img id="statusSinalGPS" name="statusSinalGPS" src="imagens/status_desligado.png" alt="Status do sinal" title="Status do sinal" border="0" style="display:none" />
		</h1>
		<div class="accordion_content" id="veiculos">
			<span id="spanBensDisponiveis" class="spanComentarios">Bens autorizados para monitoramento</span>
			<?php 
				include('menu_veiculos.php');
			?>
		</div>
		<h1 class="accordion_toggle">&nbsp; Comandos<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/icon_menu_comandos.png" border="0"></div></h1>
		<div class="accordion_content" id="comandos">
		
				<!-- TODO: Alterar forma de enviar comando. Atualmente é um form comum. Precisa evitar refresh no main/mapa. -->
				<form id="enviarComando" action="menu_comandos.php" method="post">
			    <input type="hidden" id="nrimei" name="imei" value="">
			    <span id="spanComandos" class="spanComentarios" style="display:block;">Para habilitar comandos selecione um veículo</span>		
				<table>
					<tr>
						<td>
							<select name="command" id="command" class="botaoBranco" disabled="true" 
								onchange="if (this.value=='powercar00') { 
											 document.getElementById('senha').style.display='block'; 
										  } else { 
										     document.getElementById('senha').style.display='none'; 
										  }
										  
										  if (this.value=='powercar11') {
										  	 document.getElementById('senha').style.display='block';
										  } else {
										  	 document.getElementById('senha').style.display='none';
										  }
										  ">
										  
								<?php

								//Administrador tem acesso a todos os comandos
								if ($master == "true")
								{
									$comando1 = 1;
									$comando2 = 1;
									$comando3 = 1;
									$comando4 = 1;
									$comando5 = 1;
									$comando6 = 1;
									$comando7 = 1;
									$comando8 = 1;
									$comando9 = 1;
									$comando10 = 1;
									$comando11 = 1;
								}
								else
								{
									$cnx = mysql_connect("localhost", "root", "suasenha") 
										  or die("Could not connect: " . mysql_error());
									mysql_select_db('tracker2', $cnx);

									$resCmd = mysql_query("select * from command_cliente where cliente = $cliente limit 1");
									
									$comando1; $comando2; $comando3; $comando4; $comando5; $comando6; $comando7; $comando8; $comando9; $comando10; $comando11; 

										for($i=0; $i < mysql_num_rows($resCmd); $i++) 
										{
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
										
									mysql_close($cnx);
								}
								
								$comando1 = 1;
								?>										  
										  
						
						     
						
						
							  <option selected value="">-- Selecione --</option>
							  <?php if ($comando1 == 1) { echo "<option value=''>-- TODOS --</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value='DESBLOQUEAR'>Habilitar Combustivel</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value='BLOQUEAR'>Cortar Combustivel</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=''>-- TK Genéricos --</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',L'>Armar</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',M'>Desarmar</option>"; }?>
						      <?php if ($comando1 == 1) { echo "<option value=',K'>Habilitar Combustivel</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',J'>Cortar Combustivel</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',T'>Modo Economico</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',E'>Cancelar Alertas</option>"; }?>
							  <?php if ($comando2 == 1) { echo "<option value=''>-- TK Xexum --</option>"; }?>
							  <?php if ($comando2 == 1) { echo "<option value=',powercar11'>Parar Veiculo</option>"; }?>
							  <?php if ($comando2 == 1) { echo "<option value=',powercar00'>Ativar Veiculo</option>"; }?>
							  <?php if ($comando2 == 1) { echo "<option value=',powercar11'>Parar Veiculo</option>"; }?>
							  <?php if ($comando2 == 1) { echo "<option value=',powercar00'>Ativar Veiculo</option>"; }?>
							  
							  <?php if ($comando1 == 1) { echo "<option value=''>-- OBD --</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',C,0'>Desabilitar OBD</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',C,1'>Solicitar multipla OBD</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',C,2'>Solicitar unica OBD</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',Q,20150411'>ENVIAR DADOS DO CARTAO</option>"; }?>
							  <?php if ($comando1 == 1) { echo "<option value=',I,-3'>Corrigir Hora</option>"; }?>
							  
							  <option value=",F,1000M">Rastrear a Cada 1KM</option>
							  <option value=",C,360s">Rastrear a Cada 5 M</option>
							  <option value=",H,060">Velocidade Limite</option>

						

							  
							  
							  
							  
							
							 
							</select>
						</td>
						<td>
							<select name="commandTime" id="commandTime" class="botaoBranco" style="display:none">
							  <option value=",C,30s">30s</option>
							  <option value=",C,01m">1m</option>
							  <option value=",C,05m">5m</option>
							  <option value=",C,10m">10m</option>
							  <option value=",C,30m">30m</option>
							  <option value=",C,01h">1h</option>
							  <option value=",C,05h">5h</option>
							  <option value=",C,10h">10h</option>
							</select>
						</td>
						<td>
							<span id="senha" style="font-size:xx-small;display:none"><input id="senha" name="senha" value="" maxlength="10" size="6" type="text" class="botaoBranco"></span>
						</td>
			    		<td><input type="submit" value="Enviar" id="btnEnviarComando" class="botaoBranco" disabled="true" onclick="return enviarComando();">
						</td>
			    		<td><a id="linkCancelarComando" href="javascript:void(0);" 
							onclick="this.style.display='none'; cancelarComando(document.getElementById('nrimei').value); return false;" 
							style="display:none;font-size:10px;" title="Cancelar um comando pendente"> cancelar</a>
						</td>
			    	</tr>
			    </table>
				</form>
			    <br />
		</div>

		
		
		<h1 class="accordion_toggle">&nbsp; Cerca Virtual<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/icon_menu_cerca.png" border="0"></div></h1>
		<div class="accordion_content" id="cerca">
			<span id="spanCercaVirtual" class="spanComentarios">Delimita uma área autorizada</span>
			<br />
			<?php
			$cnx = mysql_connect("localhost", "root", "suasenha")
				or die("Could not connect: " . mysql_error());
			mysql_select_db('tracker2', $cnx);

				$sql = "select * from bem where activated = 'S' and cliente = " . trim($cliente) . " order by name desc";
				$resultado = mysql_query($sql)
					or die (mysql_error());

				while ($data = mysql_fetch_assoc($resultado)) {
					$imei = $data["imei"];
					$name = $data["name"];

					echo "<a onclick=abrirHelp(); href='criar_cerca.php?imei=$imei'>$name</a><br />";
				}
				
			mysql_close($cnx);
			?>
			<span id="spanCercaVirtual" class="spanComentarios">Selecione para alterar ou excluir a cerca</span>
			<br />
			<?php include('menu_cerca_virtual.php'); ?>
			<br />
		</div>
		<h1 class="accordion_toggle">&nbsp; Histórico<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/icon_menu_historico.png" border="0"></div></h1>
		<div class="accordion_content" id="relatorios">
			<span id="spanHistorico" style="display:block;" class="spanComentarios">Para consultar histórico selecione um veículo</span>
				<form id="consultarHistorico" name="consultarHistorico" action="listagem_historico.php" method="post" target="bottom">
				<table width="266px" style="margin-left: -20px;">
					<tr>
						<td>
						<input type="hidden" id="nrImeiConsulta" name="nrImeiConsulta" value="">
						<span style="font-size:11px;">Início: </span>
						<input name="txtDataInicio" value="<?php echo date("d/m/Y") ?>" onkeyup="formataData(this,event)" type="text" class="botaoBranco" maxlength="10" size="12" style="height: 14px;" />
						<a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.consultarHistorico.txtDataInicio);return false;" hidefocus="">
							<img name="popcal" src="../shared-modules/assets/js/calendario/calbtn.jpg" alt="Selecione uma data" title="Selecione uma data" align="absmiddle" border="0" >
						</a>
							<select name="hrDataInicio" id="commandHourTimeIni" class="botaoBranco">
							  <option value="0" selected>00h</option>
							  <option value="1">01h</option>
							  <option value="2">02h</option>
							  <option value="3">03h</option>
							  <option value="4">04h</option>
							  <option value="5">05h</option>
							  <option value="6">06h</option>
							  <option value="7">07h</option>
							  <option value="8">08h</option>
							  <option value="9">09h</option>
							  <option value="10">10h</option>
							  <option value="11">11h</option>
							  <option value="12">12h</option>
							  <option value="13">13h</option>
							  <option value="14">14h</option>
							  <option value="15">15h</option>
							  <option value="16">16h</option>
							  <option value="17">17h</option>
							  <option value="18">18h</option>
							  <option value="19">19h</option>
							  <option value="20">20h</option>
							  <option value="21">21h</option>
							  <option value="22">22h</option>
							  <option value="23">23h</option>
							</select>
							<select name="mnDataInicio" id="commandMinuteTimeIni" class="botaoBranco">
								<option value="00" selected>00m</option>
								<option value="10">10m</option>
								<option value="15">15m</option>
								<option value="20">20m</option>
								<option value="25">25m</option>
								<option value="30">30m</option>
								<option value="35">35m</option>
								<option value="40">40m</option>
								<option value="45">45m</option>
								<option value="50">50m</option>
								<option value="55">55m</option>
								<option value="59">59m</option>
							</select>
							<!--span style="font-size:xx-small">a</span--> <br />
							<span style="font-size:11px;">&nbsp;Final:</span>
							<input name="txtDataFinal" value="<?php echo date("d/m/Y") ?>" onkeyup="formataData(this,event)" type="text" class="botaoBranco" maxlength="10" size="12" style="height: 14px"/>
							<a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fPopCalendar(document.consultarHistorico.txtDataFinal);return false;" hidefocus="">
								<img name="popcal" src="../shared-modules/assets/js/calendario/calbtn.jpg" alt="Selecione uma data" title="Selecione uma data" align="absmiddle" border="0" style="margin-left:1px">
							</a>							
							<select name="hrDataFinal" id="commandHourTimeFim" class="botaoBranco">
							  <option value="0">00h</option>
							  <option value="1">01h</option>
							  <option value="2">02h</option>
							  <option value="3">03h</option>
							  <option value="4">04h</option>
							  <option value="5">05h</option>
							  <option value="6">06h</option>
							  <option value="7">07h</option>
							  <option value="8">08h</option>
							  <option value="9">09h</option>
							  <option value="10">10h</option>
							  <option value="11">11h</option>
							  <option value="12">12h</option>
							  <option value="13">13h</option>
							  <option value="14">14h</option>
							  <option value="15">15h</option>
							  <option value="16">16h</option>
							  <option value="17">17h</option>
							  <option value="18">18h</option>
							  <option value="19">19h</option>
							  <option value="20">20h</option>
							  <option value="21">21h</option>
							  <option value="22">22h</option>
							  <option value="23" selected>23h</option>
							</select>
							<select name="mnDataFinal" id="commandMinuteTimeFim" class="botaoBranco">
								<option value="00">00m</option>
								<option value="10">10m</option>
								<option value="15">15m</option>
								<option value="20">20m</option>
								<option value="25">25m</option>
								<option value="30">30m</option>
								<option value="35">35m</option>
								<option value="40">40m</option>
								<option value="45">45m</option>
								<option value="50">50m</option>
								<option value="55">55m</option>
								<option value="59" selected>59m</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right"><input id="btnConsultar" name="btnConsultar" type="button" disabled="true" value="Consultar" class="botaoBranco" onclick="consultarHistoricoData();" /></td>
						<td></td>
					</tr>
				</table>
				</form>
				<br />
		</div>
		
		<h1 class="accordion_toggle">&nbsp; Pagamento<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/money-coin.png" border="0"></div></h1>
		<div class="accordion_content" id="estatisticas">
			<a onclick="abrirHelp();" href="pagamento/consulta_pgto.php">Consultar Situação Financeira</a></br></br></br>	
            		
		</div>	
		
		<h1 class="accordion_toggle">&nbsp; Estatísticas<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/icon_menu_estatistica.png" border="0"></div></h1>
		<div class="accordion_content" id="estatisticas">
			<span id="spanComandos" class="spanComentarios">Estatísticas do rastreamento</span>
			<p><a onclick="abrirHelp();" href="estatisticas/coordenadas_obtidas.php">Quantidade de coordenadas</a><br />
			</p>
		</div>		
		<h1 id="headerAlertas" class="accordion_toggle">&nbsp; Alertas<div style="position: relative;float:right;display:inline;padding-right:10px;width:16px;height:16px"><img src="imagens/icon_menu_alertas.png" border="0"></div></h1>
		<div class="accordion_content" id="alertas">
			<span id="spanComandos" class="spanComentarios">Exibe alertas emitido pelo gps</span>
			<p>Nenhum alerta.</p>
		</div>
	</div>
</div>
<script type="text/javascript">
	// You can hide the accordions on page load like this, it maintains accessibility
	// Special thanks go out to Will Shaver @ http://primedigit.com/
	var verticalAccordions = $$('.accordion_toggle');
	verticalAccordions.each(function(accordion) {
		$(accordion.next(0)).setStyle({
		  height: '0px'
		});
	});
</script>

<iframe name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="../shared-modules/assets/js/calendario/ipopeng.htm" style="visibility: visible; z-index: 999; position: absolute; left: -500px; top: 31px; width: 174px; height: 172px;" frameborder="0" height="189" scrolling="no" width="174"></iframe>

</body>

</html>
