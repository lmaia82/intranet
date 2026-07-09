<? $cards = $this->telefones->buildCards(); 

?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
				
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("ramal"); ?>
			
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
					
					<h2>Ramais</h2>
						  <div class="tab-control" data-role="tab-control">
							<ul class="tabs">
								
								
								<?
								$gmb = 0;
								$strr = "";
								
								foreach($cards as $card){ 
									if($gmb == 0){
											$strr = " class=\"active\" ";
											$gmb = 1;
									}else{
										$strr = "";
									}
								?>
								<li <? echo $strr; ?>><a href="#Lista_<? echo $card["ininame"]; ?>"><? echo $card["ininame"]; ?></a></li>
							 <?}?>
							</ul>

                        <div class="frames">
						
							<? foreach($cards as $card){ ?>
								
								 <div class="frame" id="Lista_<? echo $card["ininame"]; ?>">
									<? $divletter = $this->telefones->getRamalByLetter($card["ininame"]);?>		
			
										<div class="listview-outlook" data-role="listview">
											<? foreach($divletter as $letter){?>
												<a class="list" href="#">
											<div class="list-content">
												<span class="list-title"><? echo $letter["nome"]; ?></span>
												<span class="list-subtitle"><? echo $letter["setor"]; ?></span>
												<span class="list-remark"><? echo $letter["telefone"]; ?></span>
												<span class="list-remark"><? echo $letter["cargo"]; ?></span>
												<span class="list-remark"><? echo $letter["email"]; ?></span>
											</div>
											</a>
											<? } ?>
										</div>
								</div>
				
							 <?}?>
                           
							
                        
                        </div>

                    </div>


                		 
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
	
			<? foreach($cards as $card){ ?>
				<? $divletter = $this->telefones->getRamalByLetter($card["ininame"]);?>		
			
		<div id="list_<? echo $card["ininame"]; ?>" style="display: none;">
			<div class="listview-outlook" data-role="listview">
				<? foreach($divletter as $letter){?>
                                    <a class="list" href="#">
                                        <div class="list-content">
                                            <span class="list-title"><? echo $letter["nome"]; ?></span>
                                            <span class="list-subtitle"><? echo $letter["setor"]; ?></span>
                                            <span class="list-remark"><? echo $letter["telefone"]; ?></span>
                                            <span class="list-remark"><? echo $letter["email"]; ?></span>
                                        </div>
                                    </a>
                    <? } ?>
                 </div>
		</div>
	<? } ?>
	
		<script type='text/javascript' src='media/pageslide/jquery.pageslide.js'></script>	
		<script>
				$(".second").pageslide({ direction: "left", modal: false});
		</script>
	</body>
</html>