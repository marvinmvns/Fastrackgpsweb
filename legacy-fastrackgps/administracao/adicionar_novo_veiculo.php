<?php include('../../shared-modules/config/seguranca.php');

$codCliente=$_GET["codCliente"];
$imei=$_GET["imei"];
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
				
		if (!mysql_query("INSERT INTO bem (imei, name, identificacao, cliente, activated, porta, cor_grafico, liberado, numero_chip, operadora_chip, numero_chip2, operadora_chip2) VALUES
										  ('$imei', '$nome', '$ident', $codCliente, '$ativo', '7095', '$cor', 'S', $chip, '$operadora', $chip2, '$operadora2')", $con))
		{
			if (mysql_error() == "Duplicate entry '". $imei ."' for key 'imei'" or mysql_error() == "Duplicate entry '". $imei ."' for key 2")
				echo "IMEI duplicado";
			else
				die('Error: ' . mysql_error());
		}
		else
		{
		
			$idBem = "0";
			$sql = "select id from bem where imei = $imei LIMIT 1"; 
			$result = mysql_query( $sql );

			while($data = mysql_fetch_assoc($result))
			{
				$idBem = $data['id'];
			}
			
			//Gravado com sucesso
			echo "OK|$idBem";
		}
	}
}

mysql_close($con);
?>
