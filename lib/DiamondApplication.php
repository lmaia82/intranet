<?php
	class DiamondApplication{
		private $DB;
		private $name;
		private $appl;
		private $component;
		private $UrlCont;
		private $title;
		function __construct(){
			$this->DB = new DiamondDatabase();
			$this->component = new DiamondComponent();
			$this->UrlCont = new UrlControl();
		}
		
		public function name(){
			return $this->name;
		}
		
		public function title(){
			return $this->title;
		}
		
		private function loadConfigurationInstancesLib(){
			
		}
		
		public function loadApp(){
						ob_start();
						include(DIAMOND_MINE."/application/".$this->name."/default.php");
						$renderPage = ob_get_contents();
						ob_end_clean();
						
						echo $renderPage;

		}
		
		public function load(){
			if($this->UrlCont->getApplication() <> "notset"){
				$name = $this->UrlCont->getApplication();
				if(is_dir(DIAMOND_MINE."/application/".$name)){
						$this->name = $name;	
						$xmresul = simplexml_load_file(DIAMOND_MINE."/application/".$this->name."/information.xml");
						$this->title = $xmresul->title;
						ob_start();
						include(DIAMOND_MINE."/application/default.php");
						$renderPage = ob_get_contents();
						ob_end_clean();
						
						echo $renderPage;
				}else{
					header("Location: index.php?module=error&type=ErrorPage");
				}
			}
		}
		
	}
?>