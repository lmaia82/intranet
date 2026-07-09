<? 
	$setor = $this->getCookie("setor");
	$infctg = $this->categories->getCategoriesById($setor);
	$informativos = $this->articles->getList($setor);
	$informe_principal = $this->articles->getListMain();
?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			<style>
				
			</style>
			<script>
				
				$(document).ready(function(){
					$("#maps-btn").click(function(){
  						  $.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-compass-3"></span>',
       						   title: 'CETEM - Transito',
        					   content: '<iframe width=\'640\' height=\'480\'  src=\'media/mapa.php\'></iframe>'
    					});
					});
					
				});
			</script>
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("home"); ?>
			
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
						<div class="span8">
							<h2><? echo $infctg[0]["description"]; ?></h2>
							<h3>Informes</h3>
							<!-- Alteração da Thatyana -->
							
								<div class="tab-control" data-role="tab-control">
									<ul class="tabs">
										<li class="active"><a href="#informe_interno">Informes Internos</a></li>
										<li><a href="#minha_area"><? echo $infctg[0]["description"]; ?> Informa</a></li>
									</ul>
									 <div class="frames">
										 <div class="frame" id="informe_interno">
										  <!-- informe_interno -->
											<!-- Lista de Informativos-->
											 <div class="listview-outlook" data-role="listview">
											  <? foreach($informe_principal as $informativo){ 
																	$arr_date = explode(" ",$informativo['date_creation']);
																	$datea	  = explode("-",$arr_date[0]);
																	$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
											  ?>
													<a class="list marked" href="#" onclick="javascript: window.location = 'index.php?module=informativos&view=load&id=<? echo $informativo["id_article"]; ?>';">
														<div class="list-content">
															<span class="list-title"><? echo $informativo['title']; ?></span>
															<span class="list-subtitle"><? echo $dtinicio; ?></span>
														</div>
													</a>
											  <? }?>	
											</div>
										</div>
										
										 <div class="frame" id="minha_area">
											 <!-- minha_area -->
											<!-- Lista de Informativos-->
											 <div class="listview-outlook" data-role="listview">
											  <? foreach($informativos as $informativo){ 
													$arr_date = explode(" ",$informativo['date_creation']);
													$datea	  = explode("-",$arr_date[0]);
													$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
											  ?>
													<a class="list marked" href="#" onclick="javascript: window.location = 'index.php?module=informativos&view=load&id=<? echo $informativo["id_article"]; ?>';">
														<div class="list-content">
															<span class="list-title"><? echo $informativo['title']; ?></span>
															<span class="list-subtitle"><? echo $dtinicio; ?></span>
														</div>
													</a>
											  <? }?>	
											</div>
										</div>
								   </div>
								</div>
						  
							

						</div>
						<div class="span4">
							<? $this->component->loadComponent("side_bar_tool")->loadCom(); ?>
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
		</div>	
		
		<script type='text/javascript' src='media/pageslide/jquery.pageslide.js'></script>	
		<script>
				$(".second").pageslide({ direction: "left", modal: false});
		</script>		
	</body>
</html>