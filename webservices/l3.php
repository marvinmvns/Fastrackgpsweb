<?php 

    #Ensure that the client has provided a value for "FirstNameToSearch" 
    if (isset($_POST["FirstNameToSearch"]) && $_POST["FirstNameToSearch"] != ""){ 
         
        #Setup variables 
        $firstname = $_POST["FirstNameToSearch"]; 
         
        #Connect to Database 
        $con = mysqli_connect("localhost","root","suasenha", "tracker2"); 
         
        #Check connection 
        if (mysqli_connect_errno()) { 
            echo 'Database connection error: ' . mysqli_connect_error(); 
            exit(); 
        } 

        #Escape special characters to avoid SQL injection attacks 
        $firstname = mysqli_real_escape_string($con, $firstname); 
         
        #Query the database to get the user details. 
        $userdetails = mysqli_query($con, "SELECT * FROM cliente WHERE nome = '$Login'"); 

        #If no data was returned, check for any SQL errors 
        if (!$userdetails) { 
            echo 'Could not run query: ' . mysqli_error($con); 
            exit; 
        } 

        #Get the first row of the results 
        $row = mysqli_fetch_row($userdetails); 

        #Build the result array (Assign keys to the values) 
        $result_data = array( 
            'FirstName' => $row[1], 
            'LastName' => $row[2], 
            'Age' => $row[3], 
            'Points' => $row[4], 
            ); 

        #Output the JSON data 
        echo json_encode($result_data);  
    }else{ 
        echo "Could not complete query. Missing parameter";  
    } 
?>