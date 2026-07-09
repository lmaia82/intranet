<?php
	class deleteFolder{
		private $FileMan;
		function __construct(){
			$this->FileMan = new DiamondFileManager();
		}
		
		function run(){
			if($this->FileMan->deleteFolder($_POST['id_deletefile'])){
				header("Location: index.php?module=repository_administrative&id=".$_POST['destinyFolder']."&success=success");
			}else{
				header("Location: index.php?module=repository_administrative&id=".$_POST['destinyFolder']."&error=error");
			}
		}
		
	}
?>