

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Alteração de dados do usuário</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


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

<script type="text/javascript" src="../javascript/painel2.js"></script>
<script type="text/javascript" src="../javascript/mascaras.js"></script>

<body onLoad="AlternarAbas(<?php echo $abaInicial; ?>); AlternarSubAbas('td_sub_cadastro','div_sub_cadastro'); setTimeout('esconderAlerta()', 10000);  setTimeout('checarAlertasAdmin()', 10000); " 
	style=" background-position: right bottom; height:auto; border-left:thin; border-left-style: solid; 
			border-left-width: 1px; border-left-color: #CCCCCC; margin-left:0px; margin-top:-17px; 
			background-image:url('../imagens/fundo_logo_webarch.png'); background-repeat: no-repeat;">
			
										<div id="div_sub_comandos" class="conteudo" style="display:block; border-right: 1px solid #000000; border-right-color: #CCCCCC;">
								<div>
								
									
								</div>
							</div>

						<td width="100" class="menu" id="td_sub_cadastro" style="height: 7px" onclick="AlternarSubAbas('td_sub_cadastro','div_sub_cadastro')">
							<span style="font-size:14px">Veículos</span>
						</td>
			
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
									
									
if ($acao == "obterUsuario") {
	if ($codigoCliente != null) {
	
		$resUsuario = mysql_query("select c.*,
									CONCAT('(', SUBSTRING(c.telefone1,1,2), ') ', SUBSTRING(c.telefone1,3,4), '-', SUBSTRING(c.telefone1,7,4)) as stelefone1,
									CONCAT('(', SUBSTRING(c.telefone2,1,2), ') ', SUBSTRING(c.telefone2,3,4), '-', SUBSTRING(c.telefone2,7,4)) as stelefone2
									from cliente c where c.id = '$Cliente' and c.master = 'N'");
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
									
												
									
									
									include('seguranca.php');
                                    
									$cnx = mysql_connect("localhost", "admin123", "admin123") 
									or die("Could not connect: " . mysql_error());
									mysql_select_db("tracker2", $cnx);							
									 
						
									
									//Montando listagem
									$res = mysql_query("select b.*,
														CONCAT('(', SUBSTRING(b.numero_chip,1,2), ') ', SUBSTRING(b.numero_chip,3,4), '-', SUBSTRING(b.numero_chip,7,4)) as nr_chip, 
														CONCAT('(', SUBSTRING(b.numero_chip2,1,2), ') ', SUBSTRING(b.numero_chip2,3,4), '-', SUBSTRING(b.numero_chip2,7,4)) as nr_chip2
														from bem b where b.cliente = " . trim($cliente) . "
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
									<span id='imgComandosCliente' style='display:none'><img src='../imagens/executando.gif' title='Executando...' alt='Executando...' />Atribuindo...</span>
									<img id='imgComandosClienteSucesso' style='display:none' src='../imagens/sucesso.png' title='Comandos atribuidos' alt='Comandos atribuidos' />									
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