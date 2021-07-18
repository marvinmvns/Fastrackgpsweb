<?php include('seguranca.php');

//Variavel $cliente setada na sessão no include de segurança

$imei=$_GET["imei"];
$nome=$_GET["nome"];
$ident=$_GET["ident"];
$ativo=$_GET["ativo"];

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

if ($cliente != "")
{
	if ($imei != "" and $nome != "" and $ativo != "") 
	{
		if (!mysql_query("UPDATE bem set 
							name 		  = '$nome',
							identificacao = '$ident',
							activated	  = '$ativo'
						  WHERE imei 	= '$imei' and 
								cliente = $cliente", $con))
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
