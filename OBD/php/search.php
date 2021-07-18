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
 
 

     function fc($given_value)
    {
        $celsius=5/9*($given_value-32);
        return $celsius ;
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
		
		$sql2 = 'SELECT  data, kmacul ,combus, medcomb, tempdir, vel ,batt ,tempag ,percacel, rtmotor, tnsbatt, diagcar1, diagcar2, diagcar3, diagcar4  FROM obd where imei = ? and data between ? and ? order by id ';   
		$stmt = $conn->prepare($sql2);
		$stmt->bindValue(1, $datax, PDO::PARAM_INT);
		$stmt->bindValue(2, $dtinicio, PDO::PARAM_STR);
		$stmt->bindValue(3, $dtfim,    PDO::PARAM_STR);
		$results = $stmt->execute();
		$rows = $stmt->fetchAll();
		$error = $stmt->errorInfo();
		echo $error[2];
		
		
		
		
		
		
		
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
			
			
		$data = $row['data'];
		$kmacul = $row['kmacul'];
		$combus = $row['combus'];
		$medcomb = $row['medcomb'];
		$tempdir = $row['tempdir'];
		$vel = $row['vel'];
		$batt = $row['batt'];
		$tempag = $row['tempag'];
		$percacel = $row['percacel'];
		$rtmotor = $row['rtmotor'];
		$tnsbat = $row['tnsbatt'];
		$diagcar1 = $row['diagcar1'];
		$diagcar2 = $row['diagcar2'];
		$diagcar3 = $row['diagcar3'];
		$diagcar4 =	 $row['diagcar4'];	
		
		
	    if ($tempag == '+')
		{
			$tempag = "0 ºC";
		}
		else
		{ 
		
		$tempag = fc($tempag);
		$tempag = round($tempag,1);
		$tempag =  $tempag . "  ºC";
		}
		
		
		
			
			echo "<tr>";				
				echo "<td>".$data."</td>";
				echo "<td>".$kmacul."</td>";
				echo "<td>".$combus."</td>";
				echo "<td>".$medcomb."</td>";
				echo "<td>".$tempdir."</td>";
				echo "<td>".$vel."</td>";
				echo "<td>".$batt."</td>";
				echo "<td>".$tempag."</td>";
				echo "<td>".$percacel."</td>";
				echo "<td>".$rtmotor."</td>";
				echo "<td>".$tnsbat."</td>";
				echo "<td>".$diagcar1."</td>";
				echo "<td>".$diagcar2."</td>";
				echo "<td>".$diagcar3."</td>";
				echo "<td>".$diagcar4."</td>";

		   echo "</tr>";	
			
			
		}
		
	}
	
		
		
	
   

	
?>
