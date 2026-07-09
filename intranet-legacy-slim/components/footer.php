<?php
	class footer{
		private $vars;
		private $parameters;
		
		public function loadCom($parameter = ""){
			$output = "<div class='footer'><footer>
								<address>
									<h5><strong>CETEM</strong> - Centro de Tecnologia Mineral</h5>
									Copyright © 2013 - Ministério da Ciência, Tecnologia e Inovação<br>
									Av. Pedro Calmon, 900 - Cidade Universitária<br>
									CEP: 21941-908 - Rio de Janeiro - Brasil
								</address>
						</footer></div>";
						
			echo $output; 
		}
		
	
	}
?>	
	
	
	