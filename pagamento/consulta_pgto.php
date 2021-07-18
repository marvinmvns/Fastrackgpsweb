<?php
include("../seguranca.php");
include("../mysql.php");
?>




<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<title>Pagamentos</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<style>

body, table {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #A6A6A6;
}


.menu {
	border-color: #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
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
	font-size: 14px;
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
    height: 435px;
	height: 100%;
	width: auto;
	filter:alpha(opacity=90); 
  	-moz-opacity: 0.90; 
   	opacity: 0.90; 
}








</style>


<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-62540977-3', 'auto');
  ga('send', 'pageview');

</script>

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
			background-repeat: no-repeat;">

<h2 align="center" style="font-size:20px; font-weight: bold; font-family: Arial, Helvetica, sans-serif; color: #666666;">_</h2>
<table width="70%" height="70%" cellspacing="0" cellpadding="0" border="0" style="border-left: 1px solid #000000; border-left-color: #CCCCCC;" align="center">

	<tr>
		<td width="100" class="menu" id="td_cadastro"
		onclick="AlternarAbas('td_cadastro','div_cadastro')" style="height: 7px">
			Pagamento em Aberto
		</td>
		<td width="100" class="menu" id="td_consulta"
		onclick="AlternarAbas('td_consulta','div_consulta')" style="height: 7px">
			Extrato de Pagamentos
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
					Valores em Aberto(Observe a data de pagamento) <br />

				
						<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 
						
						$datainix = date("d/m/Y");	
						$datainix = SomarData($datainix, 20, 0, 0);  
						$datainix = inverte_data($datainix,'/');
						
						
						
					 function SomarData($data, $dias, $meses, $ano)
       
	          {  
                $data = explode("/", $data);
                $newData = date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses,
                $data[0] + $dias, $data[2] + $ano) );
                    return $newData;
              }
              
			  
	  			   function inverte_data($data,$separador)
              {
               $nova_data = implode("".$separador."",array_reverse(explode("".$separador."",$data)));
               return $nova_data;
			  }
						
						
						
				
						$res = mysql_query("select * from pagtos1  where data_vcto < '". $datainix ."' and codcli = " . trim($cliente) . "  and pago = 'N' ");
						
						if (mysql_num_rows($res) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum pagamento em aberto!</b></td> </tr>";
						} else {
							  echo "<tr>
										<td>Controle</td>	
                                        <td>Quantidade</td>										
										<td>Descricao</td>																		
										<td>Ultimo pagamento</td>
										<td>Vencimento</td>     
                                        <td>Status</td>  										
									</tr>";
						}
						
						for ($i=0; $i < mysql_num_rows($res); $i++) {
							$row = mysql_fetch_assoc($res);
						
					
						$res2 = mysql_query("SELECT * FROM PagSeguroTransacoes where Referencia = '". $row[codc] ."' order by Data desc limit 1");						
						$row2 = mysql_fetch_row($res2);
						
						$StatusTransacao = $row2[9];
                        $TipoPagamento = $row2[8]; 
						
						if ($StatusTransacao == '')
						{
						    $StatusTransacao = "Clique em pagar";
						}
						
						
						 $data_ult_pgto = date('d/m/Y', strtotime($row[data_ult_pgto]));
						 $data_vcto = date('d/m/Y', strtotime($row[data_vcto]));
							   					
						
						
							echo "<tr>";
								echo "<td><input maxlength='4' size='4'  id='listaImei". $row[codc] ."' name='listaImei". $row[codc] ."' type='text' value='". $row[codc] ."' class='campoNovoVeiculo' disabled /></td>";							
								echo "<td><input maxlength='10' size='9' id='listaNome". $row[qtde] ."' name='listaNome". $row[qtde] ."' type='text' value='". $row[qtde] ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='13' size='13' id='listaIdent". $row[descr] ."' name='listaIdent". $row[descr] ."' type='text' value='". $row[descr] ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='13' size='13' id='listaIdent". $data_ult_pgto  ."' name='listaIdent". $data_ult_pgto  ."' type='text' value='". $data_ult_pgto  ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='13' size='13' id='listaIdent". $data_vcto ."' name='listaIdent". $data_vcto ."' type='text' value='". $data_vcto ."' class='campoNovoVeiculo' disabled /></td>";
                                echo "<td><input maxlength='13' size='13' id='listaIdent". $StatusTransacao ."' name='listaIdent". $StatusTransacao ."' type='text' value='". $StatusTransacao ."' class='campoNovoVeiculo' disabled /></td>";								
						
                              					
						
								if (mysql_num_rows($res) == 1 )
								{
								  echo "<td> <a href ='pgtounitario.php?id=". $row[codc] ."'>Pagar</a></td>";  
					   		      echo "</tr>";
                                }
							
								
							 
								
								                                                                                                            							
							
						}
						?>
					</table>
					</form>
						<br/>
						
						
						
						 <?php
						 
						
								if (mysql_num_rows($res) > 1 )
									
									{
									  
									  echo "Ocorreu um erro! Para efetuar o pagamento entre em contato marcus@segundo.me</br>";
									
									}
						 ?>
							
				    
						
			        	
					<a href="../mapa.php" style="color:#0099FF">Voltar</a>		
                   				
				</div>
			</div>

			<div id="div_consulta" class="conteudo" style="display: none;">
				<div>
					Listagem de Pagamentos <br />
					
					<form name="listaBens" method="post" action="menu_novo_veiculo.php">
					<table cellspacing="6" cellpadding="0">
							<tr>
								<td colspan="5">
									<br />
								</td>
							</tr>					
						<?php 

						
						
						//Montando listagem - $cliente está na sessão
						$res = mysql_query("select * from pagtos1  where  codcli = ".trim($cliente)." and pago = 'S' order by codc desc");
						
						if (mysql_num_rows($res) == 0) {
							echo "<tr><td colspan='5'><b>Nenhum item encontrado.</b></td> </tr>";
						} else {
							  echo "<tr>
							  
										<td>Controle</td>		
                                        <td>Quantidade</td>											
										<td>Descrição</td>															
										<td>Dt do ultimo pagto</td>
										<td>Dt de vencimento </td>
                                        <td>Pago</td>										
										<td>OBS</td>
										
									</tr>";
						}
						
						for ($i=0; $i < mysql_num_rows($res); $i++) {
							$row = mysql_fetch_assoc($res);
							
			
							   $data_ult_pgto = date('d/m/Y', strtotime($row[data_ult_pgto]));
							   $data_vcto = date('d/m/Y', strtotime($row[data_vcto]));
							   
						
							echo "<tr>";
								echo "<td><input maxlength='4' size='4'  id='listaImei". $row[codc] ."' name='listaImei". $row[codc] ."' type='text' value='". $row[codc] ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='10' size='9' id='listaNome". $row[qtde] ."' name='listaNome". $row[qtde] ."' type='text' value='". $row[qtde] ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='13' size='13' id='listaIdent". $row[descr] ."' name='listaIdent". $row[descr] ."' type='text' value='". $row[descr] ."' class='campoNovoVeiculo' disabled /></td>";
							    echo "<td><input maxlength='13' size='13' id='listaIdent". $data_ult_pgto  ."' name='listaIdent". $data_ult_pgto  ."' type='text' value='". $data_ult_pgto  ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='13' size='13' id='listaIdent". $data_vcto ."' name='listaIdent". $data_vcto ."' type='text' value='". $data_vcto ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='1' size='1' id='listaIdent". $row[pago] ."' name='listaIdent". $row[pago] ."' type='text' value='". $row[pago] ."' class='campoNovoVeiculo' disabled /></td>";
								echo "<td><input maxlength='13' size='13' id='listaIdent". $row[obs] ."' name='listaIdent". $row[obs] ."' type='text' value='". $row[obs] ."' class='campoNovoVeiculo' disabled /></td>";
								
														
							echo "</tr>";
						}
						?>
					</table>
					</form>
					<br />
					<a href="../mapa.php" style="color:#0099FF">Voltar</a>
		
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