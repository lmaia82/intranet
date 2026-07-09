<?php
	class deleteRepositorio{
		private $FileMan;
		function __construct(){
			$this->FileMan = new DiamondFileManager();
		}
		
		function run(){
			
			
			$array_doOperation = array();
			if(isset($_POST["checks_folder"])){
				$qnt_itens = count($_POST["checks_folder"]);
				$itens = $_POST["checks_folder"];
				
				if($qnt_itens > 0){
					for($x = 0;$x <= $qnt_itens-1;$x++){
						$arra_tmp = array("item" => $itens[$x],
										  "tipo" => "0");
						$array_doOperation[] = $arra_tmp;				  
					}
						
				}
				
			}
			if(isset($_POST["checks_files"])){
				$qnt_itens_files = count($_POST["checks_files"]);
			    $files = $_POST["checks_files"];
				
				if($qnt_itens_files > 0){ 	
					for($x = 0;$x <= $qnt_itens_files-1;$x++){
						$arra_tmp = array("item" => $files[$x],
										  "tipo" => "1");
						$array_doOperation[] = $arra_tmp;				  
					}
					
					
				}
			}
			
			$this->FileMan->deleteOnRepository($array_doOperation);
			header("Location: index.php?module=repositorio_administrador&view=list&id=".$_POST["currento"]);
			
		}
		
	}
?>