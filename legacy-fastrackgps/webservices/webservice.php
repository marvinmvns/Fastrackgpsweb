<?php
	const DB_NAME="tracker2";
	const DB_USER="root";
	const DB_PASS="suasenha";
	const DB_HOST="localhost";
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
	$mysqli->select_db(DB_NAME);
	/*login section start*/
	if($_REQUEST['service_request']=='login'){
		
		$username=$_REQUEST['username'];
		$password=md5($_REQUEST['password']);
		
		$result =$mysqli->query("SELECT * FROM cliente WHERE email = '$username' OR apelido = '$username' AND senha = '$password'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		
		if(!empty($row) && count($row) > 0){

			$userId = $row['id'];
			$result_device_details = $mysqli->query("SELECT id,imei,name FROM bem WHERE activated ='S' AND cliente = '$userId'");
			while($row_device_details = $result_device_details->fetch_array(MYSQLI_ASSOC)){ 
	
					$result_device_array[]=$row_device_details;
			
			}
			$array=array("status"=>"SUCCESS","message"=>"successfully logged in ","user_details"=>$row,"device_details"=>$result_device_array);
			echo json_encode($array);
		}
		else{
		
			$array=array("status"=>"FAILURE","message"=>"fail to login in");
			echo json_encode($array);
			
		}
		$mysqli->close();
	}
	/*login section end*/
	
	/*current section start*/
	else if($_REQUEST['service_request']=='current'){
		$user_id=$_REQUEST['user_id'];
		$result =$mysqli->query("SELECT id,imei,name  FROM bem WHERE activated ='S' AND cliente = '$user_id'");
		$result_rows=$result->num_rows;
		$result_array=array();
		$result_id_array=array();
		if($result_rows>0){
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$imei=$row['imei'];
				$bem_id=$row['id'];				
				//echo " imei: " .$imei;
				//echo " bem: " .$bem_id;				
				$result_array=array();
				$sql="SELECT gprmc.infotext as extended_info, gprmc.date as time, gprmc.latitude, gprmc.longitude, gprmc.speed, bem.name, bem.cor_grafico as icon FROM gprmc inner join bem on gprmc.imei=bem.imei WHERE gprmc.imei = '$imei' ORDER BY gprmc.date DESC, gprmc.id DESC LIMIT 1";
				$result_gprmc =$mysqli->query($sql);
				$result_rows_gprmc=$result_gprmc->num_rows;
				if($result_rows_gprmc<=0){
						$sql1="SELECT positions.device_id, positions.extended_info, positions.time, positions.latitude, positions.longitude, positions.speed, bem.name, bem.cor_grafico as icon FROM positions inner join bem on positions.device_id=bem.id WHERE positions.device_id ='$bem_id' ORDER BY positions.time DESC, positions.id DESC LIMIT 1";
						$result_position =$mysqli->query($sql1);
							while($row_position = $result_position->fetch_array(MYSQLI_ASSOC)){
							$result_array[]=$row_position;
						}
				$result_id_array[$bem_id]=$result_array;
				}
				else{
					while($row_gprmc = $result_gprmc->fetch_array(MYSQLI_ASSOC)){
					
						$result_array[]=$row_gprmc;
					
					}
					$result_id_array[$bem_id]=$result_array;
				}
			}
			
	$array=array("status"=>"SUCCESS","message"=>"successfully fetched ","user_details"=>array($result_id_array));
			echo json_encode($array);
		}
		else{
		
			$array=array("status"=>"FAILURE","message"=>"fail to fetch data");
			echo json_encode($array);
			
		}
		$mysqli->close();
	}
	/*current section end*/
	
	/*history section start*/
	else if($_REQUEST['service_request']=='history'){
				$imei=$_REQUEST['imei'];
				$bem_device_id=$_REQUEST['device_id'];
				$startdate=$_REQUEST['startdate'];
				$enddate=$_REQUEST['enddate'];

				$sql="SELECT id, date as time, latitude, longitude, speed, address FROM gprmc WHERE imei = '$imei' and date BETWEEN '$startdate' AND '$enddate' ORDER BY date DESC, id DESC LIMIT 90";
				//$sql="SELECT * FROM gprmc WHERE imei = '$imei' and date BETWEEN '$startdate' AND '$enddate' ORDER BY date DESC, id DESC LIMIT 10";
				$result_gprmc =$mysqli->query($sql);
				$result_rows_gprmc=$result_gprmc->num_rows;
					if($result_rows_gprmc<=0){
							$sql1="SELECT id, extended_info, time, latitude, longitude, speed, address FROM positions WHERE device_id ='$bem_device_id' and time BETWEEN '$startdate' AND '$enddate' ORDER BY time DESC, id DESC LIMIT 90";
							$result_position =$mysqli->query($sql1);
							while($row_position = $result_position->fetch_array(MYSQLI_ASSOC)){
						
								$result_array[]=$row_position;
							
							}
							$array=array("status"=>"SUCCESS","message"=>"successfully fetched","user_details"=>$result_array);
							echo json_encode($array);
					}
					else{
						while($row_gprmc = $result_gprmc->fetch_array(MYSQLI_ASSOC)){
						
							$result_array[]=$row_gprmc;
						
						}
						$array=array("status"=>"SUCCESS","message"=>"successfully fetched","user_details"=>$result_array);
						echo json_encode($array);
					}
			
			
			
		
		
		$mysqli->close();
	}
	
	/*history section end*/
	/*device detail section start*/
	else if($_REQUEST['service_request']=='devices'){
	
			$userId = $_REQUEST['user_id'];
			$result_device_details = $mysqli->query("SELECT id,imei,name FROM bem WHERE activated ='S' AND cliente = '$userId'");
			while($row_device_details = $result_device_details->fetch_array(MYSQLI_ASSOC)){ 
	
					$result_device_array[]=$row_device_details;
			
			}
			$array=array("status"=>"SUCCESS","message"=>"successfully data fetched","device_details"=>$result_device_array);
			echo json_encode($array);
	
	}


?>