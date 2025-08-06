<?php include('../shared-modules/config/seguranca.php'); ?>

<!DOCTYPE html>

<?php

if ($_GET['imei'] <> "") {
	$inputImei = $_GET['imei'];
} elseif ($_POST['imei'] <> "") {
	$inputImei = $_POST['imei'];
}

$strImei = substr($inputImei, 11, 15);
$strId = substr($inputImei, 0, 11);
?>

<?php
$cnx = mysql_connect("localhost", "admin123", "admin123")
	or die("Could not connect: " . mysql_error());
mysql_select_db("tracker2", $cnx);
?>

<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<title>Google Maps JavaScript API v3 Example: Polygon Arrays</title>
<link href="../shared-modules/assets/css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">

  var map;
  var infoWindow;

  function initialize() {

  <?php
		$sql = "SELECT * FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = $strImei order by id desc limit 1,1";
		$resultado = mysql_query($sql)
		or die (mysql_error());

		if ( mysql_num_rows($resultado) == 0 ) {
		
		
		    $sql="SELECT * FROM bem where imei = '$strImei' ";
	        $result = mysql_query($sql);
	        while($data = mysql_fetch_assoc($result))
			{
				$idoutros = $data['id'];	  
			}
			
				$sql="SELECT * FROM positions WHERE device_id = '". $idoutros. "' ORDER BY id desc limit 1,1";
	            $result = mysql_query($sql);
				while ($data = mysql_fetch_assoc($result))
				
				{
							$latitude = $data['latitude'];
			                $longitude = $data['longitude'];
				}
				
			
				
				echo "var myLatLng = new google.maps.LatLng($latitude,$longitude);";

            	$latitude = $data['latitude'];
			    $longitude = $data['longitude']

		//	echo "map.setCenter(new GLatLng(-3.757063,-38.530316), 13);";

		} elseif ( mysql_num_rows($resultado) - 0 ) {

			while ($data = mysql_fetch_assoc($resultado)) {
				strlen($data['latitudeDecimalDegrees']) == 9 && $data['latitudeDecimalDegrees'] = '0'.$data['latitudeDecimalDegrees'];
				$g = substr($data['latitudeDecimalDegrees'],0,3);
				$d = substr($data['latitudeDecimalDegrees'],3);
				$latitudeDecimalDegrees = $g + ($d/60);
				$data['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;

				strlen($data['longitudeDecimalDegrees']) == 9 && $data['longitudeDecimalDegrees'] = '0'.$data['longitudeDecimalDegrees'];
				$g = substr($data['longitudeDecimalDegrees'],0,3);
				$d = substr($data['longitudeDecimalDegrees'],3);
				$longitudeDecimalDegrees = $g + ($d/60);
				$data['longitudeHemisphere'] == "S" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

				$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
			}
			echo "var myLatLng = new google.maps.LatLng($latitudeDecimalDegrees,$longitudeDecimalDegrees);";

			$latitude = $latitudeDecimalDegrees;
			$longitude = $longitudeDecimalDegrees;

		}
		?>

    var myOptions = {
      zoom: 15,
      center: myLatLng,
      mapTypeId: google.maps.MapTypeId.TERRAIN
    };

    var bermudaTriangle;

    map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);

	<?php
	$sql = "SELECT * FROM geo_fence WHERE id = '$strId'";
	$resultado = mysql_query($sql)
		or die (mysql_error());

    echo "var triangleCoords = [";
	while ($linha = mysql_fetch_assoc($resultado)) {
	$id = $linha["id"];
	$imei = $linha["imei"];
	$coordenada = $linha["coordenadas"];
	$replace = str_replace("|", "),\n new google.maps.LatLng(", $coordenada);
	}
	echo "new google.maps.LatLng(". $replace .")";
	echo "]";
	?>

    bermudaTriangle = new google.maps.Polygon({
      paths: triangleCoords,
      strokeColor: "#FF0000",
      strokeOpacity: 0.8,
      strokeWeight: 3,
      fillColor: "#FF0000",
      fillOpacity: 0.35,
      editable: true
    });

    bermudaTriangle.setMap(map);

    // Add a listener for the click event
    google.maps.event.addListener(bermudaTriangle, 'click', showArrays);

    infowindow = new google.maps.InfoWindow();
  }

  function showArrays(event) {

	var imei = "<?php echo $strImei; ?>";
	var id = "<?php echo $strId;?>";
	var latitude = "<?php echo $latitude; ?>";
	var longitude = "<?php echo $longitude; ?>";

    // Since this Polygon only has one path, we can call getPath()
    // to return the MVCArray of LatLngs
    var vertices = this.getPath();
    var contentString = "latitude="+ latitude +"&longitude="+ longitude +"&imei="+ imei +"&id="+ id +"&coordenadas=";

    // Iterate over the vertices.
	for (var i = 0; i < vertices.length; i++) {
	var xy = vertices.getAt(i);
		if (i+1 == vertices.length){
			contentString += xy.lat() +"," + xy.lng();
		} else {
			contentString += ''+ xy.lat() +"," + xy.lng() +'|';
		}
	}

	decisao = confirm("Deseja gravar o perímetro? ");
	if ( decisao ) {
		location.href="alterar_cerca.php?" + contentString;
	} else {
		initialize();
	}

    infowindow.open(map);
  }
</script>

<?php
mysql_close($cnx);
?>

</head>
<body onload="initialize()">
  <div id="map_canvas"></div>
</body>
</html>
