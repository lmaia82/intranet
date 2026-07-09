<?php
	class doUpload{
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
			
			if($this->FileMan->uploadFile($_POST['destiny'],$_POST['id_categorie'],$_POST['permission'],$_FILES['uploadFile']['tmp_name'],$_FILES['uploadFile']['name'],$_FILES['uploadFile'],$_POST["nome"],$arrachk)){
				header("Location: index.php?module=repositorio_administrador&view=list&id=".$_POST['destiny']);
			}
		}
	
	}
?>