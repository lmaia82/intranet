<?php
	class header_page{
		private $vars;
		private $parameters;
		
		public function loadCom($parameter = ""){
			//DO SOMETHING
			
		
				$output = "<header class='header-maroto'>
				<div class='grid fluid hackerheader2'>    
					<div class='row hackerheader'>
						<div class='span8 offset2'>
							<div class='row'>
								<a href='http://www.cetem.gov.br'><img class='place-left'  src='media/logo-cetem.png' style='padding:0px 0px 20px 0px;'/></a>
								<a href='index.php'><img class='place-right' width='150px' src='media/img/logo-intra.png'/></a>
							</div>
						</div>
					</div>
				</div>	
			</header>";
			
			echo $output;
				
		}
		
		public function setParameters($name,$value){
			
		}
	}
?>