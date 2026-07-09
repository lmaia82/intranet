<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	class deleteFile{
		private $FileMan;
		function __construct(){
			$this->FileMan = new DiamondFileManager();
		}
		
		function run(){
			if($this->FileMan->createFolder($_POST['folderName'],$_POST['destinyFolder'])){
				header("Location: index.php?module=repository_administrative&id=".$_POST['destinyFolder']."&success=success");
			}else{
				header("Location: index.php?module=repository_administrative&id=".$_POST['destinyFolder']."&error=error");
			}
		}
		
	}
?>