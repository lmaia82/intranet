<?php
	$erro = new DiamondError();
	$resultError = $erro->Invoke($_GET['type']);
?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
		
			<style>
				.error img{
					float: left;
					margin-right: 25px;
				}
				 .error h1{
					text-transform: uppercase;
					margin: 15px 0px 15px;
					border-bottom: none;	
					font-size: 5em;
					color: #393939;
				}
				 .error p{
					text-transform: uppercase;
					font-weight: bold;
					font-size: 2.3em;
					color: #5a5656;
				}
				.error{
					padding-top:50px;
				}
			</style>
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom(); ?>
			
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
						<h3>Error</h3>
						<article>
		  				<div class="error" >
									<img src="media/img/erro.png" alt="" />
									<h1>Erro <? echo $resultError['Number'];?></h1>
									<p>	<? echo $resultError['Message']; ?></p>
								</div>
						</article>
						
					</div>
				</div>
		  </div>
		  <div class="row">
			<div class="span8 offset2">
		  			<div class="row">
						<? $this->component->loadComponent("footer")->loadCom(); ?>
					</div>
			</div>
		  </div>
		</div>	
						
	</body>
</html>