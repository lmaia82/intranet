<?php
	class Header_login{
		private $vars;
		private $parameters;
		
		function __construct($vars = ""){
			$this->vars = $vars;
		}
		
		public function loadCom(){
			//DO SOMETHING
			$output = "<meta name='viewport' content='width=device-width, initial-scale=1.0'>  
			<meta charset='UTF-8'>
		  
			<!-- MetroUICSS -->
			<link rel='stylesheet' href='media/muicss/css/metro-bootstrap.css'>
			<link rel='stylesheet' href='media/muicss/css/metro-bootstrap-responsive.css'>
			<link rel='stylesheet' href='media/scrollbar/perfect-scrollbar.css'>
			
			
			<!-- jQuery -->
			<script src='http://code.jquery.com/jquery.js'></script>
			<script src='http://code.jquery.com/ui/1.10.3/jquery-ui.js'></script>
			<script src='media/muicss/js/metro/metro-core.js'></script>
			<script src='media/muicss/js/metro/metro-touch-handler.js'></script>
			<script src='media/muicss/js/metro/metro-accordion.js'></script>
			<script src='media/muicss/js/metro/metro-button-set.js'></script>
			<script src='media/muicss/js/metro/metro-date-format.js'></script>
			<script src='media/muicss/js/metro/metro-calendar.js'></script>
			<script src='media/muicss/js/metro/metro-datepicker.js'></script>
			<script src='media/muicss/js/metro/metro-carousel.js'></script>
			<script src='media/muicss/js/metro/metro-countdown.js'></script>
			<script src='media/muicss/js/metro/metro-dropdown.js'></script>
			<script src='media/muicss/js/metro/metro-input-control.js'></script>
			<script src='media/muicss/js/metro/metro-live-tile.js'></script>
			<script src='media/muicss/js/metro/metro-progressbar.js'></script>
			<script src='media/muicss/js/metro/metro-rating.js'></script>
			<script src='media/muicss/js/metro/metro-slider.js'></script>
			<script src='media/muicss/js/metro/metro-tab-control.js'></script>
			<script src='media/muicss/js/metro/metro-table.js'></script>
			<script src='media/muicss/js/metro/metro-times.js'></script>
			<script src='media/muicss/js/metro/metro-dialog.js'></script>
			<script src='media/muicss/js/metro/metro-notify.js'></script>
			<script src='media/muicss/js/metro/metro-listview.js'></script>
			<script src='media/muicss/js/metro/metro-treeview.js'></script>
			<script src='media/muicss/js/metro/metro-fluentmenu.js'></script>
			<script src='media/muicss/js/metro/metro-hint.js'></script>
			<script src='media/jquery.form.js'></script>
			<script type='text/javascript' src='media/ckeditor/ckeditor.js'></script>
			<script src='media/scrollbar/perfect-scrollbar.js' ></script>
			
			<style>
				.input-control.select{
					width:30% !important;
				}
				.table {
					text-align: left;
				}
				
				.login {
					width: 500px;
					overflow: hidden;
					margin-bottom: 15px;
				}
				.login .logoti{
					float: left;
					margin-right: 1px;
					padding-top: 100px;
					padding-right: 10px;
				}
				.login form{
					overflow: hidden;
					padding: 5px 0px 15px 50px;
				}
				/** Quick Tool **/
				.quicktool {
					padding: 10px 0px 10px 0px;
				}

				.quicktool > img {
 					 margin: 5px;
 					 display: inline-block;
				}
				.quicktool .image-container {
  					margin: 10px;
 			     	display: inline-block;
  					vertical-align: middle;
				}
			
			</style>
			";
			
			echo $output;
			
		}
		
		public function setParameters($name,$value){
			
		}
	}
?>