var xmlhttp;

function bindGrid(str)
{
	if (str == '') {
		document.getElementById("divListagem").innerHTML=
		"<table class='stripeMe'>" +
		"<thead>"+
		"<tr class='alt'>"+
			"<th>Data</th>"+
			"<th>Hora</th>"+
			"<th>Latitude</th>"+
			"<th>Longitude</th>"+
			"<th>Velocidade</th>"+
			"<th>Local</th>"+
			"<th>Ver Mapa</th>"+
		"</tr>"+
		"</thead>"+
		"<tbody><tr class=''><td colspan='7' align='center'> Selecione um veículo no menu. </td></tr></tbody></table>";
	} else {
		strLocal = str;
		
		//xmlhttp.abort();
		xmlhttp=GetXmlHttpObject();
		
		if (xmlhttp==null)
		{
			alert ("Browser does not support HTTP Request");
			return;
		}
		
		var url="listagem.php";
		url=url+"?imei="+str;
		//url=url+"&sid="+Math.random();
		xmlhttp.onreadystatechange = stateChanged;
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);
		
		//Refresh na grid a cada 4 minutos = 240.000 milisegundos
		setTimeout("bindGrid(strLocal)", 60000);
	}
}

function stateChanged()
{
	if (xmlhttp.readyState == 4)
	{
		document.getElementById("divListagem").innerHTML=xmlhttp.responseText;
	}
}

function GetXmlHttpObject()
{
	if (window.XMLHttpRequest)
	{
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	
	if (window.ActiveXObject)
	{
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	return null;
}