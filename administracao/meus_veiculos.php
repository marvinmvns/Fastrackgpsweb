<?php include('../seguranca.php');


								$cnx = mysql_connect("localhost", "admin123", "admin123")
								or die("Could not connect: " . mysql_error());
								mysql_select_db("tracker2", $cnx);

if ($acao == "obterUsuario") {
	if ($codigoCliente != null) {
	
		$resUsuario = mysql_query("select c.*,
									CONCAT('(', SUBSTRING(c.telefone1,1,2), ') ', SUBSTRING(c.telefone1,3,4), '-', SUBSTRING(c.telefone1,7,4)) as stelefone1,
									CONCAT('(', SUBSTRING(c.telefone2,1,2), ') ', SUBSTRING(c.telefone2,3,4), '-', SUBSTRING(c.telefone2,7,4)) as stelefone2
									from cliente c where c.id = '$cliente' ");
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



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Administração - Rastreamento GPS - FastrackGPS</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow">
<meta name="robots" content="noarchive">


<script type="text/javascript" src="../javascript/mascaras.js"></script>

<script>
var totalVeiculos = 0;

var xmlhttpPainel;

function GetXmlHttpObject()
{
	if (window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	
	if (window.ActiveXObject)
	{
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	return null;
}


function adicionarNovaLinhaVeiculos() {

	tbl = document.getElementById("tabelaVeiculos");
	
	if (document.getElementById("alertNenhumVeiculo") != null) {
		document.getElementById("alertNenhumVeiculo").style.display='none';
		
		if (totalVeiculos == 0) {
			var novaLinhaTopo = tbl.insertRow(-1);
			var novaCelulaTopo;
			
			novaCelulaTopo = novaLinhaTopo.insertCell(0);
			novaCelulaTopo.innerHTML = "<td>N&uacute;mero imei</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(1);
			novaCelulaTopo.innerHTML = "<td>Nome no menu</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(2);
			novaCelulaTopo.innerHTML = "<td>Identifica&ccedil;&atilde;o</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(3);
			novaCelulaTopo.innerHTML = "<td>N&ordm; Chip <span class='dicaCadastro'><br/>ex.: (11) 9876-4321</span></td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(4);
			novaCelulaTopo.innerHTML = "<td>Operadora</td>";			
			novaCelulaTopo = novaLinhaTopo.insertCell(5);
			novaCelulaTopo.innerHTML = "<td>N&ordm; Chip2 <span class='dicaCadastro'><br/>ex.: (11) 9876-4321</span></td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(6);
			novaCelulaTopo.innerHTML = "<td>Operadora2</td>";			
			novaCelulaTopo = novaLinhaTopo.insertCell(7);			
			novaCelulaTopo.innerHTML = "<td>Gr&aacute;fico</td>";			
			novaCelulaTopo = novaLinhaTopo.insertCell(8);
			novaCelulaTopo.innerHTML = "<td>Ativo?</td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(9);
			novaCelulaTopo.innerHTML = "<td><img src='../imagens/salvar_todos.gif' title='Salvar todos' alt='Salvar todos' onclick='salvarTodos();' /></td>";
			novaCelulaTopo = novaLinhaTopo.insertCell(10);
			novaCelulaTopo.innerHTML = "<td>Excluir</td>";
		}
	}
	
	totalVeiculos++;

	var novaLinha = tbl.insertRow(-1);
	var novaCelula;
	
	novaLinha.id = "linhaBemCliente" + totalVeiculos;

	novaCelula = novaLinha.insertCell(0);
	novaCelula.innerHTML =  "<input maxlength='15' size='17' id='listaImei" + totalVeiculos + "' name='listaImei" + totalVeiculos + "' type='text' value='' class='campoNovoVeiculo' />" +
							"<input maxlength='15' size='17' id='listaImeiHidden" + totalVeiculos + "' name='listaImeiHidden" + totalVeiculos + "' type='hidden' value='' />" +
							"<input maxlength='15' id='listaIdBemHidden" + totalVeiculos + "' name='listaIdBemHidden" + totalVeiculos + "' type='hidden' value='0' />";

	novaCelula = novaLinha.insertCell(1);
	novaCelula.innerHTML = "<input id='listaNome"+ totalVeiculos +"' name='listaNome"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' />";

	novaCelula = novaLinha.insertCell(2);
	novaCelula.innerHTML = "<input id='listaIdent"+ totalVeiculos +"' name='listaIdent"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' />";
	
	novaCelula = novaLinha.insertCell(3);
	novaCelula.innerHTML = "<input id='listaChip"+ totalVeiculos +"' name='listaChip"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' maxlength='14' onkeypress=\"return txtBoxFormat(this, '(99) 9999-9999', event);\" onblur=\"if (this.value != '') { return txtBoxFormat(this, '(99) 9999-9999', event); } \" size='11' />";

	novaCelula = novaLinha.insertCell(4);
	novaCelula.innerHTML = "<select id='listaOperadora"+ totalVeiculos +"' name='listaOperadora"+ totalVeiculos +"' class='campoNovoVeiculo'> "+
								"<option value=''>--Selecione--</option> "+
								"<option value='TI'>Tim</option> "+
								"<option value='CL'>Claro</option> "+
								"<option value='VI'>Vivo</option> "+
								"<option value='OI'>Oi</option> "+
								//"<option value='TM'>Telemig</option> "+
								//"<option value='SE'>Sercomtel</option> "+
								"<option value='CT'>CTBC</option> "+
								"<option value='BT'>Brasil Telecom</option> "+
								"<option value='AM'>Amazonia Celular</option> "+
							"</select>";
	

	novaCelula = novaLinha.insertCell(5);
	novaCelula.innerHTML = "<input id='lista2Chip"+ totalVeiculos +"' name='lista2Chip"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' maxlength='14' onkeypress=\"return txtBoxFormat(this, '(99) 9999-9999', event);\" onblur=\"if (this.value != '') { return txtBoxFormat(this, '(99) 9999-9999', event); } \" size='11' />";

	novaCelula = novaLinha.insertCell(6);
	novaCelula.innerHTML = "<select id='lista2Operadora"+ totalVeiculos +"' name='lista2Operadora"+ totalVeiculos +"' class='campoNovoVeiculo'> "+
								"<option value=''>--Selecione--</option> "+
								"<option value='TI'>Tim</option> "+
								"<option value='CL'>Claro</option> "+
								"<option value='VI'>Vivo</option> "+
								"<option value='OI'>Oi</option> "+
								//"<option value='TM'>Telemig</option> "+
								//"<option value='SE'>Sercomtel</option> "+
								"<option value='CT'>CTBC</option> "+
								"<option value='BT'>Brasil Telecom</option> "+
								"<option value='AM'>Amazonia Celular</option> "+
							"</select>";
	
	
	novaCelula = novaLinha.insertCell(7);
	novaCelula.innerHTML = "<input id='listaCor"+ totalVeiculos +"' name='listaCor"+ totalVeiculos +"' type='text' value='' class='campoNovoVeiculo' maxlength='6' size='6' onblur='this.value=this.value.toUpperCase();' />";	

	novaCelula = novaLinha.insertCell(8);
	novaCelula.innerHTML = "<select id='listaAtivo"+ totalVeiculos +"' name='listaAtivo"+ totalVeiculos +"' class='campoNovoVeiculo'> <option selected value='S'>Sim</option> <option value='N'>N&atilde;o</option> </select>";
	
	novaCelula = novaLinha.insertCell(9);
	novaCelula.innerHTML = "<div style='width:40px'><img id='imgGravar"+ totalVeiculos +"' src='../imagens/salvar.png' title='Salvar veiculo' alt='Salvar veiculo' onclick='adicionarVeiculoAdmin("+ totalVeiculos +");' /><img id='imgExecutando"+ totalVeiculos +"' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' /><img id='imgSucesso"+ totalVeiculos +"' style='display:none' src='../imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' /></div>";
	
	novaCelula = novaLinha.insertCell(10);
	novaCelula.innerHTML = "<div style='width:40px'><a href='javascript:void(0);'><img border=0 id='imgExcluirBem"+ totalVeiculos +"' src='../imagens/lixeira.png' title='Excluir item' alt='Excluir item' onclick='excluirBemUsuario("+ totalVeiculos +");' /></a> <img border=0 id='imgExcluindo" + totalVeiculos + "' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' /> </div>";	
	
}

function adicionarVeiculoAdmin(id) 
{
	var codigoCliente = document.getElementById('codigoCliente').value;
	var imei = document.getElementById('listaImei' + id).value;
	var nome = document.getElementById('listaNome' + id).value;
	var ident = document.getElementById('listaIdent' + id).value;
	var cor = document.getElementById('listaCor' + id).value;
	var ativoCombo = document.getElementById('listaAtivo' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var chip = removerMascaraTelefone(document.getElementById('listaChip' + id).value);
	var operadoraCombo = document.getElementById('listaOperadora' + id);
	var operadora = operadoraCombo.options[operadoraCombo.selectedIndex].value;	
	var chip2 = removerMascaraTelefone(document.getElementById('lista2Chip' + id).value);
	var operadoraCombo2 = document.getElementById('lista2Operadora' + id);
	var operadora2 = operadoraCombo2.options[operadoraCombo2.selectedIndex].value;		
	
	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && imei != '' && nome != '') {
		if (imei.length == 15) {
			//Exibe icone de executando o link
			document.getElementById('imgExecutando'+id).style.display='inline';
		
			var url="adicionar_novo_veiculo.php";
			url=url+"?codCliente="+codigoCliente;
			url=url+"&imei="+imei;
			url=url+"&nome="+nome;
			url=url+"&ident="+ident;
			url=url+"&chip="+chip;
			url=url+"&operadora="+operadora;			
			url=url+"&chip2="+chip2;
			url=url+"&operadora2="+operadora2;						
			url=url+"&cor="+cor;
			url=url+"&ativo="+ativo;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													var resposta = xmlhttpPainel.responseText;
													var idBem = null;
													
													if (resposta.substr(0,2) == "OK") 
													{
														idBem = resposta.substr(3,resposta.length)
														resposta = "OK";
													}
												
													if (resposta == 'OK') {
														document.getElementById('imgExecutando' + id).style.display='none';
														document.getElementById('imgSucesso' + id).style.display='inline';
														setTimeout("document.getElementById('imgSucesso"+id+"').style.display='none'", 5000);
														//Guardando o valor do imei, caso precise alterá-lo
														document.getElementById('listaImeiHidden' + id).value = imei;
														document.getElementById('listaIdBemHidden' + id).value = idBem;
														//Alterando o botão para salvar, ao invés de incluir
														document.getElementById('imgGravar' + id).onclick=new Function("alterarVeiculoAdmin(" + id + ")"); 
													} else {
														if (xmlhttpPainel.responseText == 'IMEI duplicado') {
															document.getElementById('imgExecutando' + id).style.display='none';
															alert('ERRO: Este imei j\u00e1 existe!');
														} else {
															alert('ERRO: ' + xmlhttpPainel.responseText);
														}
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		} else {
			alert('N\u00famero imei est\u00e1 incompleto\u0021');
		}
	}
}


function alterarVeiculoAdmin(id) 
{	
	var codigoCliente = document.getElementById('codigoCliente').value;
	var imei = document.getElementById('listaImei' + id).value;
	var imeiAntigo = document.getElementById('listaImeiHidden' + id).value;
	var nome = document.getElementById('listaNome' + id).value;
	var ident = document.getElementById('listaIdent' + id).value;
	var cor = document.getElementById('listaCor' + id).value;
	var ativoCombo = document.getElementById('listaAtivo' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var chip = removerMascaraTelefone(document.getElementById('listaChip' + id).value);
	var operadoraCombo = document.getElementById('listaOperadora' + id);
	var operadora = operadoraCombo.options[operadoraCombo.selectedIndex].value;		
	var chip2 = removerMascaraTelefone(document.getElementById('lista2Chip' + id).value);
	var operadoraCombo2 = document.getElementById('lista2Operadora' + id);
	var operadora2 = operadoraCombo2.options[operadoraCombo2.selectedIndex].value;			
	
	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && imei != '' && nome != '') {
		if (imei.length == 15) {
			//Exibe icone de executando o link
			document.getElementById('imgExecutando'+id).style.display='inline';
			
			var url="alterar_veiculo.php";
			url=url+"?codCliente="+codigoCliente;
			url=url+"&imei="+imei;
			url=url+"&imeiAntigo="+imeiAntigo;
			url=url+"&nome="+nome;
			url=url+"&ident="+ident;
			url=url+"&chip="+chip;
			url=url+"&operadora="+operadora;	
			url=url+"&chip2="+chip2;
			url=url+"&operadora2="+operadora2;						
			url=url+"&cor="+cor;
			url=url+"&ativo="+ativo;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('listaImeiHidden' + id).value = imei;
														document.getElementById('imgExecutando' + id).style.display='none';
														document.getElementById('imgSucesso' + id).style.display='inline';
														setTimeout("document.getElementById('imgSucesso"+id+"').style.display='none'", 5000);
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		} else {
			alert('N\u00famero imei est\u00e1 incompleto\u0021');
		}
	}
}

function salvarUsuarioAdmin(id)
{
	var codigoCliente = document.getElementById('listaCodigoCliente' + id).value;
	var nomeCliente = document.getElementById('listaNomeCliente' + id).value;
	var ativoCombo = document.getElementById('listaAtivoCliente' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && nomeCliente != '') {
		//Exibe icone de executando o link
		document.getElementById('imgExecutandoCliente' + id).style.display='inline';	
		
		var url="alterar_usuario.php";
		url=url+"?codCliente="+codigoCliente;
		url=url+"&nomeCliente="+nomeCliente;
		url=url+"&cpf="+cpf;
		url=url+"&ativo="+ativo;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													document.getElementById('imgExecutandoCliente' + id).style.display='none';
													document.getElementById('imgSucessoCliente' + id).style.display='inline';
													setTimeout("document.getElementById('imgSucessoCliente"+id+"').style.display='none'", 5000);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
}

function registrarPagamentoMesAdmin(mes, idCliente, img)
{
	var imgId = img.src;
	
	if (imgId.indexOf("registra_pgto.gif") != -1) 
	{
		//registra pagamento, chama função
		registrarPagamento(mes, idCliente, 'S');
		document.getElementById("imgRegistraPagto"+ mes + idCliente + "").src = '../imagens/pagou.gif';
	}
	if (imgId.indexOf("pagou.gif") != -1) 
	{
		//registra nao pagamento, chama função de nao pagamento
		registrarPagamento(mes, idCliente, 'N');
		document.getElementById("imgRegistraPagto"+ mes + idCliente + "").src = '../imagens/sem_pagamento.gif';
	}
	if (imgId.indexOf("sem_pagamento.gif") != -1) 
	{
		//registra retirada de pagamento, chama função de retirada
		registrarPagamento(mes, idCliente, 'F');
		document.getElementById("imgRegistraPagto"+ mes + idCliente + "").src = '../imagens/registra_pgto.gif';
	}
}

function registrarPagamento(mes, idCliente, pgto)
{
	var codigoCliente = idCliente;
	var mesPagamento = mes;
	var pagamento = pgto;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && mesPagamento != '') {
		var url="registrar_pagamento_usuario.php";
		url=url+"?codigoCliente="+codigoCliente;
		url=url+"&mesPagamento="+mesPagamento;
		url=url+"&pagamento="+pagamento;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													//document.getElementById('imgSucessoCliente' + id).style.display='inline';
													//setTimeout("document.getElementById('imgSucessoCliente"+id+"').style.display='none'", 5000);
													//Pagamento registrado!
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
}

function salvarUsuarioAdminPgto(id)
{
	var codigoCliente = document.getElementById('listaCodigoClientePgto' + id).value;
	var nomeCliente = document.getElementById('listaNomeClientePgto' + id).value;
	var ativoCombo = document.getElementById('listaAtivoClientePgto' + id);
	var ativo = ativoCombo.options[ativoCombo.selectedIndex].value;
	var obsCliente = document.getElementById('listaObsClientePgto' + id).value;

	xmlhttpPainel=GetXmlHttpObject();
	
	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	if (codigoCliente != '' && nomeCliente != '') {
		//Exibe icone de executando o link
		document.getElementById('imgExecutandoClientePgto'+id).style.display='inline';	
		
		var url="alterar_usuario.php";
		url=url+"?codCliente="+codigoCliente;
		url=url+"&nomeCliente="+nomeCliente;
		url=url+"&cpf="+cpf;
		url=url+"&ativo="+ativo;
		url=url+"&obsCliente="+obsCliente;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													document.getElementById('imgExecutandoClientePgto' + id).style.display='none';
													document.getElementById('imgSucessoClientePgto' + id).style.display='inline';
													setTimeout("document.getElementById('imgSucessoClientePgto"+id+"').style.display='none'", 5000);
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);
	}
}

function abrirFrotaCliente(id)
{
	window.location='admin.php?acao=obterUsuario&codigo=' + id;
}

//Exclui uma conta
function excluirUsuarioAdmin(id)
{
	if (confirm("Deseja realmente EXCLUIR esta conta e todos os bens cadastrados? Esta opera\u00e7\u00e3o n\u00e3o poder\u00e1 ser desfeita!"))
	{
		var codigoCliente = document.getElementById('listaCodigoCliente' + id).value;
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (codigoCliente != '') {
			//Exibe icone de executando o link
			document.getElementById('imgExcluindoCliente' + id).style.display='inline';	
			
			var url="excluir_usuario.php";
			url=url+"?codCliente="+codigoCliente;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('imgExcluindoCliente' + id).style.display='none';
														document.getElementById('linhaContaCliente' + id).style.display='none';
														document.getElementById('linhaContaPagtoCliente' + id).style.display='none';
														alert('Conta exclu\u00edda com sucesso.');
													} else {
														alert('ERRO: Falhar ao excluir a conta!');
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		}
	}
}

//Exclui um item da usuario
function excluirBemUsuario(id)
{

	var hist = false;
	try {
		hist = document.getElementById('ckbExcluirHistorico' + id).checked;
	} catch (err) {
		hist = false;
	}
	
	var mensagem = 'Deseja realmente EXCLUIR este item da conta?';	
	if (hist)
		mensagem = 'Deseja realmente EXCLUIR este item da conta e TODO SEU HISTORICO?';

	if (confirm(mensagem))
	{
		document.getElementById('imgExcluindo'+id).style.display='inline';	
		
		var codigoCliente = document.getElementById('codigoCliente').value;
		var imei = document.getElementById('listaImei' + id).value;
		var idBem = document.getElementById('listaIdBemHidden' + id).value;
		var apagaHistorico = hist == true ? 'S' : 'N';
		
		xmlhttpPainel=GetXmlHttpObject();
		
		if (xmlhttpPainel==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		if (codigoCliente != '' && imei != '') {
	
			var url="excluir_bem_conta.php";
			url=url+"?codCliente="+codigoCliente;
			url=url+"&imei="+imei;
			url=url+"&idBem="+idBem;
			url=url+"&apagaHistorico="+apagaHistorico;
			xmlhttpPainel.onreadystatechange = function () {
												if (xmlhttpPainel.readyState == 4)
												{
													if (xmlhttpPainel.responseText == 'OK') {
														document.getElementById('linhaBemCliente' + id).style.display='none';
														document.getElementById('imgExcluindo'+id).style.display='none';
														alert('Item exclu\u00eddo com sucesso.');
													} else {
														alert('ERRO: Falhar ao excluir o item!');
													}
												}
											};
			xmlhttpPainel.open("GET", url, true);
			xmlhttpPainel.send(null);
		}
	}
}

function marcarTodosComandos()
{
   for (i=0;i<document.listaComandosUsuarios.elements.length;i++)
      if(document.listaComandosUsuarios.elements[i].type == "checkbox")
		if(document.getElementById('ckTodosComandos').checked==1)
			document.listaComandosUsuarios.elements[i].checked=1;
		else
			document.listaComandosUsuarios.elements[i].checked=0;
}

function atribuirComandos(usuario)
{
	var comando1 = document.getElementById('ckComando1').checked ? 1 : 0;
	var comando2 = document.getElementById('ckComando2').checked ? 1 : 0;
	var comando3 = document.getElementById('ckComando3').checked ? 1 : 0;
	var comando4 = document.getElementById('ckComando4').checked ? 1 : 0;
	var comando5 = document.getElementById('ckComando5').checked ? 1 : 0;
	var comando6 = document.getElementById('ckComando6').checked ? 1 : 0;
	var comando7 = document.getElementById('ckComando7').checked ? 1 : 0;
	var comando8 = document.getElementById('ckComando8').checked ? 1 : 0;
	var comando9 = document.getElementById('ckComando9').checked ? 1 : 0;
	var comando10 = document.getElementById('ckComando10').checked ? 1 : 0;
	var comando11 = document.getElementById('ckComando11').checked ? 1 : 0;
			
	xmlhttpPainel=GetXmlHttpObject();

	if (xmlhttpPainel==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}

	if (usuario != '') {
		//Exibe icone de executando o link
		document.getElementById('imgComandosCliente').style.display='none';
		document.getElementById('imgComandosCliente').style.display='inline';
		
		var url="atribuir_comandos_usuario.php";
		url=url+"?cliente="+usuario;
		url=url+"&comando1="+comando1;
		url=url+"&comando2="+comando2;
		url=url+"&comando3="+comando3;
		url=url+"&comando4="+comando4;
		url=url+"&comando5="+comando5;
		url=url+"&comando6="+comando6;
		url=url+"&comando7="+comando7;
		url=url+"&comando8="+comando8;
		url=url+"&comando9="+comando9;
		url=url+"&comando10="+comando10;
		url=url+"&comando11="+comando11;
		xmlhttpPainel.onreadystatechange = function () {
											if (xmlhttpPainel.readyState == 4)
											{
												if (xmlhttpPainel.responseText == 'OK') {
													document.getElementById('imgComandosCliente').style.display='none';
													document.getElementById('imgComandosClienteSucesso').style.display='inline';
													setTimeout("document.getElementById('imgComandosClienteSucesso').style.display='none'", 5000);
												} else {
													alert('ERRO: Falha ao executar atribui\u00e7\u00e3o de comandos!');
												}
											}
										};
		xmlhttpPainel.open("GET", url, true);
		xmlhttpPainel.send(null);

	}
}

//Remover mascara para salvar no banco
function removerMascaraTelefone(tel)
{
  var telSemMascara = tel;
  telSemMascara = telSemMascara.replace('(','');
  telSemMascara = telSemMascara.replace(')','');
  telSemMascara = telSemMascara.replace('-','');
  telSemMascara = telSemMascara.replace(/ /g,'');
  
  return telSemMascara;
}


/* -- checando alertas -- */

var xmlhttp;

function checarAlertasAdmin()
{
	//xmlhttp.abort();
	xmlhttp=GetXmlHttpObject();
	
	if (xmlhttp==null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}
	
	var url="checar_alertas_admin.php";
	//url=url+"?imei="+str;
	//url=url+"&sid="+Math.random();
	xmlhttp.onreadystatechange = stateChanged;
	xmlhttp.open("GET", url, true);
	xmlhttp.send(null);
	
	//Refresh na grid a cada 10s
	setTimeout("checarAlertasAdmin()", 10000);
}

function stateChanged()
{
	if (xmlhttp.readyState == 4)
	{
		var agora;
		//alert(agora.toString('H:MM:ss'));
		
		var currentTime = new Date();
		var hours = currentTime.getHours();
		var minutes = currentTime.getMinutes();
		var sec = currentTime.getSeconds();
		if (hours < 10) {
			hours = "0" + hours;
		}		
		if (minutes < 10) {
			minutes = "0" + minutes;
		}
		if (sec < 10) {
			sec = "0" + sec;
		}		
		agora = hours + ":" + minutes + ":" + sec;
		
		document.getElementById("alertas_grid").innerHTML=xmlhttp.responseText;
		document.getElementById("data_checa_alerta").innerHTML='(Atualizado as ' + agora + 'h)';
	}
}


</script>

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

<body> 

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
													<td><img src='../imagens/salvar_todos.gif' title='Salvar todos' alt='Salvar todos' onclick='salvarTodos();' /></td>
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
													echo "<img src='../imagens/salvar.png' title='Salvar alteração' alt='Salvar alteração' onclick='alterarVeiculoAdmin(". $row[id] .");' /> ";
													echo "<img id='imgExecutando". $row[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />";
													echo "<img id='imgSucesso". $row[id] ."' style='display:none' src='../imagens/sucesso.png' title='Alteração salva' alt='Alteração salva' />";
											echo "</div></td>";
											echo "<td> <div style='width:40px'>
													<a href='javascript:void(0);'><img border=0 id='imgExcluirBem". $row[id] ."' src='../imagens/lixeira.png' title='Excluir item' alt='Excluir item' onclick='excluirBemUsuario(". $row[id] .")' /><a>
																				 <span style='font-size:10px'><input name='CheckboxExcluirHist". $row[id] ."' type='checkbox' id='ckbExcluirHistorico". $row[id] ."' /><label for='ckbExcluirHistorico". $row[id] ."' style='cursor:pointer'>histórico</label></span>
																				  <img border=0 id='imgExcluindo". $row[id] ."' style='display:none' src='../imagens/executando.gif' title='Executando...' alt='Executando...' />
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
									<img src='../imagens/btnNovoVeiculo.png' title='Adicionar novo veículo' alt='Adicionar novo veículo' onclick='adicionarNovaLinhaVeiculos();' /> 
									</form>
								</div>
							</div>
							</body>



