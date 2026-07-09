<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
				<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			<script type="text/javascript">
			
			
		</script>
				
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("informativo"); ?>		
		<!-- Main -->
		<div class="grid">    
		  <div class="row">
		  		<div class="span12 offset3">
		  			<div class="row">
					<h2>Informes</h2>
					
							  	<? if($this->loadView() == "notset"){?>
										<article>
							  			<? $lista_categoria = $this->categories->getCategories(0); ?>
										<div class="listview-outlook" data-role="listview">
							  				<? foreach($lista_categoria as $categoria){ ?>
							  					<div class="list-group collapsed">
                                    			   	 <a href="" class="group-title"><? echo $categoria["description"]; ?></a>
                                     				   	<div class="group-content">
							  								<? $lista_informativo = $this->articles->getList($categoria["id_categorie"]);?>
															
																<? foreach($lista_informativo as $informativo){
																	$arr_date = explode(" ",$informativo['date_creation']);
																	$datea	  = explode("-",$arr_date[0]);
																	$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
																?>
																
																	<a class="list" onclick="javascript: window.location = 'index.php?module=informativos&view=load&id=<? echo $informativo["id_article"]; ?>';" href="#">
																	  <div class="list-content">
																		 <span class="list-title"><? echo $informativo["title"]; ?></span>
																		 <span class="list-subtitle"><? echo $dtinicio; ?></span>
																	  </div>
																	</a>
																<? } ?>
															<br/>
															
															<button class="info" onclick="javascript: window.location = 'index.php?module=informativos&view=categorie&id=<? echo $categoria["id_categorie"]; ?>';">Ver Todos os Informes do <? echo $categoria["description"]; ?></button>
							  				 			</div>	
                                    			</div>
							  				<? } ?>
									</div>
									</article>
							  	<? } ?>
								


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
	
		
		<script type='text/javascript' src='media/pageslide/jquery.pageslide.js'></script>	
		<script>
				$(".second").pageslide({ direction: "left", modal: false});
		</script>	
	</body>
</html>