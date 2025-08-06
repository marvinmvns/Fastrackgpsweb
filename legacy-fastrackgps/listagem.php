<?php include('../shared-modules/config/seguranca.php');
	  include('../shared-modules/config/google-maps.php');
	  
header("Content-Type: text/html; charset=utf-8");

$q=$_GET["imei"];

$con = mysql_connect('localhost', 'admin123', 'admin123');
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

  
   function reverse_geocode($lat, $lon) {
	$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&key=AIzaSyAkFTgGKROIxJc9ZgJBMoGwbrIsDwlv-3I&sensor=false";
    $data = json_decode(file_get_contents($url));
    if (!isset($data->results[0]->formatted_address)){
       
        $baseURL = "http://dev.virtualearth.net/REST/v1/Locations";
          $key = "Aqowm1lStvPP9K-nXuJkengNfNa2kgI0Y4yLL9DUpT9jVMeCUjEOPpwc-8Avqgzm";
          $point = $lat.",".$lon;
          $revGeocodeURL = $baseURL."/".$point."?output=xml&key=".$key;
          $rgOutput = file_get_contents($revGeocodeURL);
          $rgResponse = new SimpleXMLElement($rgOutput);
          $address = $rgResponse->ResourceSets->ResourceSet->Resources->Location->Address->FormattedAddress;
   		  return $address;
    }
    return $data->results[0]->formatted_address;
}
  
  
mysql_select_db("tracker2", $con);

echo "<table class='stripeMe'>
	<thead>
		<tr class='alt'>
			<th>Data</th>
			<th>Hora</th>
			<th>Latitude</th>
			<th>Longitude</th>
			<th>Velocidade</th>
			<th>Local</th>
			<th>Ver Mapa</th>
		</tr>
	</thead>
	<tbody>";
	
//Checando se está no modo SMS
$res = mysql_query("SELECT 1 FROM bem where imei = '$q' and modo_operacao = 'SMS' and cliente = $cliente");
if (mysql_num_rows($res) != 0) {

  echo "<tr class=''>";
	  echo "<td colspan='8' align='center'><b style='padding:3px'>Atenção:</b>Este gps está operando em modo <b style='padding:0px'>SMS</b>. Para o rastreamento, ative o modo GPRS. Para os últimos registros, ver em histórico.</td>";
  echo "</tr>";
  
} else {
	
	if (!mysql_query("delete from gprmcaux where address is null", $con))
			{
				//die('Error: ' . mysql_error());
			}
	
	
	
	$loopcount = 0;
	$class = "";
	
	$sql="SELECT id, infotext, date, latitude, longitude, speed, address
		  FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = '". $q ."' ORDER BY date DESC, id DESC LIMIT 10";
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
		
	//	$statusIginicao = $data['ignicao'];

		$address = utf8_encode($data['address']);
		//Testa se tem endereço, se nao tiver obtem do google geocode e grava
		
	
		$latitude =	round($latitudeDecimalDegrees , 4);
		$longitude = round($longitudeDecimalDegrees, 4);
	
	
		
		 if ($address == null or $address == "")
		{
   
            $lat = $latitude;
		    $lon = $longitude;

				
			
			
			  	$sql2="SELECT address
		  FROM gprmcaux WHERE    
		                  latitude =  '$lat'
                 		  and longitude = '$lon'						     
						  ";
	        $result2 = mysql_query($sql2);
		
	        while($data2 = mysql_fetch_assoc($result2))
	         {
	               $address = utf8_encode($data2['address']);
			                		 
                  
		     }

			 
			if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."', date = date where id = $idRota", $con))
			{
				//die('Error: ' . mysql_error());
			}
		}
		
		
		
		if ($address == null or $address == "")
		{
   
            $address = reverse_geocode($latitudeDecimalDegrees,$longitudeDecimalDegrees);
			
			
				
				
				require_once 'Connection.simple.php';
	            $conn = dbConnect();
			    $sqlx2 = "insert into gprmcaux  (latitude, longitude, address) values (:latitude, :longitude, :address)";						
				$stmt = $conn->prepare($sqlx2);
				$stmt->bindParam(':latitude',  $lat, PDO::PARAM_STR);	
				$stmt->bindParam(':longitude', $lon, PDO::PARAM_STR);
				$stmt->bindParam(':address',   $address, PDO::PARAM_STR);
				$stmt->execute();	
				
			
			
			
			

			 
			if (!mysql_query("UPDATE gprmc set address = '". utf8_decode($address) ."', date = date where id = $idRota", $con))
			{
				//die('Error: ' . mysql_error());
			}
		}

		$img = ""; //adiciona imagens de alerta na grid
		switch($infotext)
		{
			case "low battery": $img = "<img src='imagens/battery-low.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Fraca' alt='Bat. Fraca' />"; break;
			case "help me": $img = "<img src='imagens/help.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='SOS!' alt='SOS!' />"; break;
			case "speed": $img = "<img src='imagens/velocidade.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Velocidade' alt='Velocidade' />"; break;
			case "block": $img = "<img src='imagens/bloqueado.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bloqueado' alt='Bloqueado' />"; break;
			
			default: $img = "";
		}
		
		if (($loopcount % 2) == 0) 
			$class = "alt";
		else
			$class = "";

		  echo "<tr class='". $class ."' onmouseover=\"this.className='alt over'\" onmouseout=\"this.className='". $class ."'\">";
		//	  echo "<td><img src='imagens/chave". $statusIginicao .".png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='' alt='' /> </td>";
			  echo "<td>" . date('d/m/Y', strtotime($data['date'])) . "</td>";
			  echo "<td>" . date('H:i:s', strtotime($data['date'])) . "</td>";
			  echo "<td>" . $latitudeDecimalDegrees . "</td>";
			  echo "<td>" . $longitudeDecimalDegrees . "</td>";
			  echo "<td>" . floor($speed). " Km/h" . " </td>";
			  echo "<td>" . $address . " </td>";
			  echo "<td> <input type=\"submit\" value=\"Ver\" class=\"botaoBranco\" onclick=\"parent.main.verNoMapa(" . $latitudeDecimalDegrees . "," . $longitudeDecimalDegrees . "); this.style.color='#c0c0c0'; \" />$img</td>";
		  echo "</tr>";
	  
		$loopcount++;
	}
	
	if ($loopcount == 0) {
		if ($q == "ALL") {
			echo "<tr class=''>";
			echo "<td colspan='8' align='center'> Visualizando toda a frota. Aguarde carregar no mapa. Clique sobre o ícone para identificar. </td>";
			echo "</tr>";
		} else {
			echo "<tr class=''>";
			//echo "<td colspan='8' align='center'> Nenhum registro foi encontrado! Aguarde o sinal do GPS, ou configure corretamente. </td>";
			echo "</tr>";
		}
	}

}


//Segunda tabela!

	$sql="SELECT * FROM bem where imei = '$q' ";
	$result = mysql_query($sql);
	while($data = mysql_fetch_assoc($result))
	{
	  $idoutros = $data['id'];	  
	}


$res = mysql_query("SELECT 1 FROM bem where imei = '$q' and modo_operacao = 'SMS' and cliente = $cliente");
if (mysql_num_rows($res) != 0) {

  echo "<tr class=''>";
	  echo "<td colspan='8' align='center'><b style='padding:3px'>Atenção:</b>Este gps está operando em modo <b style='padding:0px'>SMS</b>. Para o rastreamento, ative o modo GPRS. Para os últimos registros, ver em histórico.</td>";
  echo "</tr>";
  
} else {
	
	$loopcount = 0;
	$class = "";
	
	$sql="SELECT id, extended_info, time, latitude, longitude, speed, address
		  FROM positions WHERE device_id = '". $idoutros. "' ORDER BY time DESC, id DESC LIMIT 6";
	$result = mysql_query($sql);

	while($data = mysql_fetch_assoc($result))
	{
		$idRota = $data['id'];
		$latitude = $data['latitude'];
		$longitude = $data['longitude'];
		
		// Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
		$tracker2date = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['time']);

		
		$speed = $data['speed'] * 1.609344 ;

		$infotext = $data['extended_info'];
		

		$address = utf8_encode($data['address']);

					
		
		if ($address == null or $address == "")
		{
   
            $address = reverse_geocode($latitude,$longitude);

			 
			if (!mysql_query("UPDATE positions set address = '". utf8_decode($address) ."' where id = $idRota", $con))
			{
				//die('Error: ' . mysql_error());
			}
		}

		$img = ""; //adiciona imagens de alerta na grid
		switch($infotext)
		{
			case "low battery": $img = "<img src='imagens/battery-low.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Fraca' alt='Bat. Fraca' />"; break;
			case "help me": $img = "<img src='imagens/help.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='SOS!' alt='SOS!' />"; break;
			case "speed": $img = "<img src='imagens/velocidade.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Velocidade' alt='Velocidade' />"; break;
			case "block": $img = "<img src='imagens/bloqueado.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bloqueado' alt='Bloqueado' />"; break;
			
			default: $img = "";
		}
		
		if (($loopcount % 2) == 0) 
			$class = "alt";
		else
			$class = "";

		  echo "<tr class='". $class ."' onmouseover=\"this.className='alt over'\" onmouseout=\"this.className='". $class ."'\">";
		//	  echo "<td><img src='imagens/chave". $statusIginicao .".png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='' alt='' /> </td>";
			  echo "<td>" . date('d/m/Y', strtotime($data['time'])) . "</td>";
			  echo "<td>" . date('H:i:s', strtotime($data['time'])) . "</td>";
			  echo "<td>" . $latitude . "</td>";
			  echo "<td>" . $longitude . "</td>";
			  echo "<td>" . floor($speed). " Km/h" . " </td>";
			  echo "<td>" . $address . " </td>";
			  echo "<td> <input type=\"submit\" value=\"Ver\" class=\"botaoBranco\" onclick=\"parent.main.verNoMapa(" . $latitude . "," . $longitude . "); this.style.color='#c0c0c0'; \" />$img</td>";
		  echo "</tr>";
	  
		$loopcount++;
	}
	
	if ($loopcount == 0) {
		if ($q == "ALL") {
			echo "<tr class=''>";
			echo "<td colspan='8' align='center'> Visualizando toda a frota. Aguarde carregar no mapa. Clique sobre o ícone para identificar. </td>";
			echo "</tr>";
		} else {
			echo "<tr class=''>";
		//	echo "<td colspan='8' align='center'> Nenhum registro foi encontrado! Aguarde o sinal do GPS, ou configure corretamente. </td>";
			echo "</tr>";
		}
	}

}

//echo "<td>" . $address. " </td>"; 
echo "</tbody>";
echo "</table>";

mysql_close($con);
?>
