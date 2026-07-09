<?php
	class updateTelefone{
		private $DB;
		private $Telefone;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Telefone = new DiamondTelephone();
		}
		
		function run(){
		
			if($this->Telefone->updateTelefone(addslashes($_POST['idUp']),addslashes($_POST['nome']),addslashes($_POST['telefone']),addslashes($_POST['id_setor']),$_POST['email'])){
				header("Location: index.php?module=telefones_administrador&view=list");
			
			}
			
		}
	}
?>