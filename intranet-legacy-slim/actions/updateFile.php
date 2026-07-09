<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	class updateFile{
		private $FileMan;
		function __construct(){
			$this->FileMan = new DiamondFileManager();
		}
		
		function run(){
			if(isset($_POST['checks'])){
					$check = $_POST['checks'];
			
				}else{
				$check = array();
			}
			$gmb = 0;
			$arrachk = "";
			if($_POST['permission'] == 3){
				foreach($check as $ck){
					if($gmb == 0){
						$gmb = 1;
						$arrachk = $ck;
					}else{
						$arrachk .= "|".$ck;
					}
				}
			}
			
			
			if($this->FileMan->updateFile($_POST['id_U'],$_POST['nome'],$_POST['id_categorie'],$_POST['permission'],$arrachk)){
				header("Location: index.php?module=repositorio_administrador&view=list&id=".$_POST['destiny']);
			}
		}
		
	}
?>