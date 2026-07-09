<?php
	class navBar{
		private $vars;
		private $parameters;
		
		
		public function loadCom($parameter = ""){
		$data_cls = new DiamondDate();
		
		$status_login = false;
		$status_login_adm = false;
			//DO SOMETHING
				if($this->verify_Exists_cookies()){
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
			
			
			
			$home = false;
			$informativo = false;
			$arquivo = false;
			$ramal = false;
			$apps = false;
			$agenda = false;
			
			switch($parameter){
				case "home":
					$home = true;
					break;
				case "informativo":
					$informativo = true;
					break;
				case "arquivo":
					$arquivo = true;
					break;
				case "ramal":
					$ramal = true;
					break;
				case "apps":
					$apps = true;
					break;
				case "agenda":
					$agenda = true;
					break;
			}
			
			
			$arrayactive = "var menuactive = [\"".$home."\",\"".$informativo."\",\"".$arquivo."\",\"".$ramal."\",\"".$apps."\",\"".$agenda."\"];";
			
			
			$output = "<script>
		
			$(document).ready(function(){
				".$arrayactive."
				
				//Home
				if(menuactive[0]){
						$('#home-button').addClass(\"activeMenuwhenInside\");
						$('#home-text').show('slow');
					}else{
						$('#home-text').hide();
						
						//Home
						$('#home-button').mouseenter(function() {
								$('#home-text').show('slow');
						});
						
						$('#home-button').mouseleave(function() {
								$('#home-text').hide();
								
						});
				}
				
				
				//Informativos
				if(menuactive[1]){
						$('#info-button').addClass(\"activeMenuwhenInside\");
						$('#info-text').show('slow');
					}else{
						$('#info-text').hide();
						//Informativos
							$('#info-button').mouseenter(function() {
									$('#info-text').show('slow');
							});
							
							$('#info-button').mouseleave(function() {
									$('#info-text').hide();
									
							});
				}
				
				//Arquivos
				if(menuactive[2]){
						$('#up-button').addClass(\"activeMenuwhenInside\");
						$('#up-text').show('slow');
					}else{
						$('#up-text').hide();
						//Arquivos
							
							$('#up-button').mouseenter(function() {
									$('#up-text').show('slow');
							});
							
							$('#up-button').mouseleave(function() {
									$('#up-text').hide();
									
							});
							
				}
				
				//Ramais
				if(menuactive[3]){
						$('#ramal-button').addClass(\"activeMenuwhenInside\");
						$('#ramal-text').show('slow');
					}else{
						$('#ramal-text').hide();
						
						//Ramal
							$('#ramal-button').mouseenter(function() {
									$('#ramal-text').show('slow');
							});
							
							$('#ramal-button').mouseleave(function() {
									$('#ramal-text').hide();
									
							});
					
				}
				
				//Apps
				if(menuactive[4]){
						$('#apps-button').addClass(\"activeMenuwhenInside\");
						$('#apps-text').show('slow');
					}else{
						$('#apps-text').hide();
						//Apps
							$('#apps-button').mouseenter(function() {
									$('#apps-text').show('slow');
							});
							
							$('#apps-button').mouseleave(function() {
									$('#apps-text').hide();
									
							});
				}
				
				//Agenda
				if(menuactive[5]){
						$('#agenda-button').addClass(\"activeMenuwhenInside\");
						$('#agenda-text').show('slow');
					}else{
						$('#agenda-text').hide();
						//Agenda
						$('#agenda-button').mouseenter(function() {
								$('#agenda-text').show('slow');
						});
						
						$('#agenda-button').mouseleave(function() {
								$('#agenda-text').hide();
								
						});
					
				}
				
		
					
				$('#sair-text').hide();
					
					//Sair
					$('#sair-button').mouseenter(function() {
							$('#sair-text').show('slow');
					});
					
					$('#sair-button').mouseleave(function() {
							$('#sair-text').hide();
							
					});
					
					$('#administrador-text').hide();
					
					//administrador-text
					$('#administrador-button').mouseenter(function() {
							$('#administrador-text').show('slow');
					});
					
					$('#administrador-button').mouseleave(function() {
							$('#administrador-text').hide();
							
					});	
				
					$('#links-uteis-text').hide();
					
					//links-uteis-text
					$('#links-uteis-button').mouseenter(function() {
							$('#links-uteis-text').show('slow');
					});
					
					$('#links-uteis-button').mouseleave(function() {
							$('#links-uteis-text').hide();
							
					});	
					
				
				$('#navlegal').scrollspy({
    					min: $('#navlegal').offset().top,
    					onEnter: function(element, position) {
    						$('#navlegal').addClass('fixed-top');
    					},
    					onLeave: function(element, position) {
    						$('#navlegal').removeClass('fixed-top');
    					}
        		});
			
				$('#modal a').click(function(){
					$.pageslide.close();
				});
				
			});
			</script>
			<!-- NavBar -->
			<style>
				
			</style>";
			
			
						
			$output .= "<nav id='navlegal' class='navigation-bar menu-maroto'>
						<nav class='navigation-bar-content container'>";
		
			$menuu = "<span class='element-divider place-left'></span>	
				<a class='element place-left' href='index.php?module=minha_area' id='home-button'><span class='icon-home'></span><span id='home-text'>&nbsp;Início</span></a>
			<span class='element-divider place-left'></span>	
				<a class='element place-left' href='index.php?module=informativos' id='info-button'><span class='icon-newspaper'></span><span id='info-text'>&nbsp;Informes</span></a>
			<span class='element-divider place-left'></span>
				<a class='element place-left' href='index.php?module=repositorio' id='up-button'><span class='icon-upload-2'></span><span id='up-text'>&nbsp;Arquivos</span></a>
			<span class='element-divider place-left'></span>
				<a class='element place-left' href='index.php?module=telefones' id='ramal-button'><span class='icon-phone'></span><span id='ramal-text'>&nbsp;Ramais</span></a>
			<span class='element-divider place-left'></span>
				<a class='element place-left' href='index.php?module=apps' id='apps-button'><span class='icon-cog'></span><span id='apps-text'>&nbsp;Sistemas</span></a>
			<span class='element-divider place-left'></span>
				<a class='element place-left' href='index.php?module=agenda' id='agenda-button'><span class='icon-calendar'></span><span id='agenda-text'>&nbsp;Eventos</span></a>
			<span class='element-divider place-left'></span>
				<a class='element place-left second' href='#modal' id='links-uteis-button'><span class='icon-link'></span><span id='links-uteis-text'>&nbsp;Links Uteis</span></a>
			<span class='element-divider place-left'></span>";
			
			
			if($parameter != "autenticar"){
				$output .= $menuu;
			}
			
    			if(!$status_login ){
				
					$output .= "<span class='element-divider place-right'></span>
									<a class='element place-right' href='index.php?module=autenticar'>Entrar</a>
								<span class='element-divider place-right'></span>";
				}else{
					   $output .= "<span class='element-divider place-right'></span>
										<a onclick='javascript: frmlogoff.submit();' class='element place-right' href='#' id='sair-button'><span class='icon-switch'></span><span id='sair-text'>&nbsp;Sair</span></a>
										<span class='element-divider place-right'></span>";
										if($status_login_adm){
											$output .=	"<a id='administrador-button' class='element place-right' href='index.php?module=administrador'><span class='icon-grid-view'></span><span id='administrador-text'>&nbsp;Administrador</span></a>
										<span class='element-divider place-right'></span>";
										}
										
						$output .=        	"<a class='element place-right' href='#'>".$data_cls->getWelcomeMessage().$_COOKIE["nome"]."</a>
										<span class='element-divider place-right'></span>";
						
				}       
           	$output .= "</nav>
			</nav><form id='frmlogoff' action='index.php?action=doLogoff' method='POST' style='display: none;'>
			</form>";
				
				echo $output;
				
		}
		
		public function setParameters($name,$value){
			
		}
		
		private function verify_Exists_cookies()
		{
			if(!empty($_COOKIE['nome']) && isset($_COOKIE['nome']))
			{
				return true;
			}else{
				return false;
			}
	
	
}
	}
?>	
	
	
	