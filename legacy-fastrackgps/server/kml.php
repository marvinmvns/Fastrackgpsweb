<?php
if($_GET['sources'])
    show_source(__FILE__);
else
    header('Content-Type: application/vnd.google-earth.kml+xml');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

$cnx = mysql_connect('localhost', 'admin123', 'admin123');
mysql_select_db("tracker2", $cnx);

if($_GET['entries'] <> ""){
	$entries = $_GET['entries'];
} elseif ($_POST['entries'] <> "") {
	$entries = $_POST['entries'];
} else {
	$entries = 50;
}
// echo $entries

if($_GET['imei'] <> "") {
	$imei = $_GET['imei'];
} elseif ($_POST['imei'] <> "") {
	$imei = $_POST['imei'];
} else {
	$imei = 0;
}

$step = 255 / $entries;
//echo $step;




$loop = 0;
$res1 = mysql_query("SELECT b.id as ide, b.name as nomeBem, b.identificacao, b.cor_grafico FROM bem b WHERE b.imei = $imei LIMIT 1");
while($data1 = mysql_fetch_assoc($res1)) {
	$nomeBem = $data1['nomeBem'];
	$identificacao = $data1['identificacao'];
	$idoutros = $data1['ide'];	
     $tipoBem = $data1['cor_grafico'];
		
			switch ($tipoBem) {
			case "NULL": $imageBem = "ultimo_local"; break;
			case "0": $imageBem = "ultimo_local"; break;
			case "1": $imageBem = "1"; break;
			case "2": $imageBem = "2"; break;
			case "3": $imageBem = "3"; break;
			case "4": $imageBem = "4"; break;
			case "5": $imageBem = "5"; break;
			case "6": $imageBem = "6"; break;
			case "7": $imageBem = "7"; break;
			case "8": $imageBem = "8"; break;
			case "9": $imageBem = "9"; break;
			case "10": $imageBem = "10"; break;
			case "11": $imageBem = "11"; break;
			case "12": $imageBem = "12"; break;
			case "13": $imageBem = "13"; break;
			case "14": $imageBem = "14"; break;
			case "15": $imageBem = "15"; break;
			case "16": $imageBem = "16"; break;
			case "17": $imageBem = "17"; break;
			case "18": $imageBem = "18"; break;
			case "19": $imageBem = "19"; break;
			case "20": $imageBem = "20"; break;
			case "21": $imageBem = "21"; break;
			case "22": $imageBem = "22"; break;
			case "23": $imageBem = "23"; break;
			case "24": $imageBem = "24"; break;
			case "25": $imageBem = "25"; break;
			case "26": $imageBem = "26"; break;
			case "27": $imageBem = "27"; break;
			case "28": $imageBem = "28"; break;
			case "29": $imageBem = "29"; break;
			case "30": $imageBem = "30"; break;
			case "31": $imageBem = "31"; break;
			case "32": $imageBem = "32"; break;
			case "33": $imageBem = "33"; break;
			case "34": $imageBem = "34"; break;
            case "35": $imageBem = "35"; break;			
			case "36": $imageBem = "36"; break;	
			case "37": $imageBem = "37"; break;	
			case "38": $imageBem = "38"; break;	
			case "39": $imageBem = "39"; break;	
			case "40": $imageBem = "40"; break;	
			case "41": $imageBem = "41"; break;	
			case "42": $imageBem = "42"; break;	
			case "43": $imageBem = "43"; break;	
			case "44": $imageBem = "44"; break;	
			case "45": $imageBem = "45"; break;	
			case "46": $imageBem = "46"; break;	
			case "47": $imageBem = "47"; break;
			case "48": $imageBem = "48"; break;
			case "49": $imageBem = "49"; break;
			case "50": $imageBem = "50"; break;
			case "51": $imageBem = "51"; break;
			case "52": $imageBem = "52"; break;
			case "53": $imageBem = "53"; break;
			case "54": $imageBem = "54"; break;
			case "55": $imageBem = "55"; break;
			case "56": $imageBem = "56"; break;
			case "57": $imageBem = "57"; break;
			case "58": $imageBem = "58"; break;
			case "59": $imageBem = "59"; break;
			case "60": $imageBem = "60"; break;
            case "61": $imageBem = "61"; break;			
			case "62": $imageBem = "62"; break;	
			case "63": $imageBem = "63"; break;	
			case "64": $imageBem = "64"; break;	
			case "65": $imageBem = "65"; break;	
			case "66": $imageBem = "66"; break;	
			case "67": $imageBem = "67"; break;	
			case "68": $imageBem = "68"; break;	
			case "69": $imageBem = "69"; break;	
			case "70": $imageBem = "70"; break;	
			case "71": $imageBem = "71"; break;	
			case "72": $imageBem = "72"; break;	
			case "73": $imageBem = "73"; break;
			case "74": $imageBem = "74"; break;
			case "75": $imageBem = "75"; break;
			case "76": $imageBem = "76"; break;
			case "77": $imageBem = "77"; break;
			case "78": $imageBem = "78"; break;
			case "79": $imageBem = "79"; break;
			case "80": $imageBem = "80"; break;
			case "81": $imageBem = "81"; break;
			case "82": $imageBem = "82"; break;
			case "83": $imageBem = "83"; break;
			case "84": $imageBem = "84"; break;
			case "85": $imageBem = "85"; break;
			case "86": $imageBem = "86"; break;
            case "87": $imageBem = "87"; break;			
			case "88": $imageBem = "88"; break;	
			case "89": $imageBem = "89"; break;	
			case "90": $imageBem = "90"; break;	
			case "91": $imageBem = "91"; break;	
			case "92": $imageBem = "92"; break;	
			case "93": $imageBem = "93"; break;	
			case "94": $imageBem = "94"; break;	
			case "95": $imageBem = "95"; break;	
			case "96": $imageBem = "96"; break;	
			case "97": $imageBem = "97"; break;	
			case "98": $imageBem = "98"; break;	
			case "99": $imageBem = "99"; break;
			case "100": $imageBem = "100"; break;
			case "101": $imageBem = "101"; break;
			case "102": $imageBem = "102"; break;
			case "103": $imageBem = "103"; break;
			case "104": $imageBem = "104"; break;
			case "105": $imageBem = "105"; break;
			case "106": $imageBem = "106"; break;
			case "107": $imageBem = "107"; break;
			case "108": $imageBem = "108"; break;
			case "109": $imageBem = "109"; break;
			case "110": $imageBem = "110"; break;
			case "111": $imageBem = "111"; break;
			case "112": $imageBem = "112"; break;
            case "113": $imageBem = "113"; break;			
			case "114": $imageBem = "114"; break;	
			case "115": $imageBem = "115"; break;	
			case "116": $imageBem = "116"; break;	
			case "117": $imageBem = "117"; break;	
			case "118": $imageBem = "118"; break;	
			case "119": $imageBem = "119"; break;	
			case "120": $imageBem = "120"; break;	
			case "121": $imageBem = "121"; break;	
			case "122": $imageBem = "122"; break;	
			case "123": $imageBem = "123"; break;	
			case "124": $imageBem = "125"; break;	
			case "125": $imageBem = "125"; break;	
			case "126": $imageBem = "126"; break;	
			case "127": $imageBem = "127"; break;	
			case "128": $imageBem = "128"; break;	
			case "129": $imageBem = "129"; break;
			case "130": $imageBem = "130"; break;
			case "131": $imageBem = "131"; break;
			case "132": $imageBem = "132"; break;
			case "133": $imageBem = "133"; break;
			case "134": $imageBem = "134"; break;
			case "135": $imageBem = "135"; break;
			case "136": $imageBem = "136"; break;
			case "137": $imageBem = "137"; break;
			case "138": $imageBem = "138"; break;
			case "139": $imageBem = "139"; break;
			case "140": $imageBem = "140"; break;
			case "141": $imageBem = "141"; break;
			case "142": $imageBem = "142"; break;
            case "143": $imageBem = "143"; break;			
			case "144": $imageBem = "144"; break;	
			case "145": $imageBem = "145"; break;	
			case "146": $imageBem = "146"; break;	
			case "147": $imageBem = "147"; break;	
			case "148": $imageBem = "148"; break;	
			case "149": $imageBem = "149"; break;	
			case "150": $imageBem = "150"; break;	
			case "151": $imageBem = "151"; break;	
			case "152": $imageBem = "152"; break;	
			case "153": $imageBem = "152"; break;	
			case "154": $imageBem = "154"; break;
			case "155": $imageBem = "155"; break;
			case "156": $imageBem = "156"; break;
			case "157": $imageBem = "157"; break;
			case "158": $imageBem = "158"; break;
			case "158": $imageBem = "158"; break;
			case "160": $imageBem = "160"; break;
			case "161": $imageBem = "161"; break;
			case "162": $imageBem = "162"; break;
            case "163": $imageBem = "163"; break;			
			case "164": $imageBem = "164"; break;	
			case "165": $imageBem = "165"; break;	
			case "166": $imageBem = "166"; break;	
			case "167": $imageBem = "167"; break;	
			case "168": $imageBem = "168"; break;	
			case "169": $imageBem = "169"; break;	
			case "170": $imageBem = "170"; break;	
			case "171": $imageBem = "171"; break;	
			case "172": $imageBem = "172"; break;	
			case "173": $imageBem = "173"; break;	
			case "174": $imageBem = "174"; break;	
			case "175": $imageBem = "175"; break;	
			case "176": $imageBem = "176"; break;	
			case "177": $imageBem = "177"; break;	
			case "178": $imageBem = "178"; break;
			case "179": $imageBem = "179"; break;
			case "180": $imageBem = "180"; break;
			case "181": $imageBem = "181"; break;
			case "182": $imageBem = "182"; break;
			case "183": $imageBem = "183"; break;
			case "184": $imageBem = "184"; break;
			case "185": $imageBem = "185"; break;
			case "186": $imageBem = "186"; break;
            case "187": $imageBem = "187"; break;			
			case "188": $imageBem = "188"; break;	
			case "189": $imageBem = "189"; break;	
			case "190": $imageBem = "190"; break;	
			case "191": $imageBem = "191"; break;	
			case "192": $imageBem = "192"; break;	
			case "193": $imageBem = "193"; break;	
			case "194": $imageBem = "194"; break;	
			case "195": $imageBem = "195"; break;	
			case "196": $imageBem = "196"; break;	
			case "197": $imageBem = "197"; break;	
			case "198": $imageBem = "198"; break;	
			case "199": $imageBem = "199"; break;	
			case "200": $imageBem = "200"; break;	
			case "201": $imageBem = "201"; break;	
			case "202": $imageBem = "202"; break;	
			case "203": $imageBem = "203"; break;	
			case "204": $imageBem = "204"; break;	
			case "205": $imageBem = "205"; break;	
			case "206": $imageBem = "206"; break;	
			case "207": $imageBem = "207"; break;	
			case "209": $imageBem = "208"; break;
	  	    
			}
	
	
	
}






$res = mysql_query("SELECT id, infotext, date, latitude, longitude, speed, address FROM gprmc WHERE gpsSignalIndicator = 'F' and imei = '$imei' ORDER BY date DESC, id DESC LIMIT 10");
$line_coordinates = "";
$ballons = "";

while($data = mysql_fetch_assoc($res)) {
    $tracker2date = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['date']);

	
	$longitudeDecimalDegrees = $data['longitude'];
	$latitudeDecimalDegrees = $data['latitude'];

    $speed = $data['speed'] * 1.609;
	
	$datat = $data['date'];

	$address = $data['address'] == "" ? "<i>Carregando endereço...</i>" : utf8_encode($data['address']);
	
	$line_coordinates .= "$longitudeDecimalDegrees,$latitudeDecimalDegrees,0\n";
	$line_coordinates_green = "$longitudeDecimalDegrees,$latitudeDecimalDegrees,0\n";

	if ($loop != 0) {
		$ballons .= '
		<Placemark>
	
		</Placemark>
		';
	} else {
		//O ultimo registro obtido pelo gps fica verde; o ultimo é o primeiro da lista. ORDER BY DESC.
		if ($loop == 0) {
			$greenBallons = '
			<Placemark>
				<name>'.$nomeBem.' - '.$identificacao.'</name>
				<styleUrl>#highlightPlacemarkGreen</styleUrl>
				<description>Velocidade : '.floor($speed).'Km/h - Data : '.date('d/m/Y H:i:s', strtotime($data['date'])).' &lt;br/&gt; 
							 Lat: '.$longitudeDecimalDegrees.', Long:'.$latitudeDecimalDegrees. ' &lt;br/&gt; 
							 End.: '. $address .'
				</description>
				<Point>
				  <coordinates>'."$longitudeDecimalDegrees,$latitudeDecimalDegrees,0".'</coordinates>
				</Point>
			</Placemark>
		';
		}
	}
	
	$loop++;
}




//parte 2



$res = mysql_query("SELECT * FROM positions WHERE device_id = '". $idoutros. "' ");
if (mysql_num_rows($res) != 0) {

$loop = 0;
//$res1 = mysql_query("SELECT * FROM bem b WHERE b.imei = $imei LIMIT 1");
//while($data1 = mysql_fetch_assoc($res1)) {
//	$idoutros = $data['id'];	
//	$nomeBem = $data1['name'];
//	$identificacao = $data1['identificacao'];
//}


$res = mysql_query("SELECT id, extended_info, time, latitude, longitude, speed, address FROM positions WHERE device_id = '". $idoutros. "' ORDER BY time DESC, id DESC LIMIT 10");
$line_coordinates = "";
$ballons = "";



while($data = mysql_fetch_assoc($res)) {

  		$idRota = $data['id'];
		$latitude = $data['latitude'];
		$longitude = $data['longitude'];
		$tracker2date = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['time']);
		$datat = $data['time'];
		
 //   $tracker2date = ereg_replace("^(..)(..)(..)(..)(..)$","\\3/\\2/\\1 \\4:\\5",$data['date']);
 //   strlen($data['latitudeDecimalDegrees']) == 9 && $data['latitudeDecimalDegrees'] = '0'.$data['latitudeDecimalDegrees'];
 //   $g = substr($data['latitudeDecimalDegrees'],0,3);
 //   $d = substr($data['latitudeDecimalDegrees'],3);
 //   $latitudeDecimalDegrees = $g + ($d/60);
 //   $data['latitudeHemisphere'] == "S" && $latitudeDecimalDegrees = $latitudeDecimalDegrees * -1;

 //   strlen($data['longitudeDecimalDegrees']) == 9 && $data['longitudeDecimalDegrees'] = '0'.$data['longitudeDecimalDegrees'];
 //   $g = substr($data['longitudeDecimalDegrees'],0,3);
 //   $d = substr($data['longitudeDecimalDegrees'],3);
 //   $longitudeDecimalDegrees = $g + ($d/60);
 //   $data['longitudeHemisphere'] == "S" && $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

//    $longitudeDecimalDegrees = $longitudeDecimalDegrees * -1;

 //   $speed = $data['speed'] * 1.609;

//	$address = $data['address'] == "" ? "<i>Carregando endereço...</i>" : utf8_encode($data['address']);

	    $speed = $data['speed'];
		$infotext = $data['extended_info'];
		$address = utf8_encode($data['address']);
	
		$line_coordinates .= "$longitude,$latitude,0\n";
		$line_coordinates_green = "$longitude,$latitude,0\n";


		//O ultimo registro obtido pelo gps fica verde; o ultimo é o primeiro da lista. ORDER BY DESC.
		if ($loop == 0) {
			$greenBallons = '
			<Placemark>
				<name>'.$nomeBem.' - '.$identificacao.'</name>
				<styleUrl>#highlightPlacemarkGreen</styleUrl>
				<description>Velocidade : '.floor($speed).'Km/h - Data : '.date('d/m/Y H:i:s', strtotime($data['date'])).' &lt;br/&gt; 
							 Lat: '.$longitude.', Long:'.$latitude. ' &lt;br/&gt; 
							 End.: '. $address .'
				</description>
				<Point>
				  <coordinates>'."$longitude,$latitude,0".'</coordinates>
				</Point>
			</Placemark>
		';
		}
	
	
	$loop++;
}

}

mysql_close($cnx);
?>
<kml xmlns="http://earth.google.com/kml/2.1">
  <Document>
    <name>tracker2 Map</name>
    <description>tracker2</description>



    <Style id="highlightPlacemarkGreen">
      <IconStyle>
	         <Icon>
       <href><?php echo "http://" . $_SERVER['SERVER_NAME']. "/server/icones/" .$imageBem.".png"?></href>
        </Icon>
		<scale>2</scale>
        </IconStyle>
    </Style>

    <Style id="redLine">
      <LineStyle>
        <color>ff0000ff</color>
        <width>4</width>
      </LineStyle>
    </Style>

    <Style id="BalloonStyle">
      <BalloonStyle>
        <!-- a background color for the balloon -->
        <bgColor>ffffffbb</bgColor>
        <!-- styling of the balloon text -->
        <text><![CDATA[
        <b><font color="#CC0000" size="+3">$[name]</font></b>
        <br/><br/>
        <font face="Courier">$[description]</font>
        <br/><br/>
        Extra text that will appear in the description balloon
        <br/><br/>
        <!-- insert the to/from hyperlinks -->
        $[geDirections]
        ]]></text>
      </BalloonStyle>
    </Style>

    <Style id="greenPoint">
      <LineStyle>
        <color>ff009900</color>
        <width>4</width>
      </LineStyle>
    </Style>

    <Placemark>
      <name>Red Line</name>
      <styleUrl>#redLine</styleUrl>
      <LineString>
        <altitudeMode>relative</altitudeMode>
        <coordinates>
			<?php echo $line_coordinates; ?>
        </coordinates>
      </LineString>
    </Placemark>
	
	<?php echo $greenBallons; ?>

    <?php echo $ballons; ?>

  </Document>
</kml>
