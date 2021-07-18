<?php include('seguranca.php'); ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php
if ($_GET['imei'] <> "") {
	$imei = $_GET['imei'];
} elseif ($_POST['imei'] <> "") {
	$imei = $_POST['imei'];
}
?>

<?php
$cnx = mysql_connect("localhost", "admin123", "admin123")
	or die("Could not connect: " . mysql_error());
mysql_select_db("tracker2", $cnx);
?>

<head>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<script type="text/javascript" src="javascript/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="javascript/polygon.min.js"></script>
	<script type="text/javascript">
	$(function(){
		  //create map
<?php
		$sql = "SELECT * FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = $imei order by id desc limit 1,1";
		$resultado = mysql_query($sql)
		or die (mysql_error());

		if ( mysql_num_rows($resultado) == 0 ) {

		
		  $sql="SELECT * FROM bem where imei = '$imei' ";
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
		
		    echo "var singapoerCenter=new google.maps.LatLng($latitude,$longitude);";
			//echo "map.setCenter(new GLatLng(-3.757063,-38.530316), 13);";

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

			echo "var singapoerCenter=new google.maps.LatLng($latitudeDecimalDegrees,$longitudeDecimalDegrees);";

	               $latitude = $latitudeDecimalDegrees;
        	       $longitude = $longitudeDecimalDegrees;

		}
		?>

		 var myOptions = {
		  	zoom: 13,
		  	center: singapoerCenter,
		  	mapTypeId: google.maps.MapTypeId.ROADMAP
		  }

		 map = new google.maps.Map(document.getElementById('main-map'), myOptions);

		 var creator = new PolygonCreator(map);

		 //reset
		 $('#btnApagarPontos').click(function(){
		 		creator.destroy();
		 		creator=null;

		 		creator=new PolygonCreator(map);
		 });

		 var imei = "<?php echo $imei; ?>";
                 var latitude = "<?php echo $latitude; ?>";
                 var longitude = "<?php echo $longitude; ?>";

		 //show paths
		 $('#btnSalvar').click(function() {

				if($('#NomeCerca').val()=="" || $('#NomeCerca').val()==null) {
					window.alert("Digite Nome do Arquivo");
				} else {
					if(null==creator.showData()) {
						window.alert("Favor criar ou fechar o poligono");
					} else {
						window.location.href = "incluir_cerca.php?imei="+ imei +"&latitude="+ latitude  +"&longitude="+ longitude  +"&NomeCerca="+ $('#NomeCerca').val() +"&cerca="+ creator.showData() +"&tipoAcao=0";
					}
				}
		 });

	});
	</script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"  />
<meta name="Revisit-after" content="60">
<title></title>
<style type="text/css">
div.flutuante {
  margin: 0;
  text-align: center;
  position: fixed;
  top: 1em;
  left: auto;
  right: 1em;
  background-color:#FFFFFF;
  font-family:Verdana;
  font-size:7pt;
}
</style>
</head>
<body onLoad="load()">
<div id="main-map" style="width:100%; height: 500px"></div>
<div class="flutuante">
<span id="help" style="color: #666;">Digite um nome para "Cerca" e defina os pontos
<input name="NomeCerca" type="text" id="NomeCerca" size="45" maxlength="45">
<input type="submit" name="btnSalvar" id="btnSalvar" value="Salvar">
<input type="submit" name="btnApagarPontos" id="btnApagarPontos" value="Apagar os Pontos">
</span>
</div>
</body>

<?php
mysql_close($cnx);
?>

</html>
