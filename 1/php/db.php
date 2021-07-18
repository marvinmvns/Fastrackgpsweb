<?php
require_once("config.php"); /* Configuration File */


		
		//adicionar do campo $sel por enquanto
		
		
		function inverte_data($data,$separador)   
		{
             $nova_data = implode("".$separador."",array_reverse(explode("".$separador."",$data)));
			  return $nova_data;
		}				

		function SomarData($data, $dias, $meses, $ano)
					   
		{  
			$data = explode("/", $data);
			$newData = date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses,
			$data[0] + $dias, $data[2] + $ano) );
			return $newData;
		}
		
		
				function SendSMS ($host, $port, $username, $password, $phoneNoRecip, $msgText) { 

				$fp = fsockopen($host, $port, $erno, $errstr);
				if (!$fp) {
				   				}
				fwrite($fp, "GET /PhoneNumber=" . rawurlencode($phoneNoRecip) . "&Text=" . rawurlencode($msgText) . " HTTP/1.0\n");
				
				if ($username != "") {
				   $auth = $username . ":" . $password;
				  
				   $auth = base64_encode($auth);

				   fwrite($fp, "Authorization: Basic " . $auth . "\n");
				}
				fwrite($fp, "\n");
			  
				$res = "";
			 
				while(!feof($fp)) {
					$res .= fread($fp,1);
				}
				fclose($fp);
				
				
																							}
		




class DB{
	
	private $link;
	
	public function __construct(){
		$this->link = mysqli_connect(DB_SERVER, DB_USER, DB_PASS,DB_NAME);
		if (mysqli_connect_errno())
		    exit();
	}
	
	public function __destruct() {
		mysqli_close($this->link);
	}
	
	
	public function dbNewMessage($email, $name, $imei, $login, $senha, $modelo, $nro, $nroc, $operadora, $nomeveic, $sel, $senharas ){
		$email 	 	= mysqli_real_escape_string($this->link,$email);
		$name 		= mysqli_real_escape_string($this->link,$name);		
		$imei    	= mysqli_real_escape_string($this->link,$imei);		
		$login   	= mysqli_real_escape_string($this->link,$login);
		$senha   	= mysqli_real_escape_string($this->link,$senha);
		$modelo   	= mysqli_real_escape_string($this->link,$modelo);
		$nro     	= mysqli_real_escape_string($this->link,$nro);		
		$nroc    	= mysqli_real_escape_string($this->link,$nroc);
		$operadora	= mysqli_real_escape_string($this->link,$operadora);
		$nomeveic   = mysqli_real_escape_string($this->link,$nomeveic);
		$sel 	    = mysqli_real_escape_string($this->link,$sel);		
		$senharas	= mysqli_real_escape_string($this->link,$senharas);
		
		
		$dataini = date("d/m/Y");	        
		$dtvcto = SomarData($dataini, 7, 0, 0);             
        $dtcorte = SomarData($dtvcto, 7, 0, 0); 			 
		$dataini = inverte_data($dataini,'/');
		$dtvcto  = inverte_data($dtvcto,'/');	
		$dtcorte  = inverte_data($dtcorte,'/');
		$endereço = 'via sistema';
		$cpf = '330222988475';
		$operadora2 = $operadora1;
		
		

		$findme   = 'icones/';
		$pos = strpos($sel, $findme);
		$findme2   = '.png';
		$pos2 = strpos($sel, $findme2);
		$posini = ($pos + 7);
		$posfim = ($pos2 - $posini);
		mb_internal_encoding("UTF-8");
		$cor = mb_substr($sel,$posini,$posfim);

				
		
		

		
		
		mysqli_autocommit($this->link,FALSE);
		
		$query = "INSERT INTO CONTACT(pk_contact,name,email,imei,login,senha,modelo,nro,message,nroc,operadora) 
				  VALUES('NULL','$name','$email','$imei','$login','$senha','$modelo','$nro','$senharas','$nroc','$operadora')";
		mysqli_query($this->link,$query);
		
		
		
		$query = "INSERT INTO cliente VALUES (NULL, '$email', '$name', '$login', '". md5($senha) ."', 'S', '$dtvcto', 'via cadastro 7 dias', 'N', '$nroc', '$nro', '$endereco', '$cpf')";
		mysqli_query($this->link,$query);
 	    $codCliente = $this->link->insert_id;
		
	
		
		$query = "INSERT INTO bem (imei, name, identificacao, cliente, activated, porta, cor_grafico, liberado, numero_chip, operadora_chip, numero_chip2, operadora_chip2) 
		          VALUES ('$imei', '$nomeveic', '$nomeveic', '$codCliente', 'S', '7095' ,'$cor', 'S', '$nroc', '$operadora', '$nro', '$operadora2')";
		mysqli_query($this->link,$query);
        
		
		$query = "INSERT INTO pagtos1 (codcli, descr, data_ult_pgto, data_vcto, dtcorte, pago, obs, qtde) VALUES
						      ('$codCliente', 'Demonstração',  '$dataini' , '$dtvcto', '$dtcorte' , 'N', '7 dias', 1)";
		mysqli_query($this->link,$query);



		
	
		
		
		
		
		if(mysqli_errno($this->link))
			return -1;
		else{
			mysqli_commit($this->link);
			return 1;
		}
	}   
};





?>