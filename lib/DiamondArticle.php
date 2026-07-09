<?php
	class DiamondArticle{
		public $content;
		public $title;
		private $DB;
		
		function __construct()
		{
			$this->DB = new DiamondDataBase();
		}
		
		private function session_teste(){
			if(!isset($_SESSION))
			{ 
				session_start();
			}
		}
		
		function makeArticle(){
			$ID = $_GET['id'];
			$SECTOR = $_GET['sector'];
			$result = $this->DB->selectQuery("select * from article where id_categorie=".$SECTOR." and id_article=".$ID);
			
			$this->content = $result[0]['content'];
			$this->title = $result[0]['title'];
		}
		
		function getInformationArticle($ID){
			$result = $this->DB->selectQuery("select * from article where id_article=".$ID);
			/** INCLUSAO GRUPO DE CONTROLE **/
					$prim = array();
					$this->session_teste();
					$groups_gr = $_SESSION["groups"];
					$groups_ca = array();
						foreach($groups_gr as $grp){
							$result_granulas = $this->DB->selectQuery("select id_categorie from categories where ldap_config='".$grp."'");
							if(count($result_granulas)> 0){
								$groups_ca[] = $result_granulas[0]["id_categorie"];
							}else{
								$result_granulas2 = $this->DB->selectQuery("select id_categorie from categories where ldap_administrator='".$grp."'");
								if(count($result_granulas2)> 0){
									$groups_ca[] = $result_granulas2[0]["id_categorie"];
								}
							}
						}
						
			/** INCLUSAO GRUPO DE CONTROLE **/
		
			if($result[0]["permission"] == 1){
						//Privado
						if(($result[0]["id_categorie"] == $_COOKIE["setor"]) || in_array($result[0]["id_categorie"], $groups_ca)){
							return $result;	
						}else{
							header("Location: index.php?module=error&type=ErrorPermission");
							 
					}
			}else{
				return $result;
			}		
					
			
		}
		
		function getListMain(){
				$result = $this->DB->selectQuery("select a.id_article as id_article,a.title as title,c.description as category,a.permission as permission,a.date_creation as date_creation from article a,categories c where a.id_categorie = c.id_categorie and c.id_index = 1 order by id_article desc");
				
				return $result; 
		}
		
		function getList($idsetor,$cuant = 5){
			if($idsetor == 0){
					$st = "";
					if($cuant <> 5){
						 $st = " limit ".$cuant;
					}
					$result = $this->DB->selectQuery("select a.id_article as id_article,a.title as title,c.description as category,a.permission as permission,a.date_creation as date_creation from article a,categories c where a.id_categorie = c.id_categorie order by id_article desc ".$st);
					/** INCLUSAO GRUPO DE CONTROLE **/
					$prim = array();
					$this->session_teste();
					$groups_gr = $_SESSION["groups"];
					$groups_ca = array();
						foreach($groups_gr as $grp){
							$result_granulas = $this->DB->selectQuery("select id_categorie from categories where ldap_config='".$grp."'");
							if(count($result_granulas)> 0){
								$groups_ca[] = $result_granulas[0]["id_categorie"];
							}else{
								$result_granulas2 = $this->DB->selectQuery("select id_categorie from categories where ldap_administrator='".$grp."'");
								if(count($result_granulas2)> 0){
									$groups_ca[] = $result_granulas2[0]["id_categorie"];
								}
							}
						}
						
					/** INCLUSAO GRUPO DE CONTROLE **/
					
					foreach($result as $rslt){
						if($rslt["permission"] == 1){
							//Privado
								if(($rslt["id_categorie"] == $_COOKIE["setor"]) || in_array($rslt["id_categorie"], $groups_ca)){
									$prim[] = $rslt;
								} 
							}else{
								$prim[] = $rslt;
						}
					}
					return $prim;
					
			}else{
					$st = "";
					if($cuant <> 5){
						if($cuant <> 0){
						 $st = " limit ".$cuant;
						}
					}
					$result = $this->DB->selectQuery("select a.id_article as id_article,a.title as title,c.description as category,a.permission as permission,a.date_creation as date_creation from article a,categories c where a.id_categorie = c.id_categorie and c.id_categorie = ".$idsetor." order by id_article desc ".$st);
			}
				
				return $result;
			
		}
		
		
		public function getListAdm(){
			$triste = $_COOKIE["administrative"];
			$newtriste = implode(",",$triste);
			
			$result = $this->DB->selectQuery("select a.id_article as id_article,a.title as title,c.description as category,c.id_categorie as id_categorie,a.permission as permission,a.date_creation as date_creation from article a,categories c where a.id_categorie = c.id_categorie and a.id_categorie in (".$newtriste.") order by id_article ");		
			return $result;
		}
		
		
		function createArticle($content,$id_categorie,$title,$permission,$tipo,$jsfoto){
			//echo "insert into article(content,id_categorie,title,permission)values('".$content."',".$id_categorie.",'".$title."',".$permission.")";
			
			$result = $this->DB->insertQuery("insert into article(content,id_categorie,title,permission,tipo,foto_gallery,date_creation)values('".$content."',".$id_categorie.",'".$title."',".$permission.",".$tipo.",'".$jsfoto."',now())");
			if($result){
				return true;
			}else{
				return false;
			}
			
		}
		
		
		
		function updateArticle($id,$content,$id_categorie,$title,$permission){
			$result = $this->DB->insertQuery("update article set content='".$content."',id_categorie=".$id_categorie.",title='".$title."',permission=".$permission." where id_article=".$id);
			if($result){
				return true;
			}else{
				return false;
			}
			
		}
		
		function deleteArticle($id){
			foreach($id as $idchk){
				$result = $this->DB->Command("delete from article where id_article=".$idchk);
			}
				return true;
				
		}
		
		public function getAllArticlesByCategorie($id,$page = 1){
			$result = $this->DB->selectQuery("select a.id_article as id_article,a.title as title,c.description as category,a.permission as permission,a.date_creation as date_creation from article a,categories c where a.id_categorie = c.id_categorie and c.id_categorie=".$id." order by id_article limit ".(($page-1) * 5).",5");
				
			$prim = array();
				foreach($result as $rslt){
					if($rslt["permission"] == 1){
						//Privado
							if($rslt["id_categorie"] == $_COOKIE["setor"]){
								$prim[] = $rslt;
							} 
						}else{
							$prim[] = $rslt;
					}
				}
				return $prim;
				
			
			}
		public function getAllArticlesByCategorieWithout($id,$page = 1){
			$result = $this->DB->selectQuery("select a.id_article as id_article,a.title as title,c.description as category,a.permission as permission,a.date_creation as date_creation from article a,categories c where a.id_categorie = c.id_categorie and c.id_categorie=".$id." order by id_article");
				
			$prim = array();
				foreach($result as $rslt){
					if($rslt["permission"] == 1){
						//Privado
							if($rslt["id_categorie"] == $_COOKIE["setor"]){
								$prim[] = $rslt;
							} 
						}else{
							$prim[] = $rslt;
					}
				}
				return $prim;
				
			
			}
			
		public function getCountArticles($id){
			return count($this->getAllArticlesByCategorieWithout($id));
		}	
		
	}

?>