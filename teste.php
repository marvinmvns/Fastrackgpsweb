<?php 


$loop = 0;
$res1 = mysql_query("SELECT b.id as ide, b.name as nomeBem, b.identificacao FROM bem b WHERE b.imei = '354779034420621' LIMIT 1");
while($data1 = mysql_fetch_assoc($res1)) {
	$nomeBem = $data1['nomeBem'];
	$identificacao = $data1['identificacao'];
	$idoutros = $data1['id'];	
	
	echo $identificacao;
}

?>

