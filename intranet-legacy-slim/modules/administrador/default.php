<? $this->component->loadComponent("CheckPermissionAdm")->loadCom(); 
	$setor = $this->getCookie("setor");
	$infctg = $this->categories->getCategoriesById($setor);
	$inf_menu = $this->categories->getPermissionAdmMenu($setor);
	$informativo = false;
	$arquivos = false;
	$eventos = false;
	$telefone = false;
	foreach($inf_menu as $ifm){
		switch($ifm['mod']){
			case "Informativos":
								if($ifm['active'] == 1){
									$informativo = true;
								}
								break;
			case "Arquivos":
								if($ifm['active'] == 1){
									$arquivos = true;
								}
								break;
			case "Eventos":
								if($ifm['active'] == 1){
									$eventos = true;
								}
								break;
			case "Telefones":
								if($ifm['active'] == 1){
									$telefone = true;
								}
								break;
		
		}
	}
?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			
				<style>
				.table {
					text-align: left;
				}
				.repositorio-icon {
					font-size: 20px !important;
				}
			</style>
			<script>
			$(document).ready(function(){
				loadFiles(0);
			});
			
			function loadFiles(id){
					$.get( "index.php?action=ajaxFiles&id_f=" + id, 
						function(data) {
							$("#filesshow").html(data);
						}
					);
				}
				function showURL(url){
						$.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-compass-3"></span>',
							   width:150,
							   height:30,
       						   title: 'CETEM - Compartilhar',
        					   content: '<div style="padding:40px 30px 0px 50px;"><strong ><a href=\"' + url + '\">' + url + '</a></strong></div>'
    					});
					}
			
				
			</script>
			
		</head>
	<body class="metro">
			<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom(); ?>
			<? $this->fileman->buildTreeView(); ?>
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
			<div class="span8 offset2">
		  			<div class="row">
						<h2>Área de Conteúdo</h2>
						<p>Administre os Conteúdos da Intranet.</p>
							<div class="quicktool">
												<? if($informativo){?>
												<div onclick="javascript: window.location = 'index.php?module=informativos_administrador&view=list';" class="tile menu-maroto">
		                      				  		<div class="tile-content icon">
        		                			    		<i class="icon-newspaper"></i>
                		        					</div>
                        							<div class="tile-status">
                            							<span class="name">Gerenciar Informativos</span>
                        							</div>
                    							</div>
												<?}?>
												
												<? if($arquivos){?>
												<div onclick="javascript: window.location = 'index.php?module=repositorio_administrador&view=list';" class="tile menu-maroto">
		                      					  	<div class="tile-content icon">
        		                    					<i class="icon-upload-2"></i>
                		        					</div>
                        							<div class="tile-status">
                            							<span class="name">Gerenciar Arquivos</span>
                        							</div>
												</div>
												<?}?>
												<? if($eventos){?>
													<div onclick="javascript: window.location = 'index.php?module=eventos&view=list';"  class="tile menu-maroto">
														<div class="tile-content icon">
															<i class="icon-calendar"></i>
														</div>
														<div class="tile-status">
															<span class="name">Gerenciar Eventos</span>
														</div>
													</div>
												<?}?>
										<? if($telefone){?>
                    						<div onclick="javascript: window.location = 'index.php?module=telefones_administrador&view=list';" class="tile menu-maroto">
		                        				<div class="tile-content icon">
        		                    				<i class="icon-phone"></i>
                		        				</div>
                        						<div class="tile-status">
                            						<span class="name">Gerenciar Telefones</span>
                        						</div>
                    						</div>
										<?}?>
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