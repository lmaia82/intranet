<?php
class updateArticle{
		private $DB;
		private $Article;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Article = new DiamondArticle();
		}
		
		function run(){
			if($this->Article->updateArticle($_POST['idUp'],addslashes($_POST['content']),addslashes($_POST['id_categorie']),addslashes($_POST['title']),$_POST['id_permission'])){
				header("Location: index.php?module=informativos_administrador&view=list");
			}
		}
	}
?>