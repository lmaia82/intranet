<?
	
	if($this->getParameterUrl("id") == "notset"){
		$id_fo = 0;
	}else{
		$id_fo = $this->getParameterUrl("id");
	}
	
	$listFolders = $this->fileman->getFolders($id_fo);
	$listFiles = $this->fileman->getFiles($id_fo);
	
	$setor = $this->getCookie("setor");
	if($setor=="notset"){
		header("Location: index.php?module=apps");
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
				loadFiles(<? echo $id_fo; ?>);
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
							
       						   title: 'CETEM - Compartilhar',
        					   content: '<div style="padding: 10px 10px 10px 10px;"><em>Copie o Link abaixo e cole no Email para compartilhar este arquivo:</em></br><a href=\"' + url + '\">' + url + '</a></strong></div>'
    					});
					}
			
				
			</script>
			
		</head>
	<body class="metro">
			<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("arquivo"); ?>
			<? $this->fileman->buildTreeView(); ?>
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
			<div class="span12">
		  			<div class="row">
						<div class="span2">
							<h2>Arquivos</h2>
							<div class="row">
									
									<ul class="treeview" data-role="treeview">
									
										<? echo $this->fileman->buildTreeView($id_fo); ?>
									
									</ul>
						
							</div>
						</div>
						<div class="span10">
							<div class="row">
								<div id="filesshow">
								</div>
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
		</div>
		
		<script type='text/javascript' src='media/pageslide/jquery.pageslide.js'></script>	
		<script>
				$(".second").pageslide({ direction: "left", modal: false});
		</script>
	</body>
</html>