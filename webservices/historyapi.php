<?php

$dbname="tracker2";
        $dbuser="root";
        $dbpass="suasenha";
        $dbhost="localhost";
		
	$con = mysqli_connect( $dbhost,$dbuser,$dbpass,$dbname);
	// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
?> 