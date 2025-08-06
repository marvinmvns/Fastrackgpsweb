<?php 

    #Ensure that the client has provided a value for "FirstNameToSearch" 
  if (isset($_POST["Login"]) && $_POST["Login"] != "")
			
	
	
	{ 
         
        #Setup variables 
        $Login = $_POST["Login"]; 
		$Password = $_POST["Password"]; 
		
		
		#$Login = "marcus@segundo.me";
		#$Password = "admin123";
         
        #Connect to Database 
        $con = mysqli_connect("localhost","root","suasenha", "tracker2"); 
         
        #Check connection 
        if (mysqli_connect_errno()) { 
            echo 'Database connection error: ' . mysqli_connect_error(); 
            exit(); 
        } 

        #Escape special characters to avoid SQL injection attacks 
        $Login = mysqli_real_escape_string($con, $Login); 
		#$Password = mysqli_real_escape_string($con, $Password); 
         
        #Query the database to get the user details. 
        $userdetails = mysqli_query($con, "SELECT * FROM cliente WHERE email = '$Login' and senha = '". md5($Password)."'  "); 

	
		
        #If no data was returned, check for any SQL errors 
        if (!$userdetails) { 
            echo 'Could not run query: ' . mysqli_error($con); 
            exit; 
        } 

        #Get the first row of the results 
        $row = mysqli_fetch_row($userdetails); 

        #Build the result array (Assign keys to the values) 
        $result_data = array( 
            'cod' => $row[0], 
   
            ); 

        #Output the JSON data 
		#echo $result_data;
        echo json_encode($result_data);  
    }else{ 
        echo "Could not complete query. Missing parameter";  
    } 
?>