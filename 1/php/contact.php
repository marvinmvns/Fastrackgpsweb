<?php
require_once("db.php");					/* Database Class */
require_once('utils/is_email.php');		/* Email Validation Script */


/* Handle Ajax Request */
if(isset($_POST['newcontact'])){
	$contact = new Contact();
	unset($contact);
}
else{
	header('Location: /');
}

/* Class Contact */
class Contact{
	
	private $db; 						/* the database obj */
	
	private $errors 		= array();  /* holds error messages */
	private $num_errors;   				/* number of errors in submitted form */
	
	public function __construct(){
		$this->db = new DB();
		if(isset($_POST['newcontact']))
			$this->processNewMessage();
		else
			header("Location: /");
	}

	public function processNewMessage(){
		
		$name		= $_POST['name'];
		$login      = $_POST['login'];
		$senha      = $_POST['senha'];
		$email		= $_POST['email'];			
		$nroc 		= $_POST['nroc'];
		$nomeveic 	= $_POST['nomeveic'];
		$sel	    = $_POST['sel'];
		$modelo     = $_POST['modelo'];
		$imei 		= $_POST['imei'];		
		$nro        = $_POST['nro'];		
        $operadora  = $_POST['operadora'];		
		$senharas	= $_POST['senharas'];		
		
		
	
		
		
		
		
		$mysqli = new mysqli("localhost","root", "suasenha", "tracker2");					

		$query = $mysqli->prepare("SELECT * FROM bem where imei = '$imei'");
		$query->execute();
		$query->store_result();
		$consultaimei = $query->num_rows;
		
		$query = $mysqli->prepare("SELECT * FROM cliente where email = '$email' ");
		$query->execute();
		$query->store_result();
		$consultaemail = $query->num_rows;
		
		$query = $mysqli->prepare("SELECT * FROM cliente where apelido = '$login' ");
		$query->execute();
		$query->store_result();
		$consultalogin = $query->num_rows;
		

		
		
					/* Name Validation */
		if(!$nomeveic || mb_strlen($nomeveic = trim($nomeveic)) == 0)
			$this->setError('nomeveic', 'Campo Obrigatorio');
		else if(mb_strlen(trim($nomeveic)) > 120)
			$this->setError('nomeveic', 'Muito longo! 20 characters');
			
		
		
		
			/* Name Validation */
		if(!$name || mb_strlen($name = trim($name)) == 0)
			$this->setError('name', 'Campo Obrigatorio');
		else if(mb_strlen(trim($name)) > 120)
			$this->setError('name', 'Muito longo! 120 characters');
			
			
			/* login Validation */
		$login = trim($login);
		if(!$login || mb_strlen($login = trim($login)) == 0)
			$this->setError('login','Campo Obrigatorio');
		elseif(mb_strlen($login) > 15)
			$this->setError('login', 'Muito longo, menos de 50 caracteres');	
		elseif($consultalogin != 0)
			$this->setError('login', 'Usuario ja cadastrado, tente novamente');
		
		
					/* Name Validation */
		if(!$senha || mb_strlen($senha = trim($senha)) == 0)
			$this->setError('senha', 'Campo Obrigatorio');
		else if(mb_strlen(trim($senha)) > 120)
			$this->setError('senha', 'Muito longo! 120 characters');
		
		
		/* Email Validation */
		if(!$email || mb_strlen($email = trim($email)) == 0)
			$this->setError('email','Campo Obrigatorio');
		else{
			if(!is_email($email))
				$this->setError('email', 'e-mail invalido');
			else if(mb_strlen($email) > 120)
				$this->setError('email', 'muito longo! 120');
			else if ($consultaemail != 0)		
			$this->setError('email', 'E-mail ja cadastrado');
			
		}
	
			
					/* modelo Validation */
		$nroc = trim($nroc);
		if(!$nroc || mb_strlen($nroc = trim($nroc)) == 0)
			$this->setError('nroc','Campo obrigatorio');
		elseif(mb_strlen($nroc) > 22)
			$this->setError('nroc', '22 digitos apenas');	
				
			
		/* imei Validation */
		$imei = trim($imei);
			if ($consultaimei != 0)
		    $this->setError('imei', 'identificador ja cadastrado, caso deseje usar o sistema entre em contato marcus@segundo.me');
			elseif(!$imei || mb_strlen($imei = trim($imei)) == 0)
			$this->setError('imei','Campo obrigatorio');

			
										
				/* telefone Validation */
		$modelo = trim($modelo);
		if(!$modelo || mb_strlen($modelo = trim($modelo)) == 0)
			$this->setError('modelo','Campo obrigatorio');
		elseif(mb_strlen($modelo) > 15)
			$this->setError('modelo', '3 digitos apenas');	
			
				/* telefone Validation */
		$operadora = trim($operadora);
		if(!$operadora || mb_strlen($operadora = trim($operadora)) == 0)
			$this->setError('operadora','Campo obrigatorio');
		elseif(mb_strlen($operadora) > 15)
			$this->setError('operadora', '3 digitos apenas');	

			/* modelo Validation */
		$nro = trim($nro);
		if(!$nro || mb_strlen($nro = trim($nro)) == 0)
			$this->setError('nro','Campo obrigatorio');
		elseif(mb_strlen($nro) > 22)
			$this->setError('nro', '22 digitos apenas');					
			
					
		/* modelo Validation */
		$nro = trim($nro);
		if(!$senharas || mb_strlen($senharas = trim($senharas)) == 0)
			$this->setError('senharas','Campo obrigatorio');
		elseif(mb_strlen($senharas) > 22)
			$this->setError('senharas', '22 digitos apenas');
			
	

			
		/* Errors exist */
		if($this->countErrors() > 0){
			$json = array(
				'result' => -1, 
				'errors' => array(
								array('name' => 'email'		,'value' => $this->error_value('email')),
								array('name' => 'name' 		,'value' => $this->error_value('name')),	
                                array('name' => 'nroc'	    ,'value' => $this->error_value('nroc')),								
								array('name' => 'imei'	    ,'value' => $this->error_value('imei')),
								array('name' => 'login'	    ,'value' => $this->error_value('login')),
								array('name' => 'senha' 	,'value' => $this->error_value('senha')),		
								array('name' => 'modelo'	,'value' => $this->error_value('modelo')),
								array('name' => 'operadora'	,'value' => $this->error_value('operadora')),
								array('name' => 'nro'	    ,'value' => $this->error_value('nro')),
								array('name' => 'senharas'	,'value' => $this->error_value('senharas')),
								array('name' => 'sel'		,'value' => $this->error_value('sel')),
								array('name' => 'nomeveic'	,'value' => $this->error_value('nomeveic'))
								
							)
				);				
			$encoded = json_encode($json);
			echo $encoded;
			unset($encoded);
		}
		/* No errors, insert in db*/
		else{
			if(($ret = $this->db->dbNewMessage($email, $name, $imei, $login, $senha, $modelo, $nro, $nroc, $operadora, $nomeveic, $sel, $senharas)) > 0){
				$json = array('result' 		=> 1); 
				if(SEND_EMAIL)
					$this->sendEmail($email, $name, $nroc, $imei, $login, $senha, $modelo, $operadora, $nro, $senharas, $sel);
							

		
		$message_body		= "Ola, ".$name." Se Cadastrou no seu Sistema WEB \n"
									."email: ".$email."\n"
									."name: ".$name."\n"
									."imei: ".$imei."\n"
									."login: ".$login."\n"
									."senha: ".$senha."\n"
									."modelo: ".$modelo."\n"
									."nome veiculo: ".$nomeveic."\n"
									."icone: ".$sel."\n"
									."nro: ".$nro."\n"
									."nro celular: ".$nroc."\n"
									."operadora: ".$operadora."\n"
									."senha: "."\n"	.$senharas; 
		$headers			= "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
		

require_once('class.phpmailer.php');
$mail = new PHPMailer ();
$mail->From = "naoresponder3@segundo.me";
$mail->FromName = "naoresponder3@segundo.me";
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->Host = "smtp.gmail.com";
$mail->Mailer = "smtp";
$mail->AddAddress ( "marcus@segundo.me" );
$mail->Subject =  $headers;
$mail->IsHTML(true);
$mail->IsSMTP ( true );
$mail->Body = $message_body;
$mail->SMTPAuth = "true";
$mail->Username = "naoresponder3@segundo.me";
$mail->Password = "xuxu1234";
$mail->send ();

$mail->AddAddress ($email);
$mail->Subject = "[Dados De acesso] - FastrackGPS ";

	
			$message = '<html><body>';
			$message .= '<img src="http://fastrackgps.net/1/logo.png" alt="Logo" />';
			$message .= '<h3>Olá, '.$name.'</h3>';
			$message .= '<h4>Seu aparelho: '.$modelo.' esta sendo configurando em nosso sistema, pedimos que o deixe ligado nas próximas 12 horas</h4>';
			$message .= '<h4>Você pode usar o sistema gratuitmente por 7 dias, e se não gostar não precisa pagar !</h4>';
			$message .= '<h4>Vale lembrar que ao operar no sistema, não devera enviar nenhum comando ao aparelho, pois isso o deconecta do sistema.</h4>';
			$message .= '<br></br>';
			$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
			$message .= "<tr style='background: #eee;'><td><strong>Email:</strong> </td><td>".$email."</td></tr>";
			$message .= "<tr><td><strong>Usuário:</strong> </td><td>".$login."</td></tr>";
			$message .= "<tr><td><strong>Senha:</strong> </td><td>".$senha."</td></tr>";
			$message .= "<tr><td><strong>Nome do Veículo:</strong> </td><td>".$nomeveic."</td></tr>";
			$message .= "<tr><td><strong>Imei:</strong> </td><td>".$imei."</td></tr>";
			$message .= "<tr><td><strong>Endereço de Acesso:</strong> </td><td><a href='http://fastrackgps.net/'>http://fastrackgps.net/</a></td></tr>";
			$message .= "<tr><td><strong>Endereço de Configuração:</strong> </td><td><a href='http://fastrackgps.net/assistente2'>Assistente</a></td></tr>";	
			
			
			$message .= "</table>";
			$message .= '<br></br>';
			$message .= '<h4>Se alguma dessas informações estiver incorreta, ou tiver duvidas, por favor, entre em contato marcus@segundo.me para maiores informações </h4>';
			

			$message .= '<h4> Muito Obrigado ! </h4>';
			$message .= "</body></html>";
			
$mail->Body = $message;
				
				  
			  
			  
$mail->send ();






			}	
			else
				$json = array('result' 		=> -2); /* something went wrong in database insertion  */
			$encoded = json_encode($json);
			echo $encoded;
			unset($encoded);
		}
	}
	
	public function sendEmail($email, $name, $imei, $login, $modelo, $nro, $senharas){
		/* Just format the email text the way you want ... */
		
		

		
		$message_body		= "Hi, ".$name."(".$email." - ".$website.") sent you a message from yoursite.com\n"
									."email: ".$email."\n"
									."message: "."\n"
									.$message; 
		$headers			= "From: ".EMAIL_FROM_NAME." <".EMAIL_FROM_ADDR.">";
		



		
		
		return mail(EMAIL_TO,MESSAGE_SUBJECT,$message_body,$headers);
	}
	
	public function setError($field, $errmsg){
		$this->errors[$field] 	= $errmsg;
		$this->num_errors 		= count($this->errors);
	}
	
	public function error_value($field){
		if(array_key_exists($field,$this->errors))
			return $this->errors[$field];
		else
			return '';
	}
	
	public function countErrors(){
		return $this->num_errors;
	}
};
?>	