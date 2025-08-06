<?php include('../shared-modules/config/seguranca.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Grid Registros Histórico</title>

<?php

$dataInicio = $_POST["txtDataInicio"];
$dataFinal = $_POST["txtDataFinal"];
$nrImeiConsulta = $_POST["nrImeiConsulta"];
$hrDataInicio = $_POST["hrDataInicio"];
$hrDataFinal = $_POST["hrDataFinal"];
$mnDataInicio = $_POST["mnDataInicio"];
$mnDataFinal = $_POST["mnDataFinal"];

?>

<base target="contents" />
</head>
<script type="text/javascript" src="../shared-modules/assets/js/comandarHistorico.js"></script>
<script language="JavaScript">
	function fecharHistorico() {
		var benSelecionado = parent.contents.document.getElementById('bens');
		var imei = benSelecionado.options[benSelecionado.selectedIndex].value;
	
		parent.bottom.location.href = 'listagem.html';
		parent.main.stop();
		parent.contents.refreshGridMapFecharHistorico(imei);
	}
	
	function imprimirHistorico() { 
	  var disp_setting="toolbar=no,location=no,directories=yes,menubar=yes,"; 
		  disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25"; 
	  
	  var dadosVeiculo = window.frames["gridHistorico"].document.getElementById("divDadosBem").innerHTML;
	  var divListagemRotas = window.frames["gridHistorico"].document.getElementById("divListagem").innerHTML;
	  
	  var docprint=window.open("","",disp_setting); 
	   docprint.document.open(); 
	   docprint.document.write('<html><head><title>Impressão de histórico - Rastreamento GPS - Loctrac</title>');
	   docprint.document.write('<link rel="stylesheet" type="text/css" href="../shared-modules/assets/css/historico.css" />');
	   docprint.document.write('</head><body onLoad="self.print()"><center>');
	   docprint.document.write(dadosVeiculo);
	   docprint.document.write('<br/>');
	   docprint.document.write(divListagemRotas);
	   docprint.document.write('</center></body></html>'); 
	   docprint.document.close(); 
	   docprint.focus(); 
	}
</script>
<style type="text/css">
.botaoBranco {
	background-color:#FFFFFF;
	border:1px solid #999999;
	color:#333333;
	font-family:Verdana;
	font-size:7pt;
	font-style:normal;
	font-weight:normal;
	text-decoration:none;
	padding:1px;
}
</style>

<body onload="document.getElementById('gridHistorico').height = parseInt(document.body.parentNode.clientHeight) - 12;" 
	  onresize="document.getElementById('gridHistorico').height = parseInt(document.body.parentNode.clientHeight) - 12;">
<center style="margin-top:-8px">
<input type="hidden" id="imeiHistorico" name="imeiHistorico" value="" />
<table width="100%" style="margin-top:0px" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td style="border: 1px solid #FFD0A6; width:100px; background-color:#FFEFD5; background-image:url('imagens/historico.jpg'); background-repeat: repeat-y;"></td>
		<td style="background-position: center center; background-image:url('imagens/carregando.gif'); background-repeat: no-repeat;">
			<iframe id="gridHistorico" name="gridHistorico" 
				src="listagem_historico_grid.php?dataInicio=<?php echo $dataInicio ?>
				&dataFinal=<?php echo $dataFinal ?>
				&nrImeiConsulta=<?php echo $nrImeiConsulta ?>
				&hrDataInicio=<?php echo $hrDataInicio ?>
				&mnDataInicio=<?php echo $mnDataInicio ?>
				&hrDataFinal=<?php echo $hrDataFinal ?>
				&mnDataFinal=<?php echo $mnDataFinal ?>" frameBorder="no" width="100% "height="141" scrolling="auto" allowtransparency="true" target="contents" style="margin-top:0px;"></iframe>
		</td>
		<td style="width:220px;vertical-align:top; border: 1px solid #FFD0A6; font-family:Arial, Helvetica, sans-serif" align="center">
			<span style="font-size:large; color:#CDAF95">Comandos de Histórico</span><br />
			<span style="font-size:x-small;color:#6A6A6A;padding:2px">Simule o passo-a-passo das rotas selecionadas com os comandos abaixo</span>
			<br />
			<div style="width:80%">
				<img id="playRotaHistorico" src="imagens/play_rota_historico.jpg" alt="Simular passo-a-passo" title="Play" onclick="pressPlay(this);" />
				<img id="pauseRotaHistorico" src="imagens/pause_rota_historico.jpg" alt="Pausar passo-a-passo" title="Pause" onclick="pressPause(this);" />
				<img id="stopRotaHistorico" src="imagens/stop_rota_historico.jpg" alt="Parar passo-a-passo" title="Stop" onclick="pressStop(this);"/>
			</div>
			<img id="imgExecutandoHistorico" style='display:inline' src='imagens/executando.gif' title='Carregando, aguarde...' alt='Carregando, aguarde...' />
			<span style="font-size:10px;color:#c0c0c0" id="spanComandoAcionado"> Carregando...</span>
			<br />
			<div style="width:100%">
				<table>
				<tr>
					<td>				
						<input name="btnFecharHistorico" type="button" value="Fechar histórico" alt="Fechar histórico" title="Fechar histórico" onclick="fecharHistorico();" class="botaoBranco" />
					</td>
					<td>
						<input name="btnImprimirHistorico" id="btnImprimirHistorico" type="button" value="Imprimir histórico" alt="Imprimir histórico" title="Imprimir histórico" onclick="imprimirHistorico();" class="botaoBranco" style="display:none" />
					</td>
				</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
</center>		
</body>
</html>