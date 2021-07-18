<?php include('seguranca.php');

//Variavel $cliente setada na sessão no include de segurança

$fechar=isset($_GET['fechar']) ? $_GET['fechar'] : "";
	
$imei=isset($_GET['imei']) ? $_GET['imei'] : "";

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

if ($cliente != "")
{

	$sql="SELECT b.name, m.message, m.imei, count(*) as qtde
		  FROM bem b inner join message m on (b.imei = m.imei)
		  WHERE b.cliente = $cliente and m.viewed = 'N'
		  GROUP BY 1, 2, 3";

	$result = mysql_query($sql);

	$loopcount = 0;

	while($data = mysql_fetch_assoc($result))
	{
		if ($loopcount == 0)
			echo "<br />
			<table width=\"100%\">";
			
		echo "<tr> 
				<td>" . $data['name'] . "</td>
				<td><b>" . $data['message'] . "</b> (". $data['qtde'] .") </td>
				<td><input type=\"button\" value=\"Seguir\" title=\"Clique para seguir no mapa\" class=\"botaoBranco\" onclick=\"seguirBemAlertado('". $data['imei'] ."'); this.style.color='silver'; this.style.border='1px solid silver'; this.onclick=''; this.value='Seguindo...'; this.disabled=true; \" /></td>
				<td><input type=\"button\" value=\"Visto\" title=\"Clique para fechar o alerta\" class=\"botaoBranco\" onclick=\"fecharAlerta('". $data['message'] ."', '". $data['imei'] ."'); this.style.color='silver'; this.style.border='1px solid silver'; this.onclick=''; this.value='OK'; this.disabled=true; \" /></td>
			  </tr>";

		$loopcount++;
	}

	if ($loopcount == 0) {
	  echo "<p>Nenhum alerta.</p>";
	} 
	else 
	{
		echo "</table>
			  <br />";
	}
}

if ($fechar != "" and $imei != "") 
{
	if (!mysql_query("UPDATE message set viewed = 'S', date = date WHERE imei = '$imei' and message = '$fechar' and viewed = 'N'", $con))
	{
		die('Error: ' . mysql_error());
	}
}

mysql_close($con);
?>