<?php
	class DiamondComponent{
		private $vars;
		
		function __construct($var = ""){
			$this->vars = $var;
		}	
		
		public function loadComponent($name){
			include(DIAMOND_MINE . "/components/".$name.".php");
			$actionTmp = new $name();
			return $actionTmp;
		}
	}
?>