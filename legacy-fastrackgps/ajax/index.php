<?php
include('../../shared-modules/config/seguranca.php');
include('usuario/config.php');
/**
*	@author 	Ing. Israel Barragan C.  Email: ibarragan at behstant dot com
*	@since 		07-Nov-2013
*	##########################################################################################
*	Comments:
*	This file is to show how to extract records from a database with PDO
*	The records are shown in a HTML table and the employee Id has link with his Id.
*
*	Requires:
*	Connection.simple.php, get this file here: http://behstant.com/blog/?p=413
*   search.php located on the php folder.
*   Boostrap and jQuery.
*
* 	LICENCE:
*	You can use this code to any of your projects as long as you mention where you
* 	downloaded it and the author which is me :) Happy Code.
*
* 	LICENCIA:
*	Puedes usar este código para tus proyectos, pero siempre tomando en cuenta que
* 	debes de poner de donde lo descargaste y el autor que soy yo :) Feliz Codificación.
*	##########################################################################################
*	@version
*	##########################################################################################
*	1.0	|	11-Nov-2013	|	Creation of new file to integrate the user ID on the query string.
*	##########################################################################################
*/
	require_once 'php/Connection.simple.php';
    $tutorialTitle = $cliente;

	$conn = dbConnect();
 ?>
 <!DOCTYPE html>
<html lang="en">
    <head>
    	<meta charset="UTF-8" />
        <title><?php echo $tutorialTitle;?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=9" />
		<meta name="copyright" content="BEHSTANT SOFTWARE | Datasoft Engineering 2013"/>
		<meta name="author" content="Reedyseth"/>
		<meta name="email" content="ibarragan at behstant dot com"/>
		<meta name="description" content="<?php echo $tutorialTitle;?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel=stylesheet href="../shared-modules/assets/css/style01.css">
		<!-- Bootstrap -->
    	<link href="../shared-modules/assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="../shared-modules/assets/css/datepicker.css" rel="stylesheet">

	</head>
    <body>
    	<div class="wrapper">

    		<div class="mainContent">
    			<form class="form-inline" role="form" method="get">
    				<div class="container-fluid">				
						
						<?php 
						
							//$cliente = 1;
							require_once 'php/Connection.simple.php';
							$conn = dbConnect();
							$OK = true; // We use this to verify the status of the update.
							$sql2 = "select imei, name from bem where activated = 'S' and cliente = ? order by name";
							//$sql2 = "select imei, name from bem where activated = 'S' order by name";
							$stmt = $conn->prepare($sql2);
							$stmt->bindParam(':cliente', $cliente, PDO::PARAM_INT, 10);
							$results = $stmt->execute(array($cliente));			
							$rows = $stmt->fetchAll();
							$error = $stmt->errorInfo();
							
							
					
						
						
							
						
						
							
							echo "<select id=\"name\" name=\"name\" type=\"text\"  class=\"form-control\" style=\"width: 190px;\">";
							echo "<option value='' selected>-- Selecione --</option>";
							
						   if(empty($rows)) {
							echo "faiu";
											}
							else {					
							foreach ($rows as $row) {													
								
								echo "<option value='$row[imei]'>$row[name]</option>";
													}
								}
												
						echo "</select>";
						
						?>
						
						
						
						
										
					
						<input type="text" id="dtini"  placeholder="Data Inicio" class=form-control style="width: 110px">
						<input type="time" name="hrini" id="hrini" class=form-control style="width: 100px">					
						<input type="text" id="dtfim"  placeholder="Data Fim" style="width: 110px" class=form-control ">
						<input type="time" name="hrfim" id="hrfim" class=form-control style="width: 100px">
							
					
    						
							<span class="input-grup-btn">
    								<button type="button" class="btn btn-default btnSearch">
    									<span class="glyphicon glyphicon-search"> OK</span>
    								</button>
    						</span>
							
							<input type="button" value="Imprimir"  onclick="imprimirHistorico();" />
							
							
							
							
    				
						
						
			
						
    				</div>
		
				<br>
				</br>
		
		
    			</form>
				
                <div class="col-sm"></div>
                <div id = "relat" class="col-sm">
                <!-- This table is where the data is display. -->
                    <table id="resultTable" class="table table-striped table-hover">
                        <thead>
                            <th>Data</th>
                            <th>Latitude</th>
                            <th>Longitude</th>                           
							<th>Status</th>							
							<th>Endereço</th>
							<th>Velocidade</th>							
							<th>Motor</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
    		</div>
		</div>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery-1.10.2.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

	
	

<script language="JavaScript">
	
	function imprimirHistorico()
	{ 
	  var disp_setting="toolbar=no,location=no,directories=yes,menubar=yes,"; 
		  disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25"; 
	  
	  
	  

	  var info = document.getElementById("info").innerHTML;
	  var rotas = document.getElementById("relat").innerHTML;
	  var motor = document.getElementById("relat2").innerHTML;
		
	
		

	  
	  
	  
	  var docprint=window.open("","",disp_setting); 
	   docprint.document.open(); 
	   docprint.document.write('<html><head><title>Impressão de histórico - Rastreamento GPS</title>');
	   docprint.document.write('<link rel="stylesheet" type="text/css" href="../shared-modules/assets/css/impress.css" />');
	   docprint.document.write('</head><body onLoad="self.print()"><center>');
	   docprint.document.write(info);
	   docprint.document.write(motor);
	   docprint.document.write('<br/>');
	   docprint.document.write(rotas);
	   docprint.document.write('</center></body></html>'); 
	   docprint.document.close(); 
	   docprint.focus(); 
	}
</script>
	
	
    <script type="text/javascript">
    	jQuery(document).ready(function($) {
    		$('.btnSearch').click(function(){
    			makeAjaxRequest();
    		});

            $('form').submit(function(e){
                e.preventDefault();
                makeAjaxRequest();
                return false;
            });

			//var name=$("input#name").val();
		    //var dtini=$("input#dtini").val();
		    //var hrini=$("input#hrini").val();
		    //var dtfim=$("input#dtfim").val();	
			//var hrfim=$("input#hrfim").val();
		
			
            function makeAjaxRequest() {
                $.ajax({
                    url: 'php/search.php',
                    type: 'get',	
                    data: {
					       name: $("#name option:selected").val(),
					      //name: $('name').val(),
						 
					      dtini: $("input#dtini").val(),
						  hrini: $("input#hrini").val(),
						  dtfim: $("input#dtfim").val(),
						  hrfim: $("input#hrfim").val()
					},

					
                    success: function(response) {
                        $('table#resultTable tbody').html(response);
						
                    }
                });
            }
    	});
 
 </script>
 
  <!-- Referência do arquivo JS do plugin após carregar o jquery -->
      <!-- Datepicker -->
      <script src="js/bootstrap-datepicker.js"></script>
 
   <script>
      $(document).ready(function () {
        $('#dtini').datepicker({
            format: "dd/mm/yyyy",
            language: "pt-BR"
        });
      });
    </script>
	
	  <script>
      $(document).ready(function () {
        $('#dtfim').datepicker({
            format: "dd/mm/yyyy",
            language: "pt-BR"
        });
      });
    </script>
	

	
	
	</body>
</html>