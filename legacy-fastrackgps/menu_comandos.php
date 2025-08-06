<?php include('../shared-modules/config/seguranca.php');
header("Content-Type: text/html; charset=iso-8859-1");

  $imei = $_POST['imei'];
  $command = $_POST['command'];
  $commandTime = $_POST['commandTime'];
  $commandSpeedLimit = $_POST['commandSpeedLimit'];
  
  if ($command == ',C,30s')
	$command = $commandTime;
  else
	if (($command == ',H,060'))
		$command = ',H,0' . floor($commandSpeedLimit / 1.609);
  
  
  $cancelar = $_GET['cancelar'];
  
  $command_path = $_SERVER['DOCUMENT_ROOT']."/sites/1/";
  
  #echo $command_path ;
  
  #echo "IMEI:$imei Command:$command";
  #echo "$_POST['imei']";
  
  if ($imei != "" and $command != "")
  {
	/****** DESCOMENTAR EM PRODUÇÃO *****/
	// Utilizando arquivos para guardar o comando
	// your path to command files
	$fn = "$command_path/$imei";
	$fh = fopen($fn, 'w') or die ("Can not create file");
	$tempstr = "**,imei:$imei$command"; 
	echo $tempstr ;
	
	
	fwrite($fh, $tempstr);
	fclose($fh);
	
	
	
	// Guardando comandos a ser executado no banco
	$tempstr = "**,imei:$imei$command"; 
	
	$cnx = mysql_connect("localhost", "admin123", "admin123")
	  or die("Could not connect: " . mysql_error());
	mysql_select_db("tracker2", $cnx);
	
	if ($command == ',N')
	{
		//Ativando o modo SMS
		if (!mysql_query("UPDATE bem set modo_operacao = 'SMS' where imei = '$imei' and modo_operacao = 'GPRS'", $cnx))
			die('Error: ' . mysql_error());
	}
	

	
	if (!mysql_query("INSERT INTO command (imei, command, userid) VALUES ('$imei', '$tempstr', '$userid')", $cnx))
	{
		// Se der erro, atualiza o comando existente
		mysql_query("UPDATE command set command = '$tempstr' WHERE imei = '$imei'", $cnx);
		echo "<script language=javascript>alert('Comando enviado com sucesso!'); window.location = 'mapa.php?imei=$imei';</script>";
		//die('Error: ' . mysql_error());
	}
	
	mysql_close($cnx);
	
	echo "<script language=javascript>alert('Comando enviado com sucesso!'); window.location = 'mapa.php?imei=$imei';</script>";
  }

  //Cancelando o comando enviado
  if ($cancelar != "") 
  {
	$cnx = mysql_connect("localhost", "admin123", "admin123")
	  or die("Could not connect: " . mysql_error());
	mysql_select_db("tracker2", $cnx);
	
	if (!mysql_query("DELETE FROM command WHERE imei = '$cancelar'", $cnx))
	{
		die('Error: ' . mysql_error());
	}
		
	mysql_close($cnx);	
  }
?>