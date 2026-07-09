<?php
	class DiamondCategorie{
		private $DB;
		
		function __construct(){
			$this->DB = new DiamondDatabase();
		}
		
		function getCategories($id){
			if($id == 0){
				return $this->DB->selectQuery("select * from categories c where (select count(*) from article where id_categorie = c.id_categorie) > 0 order by id_categorie");	
			}else{
				return $this->DB->selectQuery("select * from categories where id_categorie = ".$id." order by id_categorie");	
			}
		}
		function getPermissionAdmMenu($setor){
			return $this->DB->selectQuery("select mam.description as 'mod',pam.active as 'active' from categories c,modulo_administrador_menu mam,permission_adm_mod pam where pam.id_mod = mam.id_mod and pam.id_categorie=c.id_categorie and c.id_categorie=".$setor);			
		}
		
		
		
		function getCategoriesToPermission(){
			return $this->DB->selectQuery("select * from categories order by id_categorie");	
		}
		
		function getCategoriesAll(){
			return $this->DB->selectQuery("select * from categories order by description");	
		}
		
		function getCategoriesOnArray($categrarr){
			$array = array();
			foreach($categrarr as $item){
				$results = $this->DB->selectQuery("select description from categories where id_categorie = ".$item." order by id_categorie");
				
				$arraytmp = array("desc" => $results[0]['description'],
								  "id" => $item
				);
				$array[] = $arraytmp;
			}
			return $array;
		}
		
		function getCategoriesById($idctg){
			return $this->DB->selectQuery("select * from categories where id_categorie = ".$idctg." order by id_categorie");
		}
	}
?>
