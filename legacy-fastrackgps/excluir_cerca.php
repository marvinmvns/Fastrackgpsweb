<?php include('../shared-modules/config/seguranca.php');

$codCerca = $_GET["codCerca"];

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con) {
	die('Could not connect: ' . mysql_error());
}

mysql_select_db("tracker2", $con);

if ($codCerca != "") {
	if (!mysql_query("DELETE FROM geo_fence WHERE id = '$codCerca'", $con)) {
		echo "Error: " . mysql_error();
	} else {
		echo "OK";
	}
}

mysql_close($con);
?>
