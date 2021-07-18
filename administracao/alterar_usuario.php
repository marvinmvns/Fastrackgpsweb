<?php include('../seguranca.php');

$codCliente=$_GET["codCliente"];
$nomeCliente=$_GET["nomeCliente"];
$ativo=$_GET["ativo"];
$obsCliente=$_GET["obsCliente"];

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

if ($codCliente != "")
{
	if ($nomeCliente != "" and $ativo != "") 
	{
		if (!mysql_query("UPDATE cliente set 
							nome 		  = '$nomeCliente',
							observacao    = '$obsCliente',
							ativo		  = '$ativo'
						  WHERE	id = $codCliente", $con))
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
