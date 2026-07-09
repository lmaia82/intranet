<?php
	class pagination_categories{
		private $vars;
		private $parameters;
		public function loadCom($parameter = ""){
			$actualPage = $parameter;
			$ptc = $this->parameters["count_articles"];
			//$ptc = 80;
			$id = $this->parameters["id_categorie"];
			$ppp = 5;
			$ipm = 5;
			if(($ptc % $ppp) == 0){
				$pr = (int)($ptc / $ppp);
			}else{
				$pr = ((int)($ptc / $ppp))+ 1;
			}
		
			//DO SOMETHING
		
				$output = "<div class='pagination'>
							<ul>";
							
				$outItens = "";
				if($pr <= $ipm){
					for($x = 1;$x <= $pr;$x++){
						$active = "";
						if($actualPage == $x){
							$active = " class='active' ";
						}
						$outItens .= "<li ".$active."><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".$x."'>".$x."</a></li>";
					}
				}elseif($actualPage == 1){
					for($x = ($actualPage);$x <= (($actualPage) + ($ipm-1));$x++){
						$active = "";
						if($actualPage == $x){
							$active = " class='active' ";
						}
						$outItens .= "<li ".$active."><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".$x."'>".$x."</a></li>";
					}
					$outItens .= "<li class='next'><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".($actualPage + 1)."'><i class='icon-next'></i></a></li>";
				}elseif($actualPage == $pr){
					$outItens .= "<li class='prev'><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".($actualPage - 1)."'><i class='icon-previous'></i></a></li>";
					for($x = ($actualPage - ($ipm-1));$x <= ($pr);$x++){
						$active = "";
						if($actualPage == $x){
							$active = " class='active' ";
						}
						$outItens .= "<li ".$active." ><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".$x."'>".$x."</a></li>";
					}
				}else{
					$maxOverload=0;
					if((($actualPage - 1) + ($ipm-1)) >= $pr){
						$value = $pr;
						$maxOverload=1;
					}else{
						$value = (($actualPage - 1) + ($ipm -1));
					}
					$outItens .= "<li class='prev'><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".($actualPage - 1)."'><i class='icon-previous'></i></a></li>";
					for($x = ($actualPage - 1);$x <= $value;$x++){
						$active = "";
						if($actualPage == $x){
							$active = " class='active' ";
						}
						$outItens .= "<li ".$active." ><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".$x."'>".$x."</a></li>";
					}
					if($maxOverload == 0){
					$outItens .= "<li class='next'><a href='index.php?module=informativos&view=categorie&id=".$id."&page=".($actualPage + 1)."'><i class='icon-next'></i></a></li>";
				}
				}
				
				$output .= $outItens;				
				$output .=	"</ul>
						</div>";
			
			echo $output;
				
		}
		
		public function setParameters($name,$value){
			$this->parameters[$name]=$value;
		}
	}
?>