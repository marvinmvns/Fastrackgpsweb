<?php


function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}


echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";

//Checando se está no modo SMS
$res = mysql_query("SELECT 1 FROM bem where imei = '$q' and modo_operacao = 'SMS' and cliente = $cliente");
if (mysql_num_rows($res) != 0) {


  
} else {
	
	$loopcount = 0;
	$class = "";
	
	$sql="SELECT id, infotext, date, latitude, longitude, speed, address
		  FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = '". $q ."' ORDER BY date DESC, id DESC LIMIT 1";
	$result = mysql_query($sql);

	while($data = mysql_fetch_assoc($result))
	{
		$idRota = $data['id'];
		
		// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
		$tracker2date = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['date']);
		
		$speed = $data['speed'] * 1.609344;
		
		$latitudeDecimalDegrees = $data['latitude'];
		$longitudeDecimalDegrees = $data['longitude'];
		

		$infotext = $data['infotext'];
		

	



	}
				}
	





?>