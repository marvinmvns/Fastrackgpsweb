<?php
$EmailVendedor = 'bigfriendpunk@hotmail.com';
include('../../shared-modules/config/seguranca.php');
include('../../shared-modules/config/mysql-legacy.php');


?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>




<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Geração Pagamento FastrackGPS</title>
<script src="selecionar.js"></script>
<style type="text/css" media="all">


body, table {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #08153d;
}

.menu {
	border-color: #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #08153d;
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
	color: #08153d;
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


.okButton {
 background:url(http://virtualstaff.files.wordpress.com/2009/11/pagseguro.png) no-repeat;
 cursor:pointer;
 width: 238px;
 height:104px;
 border: none;
 font-size:0%
}





</style>


		
			
			
		
			<div id="div_cadastro" class="conteudo" align="center"	>
			
			<p style="height: 60px"></p>
			
			<div id="fadeBlock" style="text-align:center;opacity: 1; display:block;">
				
			</div>
			
               			
			<form target="PagSeguro" action="https://pagseguro.uol.com.br/security/webpagamentos/webpagto.aspx" method="post" name="TestePS" id="TestePS" />
			<div id="texto" align="center" ><b>Descrição do Valor à Pagar</b></div>				
			<input type="hidden" name="email_cobranca" value="<?php echo $EmailVendedor; ?>">
            <input type="hidden" name="tipo" value="CP" />
            <input type="hidden" name="moeda" value="BRL" />				
            <input type="hidden" name="item_id_1" value="1" />   
            <input type="hidden" name="item_frete_1" value="0" />
			<input type='hidden' name='item_descr_1' value='Mensalidade FastrackGPS'/>

            
			
			<table width="200px" align="center">
		   <tbody>					
            
<?php
			
	


			
$resUsu = "select * from bem, pagtos where bem.cliente = '".$cliente."' and bem.activated = 'S' and bem.imei = pagtos.imei and pago = 'N' ";				
$resultado = mysql_query($resUsu);
			
			
		
$resultado1 = mysql_query($resUsu);
$numero = mysql_num_rows($resultado1); // obtenemos el número de filas


print("<table width='70%' border='1' ALIGN='center'>
<tr align='center'>
<input type='hidden' name='item_valor_1' value='10,00' />
<input type='hidden' name='item_quant_1' value= '".$numero."' />

<tr>
<th>Id</th>
<th>Nome do Bem</th>
<th>Imei do Aparelho</th>
<th>Código da Cobrança</th>
<th>Vencimento</th>
</tr>"



);



while($row = mysql_fetch_array($resultado))
{

$data_vcto = date('d/m/Y', strtotime($row[data_vcto]));

echo "<tr>";
echo "<td>" . $row['id'] . "</td>";
echo "<td>" . $row['name'] . "</td>";
echo "<td>" . $row['imei'] . "</td>";
echo "<td>" . $row['codc'] . "</td>";
echo "<td>" . $data_vcto . "</td>";
echo "</tr>";
echo "<input type='hidden' name='ref_transacao' value='".$cliente."' />";




}
echo "</table>";
echo '<br>';
echo "O total para pagamento R$";
echo (10 * $numero) ;
echo '<br>';
echo '<br>';
echo "\n Escolhe Abaixo a forma de pagamento \n";


//	http://www.ds160.net.br/imagens/moip.png	

mysql_close($conexao);
?>
                    </br>
                    </br>					
					<tr>					
						<td colspan="2"  >
							<input name="ok" type="submit" class="okButton" value="Iniciar Pagamento" />
						</td>	
					</tr>
				</tbody>
			</table>
			</form>
			


	  	   
                  			   
				   
	
			
		 
        	 
</body>
</html>
