<?php include('../seguranca.php');

$codCliente=$_GET["codCliente"];
$imei=$_GET["imei"];
$idBem=$_GET["idBem"];
$apagaHistorico=$_GET["apagaHistorico"];

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

$result = "OK";

if ($codCliente != "" and $imei != "" and $idBem != "")
{
	if (!mysql_query("DELETE from bem WHERE cliente = $codCliente and imei = $imei and id = $idBem", $con))
	{
		$result = 'Error: ' . mysql_error();
	}
	else
	{
	
		if ($apagaHistorico == "S" and is_numeric($imei))
		{
			if (!mysql_query("DELETE from gprmc WHERE imei = $imei", $con))
			{
				$result = 'Error: ' . mysql_error();
			}
			else
			{
				$result = "OK";
			}
		} else {
			$result = "OK";
		}
	}
}

echo $result;

mysql_close($con);
?>
