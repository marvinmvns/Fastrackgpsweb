	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>LocalizaAuto - Rastreamento GPS</title>
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
	
</head>
<body>

<body onload="moveRelogio()">
<div style="width:105%; height: 83px; background-position: bottom; margin-left:-10px; background-position: bottom; background-image:url('imagens/sombra_topo.png'); background-repeat: repeat-x;">
	<div style="float:right; padding-right: 4%; text-align:right; width:107px">
		<form name="form_relogio" style="font-family:Arial, Helvetica, sans-serif;">
			<input style="padding-left:43px; border:0px; font-size: 20px; font-weight: bold; color: #CCCCCC;" type="text" name="relogio"/><br/>
			<a href="administracao/meus_dados_admin.php" title="Altere seus dados de administrador" target="main" style="font-size:xx-small; color:#c0c0c0; text-decoration: none;"> Gerenciamento |</a>
			<a href="logout.php" title="Sair do sistema - encerrar sessão" rel="nofollow" target="_top" style="font-size:xx-small; color:#c0c0c0; text-decoration: none;"> Sair </a>&nbsp;
		</form>
	</div>

	<a href="default.php" rel="nofollow"><img style="margin-left:15px; margin-top:-5px" src="imagens/topo1.png" alt="" title="" border="false" /></a-->
</div>


<br/><br/>




<div id="menu" >
	<ul>
	    
		<li><a href="#"><img src="/menu/user.png" width="32" height="32" alt="Minha Conta" title=""/></a></li>
		<li><a href="http://LocalizaAuto.net/meus_veiculos.php"><img src="/menu/carro.png" width="32" height="32" alt="Meu Veiculos" title=""/></a></li>
		<li><a href="#"><img src="/menu/dinheiro.png" width="32" height="32" alt="Financeiro" title=""/></a></li>
		<li><a href="#"><img src="/menu/relatorios.png" width="32" height="32" alt="Relatórios" title=""/></a></li>
		<li><a href="#"><img src="/menu/compativeis.png" width="32" height="32" alt="Aparelhos Compativeis" title=""/></a></li>			
		<li class="selected"><a href="#"><img src="/menu/ajuda.png" width="32" height="32" alt="Ajuda" title=""/></a></li>

	</ul>
	<!-- If you want to make it even simpler, you can append these html using jquery -->
	<div id="box"><div class="head"></div></div>

</div>


</body>
</html>