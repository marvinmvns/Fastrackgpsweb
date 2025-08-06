<?php include('../../shared-modules/config/seguranca.php');

$codCliente=$_GET["codCliente"];
$imei=$_GET["imei"];
$imeiAntigo=$_GET["imeiAntigo"];
$nome=$_GET["nome"];
$ident=$_GET["ident"];
$cor=$_GET["cor"];
$ativo=$_GET["ativo"];
$chip=$_GET["chip"];
$operadora=$_GET["operadora"];
$chip2=$_GET["chip2"];
$operadora2=$_GET["operadora2"];


$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

if ($codCliente != "")
{
	if ($imei != "" and $nome != "" and $ativo != "") 
	{
	
		if ($chip == '')
			$chip = 'null';
			
		if ($chip2 == '')
			$chip2 = 'null';			
			
		if (!mysql_query("UPDATE bem set 
							name 		  = '$nome',
							identificacao = '$ident',
							cor_grafico   = '$cor',
							activated	  = '$ativo',
							numero_chip   = $chip,
							operadora_chip= '$operadora',
							numero_chip2   = $chip2,
							operadora_chip2= '$operadora2',
							imei          = '$imei'
						  WHERE imei 	= '$imeiAntigo' and 
								cliente = $codCliente", $con))
		{
			die('Error: ' . mysql_error());
		}
		else
		{
			//Gravado com sucesso
			echo "OK";
		}
	}
}

mysql_close($con);
?>
