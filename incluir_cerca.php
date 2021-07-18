<?php

include('seguranca.php');

$imei = $_GET["imei"];
$nome = $_GET["NomeCerca"];
$coordenadas = $_GET["cerca"];
$lat_point = $_GET["latitude"];
$lng_point = $_GET["longitude"];
$tipoEnvio = "0";
$tipoAcao = $_GET["tipoAcao"];

$cerca = str_replace("(", "", str_replace(")", "", str_replace(")(", "|", $coordenadas)));

$exp = explode("|", $cerca);

if((count($exp)) < 5) {
	$strExp = explode(",", $exp[0]);
	$strExp1 = explode(",", $exp[2]);
} else {
	$int = (count($exp)) / 2;
	$strExp = explode(",", $exp[0]);
	$strExp1 = explode(",", $exp[$int]);
}

$lat_vertice_1 = $strExp[0];
$lng_vertice_1 = $strExp[1];
$lat_vertice_2 = $strExp1[0];
$lng_vertice_2 = $strExp1[1];

if ( $lat_vertice_1 < $lat_point ||  $lat_point < $lat_vertice_2 && $lng_point < $lng_vertice_1 || $lng_vertice_2 < $lng_point ) {
	$status = '0';
} else {
	$status = '1';
}

$cnx = mysql_connect("localhost", "admin123", "admin123")
  or die("Could not connect: " . mysql_error());
mysql_select_db("tracker2", $cnx);

$sql = "INSERT INTO geo_fence (coordenadas,nome,imei,tipo,tipoEnvio,tipoAcao,dt_incao,disp) VALUES('$cerca','$nome','$imei','$status','$tipoEnvio','$tipoAcao','". date("d/m/Y") ." ". date("H:i:s") ."','S')";
$resultado = mysql_query($sql)
or die (mysql_error());

echo "<script language='javascript'>alert('Cerca criada com sucesso!');location.href='mapa.php';</script>";

mysql_close($cnx);
?>
