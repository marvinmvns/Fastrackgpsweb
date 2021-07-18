<?php

include('seguranca.php');

$id = $_GET["id"];
$imei = $_GET["imei"];
$coordenadas = $_GET["coordenadas"];
$latitude = $_GET["latitude"];
$longitude = $_GET["longitude"];

$exp = explode("|", $coordenadas);

if( ( count($exp) ) <= 4 ) {
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

if ( $lat_vertice_1 < $latitude Or $latitude < $lat_vertice_2 And $longitude < $lng_vertice_1 Or $lng_vertice_2 < $longitude ) {
	$status = '0';
} else {
	$status = '1';
}

$cnx = mysql_connect("localhost", "admin123", "admin123")
  or die("Could not connect: " . mysql_error());
mysql_select_db("tracker2", $cnx);

$sql = "UPDATE geo_fence SET coordenadas = '$coordenadas', tipo = '$status', disp = 'S', dt_altao = '". date("d/m/Y") ." ". date("H:i:s") ."' WHERE id = '$id'";
$resultado = mysql_query($sql)
or die (mysql_error());

echo "<script language='javascript'>alert('Cerca alterada com sucesso!'); window.location = 'mapa.php?imei=$imei'</script>";
mysql_close($cnx);
?>
