<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html><head>
	        <!-- The JavaScript -->
	    <script src="source/jquery-1.js"></script>
		<script src="source/jquery.js"></script>
	    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script src="source/jquery_002.js" type="text/javascript"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-62540977-3', 'auto');
  ga('send', 'pageview');

</script>
		

	
        <title>Pré Cadastro FastrackGPS</title>       
		<meta http-equiv="Content-type" content="text/html; charset=windows-1252">
        <meta name="description" content="PHP/MySQL Contact Form with jQuery">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <meta name="keywords" content="contact form, php, mysql, jquery">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <!--link rel="stylesheet" href="source/style2.css" type="text/css" media="all"-->
		<link rel="stylesheet" href="css/style3.css" type="text/css" media="all" >



    </head>
    <body>
	

	
	<div class="hearder">
		<div class="container">
		<center><img src="logo.png"/></center>
		</div>
    </div>
	
        <div id="contact" class="container">
		
		       
					

            <h2>Cadastro</h2>
			<p></p>
			
			<b><h3>Antes de Efetuar o Cadastro Verifique se seu aparelho esta ligado e com créditos</h3></b>
			<h3>Dependendo do modelo o processo pode não ser automático </h3>
            <form id="ContactForm" action="" class="">
                <div>
                    <label>Nome Completo</label>
                    <input id="name" name="name" class="inplaceError" maxlength="120" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
				

				<div>
                    <label>Login<!--<span>Nome do Usuario de Acesso.</span>--></label>
                    <input id="login" name="login" class="inplaceError" maxlength="20" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
                </div>
				
				<div>
                    <label>Senha de Acesso <!--<span>Sua senha de Acesso</span>--></label>
                    <input id="senha" name="senha" class="inplaceError" maxlength="20" autocomplete="off" type="password">
					<span class="error" style="display:none;"></span>
                </div>

				 <div>
                    <label>Email <!--<span>a valid email address</span>--></label>
                    <input id="email" name="email" class="inplaceError" maxlength="120" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
                </div> 
				
				<div>
                    <label>Telefone de Contato com DDD (XX) XXXX-XXXX <!--<span>full name</span>--></label>
                    <input id="nroc" name="nroc" class="inplaceError" maxlength="20" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
                </div>   
				
				
			  <b><h3>Cadastro do Aparelho e Detalhes do Bem</h3></b>
				
				
				<div>
                    <label>Nome do objeto que deseja no Sistema <!--<span>ou ID</span>--></label>
                    <input id="nomeveic" name="nomeveic" class="inplaceError" maxlength="15" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
				</div>
				
				<div>
                    <label>Segunda Identificação do Objeto <!--<span>ou ID</span>--></label>
                    <input id="idveic" name="idveic" class="inplaceError" maxlength="15" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
				</div>
				
				
				<div>
					<label>Icone no Sistema<!--<span>ou ID</span>--></label>
					<span class="error" style="display:none;"></span>
					<select id="sel" name="sel" class="inplaceError" maxlength="10" type="soflow" autocomplete="off">
					
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/118.png>Carro Vermelho</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/106.png>Carro	 Roxo</option>"?>						
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/128.png>Moto Verde</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/127.png>Carro Azul</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/117.png>Veiculo Cinza</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/48.png>Caminhão Azul</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/93.png>Caminhão Baú Branco</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/76.png>Caminhão Branco</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/80.png>Fusca Verde</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/94.png>Suv Preto</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/105.png>Conversivel Vermelho</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/120.png>New Beattle Preto </option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/188.png>Mulher</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/189.png>Homem</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/191.png>Coubói</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/118.png>Carro Vermelho</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/106.png>Carro	 Roxo</option>"?>						
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/128.png>Moto Verde</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/127.png>Carro Azul</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/117.png>Veiculo Cinza</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/48.png>Caminhão Azul</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/93.png>Caminhão Baú Branco</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/76.png>Caminhão Branco</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/80.png>Fusca Verde</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/94.png>Suv Preto</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/105.png>Conversivel Vermelho</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/120.png>New Beattle Preto </option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/188.png>Mulher</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/189.png>Homem</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/191.png>Moto Harley</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/202.png>Caminhonete</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/204.png>Carro Preto</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/202.png>Picape Cinza</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/203.png>Moto Esportiva</option>"?>
						<?php echo "<option value=http://" . $_SERVER['SERVER_NAME']. "/server/icones/193.png>Picape Vermelha</option>"?>
			
					</select>
					</div>
					
					<center><img id="swapImg" src=<?php echo "http://". $_SERVER['SERVER_NAME']."/server/icones/110.png"?>></center>
					<span class="error" style="display:none;"></span>
				
				 
				
				<div>
                    <label>Modelo/Nome Aparelho</label>
					<select id="soflow" name="modelo" class="inplaceError" maxlength="10" type="soflow" autocomplete="off">
					<option selected="selected" value="TK101">TK101</option>
					<option value="TK102">TK102</option>
					<option value="TK102-2">TK102-2</option>
					<option value="TK103">TK103</option>
					<option value="TK103-2">TK103-2</option>
					<option value="XT009">XT009</option>
					<option value="XT011">XT011</option>
					<option value="TK103-2B">TK103-2B</option>
					<option value="TK104">TK104</option>
					<option value="TK106">TK106</option>
					<option value="TK201">TK201</option>
					<option value="TK201-2">TK201-2</option>
					<option value="TK202">TK202</option>
					<option value="TK203">TK203</option>
					<option value="TK206">TK206</option>
					<option value="GPS-103">GPS-103</option>
					<option value="GPS-103-A">GPS-103-A</option>
					<option value="TK116">TK116</option>
					<option value="TK115">TK115</option>
					<option value="ST200">ST200</option>
					<option value="ST210">ST210</option>
					<option value="ST215">ST215</option>
					<option value="ST215I">ST215I</option>
					<option value="ST215E">ST215E</option>
					<option value="ST240">ST240</option>
					<option value="ST230">ST230</option>
					<option value="ST900">ST900</option>
					<option value="ST910">ST910</option>
					<option value="TW-MD1101">TW-MD1101</option>
					<option value="GPS102B">GPS102B</option>
					<option value="GPS104">GPS104</option>
					<option value="GT02">GT02</option>
					<option value="TR02">TR02</option>
					<option value="GT06">GT06</option>
					<option value="GT06N">GT06N</option>
					<option value="GT09">GT09</option>
					<option value="Heacent 908">Heacent 908</option>
					<option value="GT06">GT06</option>
					<option value="GT03A">GT03A</option>
					<option value="GT03B">GT03B</option>
					<option value="EC-546">EC-546</option>
					<option value="TLT-2H">TLT-2H</option>					
					<option value="GT02A">GT02A</option>
					<option value="TT0024">TT0024</option>
					<option value="T1024">T1024</option>
					<option value="T1080">T1080</option>
					<option value="T2024">T2024</option>
					<option value="T2124">T2124</option>					
					<option value="TLT-1C">TLT-1C</option>
					<option value="V690">V690</option>
					<option value="VSUN3338">VSUN3338</option>
					<option value="TLT-3A">TLT-3A</option>
					<option value="V580">V580</option>
					<option value="TLT-1B">TLT-1B</option>
					<option value="TLT-2K">TLT-2K</option>
					<option value="TLT-2N">TLT-2N</option>
					<option value="TLT-1F">TLT-1F</option>
					<option value="TLT-8A">TLT-8A</option>
					<option value="TLT-8B">TLT-8B</option>
					<option value="TLT-3A">TLT-3A</option>
					<option value="TLT-1D">TLT-1D</option>
					<option value="TLT-6C">TLT-6C</option>
					<option value="TLT-7B">TLT-7B</option>
					<option value="GL100">GL100</option>
					<option value="GL100M">GL100M</option>
					<option value="GL200">GL200</option>
					<option value="GV55">GV55</option>
					<option value="GV55 Lite">GV55 Lite</option>
					<option value="TZ-AVL02">TZ-AVL02</option>
					<option value="TZ-AVL03">TZ-AVL03</option>
					<option value="TZ-AVL05">TZ-AVL05</option>
					<option value="TZ-AVL08">TZ-AVL08</option>
					<option value="TZ-AVL09">TZ-AVL09</option>
					<option value="TZ-AVL10">TZ-AVL10</option>
					<option value="TZ-GT08">TZ-GT08</option>
					<option value="TZ-GT09">TZ-GT09</option>
					<option value="TZ-VN06">TZ-VN06</option>
					<option value="AT03">AT03</option>
					<option value="AT06">AT06</option>
					<option value="AT06+">AT06+</option>
					<option value="AT09">AT09</option>
					<option value="Mini MT">Mini MT</option>
					<option value="Enfora GSM2448">Enfora GSM2448</option>
					<option value="Enfora MT­4000">Enfora MT­4000</option>
					<option value="GT30i">GT30i</option>
					<option value="GT60">GT60</option>
					<option value="VT300">VT300</option>
					<option value="VT310">VT310</option>
					<option value="VT400">VT400</option>
					<option value="GT30">GT30</option>
					<option value="GT30X">GT30X</option>
					<option value="PST-AVL01">PST-AVL01</option>
					<option value="PT03">PT03</option>
					<option value="PT60">PT60</option>
					<option value="PT300X">PT300X</option>
					<option value="PT30">PT30</option>
					<option value="GT-110P">GT-110P</option>
					<option value="GT-110K">GT-110K</option>
					<option value="GT-110M">GT-110M</option>
					<option value="GT-110ES">GT-110ES</option>
					<option value="GT-110ZS">GT-110ZS</option>
					<option value="AVL-011">AVL-011</option>
					<option value="VT900">VT900</option>
					<option value="P008">P008</option>
					<option value="GT 30">GT 30</option>
					<option value="CT01">CT01</option>
					<option value="CT03">CT03</option>
					<option value="CT04">CT04</option>
					<option value="CT04-R">CT04-R</option>
					<option value="CT04-X">CT04-X</option>
					<option value="OCT600">OCT600</option>
					<option value="MT01">MT01</option>
					<option value="MT02">MT02</option>
					<option value="PT01">PT01</option>
					<option value="PT03">PT03</option>
					<option value="VT1000">VT1000</option>
					<option value="MT80">MT80</option>
					<option value="MT88">MT88</option>
					<option value="MT80i">MT80i</option>
					<option value="MT90">MT90</option>
					<option value="KT90">KT90</option>
					<option value="MVT100">MVT100</option>
					<option value="MVT340">MVT340</option>
					<option value="MVT380">MVT380</option>
					<option value="MVT600">MVT600</option>
					<option value="T1">T1</option>
					<option value="MVT800">MVT800</option>
					<option value="T3">T3</option>
					<option value="TC68">TC68</option>
					<option value="TC68S">TC68S</option>
					<option value="T322">T322</option>
					<option value="Client">Client</option>
					<option value="Gelix">Gelix</option>
					<option value="Gelix-2">Gelix-2</option>
					<option value="GPS-911(M)">GPS-911(M)</option>
					<option value="AVL-900">AVL-900</option>
					<option value="AVL-900(R)">AVL-900(R)</option>
					<option value="AVL-900(M)">AVL-900(M)</option>
					<option value="AVL-901(B)">AVL-901(B)</option>
					<option value="AVL-901(C)">AVL-901(C)</option>
					<option value="AVL-901(D)">AVL-901(D)</option>
					<option value="AVL-921">AVL-921</option>
					<option value="CradlePoint IBR600">CradlePoint IBR600</option>
					<option value="Aspicore">Aspicore</option>
					<option value="Tracker for Traccar">Tracker for Traccar</option>
					<option value="MultiConnect rCell">MultiConnect rCell</option>
					<option value="GT200">GT200</option>
					<option value="GT300">GT300</option>
					<option value="GT500">GT500</option>
					<option value="GV200">GV200</option>
					<option value="Datamax">Datamax</option>
					<option value="Progress 7s">Progress 7s</option>
					<option value="H02">H02</option>
					<option value="H-02A">H-02A</option>
					<option value="H-02B">H-02B</option>
					<option value="TX-2">TX-2</option>
					<option value="H-06">H-06</option>
					<option value="H08">H08</option>
					<option value="GTLT3">GTLT3</option>
					<option value="TK110">TK110</option>
					<option value="JT600">JT600</option>
					<option value="GP4000">GP4000</option>
					<option value="GP5000">GP5000</option>
					<option value="GP6000">GP6000</option>
					<option value="EV-601">EV-601</option>
					<option value="EV-602">EV-602</option>
					<option value="EV-603">EV-603</option>
					<option value="EV-606">EV-606</option>
					<option value="EV­07P">EV­07P</option>
					<option value="V680">V680</option>
					<option value="P10">P10</option>
					<option value="HC207">HC207</option>
					<option value="VT810">VT810</option>
					<option value="KS168M">KS168M</option>
					<option value="HC06A">HC06A</option>
					<option value="PT80">PT80</option>
					<option value="PT100">PT100</option>
					<option value="PT201">PT201</option>
					<option value="PT502">PT502</option>
					<option value="PT600">PT600</option>
					<option value="PT510">PT510</option>
					<option value="AnioSmart­A510">AnioSmart­A510</option>
					<option value="TR-20">TR-20</option>
					<option value="Signal S-2115">Signal S-2115</option>
					<option value="Signal S-2117">Signal S-2117</option>
					<option value="CH-4713">CH-4713</option>
					<option value="CH-5703">CH-5703</option>
					<option value="TT8750">TT8750</option>
					<option value="TT8750+">TT8750+</option>
					<option value="TT9500">TT9500</option>
					<option value="TT9200">TT9200</option>
					<option value="TT8850">TT8850</option>
					<option value="GSM5108">GSM5108</option>
					<option value="GS503">GS503</option>
					<option value="ET100">ET100</option>
					<option value="GT100">GT100</option>
					<option value="GT06D">GT06D</option>
					<option value="GK301">GK301</option>
					<option value="MT-90">MT-90</option>
					<option value="MT-100">MT-100</option>
					<option value="GPT-69">GPT-69</option>
					<option value="GT-89">GT-89</option>
					<option value="GT-99">GT-99</option>
					<option value="XT-007">XT-007</option>
					<option value="GMT-368">GMT-368</option>
					<option value="GVT-369">GVT-369</option>
					<option value="GVT-390">GVT-390</option>
					<option value="GVT-500">GVT-500</option>
					<option value="GVT-510">GVT-510</option>
					<option value="GMT-368SQ">GMT-368SQ</option>
					<option value="TD230">TD230</option>
					<option value="uTrace03e">uTrace03e</option>
					<option value="GpsGate">GpsGate</option>
					<option value="FM1100">FM1100</option>
					<option value="FM2100">FM2100</option>
					<option value="FM2200">FM2200</option>
					<option value="FM3200">FM3200</option>
					<option value="FM4100">FM4100</option>
					<option value="FM4200">FM4200</option>
					<option value="FM5300">FM5300</option>
					<option value="GH3000">GH3000</option>
					<option value="FM3300">FM3300</option>
					<option value="Gruz">Gruz</option>
					<option value="Personal">Personal</option>
					<option value="ZoomBox">ZoomBox</option>
					<option value="MPU-01">MPU-01</option>
					<option value="MPU-01 GLONASS">MPU-01 GLONASS</option>
					<option value="MTA-02">MTA-02</option>
					<option value="MTA-02-GLONASS">MTA-02-GLONASS</option>
					<option value="MTA-02-CAM">MTA-02-CAM</option>
					<option value="MTA-03">MTA-03</option>
					<option value="MTA-12">MTA-12</option>
					<option value="TLT-2F">TLT-2F</option>
					<option value="V520">V520</option>
					<option value="AT-12A">AT-12A</option>
					<option value="Syrus GPS">Syrus GPS</option>
					<option value="E-Track">E-Track</option>
					<option value="WondeX VT300">WondeX VT300</option>
					<option value="WondeX SPT-10">WondeX SPT-10</option>
					<option value="TK5000">TK5000</option>
					<option value="Navixy M7">Navixy M7</option>
					<option value="CelloTrack 6M (IP65)">CelloTrack 6M (IP65)</option>
					<option value="CelloTrack IP67">CelloTrack IP67</option>
					<option value="CelloTrack XT">CelloTrack XT</option>
					<option value="GalileoSky">GalileoSky</option>
					<option value="V-MT001">V-MT001</option>
					<option value="V208">V208</option>
					<option value="TK102 Clone">TK102 Clone</option>
					<option value="IntelliTrac X1 Plus">IntelliTrac X1 Plus</option>
					<option value="IntelliTrac X8">IntelliTrac X8</option>
					<option value="IntelliTrac P1">IntelliTrac P1</option>
					<option value="XT7">XT7</option>
					<option value="Wialon IPS">Wialon IPS</option>
					<option value="CCTR-620">CCTR-620</option>
					<option value="CCTR-622">CCTR-622</option>
					<option value="CCTR-700">CCTR-700</option>
					<option value="CCTR-800">CCTR-800</option>
					<option value="CCTR-801">CCTR-801</option>
					<option value="CCTR-802">CCTR-802</option>
					<option value="CCTR-803">CCTR-803</option>
					<option value="CCTR-808">CCTR-808</option>
					<option value="CCTR-810">CCTR-810</option>
					<option value="T-104">T-104</option>
					<option value="T-104PRO">T-104PRO</option>
					<option value="T-104 GLONASS">T-104 GLONASS</option>
					<option value="MP2030A">MP2030A</option>
					<option value="MP2030B">MP2030B</option>
					<option value="MP2031A">MP2031A</option>
					<option value="MP2031B">MP2031B</option>
					<option value="MP2031C">MP2031C</option>
					<option value="TR-600">TR-600</option>
					<option value="TR-600G">TR-600G</option>
					<option value="TR-606B">TR-606B</option>
					<option value="GTR-128/129">GTR-128/129</option>
					<option value="TR-206">TR-206</option>
					<option value="TR-203">TR-203</option>
					<option value="TR-151">TR-151</option>
					<option value="TR-151SP">TR-151SP</option>
					<option value="AT1">AT1</option>
					<option value="AT1Pro">AT1Pro</option>
					<option value="AT5i">AT5i</option>
					<option value="AU5i">AU5i</option>
					<option value="AX5">AX5</option>
					<option value="AY5i">AY5i</option>
					<option value="AT3">AT3</option>
					<option value="AT5">AT5</option>
					<option value="AU5">AU5</option>
					<option value="AY5">AY5</option>
					<option value="AT1">AT1</option>
					<option value="AX5C">AX5C</option>
					<option value="PT3000">PT3000</option>
					<option value="FM-Pro3-R">FM-Pro3-R</option>
					<option value="FM-Tco3">FM-Tco3</option>
					<option value="FM-Pro3">FM-Pro3</option>
					<option value="FM-Eco3">FM-Eco3</option>
					<option value="Trailer Tracker">Trailer Tracker</option>
					<option value="T8803">T8803</option>
					<option value="T8801">T8801</option>
					<option value="T8901">T8901</option>
					<option value="StarFinder AIRE">StarFinder AIRE</option>
					<option value="StarFinder Lite">StarFinder Lite</option>
					<option value="StarFinder Bus">StarFinder Bus</option>
					<option value="S911 Lola">S911 Lola</option>
					<option value="S911 Bracelet Locator">S911 Bracelet Locator</option>
					<option value="S911 Bracelet Locator HC">S911 Bracelet Locator HC</option>
					<option value="S911 Bracelet Locator ST">S911 Bracelet Locator ST</option>
					<option value="S911 Personal Locator">S911 Personal Locator</option>
					<option value="A9">A9</option>
					<option value="A1 Max">A1 Max</option>
					<option value="A1 Trax">A1 Trax</option>
					<option value="A1 M2M">A1 M2M</option>
					<option value="A5 GLX">A5 GLX</option>
					<option value="Aplicom C-series">Aplicom C-series</option>
					<option value="Aplicom Q-series">Aplicom Q-series</option>
					<option value="Omega T600">Omega T600</option>
					<option value="TL007">TL007</option>
					<option value="TL201">TL201</option>
					<option value="TL206">TL206</option>
					<option value="VT108">VT108</option>
					<option value="VT1081">VT1081</option>
					<option value="TP-20">TP-20</option>
					<option value="EQT-20">EQT-20</option>
					<option value="G-TL-020">G-TL-020</option>
					<option value="GP106M">GP106M</option>
					<option value="PT200">PT200</option>
					<option value="PT350">PT350</option>
					<option value="TK06A">TK06A</option>
					<option value="GC-101">GC-101</option>
					<option value="CT-24">CT-24</option>
					<option value="CT-58">CT-58</option>
					<option value="CT-58A">CT-58A</option>
					<option value="GX-101">GX-101</option>
					<option value="GS-818">GS-818</option>
					<option value="MT-101">MT-101</option>
					<option value="MU-201">MU-201</option>
					<option value="QG-201">QG-201</option>
					<option value="M588S">M588S</option>
					<option value="M528">M528</option>
					<option value="M508">M508</option>
					<option value="M518">M518</option>
					<option value="M588N">M588N</option>
					<option value="S208">S208</option>
					<option value="S228">S228</option>
					<option value="M518S">M518S</option>
					<option value="NR002">NR002</option>
					<option value="NR006">NR006</option>
					<option value="NR008">NR008</option>
					<option value="NR016">NR016</option>
					<option value="NR024">NR024</option>
					<option value="NR028">NR028</option>
					<option value="NR032">NR032</option>
					<option value="UT01">UT01</option>
					<option value="UM02">UM02</option>
					<option value="UT04">UT04</option>
					<option value="UT03">UT03</option>
					<option value="UT05">UT05</option>
					<option value="UT06">UT06</option>
					<option value="UP102">UP102</option>
					<option value="M2M-Mini">M2M-Mini</option>
					<option value="OsmAnd">OsmAnd</option>
					<option value="SendLocation">SendLocation</option>
					<option value="Locus Pro Android">Locus Pro Android</option>
					<option value="ET-01">ET-01</option>
					<option value="ET-06">ET-06</option>
					<option value="Sierra">Sierra</option>
					<option value="KG100">KG100</option>
					<option value="KG200">KG200</option>
					<option value="KG300">KG300</option>
					<option value="KC200">KC200</option>
					<option value="T360-101A">T360-101A</option>
					<option value="T360-101P">T360-101P</option>
					<option value="T360-101E">T360-101E</option>
					<option value="T360-103">T360-103</option>
					<option value="T360-106">T360-106</option>
					<option value="T360-108">T360-108</option>
					<option value="T360-269">T360-269</option>
					<option value="T360-269B">T360-269B</option>
					<option value="T360-269JT">T360-269JT</option>
					<option value="VT600">VT600</option>
					<option value="VT600X">VT600X</option>
					<option value="Piligrim PL250">Piligrim PL250</option>
					<option value="Piligrim 6000N">Piligrim 6000N</option>
					<option value="Piligrim Patrol">Piligrim Patrol</option>
					<option value="Piligrim Stealth">Piligrim Stealth</option>
					<option value="Piligrim Tracker-6000">Piligrim Tracker-6000</option>
					<option value="STL060">STL060</option>
					<option value="iTrackPro">iTrackPro</option>
					<option value="MiniFinder Pico">MiniFinder Pico</option>
					<option value="HI-605X">HI-605X</option>
					<option value="HI-604X">HI-604X</option>
					<option value="HI-603X">HI-603X</option>
					<option value="HI-602X">HI-602X</option>
					<option value="HI-602">HI-602</option>
					<option value="HI-603">HI-603</option>
					<option value="HI-604">HI-604</option>
					<option value="GOT10">GOT10</option>
					<option value="GOT08">GOT08</option>
					<option value="GPT06">GPT06</option>
					<option value="K9+">K9+</option>
					<option value="K6">K6</option>
					<option value="BOXoptions+">BOXoptions+</option>
					<option value="BOXtracker">BOXtracker</option>
					<option value="BOXsolo">BOXsolo</option>
					<option value="BOX iSpot">BOX iSpot</option>
					<option value="Freedom PT-9">Freedom PT-9</option>
					<option value="Telic SBC-AVL">Telic SBC-AVL</option>
					<option value="Telic SBC3">Telic SBC3</option>
					<option value="SBC3">SBC3</option>
					<option value="Picotrack">Picotrack</option>
					<option value="Picotrack IP69 K">Picotrack IP69 K</option>
					<option value="Picotrack Endurance Primary">Picotrack Endurance Primary</option>
					<option value="Picotrack Endurance Rechargeable">Picotrack Endurance Rechargeable</option>
					<option value="Trackbox">Trackbox</option>
					<option value="84 VT">84 VT</option>
					<option value="86 VT">86 VT</option>

					</select>
				</div>
				</div>
					

					
					
				<div>
                    <label>IMEI ou ID do Aparelho. <!--<span>ou ID</span>--></label>
                    <input id="imei" name="imei" class="inplaceError" maxlength="15" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
				</div>
				

				<div>
                    <label>Numero do Rastreador com DDD  (XX) XXXX-XXXX  <!--<span>Não esquecer do DDD</span>--></label>
                    <input id="nro" name="nro" class="inplaceError" maxlength="20" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
				</div>       
	         
				
				<div>
                    <label>Operadora <!--<span>Operadora do seu Rastreador</span>--></label>
					<select id="soflow" name="operadora" class="inplaceError" maxlength="10" type="soflow" autocomplete="off">
					<option selected="selected" value="CL">CLARO</option>
					<option value="OI">OI</option>
					<option value="VI">VIVO</option>
					<option value="TI">TIM</option>
					</select>
               </div>
			   
               <div>
                   <label>Senha do Rastreador EX: begin (123456)</label>
                   <input id="senharas" name="senharas" class="inplaceError" maxlength="20" autocomplete="off" type="text">
					<span class="error" style="display:none;"></span>
               </div>	


  	
								
             		   
         
                <div class="submit">
                    <input id="send" value="Cadastrar" type="button">
                    <span id="loader" class="loader" style="display:none;"></span>
					<span id="success_message" class="success"></span>
				<input id="newcontact" name="newcontact" value="1" type="hidden">
				
            </form>
        </div>
	</div>
 <div class="footer">
<div class="container">
<p>FastrackGPS Todos os Direitos Reservados.</p>
</div>
</div>   
	<script>
$(document).ready(function() {
    $("#sel").change(function() {
        var imgUrl = $(this).val();
        $("#swapImg").attr("src", imgUrl);
    });
});
	</script>

</body></html>