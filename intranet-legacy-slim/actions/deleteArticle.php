<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	class deleteArticle{
		private $DB;
		private $Article;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Article = new DiamondArticle();
		}
		
		function run(){
			if($this->Article->deleteArticle($_POST['checks'])){
				header("Location: index.php?module=informativos_administrador&view=list");
			}else{
				header("Location: index.php?module=informativos_administrador&view=list");
			}; 	
		}
	}
?>