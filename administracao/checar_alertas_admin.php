<?php include('../seguranca.php');

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

$sql="SELECT (select x.nome from cliente x where x.id = b.cliente limit 1) as nomeUsuario, 
				  b.cliente, b.name, m.message, m.date
			FROM bem b inner join message m on (b.imei = m.imei) 
			WHERE b.cliente is not null and m.viewed = 'N'";

$result = mysql_query($sql);

$loopcount = 0;

$num_linhas = mysql_num_rows($result) + 1;

echo "<table cellspacing='6' cellpadding='0'>
		<tr>
			<td colspan='5'>
				<br />
			</td>
		</tr>
		<tr>
			<th valign='top'>Tipo de alerta</th>
			<th valign='bottom' rowspan='". $num_linhas ."' class='divisorLog'></th>
			<th valign='top'>Veiculo <br/></th>
			<th valign='bottom' rowspan='". $num_linhas ."' class='divisorLog'></th>
			<th valign='top'>Hora / Data <br/></th>
			<th valign='bottom' rowspan='". $num_linhas ."' class='divisorLog'></th>
			<th valign='top'>Acesso<br/></th>
		</tr>";

while($data = mysql_fetch_assoc($result))
{
	echo "<tr>";
		echo "<td>". $data[message] ."</td>";
		echo "<td>". $data[name] ."</td>";
		echo "<td>". date('H:i', strtotime($data['date'])) ."h em ". date('d/m', strtotime($data['date'])) ."</td>";
		echo "<td><a href='/default.php?user=". $data[cliente] ."&admin=true' target='_top' style='color:#0099FF'> <img border=0 src='../imagens/admin.png' style='height:25px' title='Acessar conta do cliente' alt='Acessar conta do cliente'/> ". $data[nomeUsuario] ."</a></td>";
	echo "</tr>";

	$loopcount++;
}

if ($loopcount == 0) {
	echo "<tr>";
		echo "<td colspan='7'><i>Nenhum alerta recebido.</i></td>";
	echo "</tr>";	
}

echo "</table>";

mysql_close($con);
?>
