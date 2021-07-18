<?php



 
 $latarray = array();
 $longarray = array();
 $somar = array();
 $contador = 0;
 $accon = array();
 $accoff = array();
 $contaccon = 0;
 $contaccoff = 0;
 $data = 0;
 $conta = "S";
 $imgmotor = "<img src='icon-grey.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:32px;width:32px' title='' alt='' />";
 
 

 
 
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



		function inverteData($data){
    if(count(explode("/",$data)) > 1){
        return implode("-",array_reverse(explode("/",$data)));
    }elseif(count(explode("-",$data)) > 1){
        return implode("/",array_reverse(explode("-",$data)));
    }
}

function getDir($b)
{
   $dirs = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N');
   return $dirs[round($b/45)];
}

 

 
    function reverse_geocode($lat, $lon) {
	$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=false";
    $data = json_decode(file_get_contents($url));
	
	
	
  
    if (!isset($data->results[0]->formatted_address)){
          $baseURL = "http://dev.virtualearth.net/REST/v1/Locations";
          $key = "Ahlj3rt6rBOUmnWE2OVnKNvNrMeAv9vpu5t0HO7HlsO2v6Qi-ZgJ5Y-FjH45iLoZ";
          $point = $lat.",".$lon;
          $revGeocodeURL = $baseURL."/".$point."?output=xml&key=".$key;
          $rgOutput = file_get_contents($revGeocodeURL);
          $rgResponse = new SimpleXMLElement($rgOutput);
          $address = $rgResponse->ResourceSets->ResourceSet->Resources->Location->Address->FormattedAddress;
		  return $address;
    }
	
	
    return $data->results[0]->formatted_address;
}
  
 


/**
*	@author 	Ing. Israel Barragan C.  Email: ibarragan at behstant dot com
*	@since 		07-Nov-2013
*	##########################################################################################
*	Comments:
*	This file is to show how to retrieve records from a database with PDO
*	The records are shown in a HTML table.
*
*	Requires:
*	Connection.simple.php, get this file here: http://behstant.com/blog/?p=413
*   jQuery and Boostrap.
*
* 	LICENCE:
*	You can use this code to any of your projects as long as you mention where you
* 	downloaded it and the author which is me :) Happy Code.
*
* 	LICENCIA:
*	Puedes usar este código para tus proyectos, pero siempre tomando en cuenta que
* 	debes de poner de donde lo descargaste y el autor que soy yo :) Feliz Codificación.
*	##########################################################################################
*	@version
*	##########################################################################################
*	1.0	|	07-Nov-2013	|	Crea	tion of new file to search a record.
*	##########################################################################################
*/
	require_once 'Connection.simple.php';
	$conn = dbConnect();
	$OK = true; // We use this to verify the status of the update.
	// If 'buscar' is in the array $_POST proceed to make the query.
	if (isset($_GET['name'])) {
		// Create the query
		$drini = $_GET['dtini']; 
		$hrini = $_GET['hrini'];
		$drfim = $_GET['dtfim']; 
		$hrfim = $_GET['hrfim'];
		
		
		
		
		$drini = inverteData($drini);
		$drfim = inverteData($drfim);
		
		
		$dtinicio = $drini  ." ". $hrini . ":00";
		$dtfim =    $drfim  ." ". $hrfim .  ":00";
		
		$datax = $_GET['name'];
		
		$sql2 = 'SELECT  date, phone ,satelliteFixStatus, latitude, longitude, speed ,gpsSignalIndicator ,infotext ,address FROM gprmc where imei = ? and date between ? and ? order by id ';   
		$stmt = $conn->prepare($sql2);
		$stmt->bindValue(1, $datax, PDO::PARAM_INT);
		$stmt->bindValue(2, $dtinicio, PDO::PARAM_STR);
		$stmt->bindValue(3, $dtfim,    PDO::PARAM_STR);
		$results = $stmt->execute();
		$rows = $stmt->fetchAll();
		$error = $stmt->errorInfo();
		//echo $error[2];
		
		
		
		
		
		
		
							$clicli = $datax;		                    
							$conn = dbConnect();
							$OK = true; // We use this to verify the status of the update.
							$sql3 = "select  name, identificacao from bem where imei = ? order by name";
							$stmt = $conn->prepare($sql3);
							$stmt->bindParam(':clicli', $clicli, PDO::PARAM_INT);
							$results = $stmt->execute(array($clicli));			
							$rowsx = $stmt->fetchAll();
							$error = $stmt->errorInfo();						
						
				
							
						   if(empty($rowsx)) {
							echo "Antes de imprimir você deve fazer uma consulta";
											}
							else {					
							foreach ($rowsx as $rowx) {													
																
								$nome = $rowx[name];
								$identificacao = $rowx[identificacao];
					}
								}
												
				
		 $dtiniciox  = date(" d/m/y H:i ",strtotime($dtinicio));
		 $dtfimx =     date(" d/m/y H:i ",strtotime($dtfim));
		
		
		//$dtiniciox = strtotime($dtinicio);
		//$dtiniciox = date("d/m/y h:m", $dtiniciox);
		//$dtfimx = strtotime($dtfim);
		//$dtfimx = date("d/m/y h:m", $dtfimx);
		
		

		  echo '<div id="info" style="display:none" align="left"> ';
          echo '<font style="font-size:18px">Impressão de Histórico <br/></font>';
          echo '<font style="font-size:18px"><b>Dados do veículo </b> <br/>';
		  echo '<font style="font-size:12px">';
	
                echo "
                    Número imei: $datax <br/>
                    Nome: $nome <br/>
                    Identificação: $identificacao  <br/>
                    Período: " . $dtiniciox . " a " . $dtfimx . " 
                    <br/><br/>
                ";
           
		  
         
          echo ' </font>';
          echo '</div>';		
		
		
		
		
		
	}
	// If there are no records.
	if(empty($rows)) {
		echo "<tr>";
			echo "<td colspan='4'>Nenhum registro encontrado</td>";
			echo "<td>".$_GET['name']."</td>";
			echo "<td>".$dtinicio ."</td>";	
			echo "<td>".$dtfim."</td>";
				
		echo "</tr>";
	}
	else {
		foreach ($rows as $row) {
			
		$address = $row['address'];
		$address = utf8_encode($address);		
		$speed = $row['speed'] * 1.609;
		$velocidade = round($speed);		
	    $velocidade = $velocidade . " km/h";
		$infotext =  $row['infotext'];	 			
		$date = $row['date'];	
		$data = date(" d/m/y H:i:s ",strtotime($date));
		$datac = date(" d/m/y H:i:s ",strtotime($date));
		$latitudeDecimalDegrees = $row['latitude'];
		$longitudeDecimalDegrees = $row['longitude'];
		$latitudeDecimalDegrees =	round($latitudeDecimalDegrees , 4);
		$longitudeDecimalDegrees =	round($longitudeDecimalDegrees, 4);
		
		
			
			
			  
			if ($address == null or $address == "")
			{
	
				 $sql2 = 'SELECT address FROM gprmcaux where latitude = ? and longitude = ?';   
				 $stmt = $conn->prepare($sql2);
				$stmt->bindValue(1, $latitudeDecimalDegrees  , PDO::PARAM_STR);
				$stmt->bindValue(2, $longitudeDecimalDegrees ,PDO::PARAM_STR);
				$results = $stmt->execute();
				$rowsx = $stmt->fetchAll();
			
				if(empty($rowsx)) {
					
			
				//$json = json_decode(file_get_contents("http://nominatim.openstreetmap.org/reverse?format=json&lat=".$latitudeDecimalDegrees."&lon=".$longitudeDecimalDegrees));	
				//$address = $json->display_name;
				//$error = $json->error;
				//if ($error == "Unable to geocode")
				//{				
					$address = reverse_geocode($latitudeDecimalDegrees,$longitudeDecimalDegrees);
				//}
				
				
			    $sqlx2 = "insert into gprmcaux  (latitude, longitude, address) values (:latitude, :longitude, :address)";						
				$stmt = $conn->prepare($sqlx2);
				$stmt->bindParam(':latitude',  $latitudeDecimalDegrees, PDO::PARAM_STR);	
				$stmt->bindParam(':longitude', $longitudeDecimalDegrees, PDO::PARAM_STR);
				$stmt->bindParam(':address',    $address, PDO::PARAM_STR);
				$stmt->execute();		

				$sqlx = "UPDATE gprmc set address = :address where imei = :imei and date = :date ";						
				$stmt = $conn->prepare($sqlx);
				$stmt->bindParam(':address', $address, PDO::PARAM_STR);	
				$stmt->bindParam(':imei', $datax, PDO::PARAM_STR);
				$stmt->bindParam(':date', $date, PDO::PARAM_STR);
				$stmt->execute();			
				
								
								}
			 else   			{
			 
			 foreach ($rowsx as $row) {
				 
				$address = $row['address']; 				 
				$sqlx = "UPDATE gprmc set address = :address where imei = :imei and date = :date ";						
				$stmt = $conn->prepare($sqlx);
				$stmt->bindParam(':address', $address, PDO::PARAM_STR);	
				$stmt->bindParam(':imei', $datax, PDO::PARAM_STR);
				$stmt->bindParam(':date', $date, PDO::PARAM_STR);
				$stmt->execute();		
										
								
									
								}			
								}
              
			 	   
			}
					
					switch($infotext)
					{
						 
					   	case "ac off" : $infotext =  "ac ar condicionado desligado"; break;
						case "ac on"  : $infotext =  "ac ar condicionado ligado"; break;
						case "acc on": $infotext =  "Motor Ligado"; 
							
								$accon[$contaccon] = $datac;	
							    $contaccon++;

						
						     $imgmotor = "<img src='icon-green.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:32px;width:32px' title='Motor Ligado' alt='Motor Ligado' />"; break;
							 
									
						
						
						case "acc off" : $infotext =  "Motor Desligado"; 
							 $accoff[$contaccoff] = $datac;
							 $contaccoff++;
						
						     $imgmotor = "<img src='icon-red.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:32px;width:32px' title='Motor Desligado' alt='Motor Desligado' />"; break;
							
						
							 
						case "acc alarm" : $infotext =  "Notificação"; break;
						    
						
						
						case "lg"     : $infotext =  "adicionado publicidade led com sucesso."; break;
						case "ac alarm"    : $infotext =  "alerta de motor ligado"; break;
						case "accident alarm": $infotext =  "alarme de acidente"; break;
						case "bonnet alarm"  : $infotext =  "alarme de capô"; break;
						case "ot"            : $infotext =  "alarme de cerca perimetro definido com sucesso!"; break;
						case "sensor"        : $infotext =  "alarme de colisão"; break;
						case "ac"            : $infotext =  "alarme de energia"; break;
						case "low battery": $infotext =  "alarme de energia  baixa "; break;
						case "speed": $infotext =  "alarme de limite de velocidade"; break;
						case "ht": $infotext =  "alarme de limite de velocidade configurado com sucesso"; break;
						case "move": $infotext =  "alarme de movimento"; break;
						case "oil": $infotext =  "alarme de óleo"; break;
						case "door": $infotext =  "alarme de porta aberta"; break;
						case "t:": $infotext =  "alarme de temperatura"; break;
						case "footbrake alarm" : $infotext =  "alarme pedal do freio"; break;
						case "lt": $infotext =  "armar alarme realizado com sucesso"; break;
						case "gg": $infotext =  "cancelado alarme de movimento com sucesso."; break;
						case "st": $infotext =  "cancelado upload de dados com sucesso."; break;
						case "dt": $infotext =  "cancelar rastreamento por gps"; break;
						case "DTC": $infotext =  "Problema no Veiculo "; break;
						case "load": $infotext =  "carregar."; break;
						case "stockade": $infotext =  "cerca de perimetro"; break;
						case "pt": $infotext =  "cerca de perimetro havia sido limpa"; break;
						case "ff": $infotext =  "close the vibration alarm success."; break;
						case "gt": $infotext =  "configuração de alarme de movimento bem sucedido"; break;
						case "deep shock": $infotext =  "deep shock awake"; break;
						case "ut": $infotext =  "definição de anglo realizada com sucesso."; break;
						case "wt": $infotext =  "definição de área ok"; break;
						case "it": $infotext =  "definição de hora local no terminal ok"; break;
						case "in": $infotext =  "dentro da área"; break;
						case "mt": $infotext =  "desarmar alarme com sucesso"; break;
						case "xt": $infotext =  "desligar salvar configuração do modo gprs "; break;
						case "jk": $infotext =  "ele será executado após a velocidade for inferior a 20 km/h"; break;
						case "yt": $infotext =  "endereço ip defino com sucesso."; break;
						case "qt": $infotext =  "enviar dados realizado com sucesso"; break;
						case "ld": $infotext =  "excluído publicidade led com sucesso."; break;
						case "lf": $infotext =  "falha em armar alarme, favor desligar acc"; break;
						case "out": $infotext =  "fora de área    "; break;
						case "vr": $infotext =  "foto recebida"; break;
						case "igniton off": $infotext =  "ignição desligada"; break;
						case "igniton on": $infotext =  "ignição ligada"; break;
						case "tracker": $infotext =  "informação de posições"; break;
						case "bb": $infotext =  "ligar motor remotamente realizado com sucesso."; break;
						case "by": $infotext =  "ligar motor remotamente sem sucesso!"; break;
						case "service": $infotext =  "maintenance alert"; break;
						case "nosignal": $infotext =  "nenhum sinal"; break;
						case "dz15": $infotext =  "o pedido de taxi por telefone tinha sido cancelado."; break;
						case "ee": $infotext =  "open the vibration alarm success."; break;
						case "bx": $infotext =  "parar motor remotamente realizado com sucesso."; break;
						case "et": $infotext =  "parar o alarme ok"; break;
						case "jt": $infotext =  "parar o motor com sucesso"; break;
						case "dg": $infotext =  "publicar o anúncio com sucesso."; break;
						case "dz10": $infotext =  "recebeu pedido de taxi por telefone"; break;
						case "kt": $infotext =  "religar motor com sucesso"; break;
						case "dz14": $infotext =  "responder pedido de taxi por telefone"; break;
						case "nt": $infotext =  "retomar o modo sms"; break;
						case "rfid": $infotext =  "rfid"; break;
						case "tt": $infotext =  "salvar configuração do modo gprs realizada com sucesso"; break;
						case "work notify": $infotext =  "schedule awake"; break;
						case "no-load": $infotext =  "sem carregamento"; break;
						case "no gps": $infotext =  "sem sinal gps"; break;
						case "help me": $infotext =  "sos panico"; break;
						case "dz16": $infotext =  "sucesso passageiro do taxi"; break;
					
					}
						
					
					
					if ($latitudeDecimalDegrees == '0' and $infotext = "informação de posições" )
					{
					
						$infotext = "Sem Sinal GPS";
						$conta = "N";
					}
					

						
					if ($speed > 1 and $conta == "S")
					{
					 $latarray[$contador] = $latitudeDecimalDegrees;
					 $longarray[$contador] = $longitudeDecimalDegrees;
					 $contador ++;
					}
					
					
					//$date = strtotime($date);
					//$date = date("d/m/y h:m:s", $date);
					
		
		
			echo "<tr>";				
				echo "<td>".$data."</td>";
				echo "<td>".round($latitudeDecimalDegrees , 4)."</td>";
				echo "<td>".round($longitudeDecimalDegrees, 4)."</td>";
				echo "<td>".$infotext."</td>";	
				echo "<td>".$address."</td>";
				echo "<td>".$velocidade."</td>";	
				echo "<td>".$imgmotor."</td>";
				
			
			
		}
		
	}
	
	$contador = $contador ;
	$conta = 0;
	$contb = 1;
	

	
	for($i=0;$i<=$contador;$i++)
								{
	 $somar[$i] = distance($latarray[$conta],$longarray[$conta],$latarray[$contb],$longarray[$contb], "K");
	 $conta++;
	 $contb++;	
	  
	if (is_nan($somar[$i])) 
	{ 
	$somar[$i] = 0;
	}
	else
	
		{ 
	
		if (round($latarray[$conta],3) == round($latarray[$contb],3) and round($longarray[$conta],3) == round($longarray[$contb],3))
		{
		
		
		$somar[$i] = 0;
		}	
	
		$somar[$i] = $somar[$i];
		}
	
	
	 
								}
								


		

			
			
		
	
	
			echo "<tr>";
			echo "<td colspan='4'></td>";
			echo "<td>Percorrido na Data = ".round(array_sum($somar))." km</td>";
			echo "<td> ".$contaccon." </td>";			
			echo "<td> ".$contaccoff." </td>";	
			echo "<td></td>";				
			echo "</tr>";
			
	
	
	
      echo ' <div id = "relat2">'	;


		
	if ($contaccon > 1)
	
	{

		
	echo ' <div id = "relat2">'	;
	echo '<table>
  <tr>
    <th style="background-color:#4297bb;" >Ligou Motor</th>
    <th style="background-color:#4297bb;">Desligou Motor</th>		
  
  </tr>';
  

   for($i=0;$i<=$contaccon;$i++)
		{			
		echo '<tr>';
		
		echo "<td> ".$accon[$i]." </td>";
		echo  "<td>".$accoff[$i]." </td>";	
		echo '</tr>'; 	
		}

		echo ' </table>';
		echo '</div>';
		
		
	}	

		
		
	
		
		
		

	
?>
