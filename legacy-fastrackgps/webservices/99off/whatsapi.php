<?php

require_once __DIR__ . '/configa.php';

header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");
iconv_set_encoding("internal_encoding", "UTF-8");
iconv_set_encoding("output_encoding", "UTF-8");


$markers = array();
$sql = "select mensagem from Promocao order by id desc 	limit 1 ";
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


if (!$mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
    exit();
} else {
    printf("Current character set: %s\n", $mysqli->character_set_name());
}


if($res = $mysqli->query($sql)){
	while($row=$res->fetch_assoc()){
                $mensagem = $row['mensagem'];
				$mensagem = smarty_modifier_emojistrip($mensagem);
	            $data= array("mensagem"=>$mensagem);
                $marker[] = $data;
	}
	

	
		
        $markers = array("mensagem"=>$marker);
		
		

		
		//$markers = utf8_encode($marker); 	
        echo json_encode($markers);
}


function smarty_modifier_emojistrip($string)
{       
    return preg_replace('/\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]/', '', $string);
}


?>
