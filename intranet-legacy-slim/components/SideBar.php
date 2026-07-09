<?php
	class SideBar{
		private $vars;
		private $parameters;
		
		public function loadCom($parameter = ""){
			//DO SOMETHING
			
			$main = "";
			$repository = "";
			$informativos = "";
			$telephone = "";
			$apps = "";
			$agenda = "";
			
			switch($parameter){
				case "minha_area":
					$main = "class='active'";
					break;
				case "informativos":
					$informativos = "class='active'";
					break;
				case "repositorio":
					$repository = "class='active'";
					break;
				case "telefones":
					$telephone = "class='active'";
					break;
				case "apps":
					$apps = "class='active'";
					break;
				case "agenda":
					$agenda = "class='active'";
					break;	
			}
			
			$output = "<div class='span1 sidebarra'><div class='row'>
					<ul class='unstyled menu-side'>
						<li ".$main." ><a href='index.php?module=minha_area'><i class='icon-home'></i><span class='label'>Minha Area</a></li>
						<li ".$informativos." ><a href='index.php?module=informativos'><i class='icon-newspaper'></i><span class='label'>Informativo</a></li>
						<li ".$repository." ><a href='index.php?module=repositorio'><i class='icon-upload-2'></i><span class='label'>Repositório</a></li>
						<li ".$telephone." ><a href='index.php?module=telefones'><i class='icon-phone'></i><span class='label'>Telefone</a></li>
						<li ".$apps." ><a href='index.php?module=apps'><i class='icon-cog'></i><span class='label'>Sistemas</a></li>
						<li ".$agenda." ><a href='index.php?module=agenda'><i class='icon-calendar'></i><span class='label'>Agenda</a></li>
					</ul>
				</div></div>";
				
				echo $output;
				
		}
		
		public function setParameters($name,$value){
			
		}
	}
?>