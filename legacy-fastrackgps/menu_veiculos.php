<?php include('../shared-modules/config/seguranca.php');
//header("Content-Type: text/html; charset=utf-8");

if ($cliente == '') {
	$cliente = "0";
}

$inativarVeiculo = isset($_GET['inativarVeiculo']) ? $_GET['inativarVeiculo'] : null;

$cnx = mysql_connect("localhost", "admin123", "admin123") 
	  or die("Could not connect: " . mysql_error());
mysql_select_db("tracker2", $cnx);

if ($inativarVeiculo == null) {

	$res = mysql_query("select imei, name, identificacao from bem where activated = 'S' and cliente = " . trim($cliente) . " order by name");
	if (mysql_num_rows($res) == 0) {
		echo "<p>Nenhum bem encontrado. &nbsp;&nbsp; <a href='menu_novo_veiculo.php' style='font-size:10px;' title='Cadastrar novo'></a></p>";
	} else {
		echo "<table><tr><td>";
		echo "<select id=\"bens\" name=\"bens\" class=\"botaoBranco\" onchange=\"alterarComboVeiculo(this); \">";
		echo "<option value='' selected>-- Selecione --</option>";
		for($i=0; $i < mysql_num_rows($res); $i++) {
			$row = mysql_fetch_assoc($res);
			echo "<option value='$row[imei]'>$row[name]</option>";
		}
		if ($i > 1) {
			echo "<option value='ALL'>TODOS</option>";
		}
		echo "</select>";
		echo "</td>
			<td><a id='imgApagarBem' style='display:none; font-size:10px;' title='Remover bem' href=\"\" onclick=\"javascript:confirmaApagarVeiculo();return false;\"></a> </td>	
			<td><a href=\"menu_novo_veiculo.php\" style=\"font-size:10px;\" onclick=\"adicionarNovoImei();\" title=\"Cadastrar novo bem\"></a></td>
		</tr> </table>";
		echo "<br />";
	}

} else {
	if (!mysql_query("UPDATE bem set activated = 'N' WHERE imei = '$inativarVeiculo' and activated = 'S'", $cnx)) {
		die('Error: ' . mysql_error());
	}
}

mysql_close($cnx);
?>