<?

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
	

	
$BcT = new DiamondTelephone();
$Bc = $BcT->buildCards();


?>	
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
		  
			<!-- Bootstrap -->
			<link href="media/css/bootstrap.min.css" rel="stylesheet" media="screen">
			<link href="media/css/custom.diamond.css" rel="stylesheet" type="text/css">
			<link href="media/css/modern.css" rel="stylesheet" type="text/css">
			<link href="media/css/styles.css" rel="stylesheet" type="text/css" />
			
			<!-- jQuery -->
			<script src="http://code.jquery.com/jquery.js"></script>
			<script src="media/js/bootstrap.min.js"></script>
			<!-- Flip -->
			<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
			<script type="text/javascript" src="media/js/jquery.flip.min.js"></script>
			<script type="text/javascript" src="media/js/script.js"></script>
			
			
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
						
					});
			
			
			</script>
		</head>
		<body>
		 
		<!-- Header -->
		<div class="navbar navbar-inverse navbar-fixed-top">
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
		</div>
		
		<!-- Main -->
		<div class="container-fluid">      
			<div class="row-fluid">
				<!-- Sidebar -->
				<div class="span2 sidebarra">
					<ul class="unstyled menu-side">
						<li><a href="index.php?module=main"><img src="media/img/ico-informativo.png" alt="Informativo" /><span class="label">Informativo</a></li>
						<!--<li><a href=""><img src="media/img/ico-eventos.png" alt="Eventos" /><span class="label">Eventos</a></li-->
						<li><a href="index.php?module=repository"><img src="media/img/ico-drive.png" alt="Repositório" /><span class="label">Repositório</a></li>
						<li class="active"><a href="index.php?module=telephone"><img src="media/img/ico-ramais-off.png" alt="Telefone" /><span class="label">Telefone</a></li>
						<!--li><a href=""><img src="media/img/ico-faq.png" alt="FAQ" /><span class="label">FAQ</a></li-->
						<li><a href="index.php?module=apps"><img src="media/img/ico-sistemas.png" alt="Sistemas" /><span class="label">Sistemas</a></li>
					</ul>
				</div>
				
				<!-- Content -->
				<div class="span10">
					<ul class="breadcrumb">
						<li><a href="#">Início</a> <span class="divider">&raquo;</span></li>
						<li class="active">Ramais</li>
					</ul>
					
					<!-- Nested Content -->
					<div class="row-fluid">
						<div class="span12">
							<article>
								<h1>Ramais Telefonicos</h1>
								<p class="lead">Selecione na lista, a inicial correspondente ao contato desejado.</p>
								<div class="sponsorListHolder">				
								<? 
								foreach($Bc as $Bcu) 
								{
								?>
								<div class="sponsor" id="src<? echo $Bcu['ininame']; ?>" title="Click to flip">
										<div id="flip<? echo $Bcu['ininame']; ?>" letter="<? echo $Bcu['ininame']; ?>" class="sponsorFlip">
											<i class="numero_telefone"><? echo $Bcu['ininame']; ?></i>
										</div>												
										<div class="sponsorData">
											<div class="sponsorFlip">
												<i class="numero_telefone_off"><? echo $Bcu['ininame']; ?></i>
											</div>
										</div>
									</div>
								<?	
									}
								?>
							</div>
							<div class="clear"></div>
									
									
					
								<div id="mdltelefone" class="modal hide fade">
											<div class="modal-header">
											<button type="button" id="crose" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<h3 id="header-md">Modal header</h3>
											</div>
											<div id="mdbody" style="overflow: auto;" class="modal-body">
												
 
											</div>
					
								</div>
						
		</article>				
							
							
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