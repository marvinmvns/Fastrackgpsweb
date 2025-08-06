<?php

$rec = "imei:359710041388647,tracker,1409210511,,F,211118.000,A,2330.2033,S,04626.6208,W,0.00,0,,0,0,0.00%,,;";

$parts = explode(',',$rec);


					  $imei			  			  = substr($parts[0],5);
					  $infotext			  		  = $parts[1];
					  $trackerdate                = $parts[2];
					  $gpsSignalIndicator         = $parts[4];
					  
					  //Se gpsSignalIndicator <> L, pega o outros dados
					  if ($gpsSignalIndicator != 'L') {
						  $phone                      = $parts[3];
						  $satelliteFixStatus         = $parts[6];					  
						  $latitudeDecimalDegrees     = $parts[7];
						  $latitudeHemisphere         = $parts[8];
						  $longitudeDecimalDegrees    = $parts[9];
						  $longitudeHemisphere        = $parts[10];
						  $speed                      = $parts[11];
													  }
															
															
		
	
		strlen($latitudeDecimalDegrees) == 9 && $latitudeDecimalDegrees = '0'.$latitudeDecimalDegrees;		
		$g = substr($latitudeDecimalDegrees,0,3);
		$d = substr($latitudeDecimalDegrees,3);
		$latitudeDecimalDegrees = $g + ($d/60);		
		$latitudeHemisphere == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;
		
	


		strlen($longitudeDecimalDegrees) == 9 && $longitudeDecimalDegrees = '0'.$longitudeDecimalDegrees;
		$g = substr($longitudeDecimalDegrees,0,3);
		$d = substr($longitudeDecimalDegrees,3);
		$longitudeDecimalDegrees = $g + ($d/60);
		$longitudeHemisphere == "S" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

		$longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;
		
			echo $latitudeDecimalDegrees . "\n";
		echo $longitudeDecimalDegrees;
		
		
		
		//echo  $longitudeDecimalDegrees;
		//echo  $latitudeDecimalDegrees;

?>