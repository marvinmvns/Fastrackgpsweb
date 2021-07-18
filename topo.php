<?php include('seguranca.php'); ?>
<?php session_start();

$nome = isset($_SESSION['logSessionName']) ? $_SESSION['logSessionName'] : "[Nome Usuário]";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="en-us" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<meta name="googlebot" content="noindex,nofollow">
<meta name="robots" content="noarchive">

<title>Rastreamento GPS</title>

<script type="text/javascript" src="/menu/js/jquery.js"></script>
<script type="text/javascript" src="/menu/js/jquery.easing.1.3.js"></script>	
<script type="text/javascript">

	
	$(document).ready(function () {

		//transitions
		//for more transition, goto http://gsgd.co.uk/sandbox/jquery/easing/
		var style = 'easeOutExpo';
		var default_left = Math.round($('#menu li.selected').offset().left - $('#menu').offset().left);
		var default_top = $('#menu li.selected').height();

		//Set the default position and text for the tooltips
		$('#box').css({left: default_left, top: default_top});
		$('#box .head').html($('#menu li.selected').find('img').attr('alt'));				
		
		//if mouseover the menu item
		$('#menu li').hover(function () {
			
			left = Math.round($(this).offset().left - $('#menu').offset().left);

			//Set it to current item position and text
			$('#box .head').html($(this).find('img').attr('alt'));
			$('#box').stop(false, true).animate({left: left},{duration:500, easing: style});	

		
		//if user click on the menu
		}).click(function () {
			
			//reset the selected item
			$('#menu li').removeClass('selected');	
			
			//select the current item
			$(this).addClass('selected');
	
		});
		
		//If the mouse leave the menu, reset the floating bar to the selected item
		$('#menu').mouseleave(function () {

			default_left = Math.round($('#menu li.selected').offset().left - $('#menu').offset().left);

			//Set it back to default position and text
			$('#box .head').html($('#menu li.selected').find('img').attr('alt'));				
			$('#box').stop(false, true).animate({left: default_left},{duration:1500, easing: style});	
			
		});
		
	});

	

	</script>


<script language="JavaScript">
function moveRelogio(){
    momentoAtual = new Date()
    hora = momentoAtual.getHours()
    minuto = momentoAtual.getMinutes()
    //segundo = momentoAtual.getSeconds() --> descomente para mostrar segundos

    /*str_segundo = new String (segundo)
    if (str_segundo.length == 1)
       segundo = "0" + segundo*/

    str_minuto = new String (minuto)
    if (str_minuto.length == 1)
       minuto = "0" + minuto

    str_hora = new String (hora)
    if (str_hora.length == 1)
       hora = "0" + hora

    horaImprimivel = hora + "h" + minuto; //+ ":" + segundo

    document.form_relogio.relogio.value = horaImprimivel

    setTimeout("moveRelogio()",1000) 
}
</script>


	
	<style type="text/css">

	body {
		font-family:georgia; 
		font-size:14px; 
	}
	
	a {
		text-decoration:none; 
		color:#333;
		outline:0;
	}
	
	img {
		outline:0; 
		border:0;
	}
	
	#menu {
		/* you must set it to relative, so that you can use absolute position for children elements */
		position:right; 
		text-align:center; 
		width:583px; 
		height:90px;

	}
	
	#menu ul {
		/* remove the list style and spaces*/
		margin:0; 
		padding:0; 
		list-style:none; 
		display:inline;
				
		/* position absolute so that z-index can be defined */
		position:absolute; 
		
		/* center the menu, depend on the width of you menu*/
		left:110px; 
		top:0; 
		margin-left:300px;
		margin-top:5px;
		
	}
	
	#menu ul li {
		
		/* give some spaces between the list items */
		margin:0 20px; 
		
		/* display the list item in single row */
		float:left;
	}
	
	#menu #box {
		
		/* position absolute so that z-index can be defined and able to move this item using javascript */
		position:absolute; 
		left:0; 
		top:0; 
		z-index:200; 

		/* image of the right rounded corner */
		background:url(/menu/tail.gif) no-repeat right center; 
		height:35px;


		/* add padding 8px so that the tail would appear */
		padding-right:8px;
		
		/* set the box position manually */
		margin-left:25px;
		

		
	}
	
	#menu #box .head {
		/* image of the left rounded corner */
		background:url(/menu/head.gif) no-repeat 0 0;
		height:35px;
		color:#eee;
		
		/* force text display in one line */
		white-space:nowrap;

		/* set the text position manually */
		padding-left:8px;
		padding-top:12px;
	}
	
	
	</style>


<base target="contents" />
</head>

<body onload="moveRelogio()">
<div style="width:105%; height: 83px; background-position: bottom; margin-left:-10px; background-position: bottom; background-image:url('imagens/sombra_topo.png'); background-repeat: repeat-x;">
	<div style="float:right; padding-right: 4%; text-align:right; width:162px">
		<form name="form_relogio" style="font-family:Arial, Helvetica, sans-serif;">
			<input style="padding-left:98px; border:0px; font-size: 20px; font-weight: bold; color: #CCCCCC;" type="text" name="relogio"/><br/>
			
			<!--a style="font-size:xx-small; color:#c0c0c0; text-decoration: none;" href="" target="_blank" title="Clique aqui para falar com o Suporte">
				<img style="padding: 0pt 2px 0pt 0pt; margin: 0pt; border: medium none;" src="http://www.google.com/talk/service/resources/chaticon.gif" alt="" height="14" width="16">
			</a-->
			<a href="logout.php" title="Sair do sistema - encerrar sessão" rel="nofollow" target="_top" style="font-size:xx-small; color:#c0c0c0; text-decoration: none;">Sair <img border="0" src="imagens/inativo.png" /></a>&nbsp;
			<br/><a href="default.php" title="Atualizar página" rel="nofollow" target="_top" style="font-size:xx-small; color:#c0c0c0; text-decoration: none;">Atualizar <img border="0" src="imagens/refresh-icon.gif" /></a>&nbsp;
		</form>
	</div>

	<a href="default.php" rel="nofollow" target="_top"><img style="margin-left:15px; margin-top:-5px" src="imagens/topo1.png" alt="" title="" border="false" /></a>
</div>

<br/><br/>

<div id="menu" >
	<ul>

	
		<li class="selected"><a href="../ajax/" target="main"><img src="/menu/relatorios.png" width="43" height="43" alt="Relatórios" title=""/></a></li>
		<li><a href="../OBD/" target="main"><img src="/menu/obd.png" width="43" height="43" alt="OBD" title=""/></a></li>
		<li><a href="../meus_dados.php" target="main"><img src="/menu/user.png" width="43" height="43" alt="Meus Dados" title=""/></a></li>
		<li><a href="../assistente2/" target="main"><img src="http://pianoeteclado.com.br/wp-content/uploads/2014/02/n%C3%A3o-sei-baixar.png" width="43" height="43" alt="Ajuda" title=""/></a></li>
	   
	   
	   

		

	</ul>
	<!-- If you want to make it even simpler, you can append these html using jquery -->
	<div id="box"><div class="head"></div></div>

</div>

</body>

</html>
