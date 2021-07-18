<?php

require_once __DIR__ . '/config.php';

$lat = $_POST['lat'];

$lng = $_POST['lng'];

if(!empty($lat) && !empty($lng)){
	$sql = "insert into markers(lat,lng) values(?,?)";

	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	$stmt = $mysqli->prepare($sql);

        $stmt->bind_param("ss",$lat,$lng);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
}

?>
