<?php

$con = mysql_connect("localhost", "root", "suasenha");
if (!$con)
  {
	die('Could not connect: ' . mysql_error());
  }

mysql_select_db('tracker2', $con);

if (!mysql_query("DELETE FROM gprmc WHERE date < DATE_SUB(CURDATE(),INTERVAL 30 DAY)", $con))
{
	die('Error: ' . mysql_error());
}
else
{
	//Executado com sucesso
	echo "OK";
}

if (!mysql_query("DELETE FROM positions WHERE time < DATE_SUB(CURDATE(),INTERVAL 30 DAY)", $con))
{
	die('Error: ' . mysql_error());
}
else
{
	//Executado com sucesso
	echo "OK2";
}

mysql_close($con);
?>
