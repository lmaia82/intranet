<?php
	class deleteTelefone{
		private $DB;
		private $Telefone;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Telefone = new DiamondTelephone();
		}
		
		function run(){
			if($this->Telefone->deleteTelefone($_POST['checks'])){
				header("Location: index.php?module=telefones_administrador&view=list");
			}else{
				header("Location: index.php?module=telefones_administrador&view=list");
			}; 	
		}
	}
?>