<?php
	class Header_HTML{
		private $vars;
		private $parameters;
		
		function __construct($vars = ""){
			$this->vars = $vars;
		}
		
		public function loadCom(){
			//DO SOMETHING
			$output = "<meta name='viewport' content='width=device-width, initial-scale=1.0'>  
			<meta charset='UTF-8'>
		  
			<!-- Bootstrap -->
			<link href='media/css/bootstrap.min.css' rel='stylesheet' media='screen'>
			<link href='media/css/custom.diamond.css' rel='stylesheet' type='text/css'>
			<link href='media/css/modern.css' rel='stylesheet' type='text/css'>
			<link href='media/css/styles.css' rel='stylesheet' type='text/css' />
			<link rel='stylesheet' href='media/muicss/css/metro-bootstrap.css'>
			
			<!-- jQuery -->
			<script src='http://code.jquery.com/jquery.js'></script>
			<script src='media/muicss/js/jquery/jquery.widget.min.js'></script>
			<script src='media/js/bootstrap.min.js'></script>
			<script src='media/muicss/js/metro-loader.js'></script>
			
			<!-- Flip -->
			<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js'></script>
			<script type='text/javascript' src='media/js/jquery.flip.min.js'></script>
			<script type='text/javascript' src='media/js/script.js'></script>";
			
			echo $output;
			
		}
		
		public function setParameters($name,$value){
			
		}
	}
?>