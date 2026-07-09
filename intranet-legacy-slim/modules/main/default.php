<?
$data = new DiamondDate();

$events = new DiamondEvents();
$eventsI = $events->getEvents();

$forecast = new DiamondForecast();
$infoC = $forecast->Information();

$mainCreate = new DiamondArticle();


$status_login = false;
$status_login_adm = false;
	
	if(verify_Exists_cookies()){
		//Verify Setores
		if(isset($_COOKIE['administrative'])){
			if($_COOKIE['administrative']!= 0){
				$status_login_adm = true;
			}
		}
	}

	if (isset($_COOKIE['nome']) && !empty($_COOKIE['nome']) ) {
			$status_login = true;
	}	
?>	
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
		  
			<? $this->component->loadComponent("Header_HTML")->loadCom(); ?>
			
			
				<script type="text/javascript">
						$(document).ready(function(){
						
							<? if(!$status_login ){?>
								$("#btnlogin").popover({
									html: true,
									content: function(){
									return $("#popover_content_login").html();
										}
								});
							<? }?>
							
		
							$(".data-event").click(function(event) {
									event.preventDefault();
							});
				
							$(".data-event").mouseleave(function(){
								$(this).popover('hide');
							});
				
							$('#weather').tooltip();
					
							$(".data-event").popover({
								html: true,
								placement: 'left',
								content: function(){
									return $("#popover_event_info").html();
								}
							});
				
					});
			
			
			</script>
		</head>
		<body>
		<section class="metro">
			<nav class="navigation-bar">
    <nav class="navigation-bar-content">
        <div class="element">
            <a class="dropdown-toggle" href="#">METRO UI CSS</a>
            <ul class="dropdown-menu" data-role="dropdown">
                <li><a href="#">Main</a></li>
                <li><a href="#">File Open</a></li>
                <li class="divider"></li>
                <li><a href="#">Print...</a></li>
                <li class="divider"></li>
                <li><a href="#">Exit</a></li>
            </ul>
        </div>
 
        <span class="element-divider"></span>
        <a class="element brand" href="#"><span class="icon-spin"></span></a>
        <a class="element brand" href="#"><span class="icon-printer"></span></a>
        <span class="element-divider"></span>
 
        <div class="element input-element">
            <form>
                <div class="input-control text">
                    <input type="text" placeholder="Search...">
                    <button class="btn-search"></button>
                </div>
            </form>
        </div>
 
        <div class="element place-right">
            <a class="dropdown-toggle" href="#">
                <span class="icon-cog"></span>
            </a>
            <ul class="dropdown-menu place-right" data-role="dropdown">
                <li><a href="#">Products</a></li>
                <li><a href="#">Download</a></li>
                <li><a href="#">Support</a></li>
                <li><a href="#">Buy Now</a></li>
            </ul>
        </div>
        <span class="element-divider place-right"></span>
        <a class="element place-right" href="#"><span class="icon-locked-2"></span></a>
        <span class="element-divider place-right"></span>
        <button class="element image-button image-left place-right">
            Sergey Pimenov
            <img src="images/211858_100001930891748_287895609_q.jpg"/>
        </button>
    </nav>
</nav>
		</section>
		<!-- Header -->
		<!--div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<div class="row-fluid">
						<div class="span4 logo">
							<a href="index.php?module=main"><h4><span></span></h4></a>
						</div>
						<div class="span3 offset4">
							<div class="pull-right">
								<ul class="nav">
									<li class="dropdown">
								
								<? if(!$status_login ){?>
									<a id="btnlogin" class="dropdown-toggle"  data-toggle="popover" data-placement="bottom" data-original-title="Autenticação" href="#">Login
									<b class="caret"></b>
									</a>
								<? }else{ ?>
									<a id="btnlogin" class="dropdown-toggle"  data-toggle="dropdown" href="#"><? echo $_COOKIE["nome"]; ?>
									<b class="caret"></b>
									</a>
									<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
										<?php
											if($status_login_adm){
										?>
										<li>
											<a href="index.php?module=administrator">Administração</a> 
										</li>
										<?php
											}
										?>
										<li>
											<a href="#" onClick="frmlogoff.submit();">Deslogar</a> 
										</li>
									</ul>
								<?
									}
								?>
								
									</li>
								</ul>
							</div>
						</div>
					</div>          
				</div>
			</div>
		</div-->
		
		
		
		<!-- Main -->
		<div class="container-fluid">      
			<div class="row-fluid">
				<!-- Sidebar -->
					<? $this->component->loadComponent("SideBar")->loadCom($this->module); ?>
				<!-- BreadCrumb -->
				<div class="span10">
					<ul class="breadcrumb">
						<li><a href="#">Início</a> <span class="divider">&raquo;</span></li>
						<li class="active">Teste Maroto</li>
					</ul>
					
					<!-- Nested Content -->
					<div class="row-fluid">
						<div class="span9">
							<article>
								<h1>Title</h1>
								<? echo $mainCreate->buildHome(); ?>
							</article>
						</div>
						
						<div class="span3">
							<aside>
								<!-- Hello User! -->
								<div class="hello-user">
									<div class="hour">
										<a href="#" id="weather" data-toggle="tooltip" data-placement="top" title="" data-original-title="<? echo $infoC['msg'].$infoC['temperature'][0].' Cº'; ?>">
											<img src="<? echo $infoC['img']; ?>" id="" alt="" />
										</a>
									</div>
									<div class="data">
										<h1><? echo $data->getDay(); ?></h1>
										<p><span><? echo $data->getLiteralDayOfWeek(); ?></span><br><? echo $data->getLiteralMonth(); ?> de <? echo $data->getYear(); ?></p>
									</div>
								</div>
								
								<!-- Next Events -->
								<div class="calendar-event">
									<h2>Próximos Eventos</h2>
									<div class="item-event">
										<?php
											foreach($eventsI as $Evi){
										?>
											<a href="" class="data-event"><i class="calendar-day"><? echo $Evi['data']; ?></i><span><? echo $Evi['title']; ?></span><br><? echo $Evi['local']; ?></a>
										<?
											}
										?>
									</div>
									
									<!-- RETIRADO PARA APRESENTAÇÃO NO DIA 09/08/2013 -->
									<!--a href="#" class="btn btn-mini btn-inverse">Confira o calendário completo</a-->
								</div>
								
								<!-- Radar UFRJ -->
								<div class="radar-ufrj">
									<h2>Radar UFRJ</h2>
									<a href="#myModal" data-toggle="modal"><img src="media/img/mapa.gif" class="img-rounded" alt="" /></a>
									<p>Confira em tempo real como está o trânsito nos arredores do CETEM<p/>
								</div>
								
								<!-- ModalMap -->
								<div id="myModal" class="modal hide fade modal-great" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-body">
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>										
										<h3>O trânsito em tempo real na UFRJ</h3>										
										<iframe name="interno" width="770" height="330" src="media/mapa.php"></iframe>
									</div>
								</div>
								
							</aside>
						</div>
					</div>					
					
					<div class="row-fluid footer">
						<footer>
							<div class="span6">
								<address>
									<h5>CETEM - Centro de Tecnologia Mineral</h5>
									Copyright © 2012 - Ministério da Ciência, Tecnologia e Inovação<br>
									Av. Pedro Calmon, 900 - Cidade Universitária<br>
									CEP: 21941-908 - Rio de Janeiro - Brasil
								</address>
							</div>
						</footer>	
					</div>
				</div>
			</div>	
		</div>
		
		<div id="popover_event_info" style="display: none">
				<a href="" class="data-event">
				<i class="calendar-day">07</i><span>Titulo Evento</span><br>Local do evento
				</a>
		</div>
		
		
		<!-- PopOver -->
				<div id="popover_content_login" style="display: none">
					<form action="index.php?action=doLogin" method="POST" class="form-vertical">
									<div class="control-group">
									
										<label class="control-label" for="inputEmail">Usuário</label>
											<div class="controls">
												<input type="text" name="usuario" placeholder="Usuario">
											</div>
									</div>
									<div class="control-group">
										<label class="control-label" for="inputPassword">Senha</label>
										<div class="controls">
											<input type="password" name="pass" placeholder="Senha">
											<input type="HIDDEN" name="urlto" id="urlto"/>
											</div>
										</div>
									<div class="control-group">
										<div class="controls">
											
										</label>
										<button type="submit" class="btn btn-primary">Logar</button>
										
									</div>
									</div>
								</form>
				</div>
				
	 <!-- Form Logoff -->
		<form id="frmlogoff" action="index.php?action=doLogoff" method="POST">
			<input type="HIDDEN" name="urlto" id="urltolog"/>
		</form>
		
		</body>
	</html>	