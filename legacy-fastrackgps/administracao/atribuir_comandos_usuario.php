<?php include('../../shared-modules/config/seguranca.php');

$cliente=$_GET["cliente"];
$comando1=$_GET["comando1"];
$comando2=$_GET["comando2"];
$comando3=$_GET["comando3"];
$comando4=$_GET["comando4"];
$comando5=$_GET["comando5"];
$comando6=$_GET["comando6"];
$comando7=$_GET["comando7"];
$comando8=$_GET["comando8"];
$comando9=$_GET["comando9"];
$comando10=$_GET["comando10"];
$comando11=$_GET["comando11"];

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con) {
	die('Could not connect: ' . mysql_error());
}

mysql_select_db("tracker2", $con);

if ($cliente != "") {
	if (!mysql_query("UPDATE command_cliente set comando1 = $comando1, comando2 = $comando2, comando3 = $comando3, comando4 = $comando4, comando5 = $comando5, comando6 = $comando6, comando7 = $comando7, comando8 = $comando8, comando9 = $comando9, comando10 = $comando10, comando11 = $comando11 WHERE cliente = $cliente", $con)) {
		$result = 'Error: ' . mysql_error();
	} else {
		$result = "OK";
	}
}

echo $result;

mysql_close($con);
?>
