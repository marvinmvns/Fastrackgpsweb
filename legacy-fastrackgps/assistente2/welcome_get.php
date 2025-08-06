<html>
<body>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

Modelo: <?php echo $_GET["modelo"]; ?><br>
Telefone: <?php echo $_GET["telefone"]; ?><br>
Operadora: <?php echo $_GET["operadora"]; ?><br>
Senha: <?php echo $_GET["senha"]; ?><br><br>


<?php
$modelo = $_GET["modelo"];
$telefone = $_GET["telefone"];
$operadora = $_GET["operadora"];
$senha = $_GET["senha"];
$ip = $_SERVER['SERVER_ADDR'];

switch ($operadora) {
    case "Tim":
        $apn = "tim.br";
		$apnpassuser = "tim";
        break;
    case "Claro":
        $apn = "claro.com.br";
		$apnpassuser = "claro";
        break;
    case "Oi":
        $apn = "gprs.oi.com.br";
		$apnpassuser = "oi";
        break;
	case "Vivo":
        $apn = "zap.vivo.com.br";
		$apnpassuser = "vivo";
        break;
		
		case "Nextel":
        $apn = "wap.nextel3g.net.br";
		$apnpassuser = "nextel";
        break;	
}

/*


 switch ($modelo)
 {
	 case "TK101":
	 case "TK102-2":
	 case "TK103-2":
	 case "XT009":
	 case "XT011":
	 case "TK201":
	 case "TK201-2":
	 case "TK202":
	 case "TK203":
	 case "TK206":
	 case "XT107":
	      echo 'Atenção observe os espaços entre os comandos envie os sms para o seguinte numero: '.$telefone.' <br><br>';
		  echo 'Envie o comando <b>imei' .$senha. '</b> e verifique no seu e-mail se o imei cadastrado esta correto<br>';
		  echo 'Envie o comando <b>begin' .$senha. '</b> aguarde o comando <b>Begin ok !</b><br>';
	      echo "Envie o comando <b>apn".$senha, " " .$apn. '</b> recebera a mensagem <b>APN  OK!</b><br>';
	      echo 'Envie o comando <b>up'.$senha."   ".$apnpassuser. ' '.$apnpassuser.'</b> recebera a mensagem <b>APN  OK!</b> <br>';
		  echo 'Envie o comando <b>adminip'.$senha.' '.$ip.' 5006</b> recebera a mensagem <b>adminip ok!</b> <br>';
		  echo 'Envie o comando <b>gprs'.$senha.' </b>recebera a mensagem <b>GPRS OK!</b><br><br>' ; 
		  echo 'Qualquer duvida entre em contato ! marcus@segundo.me</b><br>' ;	       
	 break; 
	 case "TK103-2B":
	 case "TK104":
	 case "TK106":
     case "GPS-103":
     case "GPS-103-A":
	 case "TW-MD1101":
	 case "GPS102B":
	 case "GPS104":
	 case "TK110":
		  echo 'Atenção observe os espaços entre os comandos envie os sms para o seguinte numero: '.$telefone.' <br><br>';
		  echo 'Envie o comando <b>imei' .$senha. '</b> e verifique no seu e-mail se o imei cadastrado esta correto<br>';
		  echo 'Envie o comando <b>begin' .$senha. '</b> aguarde o comando <b>Begin ok !</b><br>';
	      echo "Envie o comando <b>apn".$senha, " " .$apn. '</b> recebera a mensagem <b>APN  OK!</b><br>';
	      echo 'Envie o comando <b>up'.$senha."   ".$apnpassuser. ' '.$apnpassuser.'</b> recebera a mensagem <b>APN  OK!</b> <br>';
		  echo 'Envie o comando <b>adminip'.$senha.' '.$ip.' 5001</b> recebera a mensagem <b>adminip ok!</b> <br>';
		  echo 'Envie o comando <b>gprs'.$senha.' </b>recebera a mensagem <b>GPRS OK!</b><br><br>' ; 
		  echo 'Qualquer duvida entre em contato ! marcus@segundo.me</b><br>' ;	    
	break;

	
	 
	case "EC-546":
	case "TT0024":
	case "T1024":
	case "T1080":
	case "T2024":
	case "T2124":
	case "T12":
	case "T4400":
	case "T8800":
	case "T15400":
	case "TK05":
	case "TK10":
	case "TK15":
	case "TK20":
	case "T18":
	case "T18H":
	case "T16":
	case "GPS105":
	case "P168":	
	     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5002</b><br>' ;	    
	break; 
	case "GL100":
		     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5003</b><br>' ;	    
	break; 
	case "GL100M":
	case "GL200":
	case "GV55":
	case "GV55 Lite":
	case "GV65":
	case "GV300N":
	case "GV65 Plus":
	case "GT200":
	case "GT300":
	case "GT500":
	case "GV200":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5004</b><br>' ;	    
	break;	
	case "Gelix":
	case "Gelix-2":
	case "GPS-911(M)":
	case "AVL-900":
	case "AVL-900(R)":
	case "AVL-900(M)":
	case "AVL-901(B)":
	case "AVL-901(C)":
	case "AVL-901(D)":
	case "AVL-921":
	case "CradlePoint IBR600":
	case "Cradlepoint IBR1100":
	case "Aspicore":
	case "Tracker for Traccar":
	case "MultiConnect rCell":
	case "M2M IP Modem F7114":
		     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5005</b><br>' ;	    
	break;
	case "TZ-AVL02":
	case "TZ-AVL03":
	case "TZ-AVL05":
	case "TZ-AVL08":
	case "TZ-AVL09":
	case "TZ-AVL10":
	case "TZ-GT08":
	case "TZ-GT09":
	case "TZ-VN06":
	case "AT03":
	case "AT06":
	case "AT06+":
	case "AT09":
	case "TZ-AVL201":
	       echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5007</b><br>' ;	    
	break;
	case "Mini MT":
	case "Enfora GSM2448":
	case "Enfora MT­4000":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5007</b><br>' ;	
    break;
    case "GT30i":
	case "GT60":
	case "VT300":
	case "VT310":
	case "VT400":
	case "GT30":
	case "GT30X":
	case "PST-AVL01":
	case "PT03":
	case "PT60":
	case "PT300X":
	case "PT30":
	case "GT-110P":
	case "GT-110K":
	case "GT-110M":
	case "GT-110ES":
	case "GT-110ZS":
	case "AVL-011":
	case "VT900":
	case "P008":
	case "GT 30":
	case "CT01":
	case "CT03":
	case "CT04":
	case "CT04-R":
	case "CT04-X":
	case "OCT600":
	case "MT01":
	case "MT02":
	case "PT01":
	case "PT03":
	case "VT1000":
	case "GSY007":
	case "T200":
	case "iStartek":
         echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5009</b><br>' ;	
    break;
    case "Datamax": 
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5010</b><br>' ;	
    break;
	case "ST200":
	case "ST210":
	case "ST215":
	case "ST215I":
	case "ST215E":
	case "ST240":
	case "ST230":
	case "ST900":
	case "ST910":
				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5011</b><br>' ;	
    break;
	 case "Progress 7s":
	 				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5012</b><br>' ;	
    break;
	case "H02":
	case "H-02A":
	case "H-02B":
	case "TX-2":
	case "H-06":
	case "H08":
	case "GTLT3":
	case "TK110":
	case "NT201":
	case "NT202":
	case "S31":
	case "LK109":
	case "LK106":
	case "LK208":
	case "LK206":
	case "LK310":
	case "LK206A":
	case "LK206B":
	case "MI-G6":
	case "CC830":
	case "CCTR":
	case "CCTR-630":
	case "AT-18":
	case "GRTQ":
	case "LK210":
		 				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5013</b><br>' ;	
    break;
	case "JT600":
	case "GP4000":
	case "GP5000":
	case "GP6000":
			 			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5014</b><br>' ;	
    break;
	case "EV-601":
	case "EV-602":
	case "EV-603":
	case "EV-606":
	case "EV­07P":
	case "GPS668":
	case "SaR-mini":
				 			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5015</b><br>' ;	
    break;
	case "V680":
	case "P10":
	case "HC207":
	case "VT810":
	case "KS168M":
	case "HC06A":
	case "TL201":
	case "PT200":
	case "PT350":
	case "TK06A":
	case "SaR-mini":
				 			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5016</b><br>' ;	
    break;
	case "PT80":
	case "PT100":
	case "PT201":
	case "PT502":
	case "PT600":
	case "PT510":
	case "AnioSmart­A510":
				 			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5017</b><br>' ;	
    break;
	case "TR-20":
					 	echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5018</b><br>' ;	
    break;
	case "Signal S-2115":
	case "Signal S-2117":
	case "CH-4713":
	case "CH-5703":
		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5019</b><br>' ;
	 break;							
	case "MT80":
	case "MT88":
	case "MT80i":
	case "MT90":
	case "KT90":
	case "MVT100":
	case "MVT340":
	case "MVT380":
	case "MVT600":
	case "T1":
	case "MVT800":
	case "T3":
	case "TC68":
	case "TC68S":
	case "T322":
		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5020</b><br>' ;
    break;
	case "TT8750":
	case "TT8750+":
	case "TT9500":
	case "TT9200":
	case "TT8850":
	case "GSM5108":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5021</b><br>' ;
    break;
	case "GT02":
	case "TR02":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5022</b><br>' ;
    break;
	case "GT06":
	case "GT06N":
	case "GT09":
	case "Heacent 908":
	case "GT03A":
	case "GT03B":
	case "GS503":
	case "ET100":
	case "GT100":
	case "GT06D":
	case "GK301":
	case "JM01":
	case "JM08":
	case "GT02D":
	case "IB-GT102":
	case "CRX1":
	case "JV200":
	case "TP06A":
	case "BW08":
	case "TR06":
	case "JI09":
	case "Concox GT300": 
				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5023</b><br>' ;
    break;
	case "MT-90":
	case "MT-100":
	case "GPT-69":
	case "GT-89":
	case "GT-99":
	case "XT-007":
	case "GMT-368":
	case "GVT-369":
	case "GVT-390":
	case "GVT-500":
	case "GVT-510":
	case "GMT-368SQ":
	case "XT7":
	case "GMT368s":
		  echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5024</b><br>' ;
    break;
	case "TD230":
	case "uTrace03e":
		  echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5025</b><br>' ;
    break;
	case "GpsGate":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5026</b><br>' ;
    break;
	case "FM1100":
	case "FM2100":
	case "FM2200":
	case "FM3200":
	case "FM4100":
	case "FM4200":
	case "FM5300":
	case "GH3000":
	case "FM3300":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5027</b><br>' ;
    break;
	case "Gruz":
	case "Personal":
	case "ZoomBox":
	case "MPU-01":	
	case "MPU-01 GLONASS":
	case "MTA-02":
	case "MTA-02-GLONASS":
	case "MTA-02-CAM":
	case "MTA-03":
	case "MTA-12":
				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5028</b><br>' ;
    break;
	case "TZ-AVL19":
				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5029</b><br>' ;
    break;
	case "TLT-2F":
	case "V520":
	case "TLT-2H":
	case "TLT-1C":
	case "V690":
	case "VSUN3338":
	case "TLT-3A":
	case "V580":
	case "TLT-1B":
	case "TLT-2K":
	case "TLT-2N":
	case "TLT-1F":
	case "TLT-8A":
	case "TLT-8B":
	case "TLT-3A":
	case "TLT-1D":
	case "TLT-6C":
	case "TLT-7B":
	case "AT-12A":
				echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5030</b><br>' ;
    break;
	case "Syrus GPS":
	case "E-Track":
	case "Sierra":
	case "Lantrix": 
	     		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5031</b><br>' ;
    break;
	case "WondeX VT300":
	case "WondeX SPT-10":
	case "TK5000":
	case "Navixy M7":
	case "TK5000XL":
		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5032</b><br>' ;
    break;
	case "CelloTrack 6M (IP65)":
	case "CelloTrack IP67":
	case "CelloTrack XT":
		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5033</b><br>' ;
    break;
	case "GalileoSky":
		 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5034</b><br>' ;
    break;
	case "V-MT001":
	case "V208":
         echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5035</b><br>' ;
    break;	
	case "IntelliTrac X1 Plus":
	case "IntelliTrac X8":
	case "IntelliTrac P1":
	         echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5037</b><br>' ;
    break;	
	case "Wialon IPS":
	case "MasterKit":
	case "MasterKit BM8009":
	case "NeoTech TR­1000":
		         echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5039</b><br>' ;
    break;	
	case "CCTR-620":
	case "CCTR-622":
	case "CCTR-700":
	case "CCTR-800":
	case "CCTR-801":
	case "CCTR-802":
	case "CCTR-803":
	case "CCTR-808":
	case "CCTR-810":
	case "CCTR-620+":
			         echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5040</b><br>' ;
    break;	
	case "T-104":
	case "T-104PRO":
	case "T-104 GLONASS":
				     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5041</b><br>' ;
    break;	
	case "MP2030A":
	case "MP2030B":
	case "MP2031A":
	case "MP2031B":
	case "MP2031C":
					     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5042</b><br>' ;
    break;		
	case "TR-600":
	case "TR-600G":
	case "TR-606B":
	case "GTR-128/129":
	case "TR-206":
	case "TR-203":
	case "TR-151":
	case "TR-151SP":
						     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5043</b><br>' ;
    break;	
	case "AT1":
	case "AT1Pro":
	case "AT5i":
	case "AU5i":
	case "AX5":
	case "AY5i":
	case "AT3":
	case "AT5":
	case "AU5":
	case "AY5":
	case "AT1":
	case "AX5C":
							     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5044</b><br>' ;
    break;		
	case "PT3000":
							     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5045</b><br>' ;
    break;	
	case "FM-Pro3-R":
	case "FM-Tco3":
	case "FM-Pro3":
	case "FM-Eco3":
	case "Trailer Tracker":
	case "FM-ECO 4":
								     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5046</b><br>' ;
    break;	
     case "T8803":
	 case "T8801":
	 case "T8901":
	 	case "FM-ECO 4":
								     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5047</b><br>' ;
    break;	
    

	case "StarFinder AIRE":
	case "StarFinder Lite":
	case "StarFinder Bus":
	case "S911 Lola":
	case "S911 Bracelet Locator":
	case "S911 Bracelet Locator HC":
	case "S911 Bracelet Locator ST":
	case "S911 Personal Locator":	
		case "FM-ECO 4":
								     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5048</b><br>' ;
    break;	
	case "A9":
	case "A1 Max":
	case "A1 Trax":
	case "A1 M2M":
	case "A5 GLX":
	case "Aplicom C-series":
	case "Aplicom Q-series":
									     echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5049</b><br>' ;
    break;	
	case "Omega T600":
	case "TL007":
	case "TL201":
	case "TL206":
	case "TL218":
	case "VT108":
	case "VT1081":
	case "TP-20":
	case "EQT-20":
	case "G-TL-020":
	case "GP106M":
		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5050</b><br>' ;
    break;	
	case "GC-101":
	case "CT-24":
	case "CT-58":
	case "CT-58A":
	case "GX-101":
	case "GS-818":
	case "MT-101":
	case "MU-201":
	case "QG-201":
		 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5051</b><br>' ;
    break;	
	case "M588S":
	case "M528":
	case "M508":
	case "M518":
	case "M588N":
	case "S208":
	case "S228":
	case "M518S":
		 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5052</b><br>' ;
		break;	
	case "NR002":
	case "NR006":
	case "NR008":
	case "NR016":
	case "NR024":
	case "NR028":
	case "NR032":
	case "UT01":
	case "UM02":
	case "UT04":
	case "UT03":
	case "UT05":
	case "UT06":
	case "UP102":
		 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5053</b><br>' ;
		break;		
	case "M2M-Mini":
	 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5054</b><br>' ;
		break;			
    case "OsmAnd":
	case "SendLocation":
	case "Locus Pro Android":
	case "Custodium":
	case "Traccar Client":
		 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5055</b><br>' ;
		break;
	case "ET-01":
	case "ET-06":
		 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5056</b><br>' ;
		break;	
	case "GPS Marker M130":
	case "GPS Marker M80":
	case "GPS Marker M70":
	case "GPS Marker M100":
	case "GPS Marker M60":
			 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5057</b><br>' ;
		break;	
	case "KG100":
	case "KG200":
	case "KG300":
	case "KC200":
	case "T360-101A":
	case "T360-101P":
	case "T360-101E":
	case "T360-103":
	case "T360-106":
	case "T360-108":
	case "T360-269":
	case "T360-269B":
	case "T360-269JT":
	case "VT600":
	case "VT600X":
	case "VT800":
	case "AL900":
	case "VT900X":
	case "AL-900E":
				 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5058</b><br>' ;
		break;	
		
	case "Piligrim PL250":
	case "Piligrim 6000N":
	case "Piligrim Patrol":
	case "Piligrim Stealth":
	case "Piligrim Tracker-6000":
    				 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5059</b><br>' ;
		break;	
	case "STL060":
	    				 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5060</b><br>' ;
		break;	
		case "iTrackPro":
			    				 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5061</b><br>' ;
		break;
		case "MiniFinder Pico":
		case "EV­07":
		case "EV­07P":
		case "MiniFinder Atto":
			  echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5062</b><br>' ;
			  break;
		case "HI-605X":
		case "HI-604X":
		case "HI-603X":
		case "HI-602X":
		case "HI-602":
		case "HI-603":
		case "HI-604":
			 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5063</b><br>' ;
			break;
		case "GOT10":
		case "TK116":
		case "TK115":
		case "GOT08":
		case "GPT06":
		case "K9+":
		case "K6":
		    echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5064</b><br>' ;
			break;
		case "BOXoptions+":
		case "BOXtracker":
		case "BOXsolo":
		case "BOX iSpot":
			 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5065</b><br>' ;
			break;
				
		case "Freedom PT-9":
		case "Freedom PT-10":
			 echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5066</b><br>' ;
				break;
		case "Telic SBC-AVL":
		case "Telic SBC3":
		case "SBC3":
		case "Picotrack":
		case "Picotrack IP69 K":
		case "Picotrack Endurance Primary":
		case "Picotrack Endurance Rechargeable":
     		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5067</b><br>' ;
			break;
		case "Trackbox":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5068</b><br>' ;
			break;	
		case "84 VT":
		case "86 VT":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5069</b><br>' ;
												break;	
		case "Orion ET-100":
		case "Orion OBDtrac":
		case "BD-2012":
		case "BD-3112":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5070</b><br>' ;
			break;	
		case "SLS-00886":
		case "SLS-012SF":
		case "TYN­886":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5071</b><br>' ;
			break;	
		case "T370":
		case "T360":
		case "T303":
		case "T301":
		case "T376":
		case "T373B":
		case "T373A":
		case "T371":
		case "T366":
		case "T363B":
		case "T363A":
		case "T361":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5072</b><br>' ;
			break;
		case "T23":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5073</b><br>' ;
			break;
		case "TR-900":
		case "NEO1":
		case "NEO2":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5074</b><br>' ;
			break;
		case "Ardi 01":
		echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5075</b><br>' ;
			break;
		case "XT013":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5076</b><br>' ;
			break;	
		case "AutoFon D":
		case "AutoFon SE":
		case "AutoFon SE+":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5077</b><br>' ;
			break;	
		case "G3A":
		case "G3S":
		case "G6S":
		case "G1S":
		case "G737":
		case "G2P":
		case "G717":
		case "G777":
		case "G91I":
		case "G79":
		case "G797":
		case "G797W":
		case "GS16":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5078</b><br>' ;
			break;	
		case "StarLine M10":
		case "StarLine M11":
		case "StarLine M16":
		case "StarLine M17":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5079</b><br>' ;
			break;	
		case "BCE FM Light":
		case "BCE FM Light+":
		case "BCE FM Blue":
		case "BCE FM Blue+":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5080</b><br>' ;
			break;	
		case "XT-2000G":
		case "XT-2060G":
		case "XT-2050C":
		case "XT-2150":
		case "XT-2150G":
		case "XT-2160G":
		case "XT-2150C":
		case "XT-3200":
		case "XT-4500G":
		case "XT-4560G":
		case "XT-4550C":
		case "XT-4700":
		case "XT-4760":
		case "XT-4750C":
		case "XT-4860G":
		case "XT-4850C":
		case "XT-5000":
		case "XT-5060":
		case "XT-5050C":
		case "XT-6200":
		case "XT-6260":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5081</b><br>' ;
			break;	
		case "ATU-620":
		case "LMU-1100 Series":
		case "TTU-700 Series":
		case "TTU-1200 Series":
		case "TTU-2820 Series":
		case "LMU-200 Series":
		case "LMU-300 Series":
		case "LMU-328":
		case "LMU-400 Series":
		case "LMU-700 Series":
		case "LMU-800 Series":
		case "LMU-900 Series":
		case "LMU-1100 Series":
		case "LMU-1200 Series":
		case "LMU-2000 Series":
		case "LMU-2100 Series":
		case "LMU-2600 Series":
		case "LMU-2620":
		case "LMU-2700 Series":
		case "LMU-2720":
		case "LMU-3030":
		case "LMU-4200 Series":
		case "LMU-4520 Series":
		case "LMU-5000 Series":
		case "MDT-7":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5082</b><br>' ;
			break;	
		case "MTX-Tunnel GPS":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5083</b><br>' ;
			break;
		case "DS530":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5084</b><br>' ;
			break;
		case "TZ-AVL301":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5085</b><br>' ;
			break;
		case "IDD-213G":
		case "IDD-212GL":
		case "IDD-212B":
		case "IDD-213T":
		case "IDD-213N/E":
		case "IDD-218G":
		case "HT-196R":
		case "HT-192":
		case "MPIP-618":
		case "MPIP-619":
		case "MPIP-620":
		case "PT-718":
		case "PT-690":
		case "SAT-802":	
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5086</b><br>' ;
			break;
		case "MXT-142":
		case "MTC-700":
		case "MTC-780":
		case "MXT-140":
		case "MXT-141":
		case "IDP-780":
		case "MXT-100":
		case "MX-100":
		case "TD-50":
		case "WT-110":
		case "TD-60":
		case "G-100":
		case "i-MXT":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5087</b><br>' ;
			break;					
		case "Cityeasy 520":
		case "Cityeasy 009":
		case "Cityeasy 006":
		case "Cityeasy 007":
		case "Cityeasy 202":
		case "Cityeasy 306":
		case "Cityeasy 100":
		case "Cityeasy 200":
		case "Cityeasy 302":
		case "Cityeasy 008":
		case "Cityeasy 201":
			echo 'Aponte seu rastreador para o ip '.$ip.' porta: 5088</b><br>' ;
			break;		
		
			
	default:
       echo "i is not equal to 0, 1 or 2";
	
 }
 
	
	*/ 
 



//if $modelo

$validatk = substr($modelo,0,2);

if ($validatk == 'TK'|| $validatk == 'GP')
	{
		
		  echo 'Atenção observe os espaços entre os comandos envie os sms para o seguinte numero: '.$telefone.' <br><br>';
		  echo 'Envie o comando <b>imei' .$senha. '</b> e verifique no seu e-mail se o imei cadastrado esta correto<br>';
		  echo 'Envie o comando <b>begin' .$senha. '</b> aguarde o comando <b>Begin ok !</b><br>';
	      echo "Envie o comando <b>apn".$senha, " " .$apn. '</b> recebera a mensagem <b>APN  OK!</b><br>';
	      echo 'Envie o comando <b>up'.$senha."   ".$apnpassuser. ' '.$apnpassuser.'</b> recebera a mensagem <b>APN  OK!</b> <br>';
		  echo 'Envie o comando <b>adminip'.$senha.' '.$ip.' 7002</b> recebera a mensagem <b>adminip ok!</b> <br>';
		  echo 'Envie o comando <b>gprs'.$senha.' </b>recebera a mensagem <b>GPRS OK!</b><br><br>' ; 
		  echo 'Qualquer duvida entre em contato ! marcus@segundo.me</b><br>' ;		
 
}


if ($validatk == 'GT' || $validatk == 'ET' || $validatk == 'GK' || $validatk == 'CR' || $validatk == 'JV' || $validatk == 'TR'  || $validatk == 'CO' || $validatk == 'BW')
{
echo 'Atenção observe os espaços entre os comandos envie os sms para o seguinte numero: '.$telefone.' <br><br>';

echo 'Envie o comando <b>FACTORY# </b><br>';	
echo 'Envie o comando <b>TIMER,300,1800#  </b><br>';	
echo 'Envie o comando <b>HBT,17,19#</b><br>';
echo 'Envie o comando <b>APN,'.$apn.','.$apnpassuser.','.$apnpassuser.'# </b><br>';	
echo 'Envie o comando <b>SERVER,0,'.$ip.',5023,0#</b><br>';	
echo 'Envie o comando <b>RESET#</b><br>';	
echo 'Qualquer duvida entre em contato ! marcus@segundo.me</b><br>' ; 	
}
	

if ($validatk == 'TL' || $validatk == 'V5' || $validatk == 'VS' || $validatk == 'AT')
{
echo 'Atenção observe os espaços entre os comandos envie os sms para o seguinte numero: '.$telefone.' <br><br>';

echo 'Envie o comando <b>FACTORY# </b><br>';	
echo 'Envie o comando <b>710'.$senha.'  </b><br>';	
echo 'Envie o comando <b>220'.$senha.'</b><br>';
echo 'Envie o comando <b>896'.$senha.'W03</b><br>';
echo 'Envie o comando <b>#803#'.$senha.'#'.$apn.'#'.$apnpassuser.'#'.$apnpassuser.'# </b><br>';	
echo 'Envie o comando <b>#804#'.$senha.'#'.$ip.'#5030##</b><br>';	
echo 'Envie o comando <b>#805#'.$senha.'#60#1##</b><br>';	
	
echo 'Qualquer duvida entre em contato ! marcus@segundo.me</b><br>' ; 	
}	







?>



</body>
</html>