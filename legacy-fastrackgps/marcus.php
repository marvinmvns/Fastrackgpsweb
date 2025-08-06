
								

								
								



<?



$lat= -22.931101666667; //latitude
$lon= -47.089701666667; //longitude



								
								$json = json_decode(file_get_contents("http://nominatim.openstreetmap.org/reverse?format=json&lat=".$lat."&lon=".$lon));
									
								
						
									$address = $json->display_name;
									echo $address;
									$address = $json->error;
									echo $address;