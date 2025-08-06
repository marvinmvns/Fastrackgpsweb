<?php
include('../shared-modules/config/seguranca.php');
include('../shared-modules/config/google-maps.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <title>Grid Registros Histórico</title>
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

        <?php
        $dataInicio = $_GET["dataInicio"]; // formato: 10/03/2006
        $dataFinal = $_GET["dataFinal"];
        $nrImeiConsulta = $_GET["nrImeiConsulta"];
        $hrDataInicio = $_GET["hrDataInicio"];
        $hrDataFinal = $_GET["hrDataFinal"];
        $mnDataInicio = $_GET["mnDataInicio"];
        $mnDataFinal = $_GET["mnDataFinal"];

        /* echo $dataInicio . "<br/>";
          echo $dataFinal . "<br/>";
          echo $nrImeiConsulta . "<br/>";
          echo $hrDataInicio . "<br/>";
          echo $hrDataFinal . "<br/>";
          echo $mnDataInicio . "<br/>";
          echo $mnDataFinal . "<br/>"; */

        /** Retorna a hora no formato 00:00:00 */
        function formataHora($hrEntrada, $mnEntrada) {
            $hrSaida;
            $mnSaida;

            switch ($hrEntrada) {
                case "0": $hrSaida = "00";
                    break;
                case "1": $hrSaida = "01";
                    break;
                case "2": $hrSaida = "02";
                    break;
                case "3": $hrSaida = "03";
                    break;
                case "4": $hrSaida = "04";
                    break;
                case "5": $hrSaida = "05";
                    break;
                case "6": $hrSaida = "06";
                    break;
                case "7": $hrSaida = "07";
                    break;
                case "8": $hrSaida = "08";
                    break;
                case "9": $hrSaida = "09";
                    break;
                case "10": $hrSaida = "10";
                    break;
                case "11": $hrSaida = "11";
                    break;
                case "12": $hrSaida = "12";
                    break;
                case "13": $hrSaida = "13";
                    break;
                case "14": $hrSaida = "14";
                    break;
                case "15": $hrSaida = "15";
                    break;
                case "16": $hrSaida = "16";
                    break;
                case "17": $hrSaida = "17";
                    break;
                case "18": $hrSaida = "18";
                    break;
                case "19": $hrSaida = "19";
                    break;
                case "20": $hrSaida = "20";
                    break;
                case "21": $hrSaida = "21";
                    break;
                case "22": $hrSaida = "22";
                    break;
                case "23": $hrSaida = "23";
                    break;
            }

            switch ($mnEntrada) {
                case "00": $mnSaida = ":00:00";
                    break;
                case "10": $mnSaida = ":10:00";
                    break;
                case "15": $mnSaida = ":15:00";
                    break;
                case "20": $mnSaida = ":20:00";
                    break;
                case "25": $mnSaida = ":25:00";
                    break;
                case "30": $mnSaida = ":30:00";
                    break;
                case "35": $mnSaida = ":35:00";
                    break;
                case "40": $mnSaida = ":40:00";
                    break;
                case "45": $mnSaida = ":45:00";
                    break;
                case "50": $mnSaida = ":50:00";
                    break;
                case "55": $mnSaida = ":55:00";
                    break;
                case "59": $mnSaida = ":59:59";
                    break;
            }


            return $hrSaida . $mnSaida;
        }

        $dataInicioSql = substr($dataInicio, 6, 4) . "-" . substr($dataInicio, 3, 2) . "-" . substr($dataInicio, 0, 2);
        $dataFinalSql = substr($dataFinal, 6, 4) . "-" . substr($dataFinal, 3, 2) . "-" . substr($dataFinal, 0, 2);

        $con = mysql_connect('localhost', 'admin123', 'admin123');
        if (!$con) {
            die('Could not connect: ' . mysql_error());
        }

        mysql_select_db("tracker2", $con);

        $resultBem = mysql_query("SELECT imei, name, identificacao FROM bem where imei = $nrImeiConsulta and activated = 'S' and liberado = 'S'");

        if (mysql_num_rows($resultBem) == 0) {
            //Nao encontrado
        } else {
            while ($dataBem = mysql_fetch_assoc($resultBem)) {
                $bemImei = $dataBem['imei'];
                $bemNome = $dataBem['name'];
                $bemIdentificacao = $dataBem['identificacao'];
            }
        }

        $sql = "SELECT id, infotext, latitude, longitude, speed, date, address
		FROM gprmc g 
		WHERE g.gpsSignalIndicator in ('F','L') and 
		g.imei = '" . $nrImeiConsulta . "' and 
		g.date between '$dataInicioSql " . formataHora($hrDataInicio, $mnDataInicio) . "' and 
			       '$dataFinalSql " . formataHora($hrDataFinal, $mnDataFinal) . "' 
		ORDER BY id ASC";

//echo $sql;

        $result = mysql_query($sql);
        ?>
        <link rel="stylesheet" type="text/css" href="../shared-modules/assets/css/historico.css" />
        <base target="contents" />
    </head>
    <body onload="">
        <center style="margin-left:-6px; margin-right:-8px">
            <input type="hidden" id="imeiHistorico" name="imeiHistorico" value="" />

            <div id="divDadosBem" style="display:none" align="left">
                <font style="font-size:18px">Impressão de Histórico <br/></font>
                <font style="font-size:16px">
                    <b>Dados do veículo </b> <br/>
                    <?php
                    echo "
					Número imei: $bemImei <br/>
					Nome: $bemNome <br/>
					Identificação: $bemIdentificacao <br/>
					Período: " . $dataInicio . " " . formataHora($hrDataInicio, $mnDataInicio) . " a " . $dataFinal . " " . formataHora($hrDataFinal, $mnDataFinal) . "
					<br/><br/>
				";
                    ?>
                </font>
            </div>
            <div id="divListagem">
                <table>
                    <thead>
                        <tr class="alt">
                            <th style="width:10%">Data</th>
                            <th style="width:10%">Hora</th>
                            <th style="width:10%">Velocidade</th>
                            <th>Local</th>
                            <th style="text-align:center; width:140px">Ver no Mapa | <span style="font-size:x-small">todos :</span> <input name="Checkbox1" id="checkboxTodos" disabled type="checkbox" class="styleCheck" onclick="marcarDesmarcarTodos(this);" /></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $loopcount = 0;
                        $class = "";
                        $speed = 0;

                        if (mysql_num_rows($result) == 0) {
                            echo "<tr class=\"alt\" \">
							<td colspan=\"5\" style=\"text-align:center\">Nenhum registro de histórico encontrado nesta data para este bem.</td>
						</tr>";
                        } else {
                            while ($data = mysql_fetch_assoc($result)) {
                                $idRota = $data['id'];

                                // Calculo das coordenadas. Convertendo coordenadas do modo GPRS para GPS
                                $trackerdate = ereg_replace("^(..)(..)(..)(..)(..)$", "\\3/\\2/\\1 \\4:\\5", $data['date']);
                     
                                  $latitudeDecimalDegrees = $data['latitude'];
                                  $longitudeDecimalDegrees = $data['longitude'];


                                $speed = $data['speed'] * 1.609;

                                $infotext = $data['infotext'];

                                $address = utf8_encode($data['address']);
                                //Testa se tem endereço, se nao tiver obtem do google geocode e grava
                                if ($address == null or $address == "" or $address == " " or $address == "0,0") {
                                    # Convert the GPS coordinates to a human readable address

                                    $tempstr = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitudeDecimalDegrees,$longitudeDecimalDegrees&sensor=true"; //output = json

                                    $rev_geo_str = file_get_contents($tempstr);
                                    $jsondata = json_decode($rev_geo_str, true);
                                    $address = $jsondata[results][0][formatted_address];

                                    if (!mysql_query("UPDATE gprmc set address = '" . utf8_decode($address) . "', date = date where id = $idRota", $con)) {
                                        //die('Error: ' . mysql_error());
                                    }
                                }


                                $img = ""; //adiciona imagens de alerta na grid
                                switch ($infotext) {
                                    case "low battery": $img = "<img src='imagens/battery-low.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='Bat. Fraca' alt='Bat. Fraca' />";
                                        break;
                                    case "help me": $img = "<img src='imagens/help.png' style='margin-left:2px;margin-bottom:-4px; padding:0px;height:16px;width:16px' title='SOS!' alt='SOS!' />";
                                        break;

                                    default: $img = "";
                                }

                                if (($loopcount % 2) == 0)
                                    $class = "alt";
                                else
                                    $class = "";

                                echo "<tr class='" . $class . "' onmouseover=\"this.className='alt over'\" onmouseout=\"this.className='" . $class . "'\">";
                                echo "<td>" . date('d/m/Y', strtotime($data['date'])) . "</td>";
                                echo "<td>" . date('H:i:s', strtotime($data['date'])) . "</td>";
                                echo "<td>" . floor($speed) . " Km/h" . " </td>";
                                echo "<td>" . $address . " </td>";
                                //echo "<td>" . $latitudeDecimalDegrees . "</td>";
                                //echo "<td>" . $longitudeDecimalDegrees . "</td>";
                                echo "<td class=\"styleCheck\"><input class=\"styleCheck\" id=\"checkbox" . $loopcount . "\" name=\"checkbox" . $loopcount . "\" type=\"checkbox\" disabled />
								<input type=\"hidden\" id=\"latHistorico" . $loopcount . "\" name=\"latHistorico" . $loopcount . "\" value='" . $latitudeDecimalDegrees . "' />
								<input type=\"hidden\" id=\"lonHistorico" . $loopcount . "\" name=\"lonHistorico" . $loopcount . "\" value='" . $longitudeDecimalDegrees . "' />
								$img
								</td>";
                                echo "</tr>";

                                $loopcount++;
                            }
                            echo "<script language='JavaScript'>
							document.getElementById('checkboxTodos').disabled=false;
							try {
								parent.document.getElementById('btnImprimirHistorico').style.display='inline';
							} catch(err) {
							}
						</script>";
                        }

                        echo "<script language='JavaScript'>
						try {
							parent.document.getElementById('spanComandoAcionado').innerHTML='';
							parent.document.getElementById('imgExecutandoHistorico').style.display='none';
						} catch(err) {
						}
					</script>";
                        ?>

                    </tbody>
                </table>
            </div>
        </center>
        <script language="JavaScript">

                                function marcarDesmarcarTodos(checkbox)
                                {
                                    if (checkbox.checked) {
                                        parent.parent.main.points = [];
                                        for (i = 0; i < <?php echo $loopcount ?>; i++) {
                                            parent.parent.main.points.push(new google.maps.LatLng(document.getElementById('latHistorico' + i).value, document.getElementById('lonHistorico' + i).value));
                                            document.getElementById('checkbox' + i).checked = 1;
                                        }
                                    } else {
                                        for (i = 0; i < <?php echo $loopcount ?>; i++) {
                                            parent.parent.main.points = [];
                                            document.getElementById('checkbox' + i).checked = 0;
                                        }
                                    }
                                }

                                function playHistorico() {
                                    var nenhumMarcado = true;
                                    for (i = 0; i < <?php echo $loopcount ?>; i++) {
                                        if (document.getElementById('checkbox' + i).checked) {
                                            nenhumMarcado = false;
                                            break;
                                        }
                                    }

                                    if (nenhumMarcado) {
                                        alert('Após carregar as rotas, marque-as para simular o passo-a-passo.');
                                        return false;
                                    } else {
                                        parent.parent.main.play();
                                    }

                                    return true;
                                }

                                function pauseHistorico() {
                                    parent.parent.main.pausar();
                                }

                                function stopHistorico() {
                                    parent.parent.main.stop();
                                }
        </script>
        <?php
        mysql_close($con);
        ?>
    </body>
</html>
