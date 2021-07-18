<?php

require_once __DIR__ . '/config.php';


$markers = array();
$sql = "select latitude,longitude from positions limit 1";
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if($res = $mysqli->query($sql)){
	while($row=$res->fetch_assoc()){
                $lat = $row['latitude'];
	            $lng = $row['longitude'];
                $data= array("lat"=>$lat,"lng"=>$lng);
                $marker[] = $data;
	}

        $markers = array("markers"=>$marker);

        echo json_encode($markers);
}


?>