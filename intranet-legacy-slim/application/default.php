<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			
				<style>
				
			</style>
				
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("apps"); ?>
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
						<h2><? echo $this->title;?></h2>
						<? $this->loadApp(); ?>
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