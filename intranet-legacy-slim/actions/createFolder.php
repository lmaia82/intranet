<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	class createFolder{
		private $FileMan;
		private $sec;
		private $rul;
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
			
			if($_POST['parent_id'] == $_POST['destinyFolder']){
				
			}
			
			if($_POST['parent_id'] == 0){
			
			}
			
			if($this->FileMan->createFolder($_POST['folderName'],$_POST['destinyFolder'],$_POST['id_categorie'],$_POST['permission'],$arrachk,$_POST['tipo'],$_POST['parent_id'])){
				header("Location: index.php?module=repositorio_administrador&view=list&id=".$_POST['destinyFolder']);
			}
			
		}
		
	}
?>