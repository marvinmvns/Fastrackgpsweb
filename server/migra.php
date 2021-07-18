#!/usr/bin/php -q

<?php

$con = mysql_connect("localhost", "root", "suasenha");
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db("tracker2", $con);

	$loopcount = 0;
	$class = "";
	
	$sql="SELECT id, date, imei, phone, satelliteFixStatus, latitudeDecimalDegrees,  latitudeHemisphere, longitudeDecimalDegrees, longitudeHemisphere, speed, gpsSignalIndicator, address, infotext, address, ignicao
		  FROM gprmcbkp where id between '11163883' and '11240124' ";

	

	
	$result = mysql_query($sql);

	while($data = mysql_fetch_assoc($result))
	{		
		$imei = $data['imei'];
		$speed = $data['speed'];
		$infotext = $data['infotext'];	
		$date = $data['date'];
		$phone = $data['phone'];
		$satelliteFixStatus = $data['satelliteFixStatus'];
		$gpsSignalIndicator = $data['gpsSignalIndicator'];
		$infotext = $data['infotext'];
		$address = $data['address'];
		$ignicao = $data['ignicao'];
		$id = $data['id'];
		
	
	
								
		// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
		$tracker2date = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['date']);
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
		echo ' ';
		echo $id;
		
		
		
	  
	 
	    
   		mysql_query("INSERT INTO gprmc (id, date, imei, phone, satelliteFixStatus, latitude, longitude, speed, gpsSignalIndicator, infotext, address, ignicao ) 
                                VALUES ('$id', '$date', '$imei', '$phone', '$satelliteFixStatus', '$latitudeDecimalDegrees', '$longitudeDecimalDegrees', '$speed', '$gpsSignalIndicator', '$infotext', '$address', '$ignicao' )",  $con);

		}
		mysql_close($con);
?>
