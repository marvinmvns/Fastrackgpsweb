<?php include('../../shared-modules/config/seguranca.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Language" content="en-us" />
<title>Estatísticas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
.cabecalho {
	text-align: center;
	color:#666666;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;	
}
.subTitulo {
	width: 100%;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #CCCCCC;
}
</style>
</head>

<body style="margin-left:0px; margin-top:0px; border-top-style: solid; border-left-style: solid; border-top-width: 1px; border-left-width: 1px; border-top-color: #CCCCCC; border-left-color: #CCCCCC; background-color: #F8F8F8;">

<table style="font-family:Arial, Helvetica, sans-serif; width: 100%">
	<tr>
		<td class="cabecalho"><span class="cabecalho">Gráfico de coordenadas obtidas</span></td>
	</tr>
	<tr>
		<td><a href="../mapa.php" style="color:#0099FF;font-size:16px;font-weight:normal">Voltar</a></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="subTitulo">Coordenadas por hora<br />
		<span style="font-size:12px;font-weight:normal;color:gray">Avalie a eficácia do seu rastreador com a quantidade de coordenadas obtidas.</span>
		<span style="font-size:9px;font-weight:normal;color:black">obs: a quantidade depende do intervalo de tempo definido.</span>
		</td>
	</tr>
<?php

$cnx = mysql_connect("localhost", "root", "suasenha") 
	  or die("Could not connect: " . mysql_error());
mysql_select_db(tracker, $cnx);

$nomes = "";
$quantidades = "";
$cores = "";
$espessura_linha = "";
$range_item = "";

$max_qtde = 0;

$res = mysql_query("select b.name,
						COALESCE(b.cor_grafico, '000000') as cor_grafico,
						COALESCE(CONVERT(c.0h, CHAR), '0') as 0h,
						COALESCE(CONVERT(c.1h, CHAR), '0') as 1h,
						COALESCE(CONVERT(c.2h, CHAR), '0') as 2h,
						COALESCE(CONVERT(c.3h, CHAR), '0') as 3h,
						COALESCE(CONVERT(c.4h, CHAR), '0') as 4h,
						COALESCE(CONVERT(c.5h, CHAR), '0') as 5h,
						COALESCE(CONVERT(c.6h, CHAR), '0') as 6h,
						COALESCE(CONVERT(c.7h, CHAR), '0') as 7h,
						COALESCE(CONVERT(c.8h, CHAR), '0') as 8h,
						COALESCE(CONVERT(c.9h, CHAR), '0') as 9h,
						COALESCE(CONVERT(c.10h, CHAR), '0') as 10h,
						COALESCE(CONVERT(c.11h, CHAR), '0') as 11h,
						COALESCE(CONVERT(c.12h, CHAR), '0') as 12h,
						COALESCE(CONVERT(c.13h, CHAR), '0') as 13h,
						COALESCE(CONVERT(c.14h, CHAR), '0') as 14h,
						COALESCE(CONVERT(c.15h, CHAR), '0') as 15h,
						COALESCE(CONVERT(c.16h, CHAR), '0') as 16h,
						COALESCE(CONVERT(c.17h, CHAR), '0') as 17h,
						COALESCE(CONVERT(c.18h, CHAR), '0') as 18h,
						COALESCE(CONVERT(c.19h, CHAR), '0') as 19h,
						COALESCE(CONVERT(c.20h, CHAR), '0') as 20h,
						COALESCE(CONVERT(c.21h, CHAR), '0') as 21h,
						COALESCE(CONVERT(c.22h, CHAR), '0') as 22h,
						COALESCE(CONVERT(c.23h, CHAR), '0') as 23h
					from bem b left join est_quantidade_hora c on (b.imei = c.imei)
					where b.cliente = $cliente and b.activated = 'S' and c.data = CURDATE() 
					order by b.name");
					
	$qtde_veiculos = mysql_num_rows($res);

	for($i=0; $i < mysql_num_rows($res); $i++) {
		$row = mysql_fetch_assoc($res);
		$nomes = $nomes . $row[name] . "|";
		$cores = $cores . $row[cor_grafico] . ",";
		$espessura_linha = $espessura_linha . "2|";
		
		//Percorrendo colunas
		for($j=0; $j < 25; $j++) {
			$quantidades = $quantidades . $row[$j ."h"] . ",";
		
			if ($max_qtde < (int)$row[$j ."h"]) 
			{
				$max_qtde = (int)$row[$j ."h"];
			}
		}
		//Remove a ultima vírgula e coloca uma barra
		$quantidades = substr($quantidades, 0, (strlen($quantidades)-2));
		$quantidades = $quantidades . "|";
	}
	
	//remove ultimo caracter
	$nomes = substr($nomes, 0, (strlen($nomes)-1));
	$quantidades = substr($quantidades, 0, (strlen($quantidades)-1));
	$cores = substr($cores, 0, (strlen($cores)-1));
	$espessura_linha = substr($espessura_linha, 0, (strlen($espessura_linha)-1));
	
	/*echo $nomes ."<br>";
	echo $quantidades ."<br>";
	echo $cores ."<br>";
	echo $espessura_linha ."<br>";*/

mysql_close($cnx);	
?>
	<tr>
		<td>
			<img src="http://chart.apis.google.com/chart?
					chxl=0:|00h|01h|02h|03h|04h|05h|06h|07h|08h|09h|10h|11h|12h|13h|14h|15h|16h|17h|18h|19h|20h|21h|22h|23h|24h|1:|Hora|3:|Quantidade&
					chxp=0,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24|1,12|3,<?php echo round($max_qtde/2) ?>&
					chxr=0,0,24|1,0,24|2,0,<?php echo $max_qtde ?>|3,0,<?php echo $max_qtde ?>&
					chxs=1,676767,14.5,0,l,676767|3,676767,14.5,0,l,676767&
					chxt=x,x,y,y&
					chs=780x380&
					cht=lc&
					chco=<?php echo $cores ?>&
					chds=<?php for($k=0; $k <= $qtde_veiculos; $k++) { $range_item = $range_item . "0,$max_qtde,"; } $range_item = substr($range_item, 0, (strlen($range_item)-1)); echo $range_item; ?>&
					chd=t:<?php echo $quantidades ?>&
					chdl=<?php echo $nomes ?>&
					chg=4,0&
					chls=<?php echo $espessura_linha ?>&
					chma=40,20,20,30" width="780" height="380" alt="" />
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>	
	<tr class="subTitulo">
		<td>Coordenadas por dia<br />
		<span style="font-size:12px;font-weight:normal;color:gray">Avalie a eficácia do seu rastreador com a quantidade de coordenadas obtidas.</span>
		<span style="font-size:9px;font-weight:normal;color:black">obs: a quantidade depende do intervalo de tempo definido.</span>
		</td>
	</tr>
	<tr>
		<td>
<?php

$cnx = mysql_connect("localhost", "root", "suasenha") 
	  or die("Could not connect: " . mysql_error());
mysql_select_db(tracker, $cnx);

$nomes = "";
$quantidades = "";
$cores = "";

$max_qtde = 0;

$res = mysql_query("select b.name, 
							COALESCE(b.cor_grafico, '000000') as cor_grafico, 
							COALESCE(CONVERT(c.quantidade, CHAR), '1') as quantidade
					from bem b left join est_quantidade_coordenadas c on (b.imei = c.imei)
					where b.cliente = $cliente and b.activated = 'S' and (c.data is null OR c.data = CURDATE())
					order by b.name");

	for($i=0; $i < mysql_num_rows($res); $i++) {
		$row = mysql_fetch_assoc($res);
		$nomes = $nomes . $row[name] . "|";
		$quantidades = $quantidades . $row[quantidade] . "|";
		$cores = $cores . $row[cor_grafico] . ",";
		
		if ($max_qtde < (int)$row[quantidade]) 
		{
			$max_qtde = (int)$row[quantidade];
		}
	}
	
	//remove ultimo caracter
	$nomes = substr($nomes, 0, (strlen($nomes)-1));
	$quantidades = substr($quantidades, 0, (strlen($quantidades)-1));
	$cores = substr($cores, 0, (strlen($cores)-1));
	
	$max_qtde = $max_qtde + 100;
	
mysql_close($cnx);	
?>
		
		<img src="http://chart.apis.google.com/chart?
					chxr=0,0,<?php echo $max_qtde ?>&
					chxt=x&
					chbh=22,2,10&
					chs=780x380&
					cht=bhg&
					chco=<?php echo $cores ?>&
					chds=<?php echo "0,$max_qtde,0,$max_qtde,0,$max_qtde,0,$max_qtde" ?>&
					chd=t:<?php echo $quantidades ?>&
					chdl=<?php echo $nomes ?>&
					chdlp=l" 
			width="780" height="380" alt="" />
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><a href="../mapa.php" style="color:#0099FF;font-size:16px;font-weight:normal">Voltar</a></td>
	</tr>	
</table>

</body>

</html>
