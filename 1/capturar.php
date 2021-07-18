<?php



			//teste envio de sms//
		
		$ipservidor = "jcdbrasil.ddns.net";
		$ip = "104.131.237.162 7094"; 
		$senha = "123456";
		$nro = "981989838";
		$operadora = "TI";
		
		switch ($operadora) {
    case "TI":
        $apn = "tim.br";
		$apnpassuser = "tim";
        break;
    case "CL":
        $apn = "claro.com.br";
		$apnpassuser = "claro";
        break;
    case "OI":
        $apn = "gprs.oi.com.br";
		$apnpassuser = "oi";
        break;
	case "VI":
        $apn = "zap.vivo.com.br";
		$apnpassuser = "vivo";
        break;
}
		
	
	 $x   = SendSMS($ipservidor, 8801, "", "", $nro, "begin".$senha."" );
  	sleep(1); 	
	  $x   = SendSMS($ipservidor, 8801, "", "", $nro, "imei".$senha."" );	 	
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "apn".$senha." ".$apn."" );		  
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "apnuser".$senha." ".$apnpassuser."" );  		  
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "apnpasswd".$senha." ".$apnpassuser."" ); 	
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "adminip".$senha." ".$ip."" );		
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "gprs".$senha."" ); 	
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "up".$senha." ".$apnpassuser." ".$apnpassuser."");
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "t002m***n".$senha."" );	
      $x   = SendSMS($ipservidor, 8801, "", "", $nro, "fix120s***n".$senha."" );	
		
		
		
	function SendSMS ($host, $port, $username, $password, $phoneNoRecip, $msgText) { 

    
 
    $fp = fsockopen($host, $port, $errno, $errstr);
    if (!$fp) {

        return $result;
    }
    fwrite($fp, "GET /PhoneNumber=" . rawurlencode($phoneNoRecip) . "&Text=" . rawurlencode($msgText) . " HTTP/1.0\n");
	
    if ($username != "") {
       $auth = $username . ":" . $password;
      
       $auth = base64_encode($auth);

       fwrite($fp, "Authorization: Basic " . $auth . "\n");
    }
    fwrite($fp, "\n");
  
    $res = "";
 
    while(!feof($fp)) {
        $res .= fread($fp,1);
    }
    fclose($fp);
    
 
    return $res;
}



?>