#!/usr/bin/php -q

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


if (!mysql_query("DELETE FROM message", $con))
{
	die('Error: ' . mysql_error());
}
else
{
	//Executado com sucesso
	echo "OK3";
}



mysql_close($con);





include ("mysql.php");

$hoje  = date('Y-m-d');

	 function inverte_data($data,$separador)                    {
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
					   
					   
					   
	


//Atualiza se algum cliente pagou e gerar o proximo pagamento 30 em 30 dias!


        $res = mysql_query("select * from pagtos1, PagSeguroTransacoes where (PagSeguroTransacoes.StatusTransacao = 'Completo' or  PagSeguroTransacoes.StatusTransacao = 'Aprovado') and pagtos1.codc = PagSeguroTransacoes.Referencia and pagtos1.pago = 'N' ");
		
		
		
		for ($i=0; $i < mysql_num_rows($res); $i++) {
							$row = mysql_fetch_assoc($res);
       
				
							
		$res2 = mysql_query("UPDATE pagtos1 set 							
							pago          = 'S'
						    WHERE codc = ".$row[codc]." and  pago = 'N' ", $con);		


						

       
				
				$codCliente = $row[codcli];
				$descr = $row[descr];			
				$dataini = $row[data_vcto];
				$dtvcto = $row[data_vcto];
				$dtcorte =	 $row[dtcorte]	;		 
				$obs = $row[obs];
				$qtde = $row[qtde];
				
							
				$dtvcto = date('d/m/Y', strtotime($dtvcto));
				$dtvcto = SomarData($dtvcto, 30, 0, 0);					
				$dtvcto  = inverte_data($dtvcto,'/');	
				
				$dtcorte = date('d/m/Y', strtotime($dtcorte));
				$dtcorte = SomarData($dtcorte, 33, 0, 0);
				$dtcorte  = inverte_data($dtcorte,'/');	
				
											

							
		       	mysql_query("INSERT INTO pagtos1 (codcli, descr, data_ult_pgto, data_vcto, dtcorte, pago, obs, qtde ) VALUES
			 							        ($codCliente, '$descr',  '$dataini', '$dtvcto', '$dtcorte', 'N', 'Mensalidade mes corrente' , '1')", $con);	

				      $res2 = mysql_query("SELECT * FROM cliente where id = '". $codCliente ."' ");						
						$row2 = mysql_fetch_row($res2);
						$emailx = $row2[1];
						$nome = $row2[2];
						echo $nome;
												
                			
		       $res3 = mysql_query("UPDATE cliente set 							
				     	data_inativacao         =   null
					   WHERE id = ".$row[codcli]."", $con);													
										
							
	
require_once('/var/www/administracao/class.phpmailer.php');
$mail = new PHPMailer ();
$mail->From = "fastrackgps@bol.com.br";
$mail->FromName = "fastrackgps@bol.com.br";
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->Host = "smtps.bol.com.br";
$mail->Mailer = "smtp";
$mail->AddAddress ( "marcus@segundo.me" );
$mail->Subject =  $headers;
$mail->IsHTML ( true );
$mail->IsSMTP ( true );
$mail->SMTPAuth = "true";
$mail->Username = "fastrackgps@bol.com.br";
$mail->Password = "xuxu1234";
$mail->AddAddress ($emailx);

$mail->Subject = "FastrackGPS - Pagamento Confirmado";
$message_body	= " Olá,".$nome."! \n"
              ."Pagamento efetuado! Obrigado pela sua parceria!"			             		  
			  ."<table border='1'>" 
              ."<tr>"
			  ."<th>Cod Cliente</th>"
			  ."<th>Nome do Bem</th>"
	//		  ."<th>Imei</th>"
			  ."<th>Vencimento</th>"			 	
			  ."</tr>"
			  ."<tr>"			 
              ."<td>".$row[codc]."</td>"
              ."<td>".$row[descr]."</td>"
	//		  ."<td>".$row[imei]."</td>"
			  ."<td>".$row[data_vcto]."</td>"				 
              ."</tr>"
              ."</table>";      

			  
$mail->Body = $message_body;
sleep(5);	 
$mail->send ();			
							
						
							
					
						}
						
						


//Envia e-mail aos clientes que estão para vencer entre o corte e o vencimento
				
					   					   
					  
						$hoje  = date('Y-m-d'); 
						$res = mysql_query("select * from pagtos1 where pago = 'N' and '".$hoje."' between  data_vcto and dtcorte");  						
						
						
						
						for ($i=0; $i < mysql_num_rows($res); $i++) {
							$row = mysql_fetch_assoc($res);
			                  
							
                       
                        $res2 = mysql_query("SELECT * FROM cliente where id = '". $row[codcli] ."' ");						
						$row2 = mysql_fetch_row($res2);
						$email = $row2[1];
						$nome = $row2[2];
					
							
					    $res3 = mysql_query("UPDATE cliente set 							
						    	data_inativacao         =  '".$row[dtcorte]."'
						        WHERE id = ".$row[codcli]." ", $con);	



						
 			
							
$dtvcto = $row[data_vcto];		
$dtvcto = date('d/m/Y', strtotime($dtvcto));	

$dtcorte = $row[dtcorte];		
$dtcorte = date('d/m/Y', strtotime($dtcorte));					
							
$qtde = $row[qtde] * 10;

	
require_once('/var/www/administracao/class.phpmailer.php');
$mail = new PHPMailer ();
$mail->From = "fastrackgps@bol.com.br";
$mail->FromName = "fastrackgps@bol.com.br";
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->Host = "smtps.bol.com.br";
$mail->Mailer = "smtp";
$mail->Subject =  $headers;
$mail->IsHTML ( true );
$mail->IsSMTP ( true );
$mail->AddAddress ( "marcus@segundo.me" );
$mail->SMTPAuth = "true";
$mail->Username = "fastrackgps@bol.com.br";
$mail->Password = "xuxu1234";
$mail->AddAddress ($email);

$mail->Subject = "FastrackGPS - Pagamento em Atraso";
$message_body	= " Olá,".$nome."! \n"
              ."Consta em nosso sistema uma pendencia de pagamento vencido no dia ".$dtvcto.", "
			  ."caso o valor esteja pago, peço que entre em contato pelo e-mail marcus@segundo.me para checar eventuais problemas.  \n"			  
			  ."Para efetuar o pagamento entre na aba pagamento do seu sistema e veja qual pagamento está faltante \n"			 
              ."Segue as informações detalhadas sobre esse pagamento em aberto: \n"              		  
			  ."<table border='1'>" 
			  ."\n"
			  ."\n"
			  ."<tr>"
			  ."<th>Cod Fatura</th>"
			  ."<th>Descrição</th>"
			  ."<th>Vencimento</th>"
			  ."<th>Valor</th>"
			  ."</tr>"
			  ."<tr>"			 
              ."<td>".$row[codc]."</td>"
              ."<td>".$row[descr]."</td>"
			  ."<td>".$dtvcto."</td>"	
			  ."<td> R$".$qtde.",00</td>"	
              ."</tr>"
              ."</table>"
			  ."Você tem até o dia ".$dtcorte." para efetuar o pagamento a partir desse dia sua conta poderá ser temporariamente bloqueada. \n"
			  ."\n"
			  ."Caso esteja no periodo de testes, e nao queira mais o sistema e so ignorar a cobrança \n"
			  ."\n"
			  ."Ainda, se quiser uma alternativa mais barata em um contrato anual ou seu aparelho não funcionou no sistema, entre em contato pelo Whatsapp (11) 9-8741-5290 , podemos ver uma melhor solução !  \n"
			  ."Obrigado! \n";			 
$mail->Body = $message_body;	
sleep(5);
$mail->send ();    
						
						}
						

						
						
//Bloqueia os clientes que não pagaram.


			   $res = mysql_query("select * from pagtos1 where '".$hoje."' > dtcorte and pago = 'n'" );
						
						
						for ($i=0; $i < mysql_num_rows($res); $i++) {
							$row = mysql_fetch_assoc($res);
							
			            $res2 = mysql_query("SELECT * FROM cliente where id = '". $row[codcli] ."' ");						
						$row2 = mysql_fetch_row($res2);
						$email = $row2[1];
						$nome = $row2[2];
						
						
													
					       $res3 = mysql_query("UPDATE cliente set 							
						    	ativo         =  'N'
						        WHERE id = ".$row[codcli]." ", $con);	
							
							
							
require_once('/var/www/administracao/class.phpmailer.php');
$mail = new PHPMailer ();
$mail->AddAddress ("marcus@segundo.me");
$mail->From = "fastrackgps@bol.com.br";
$mail->FromName = "fastrackgps@bol.com.br";
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->Host = "smtps.bol.com.br";
$mail->Mailer = "smtp";
$mail->Subject =  $headers;
$mail->IsHTML ( false );
$mail->IsSMTP ( true );
$mail->SMTPAuth = "true";
$mail->Username = "fastrackgps@bol.com.br";
$mail->Password = "xuxu1234";
$mail->Subject = "FastrackGPS - Cortar Conta";
$message_body	=   "Cortar conta ".$nome." \n"
				  . "cortar conta do usuario ".$email." \n "
				  . "Motivo ".$row[codc]." ";
$mail->Body = $message_body;
sleep(5);	 
$mail->send ();			
						
						}
						
						?>
