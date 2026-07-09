<?php
	class DiamondError {
		private $message;
		private $errorNumber;
		private $arrayError = array(
									"ErrorPermission" => array (
																"Number" => "401",
																"Message" => "Permissão Negada"
																),
									"ErrorPage" => array (
																"Number" => "404",
																"Message" => "Página não Encontrada"
																)
									
									);
									
		public function Invoke($erroTitle){
			$err = array (
							"Number" => $this->arrayError[$erroTitle]['Number'],
							"Message" => $this->arrayError[$erroTitle]['Message']
						); 
			return $err;
		}
	}
?>