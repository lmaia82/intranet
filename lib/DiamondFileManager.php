<?php
	class DiamondFileManager
	{
		private $id_folder;
		private $DB;
		public $response;
		public $breadCrumb = array();
		public $breadHTML;
		private $id_folder_treeview;
		private $childs_folders = array();
		private $childs_files = array();
		private $groups_gr = array();
		private $groups_ids = array();
		
		function __construct(){
			$this->DB = new DiamondDataBase();
			/**session_start();
			$this->groups_gr = $_SESSION["groups"];
			foreach($this->groups_gr as $grp){
				$result = $this->DB->selectQuery("select id_categorie from categories where ldap_config='".$grp."'");
				$this->groups_ids[] = $result[0]["id_categorie"];
			}**/
		}
		
		private function session_teste(){
			if(!isset($_SESSION))
			{ 
				session_start();
			}
		}
		
		public function getIdFolderBySector($id){
			$result = $this->DB->selectQuery("select f.* from folder f,categories c where f.nome = c.description and c.id_categorie=".$id);
			return $result[0]["id_folder"];
		}
		
		public function getBack($idfolder){
			$result = $this->DB->selectQuery("select * from subfolder  where id_subfolder =".$idfolder);
			return $result;
		}
			
		public function buildTreeView($currentFolder = 0){
			$folders = $this->getFolders(0);
			$output = "";
			$this->id_folder_treeview = $currentFolder;
			
			if(count($folders) > 0){
				
					$output = "<li class='node'>
									<a href='#' onclick=\"loadFiles(0);\"".$this->setActive(0).">Raiz</a>
								  <ul>";
									
				foreach($folders as $folder){
					if(!$this->checkShortCut($folder['id_folder'])){
							$output .=	$this->treeViewRecursive($folder,$output);
						}
				}
				
					$output .= "</ul>
							 </li>";
			}else{
				$output = "<li class='node '>
								<a href='#' onclick=\"loadFiles(0);\"".$this->setActive(0).">Raiz</a>
						   </li>";
			}
			
			return $output;
		}
		
		public function buildTreeViewShortcut($currentFolder = 0){
			$folders = $this->getFolders(0);
			$output = "";
			$this->id_folder_treeview = $currentFolder;
			
			if(count($folders) > 0){
				
					$output = "<li class=\"node\">
									<a href=\"#\" onclick=\"selectNode(0,this);\"".$this->setActive(0).">Raiz</a>
								  <ul>";
									
				foreach($folders as $folder){
					$output .=	$this->treeViewRecursiveShortcut($folder,$output);					
				}
				
					$output .= "</ul>
							 </li>";
			}else{
				$output = "<li class=\"node \">
								<a href=\"#\" onclick=\"selectNode(0,this);\"".$this->setActive(0).">Raiz</a>
						   </li>";
			}
			
			return $output;
		}
		
		public function treeViewRecursiveShortcut($folder,$out){
			$folders = $this->getFolders($folder['id_folder']);
			$output = "";
			
			
			if(count($folders) > 0){
					$outputtmp = "<a href=\"#\" onclick=\"selectNode(".$folder['id_folder'].",this);\"".$this->setActive($folder['id_folder']).">".$folder['nome']."</a><ul>";
					if($this->setBoolActive($folder['id_folder'])){
						$output = "<li class=\"node\">".$outputtmp;
					}else{
						$output = "<li class=\"node collapsed\">".$outputtmp;
					}				
				foreach($folders as $folder){
					$output .=	$this->treeViewRecursiveShortcut($folder,$output);
					
				}
				
					$output .= "</ul>
							</li>";
			}else{
				$output .= "<li>
							<a href=\"#\" onclick=\"selectNode(".$folder['id_folder'].",this);\"".$this->setActive($folder['id_folder']).">".$folder['nome']."</a>
						   </li>";
			}
			
			return $output;
			
			
		}
		
		private function setActive($id_folder){
			if($this->id_folder_treeview == $id_folder){
				return " class=\"active\" ";
			}else{
				return "";
			}
		}
		
		private function setBoolActive($id_folder){
			if($this->id_folder_treeview == $id_folder){
				return true;
			}else{
				return false;
			}
		}
		
		public function treeViewRecursive($folder,$out){
			$folders = $this->getFolders($folder['id_folder']);
			$output = "";
			
			
			if(!$this->checkShortCut($folder['id_folder'])){
					
				if((count($folders) > 0)){
						$outputtmp = "<a href='#' onclick=\"loadFiles(".$folder['id_folder'].");\"".$this->setActive($folder['id_folder']).">".$folder['nome']."</a><ul>";
						if($this->setBoolActive($folder['id_folder'])){
							$output = "<li class='node'>".$outputtmp;
						}else{
							$output = "<li class='node collapsed'>".$outputtmp;
						}				
					foreach($folders as $folderin){
						
							$output .=	$this->treeViewRecursive($folderin,$output);
						
					}
					
						$output .= "</ul>
								</li>";
				}else{
					$output .= "<li>
								<a href='#' onclick=\"loadFiles(".$folder['id_folder'].");\"".$this->setActive($folder['id_folder']).">".$folder['nome']."</a>
							   </li>";
				}
			}else{
					$output .= "<li>
								<a href='#' onclick=\"loadFiles(".$folder['id_folder'].");\"".$this->setActive($folder['id_folder']).">".$folder['nome']."</a>
							   </li>";
			}				
			return $output;
			
			
		}
		
		private function checkShortCut($id){
			$shrt = $this->DB->selectQuery("select * from folder where id_folder=".$id);
			if($id == 0){
				return false;
			}else{
				if($shrt[0]["type"] == 0){
					return false;
				}else{
					return true;
				}
			}
		}
		
		private function getIdParentShortcut($id){
			$shrt = $this->DB->selectQuery("select * from folder where id_folder=".$id);
			return $shrt[0]["id_parent_folder"];
		}
		
		public function getFolders($id_master){
			 if(!$this->checkShortCut($id_master)){
					$result = $this->DB->selectQuery("select f.* from folder f,subfolder sf where f.id_folder = sf.id_slave and sf.id_master =".$id_master." order by f.nome");
				}else{
					$result = $this->DB->selectQuery("select f.* from folder f,subfolder sf where f.id_folder = sf.id_slave and sf.id_master =".$this->getIdParentShortcut($id_master)." order by f.nome");
			}
			
			$this->session_teste();
			$this->groups_gr = $_SESSION["groups"];
			$groups_ca = array();
			foreach($this->groups_gr as $grp){
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
			
			
			
			$resultacumulate = array();
			
			foreach($result as $resul){
				
				 
				 $prv = $this->DB->selectQuery("select * from folder where id_folder=".$resul["id_folder"]." order by nome");
				 switch($prv[0]["permission"]){
					case 1:
							$resultacumulate[] = $prv[0];
						break;
						
					case 2:
						if($prv[0]["id_categorie"] == $_COOKIE["setor"]){
							$resultacumulate[] = $prv[0];
						}
						break;
					
					case 3:
							$permis = explode("|",$prv[0]["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if(!$permission_valid){
									if($prm == $_COOKIE["setor"] || in_array($prm, $groups_ca)){
										$resultacumulate[] = $prv[0];
										$permission_valid = true;
										
									}
								}
							}
							
							/**if($permission_valid==false){
								if($resul["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
								}
							}**/
						break;
						
							/**$permis = explode("|",$prv[0]["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if($prm == $_COOKIE["setor"]){
									$resultacumulate[] = $prv[0];
									$permission_valid = true;
								}
							}
							if($permission_valid==false){
								if($prv[0]["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $prv[0];
								}
							}
						break;**/
				 
				 }
			}
			
			
			return $resultacumulate;
		}
		
		
		public function getFoldersAdministrator($id_master){
			 $admsetores = $_COOKIE["administrative"];
			 $strsetores = implode(",",$admsetores);
			 
			 if(!$this->checkShortCut($id_master)){
					$result = $this->DB->selectQuery("select f.* from folder f,subfolder sf where f.id_folder = sf.id_slave and f.id_categorie in (".$strsetores.") and sf.id_master =".$id_master." order by f.nome");
				}else{
					$result = $this->DB->selectQuery("select f.* from folder f,subfolder sf where f.id_folder = sf.id_slave and f.id_categorie in (".$strsetores.") and sf.id_master =".$this->getIdParentShortcut($id_master)." order by f.nome");
			}
			
			//session_start();
			$this->session_teste();
			
			$this->groups_gr = $_SESSION["groups"];
			$groups_ca = array();
			foreach($this->groups_gr as $grp){
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
			
			$resultacumulate = array();
			
			foreach($result as $resul){
				
				 
				 $prv = $this->DB->selectQuery("select * from folder where id_folder=".$resul["id_folder"]." order by nome");
				 switch($prv[0]["permission"]){
					case 1:
							$resultacumulate[] = $prv[0];
						break;
						
					case 2:
						if($prv[0]["id_categorie"] == $_COOKIE["setor"]){
							$resultacumulate[] = $prv[0];
						}
						break;
					
					case 3:
							$permis = explode("|",$prv[0]["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if(!$permission_valid){
									if($prm == $_COOKIE["setor"] || in_array($prm, $groups_ca)){
										$resultacumulate[] = $prv[0];
										$permission_valid = true;
										
									}
								}
							}
							
							/**if($permission_valid==false){
								if($resul["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
								}
							}**/
						break;
						
							/**$permis = explode("|",$prv[0]["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if($prm == $_COOKIE["setor"]){
									$resultacumulate[] = $prv[0];
									$permission_valid = true;
								}
							}
							if($permission_valid==false){
								if($prv[0]["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $prv[0];
								}
							}
						break;**/
				 
				 }
			}
			
			
			return $resultacumulate;
		}
		
		public function getFiles($id_folder){
			if(!$this->checkShortCut($id_folder)){
					$result = $this->DB->selectQuery("select * from files where id_folder=".$id_folder." order by description");
				}else{
					$result = $this->DB->selectQuery("select * from files where id_folder=".$this->getIdParentShortcut($id_folder)." order by description");
			}
			
			//session_start();
			$this->session_teste();
			
			$this->groups_gr = $_SESSION["groups"];
			$groups_ca = array();
			foreach($this->groups_gr as $grp){
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
			
			$resultacumulate = array();
			foreach($result as $resul){
				
				 switch($resul["permission"]){
					case 1:
							$resultacumulate[] = $resul;
						break;
						
					case 2:
						if($resul["id_categorie"] == $_COOKIE["setor"] || in_array($resul["id_categorie"], $groups_ca)){
							$resultacumulate[] = $resul;
							
						}
						break;
					case 3:
							$permis = explode("|",$resul["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if(!$permission_valid){
									if($prm == $_COOKIE["setor"] || in_array($prm, $groups_ca)){
										$resultacumulate[] = $resul;
										$permission_valid = true;
										
									}
								}
							}
							
						break;
				 
				 }
			}
			
			
			return $resultacumulate;
			
		}
		
		public function getFilesAdministrator($id_folder){
			 $admsetores = $_COOKIE["administrative"];
			 $strsetores = implode(",",$admsetores);
			 
			if(!$this->checkShortCut($id_folder)){
					$result = $this->DB->selectQuery("select * from files where id_categorie in (".$strsetores.") and id_folder=".$id_folder." order by description");
				}else{
					$result = $this->DB->selectQuery("select * from files where id_categorie in (".$strsetores.") and id_folder=".$this->getIdParentShortcut($id_folder)." order by description");
			}
			
			//session_start();
			$this->session_teste();
			
			$this->groups_gr = $_SESSION["groups"];
			$groups_ca = array();
			foreach($this->groups_gr as $grp){
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
			
			$resultacumulate = array();
			foreach($result as $resul){
				
				 switch($resul["permission"]){
					case 1:
							$resultacumulate[] = $resul;
						break;
						
					case 2:
						if($resul["id_categorie"] == $_COOKIE["setor"] || in_array($resul["id_categorie"], $groups_ca)){
							$resultacumulate[] = $resul;
						}
						break;
					case 3:
							$permis = explode("|",$resul["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if(!$permission_valid){
									if($prm == $_COOKIE["setor"] || in_array($prm, $groups_ca)){
										$resultacumulate[] = $resul;
										$permission_valid = true;
										
									}
								}
							}
							
							/**if($permission_valid==false){
								if($resul["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
								}
							}**/
							/**$permis = explode("|",$resul["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if($prm == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
									$permission_valid = true;
								}
							}**/
							/**if($permission_valid==false){
								if($resul["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
								}
							}**/
						break;
				 
				 }
			}
			
			
			return $resultacumulate;
			
		}
		
		
		public function getFilesImages($id_folder){
			if(!$this->checkShortCut($id_folder)){
					$result = $this->DB->selectQuery("select * from files where ext in ('png','jpg','jpeg','gif','bmp') and id_folder=".$id_folder);
				}else{
					$result = $this->DB->selectQuery("select * from files where ext in ('png','jpg','jpeg','gif','bmp') and id_folder=".$this->getIdParentShortcut($id_folder));
			}
			
			
			$resultacumulate = array();
			foreach($result as $resul){
				
				 switch($resul["permission"]){
					case 1:
							$resultacumulate[] = $resul;
						break;
					case 2:
						if($resul["id_categorie"] == $_COOKIE["setor"]){
							$resultacumulate[] = $resul;
						}
						break;
				
					case 3:
							$permis = explode("|",$resul["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if($prm == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
									$permission_valid = true;
								}
							}
							if($permission_valid==false){
								if($resul["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
								}
							}
						break;
				 
				 }
			}
			
			
			return $resultacumulate;
			
		}
		
		public function getFilesByDownload($id_file){
			$result = $this->DB->selectQuery("select * from files where id_file =".$id_file);
			
			//session_start();
			$this->session_teste();
			
			$this->groups_gr = $_SESSION["groups"];
			$groups_ca = array();
			foreach($this->groups_gr as $grp){
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
			
			$resultacumulate = array();
			foreach($result as $resul){
				
				 switch($resul["permission"]){
					case 1:
							$resultacumulate[] = $resul;
						break;
					case 2:
						if($resul["id_categorie"] == $_COOKIE["setor"]){
							$resultacumulate[] = $resul;
						}
						break;
					case 3:
							/**$permis = explode("|",$resul["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if($prm == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
									$permission_valid = true;
								}
							}
							if($permission_valid==false){
								if($resul["id_categorie"] == $_COOKIE["setor"]){
									$resultacumulate[] = $resul;
								}
							}**/
							$permis = explode("|",$resul["permission_granular"]);
							$permission_valid = false;
							foreach($permis as $prm){
								if(!$permission_valid){
									if($prm == $_COOKIE["setor"] || in_array($prm, $groups_ca)){
										$resultacumulate[] = $resul;
										$permission_valid = true;
										
									}
								}
							}
						break;
				 
				 }
			}
			
			
			return $resultacumulate;
			
		}
		
		public function returnIcon($ext){
			switch(strtolower($ext)){
				case "zip":
					return "<i class='icon-file-zip repositorio-icon'></i>";
					break;
				case "doc":
					return "<i class='icon-file-word repositorio-icon'></i>";
					break;
				case "docx":
					return "<i class='icon-file-word repositorio-icon'></i>";
					break;
				case "xls":
					return "<i class='icon-file-excel repositorio-icon'></i>";
					break;
				case "xlsx":
					return "<i class='icon-file-excel repositorio-icon'></i>";
					break;
				case "ppt":
					return "<i class='icon-file-powerpoint repositorio-icon'></i>";
					break;
				case "pptx":
					return "<i class='icon-file-powerpoint repositorio-icon'></i>";
					break;
				case "pdf":
					return "<i class='icon-file-pdf repositorio-icon'></i>";
					break;
				case "png":
					return "<i class='icon-pictures repositorio-icon'></i>";
					break;
				case "jpg":
					return "<i class='icon-pictures repositorio-icon'></i>";
					break;
				case "jpge":
					return "<i class='icon-pictures repositorio-icon'></i>";
					break;	
				case "gif":
					return "<i class='icon-pictures repositorio-icon'></i>";
					break;		
					
			}
		
		}
		
		private function createThumbs($path,$Image,$Ext,$thumbWidth) 
		{
			error_log("Parameters:{$path} {$Image} {$Ext} {$thumbWidth}", 3, "/var/tmp/my-errors.log");
			// continue only if this is a JPEG image
			if (strtolower($Ext) == 'jpg' || strtolower($Ext) == 'jpeg') 
			{
			  // load image and get image size
			  $img = imagecreatefromjpeg($path.$Image);
			  $width = imagesx($img);
			  $height = imagesy($img);

			  // calculate thumbnail size
			  $new_width = $thumbWidth;
			  $new_height = floor( $height * ( $thumbWidth / $width ) );

			  // create a new tempopary image
			  $tmp_img = imagecreatetruecolor( $new_width, $new_height );

			  // copy and resize old image into new image 
			  imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

			  // save thumbnail into a file
			  imagejpeg($tmp_img, "{$path}thumb_{$Image}" );
			  
			}elseif(strtolower($Ext) == 'png'){
				 // load image and get image size
			  $img = imagecreatefrompng($path.$Image);
			  $width = imagesx($img);
			  $height = imagesy($img);

			  // calculate thumbnail size
			  $new_width = $thumbWidth;
			  $new_height = floor( $height * ( $thumbWidth / $width ) );

			  // create a new tempopary image
			  $tmp_img = imagecreatetruecolor( $new_width, $new_height );

			  // copy and resize old image into new image 
			  imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

			  // save thumbnail into a file
			  imagepng( $tmp_img, "{$path}thumb_{$Image}" );
			}
		  
		  
		}

		
		public function uploadFile($destiny,$id_sector,$permission,$tmpName,$Name,$uploadArray,$informacoes,$granular){
			$name_key = md5(md5($Name).time());
			if(move_uploaded_file($tmpName,"uploads/".$name_key."_".$Name)){
				$Name_Expl = explode(".",$Name);
				$Name_Ext = $Name_Expl[count($Name_Expl) -1];
				$result = $this->DB->insertQuery("insert into files(name,key_file,ext,id_folder,date_creation,id_categorie,permission,description,permission_granular)values('".$Name."','".$name_key."','".$Name_Ext."',".$destiny.",now(),".$id_sector.",".$permission.",'".$informacoes."','".$granular."');");
				if($result){
					error_log("resultado do IF true", 3, "/var/tmp/my-errors.log");
					if(strtolower($Name_Ext) == 'png' || strtolower($Name_Ext) == 'jpg' || strtolower($Name_Ext) == 'jpeg'){
						$this->createThumbs("uploads/",$name_key."_".$Name,$Name_Ext,150);
					} 
					return true;
				}
				
			}else{
				switch($uploadArray['error']){
						case 1:
							   print '<p> The file is bigger than this PHP installation allows</p>';
							   return false;
							   break;
						case 2:
							   print '<p> The file is bigger than this form allows</p>';
							   return false;
							   break;
						case 3:
							   print '<p> Only part of the file was uploaded</p>';
							   return false;
							   break;
						case 4:
							   print '<p> No file was uploaded</p>';
							   return false;
							   break;
				}
			}
		}
		
		
		public function uploadImage($tmpName,$Name,$uploadArray){
			if(move_uploaded_file($tmpName,"images/".$Name)){
					return true;
				}else{
				switch($uploadArray['error']){
						case 1:
							   print '<p> The file is bigger than this PHP installation allows</p>';
							   return false;
							   break;
						case 2:
							   print '<p> The file is bigger than this form allows</p>';
							   return false;
							   break;
						case 3:
							   print '<p> Only part of the file was uploaded</p>';
							   return false;
							   break;
						case 4:
							   print '<p> No file was uploaded</p>';
							   return false;
							   break;
				}
			}
		}
		
		
		public function deleteFolder($id){
		$result = $this->DB->insertQuery("delete from folder where id_folder=".$id);
			if($result){
				return true;
			}else{
				return false;
			}
		}
		
		public function getAllChildsFolder($item){
			$result = $this->DB->selectQuery("select f.id_folder as id_folder from folder f,subfolder sf where f.id_folder = sf.id_slave and sf.id_master =".$item);
			foreach($result as $fold){
				$this->childs_folders[] = $fold["id_folder"];
				$this->getAllChildsFolder($fold["id_folder"]);
			}
			$this->getAllChildsFiles($item);
			
		}
		
		public function getAllChildsFiles($item){
			$resultfiles = $this->DB->selectQuery("select * from files where id_folder=".$item);
			foreach($resultfiles as $file){
				print_r($file["id_file"]);
				$this->childs_files[] = $file["id_file"];
			}
			
		}
		
		public function deleteOnRepository($dados){
				foreach($dados as $dat){
					if($dat["tipo"] == "0"){
					//Folder
						print_r($dat["item"]);
						$this->getAllChildsFolder($dat["item"]);
						
						print_r($this->childs_folders);
						print_r($this->childs_files);
						
						foreach($this->childs_folders as $folder){
							$result = $this->DB->insertQuery("delete from folder where id_folder=".$folder);
						}
						
						foreach($this->childs_files as $file){
							//$result = $this->DB->insertQuery("delete from files where id_file=".$file);
					
						
							$query = "select * from files where id_file=".$file.";";
							$rslt = $this->DB->selectQuery($query);
							
							
							$ext = "";
							$permission = 0;
							$cat = 0;
							$per_gra = "";
							foreach($rslt as $fil) {
								$ext = $fil["ext"];
								$novoNome =  $fil["name"];
								$permission = $fil["permission"];
								$cat = $fil["id_categorie"];
								$per_gra =  $fil["permission_granular"];
								$arquivo =  $fil["key_file"];

									
							}
						$arquivoLocal = DIAMOND_MINE.'/uploads/'.$arquivo."_".$novoNome; // caminho absoluto do arquivo
						unlink($arquivoLocal);
						$result = $this->DB->insertQuery("delete from files where id_file=".$file);
						}
							
						$result = $this->DB->insertQuery("delete from folder where id_folder=".$dat["item"]);
						
					}
					
					if($dat["tipo"] == "1"){
					//File
						$query = "select * from files where id_file=".$dat["item"].";";
							$rslt = $this->DB->selectQuery($query);
							
							
							$ext = "";
							$permission = 0;
							$cat = 0;
							$per_gra = "";
							foreach($rslt as $fil) {
								$ext = $fil["ext"];
								$novoNome =  $fil["name"];
								$permission = $fil["permission"];
								$cat = $fil["id_categorie"];
								$per_gra =  $fil["permission_granular"];
								$arquivo =  $fil["key_file"];

									
							}
						$arquivoLocal = DIAMOND_MINE.'/uploads/'.$arquivo."_".$novoNome; // caminho absoluto do arquivo
						unlink($arquivoLocal);
						$result = $this->DB->insertQuery("delete from files where id_file=".$dat["item"]);
						
					}
					
					
				}
		}
		
		public function createFolder($name,$destinyFolder,$id_categorie,$permission,$permission_granular,$tipo,$parent_id){
			if($tipo == 1){
				$parent_id_tr = $parent_id;
			}else{
				$parent_id_tr = 0;
			}
			$result = $this->DB->insertQuery("insert folder(nome,id_categorie,permission,permission_granular,type,id_parent_folder,date_creation) values('".addslashes($name)."',".$id_categorie.",".$permission.",'".$permission_granular."',".$tipo.",".$parent_id_tr.",now())");
			if($result){
				$lastID = $this->DB->getLastInsertID();
				$resultSub = $this->DB->insertQuery("insert subfolder(id_master,id_slave)values(".$destinyFolder.",".$lastID.")");
				if($resultSub){
					return true;
				}else{
					return false;
				}
			}
		}
				
		
		public function makeBreadCrumb($id){

                        $result = $this->DB->selectQuery("SELECT f.nome as nome from folder f where f.id_folder =".$id);
						
                        if($id != 0){
                                array_push($this->breadCrumb,"<li class=\"active\"><a href=\"#\">".$result[0]['nome']."</a></li>");
                                $this->makeOthersBreadCrumb($id);
                        }
                        array_push($this->breadCrumb,"<li><a onClick=\"loadFiles(0);\" href=\"#\">Raiz</a></li>");

                        $this->breadCrumb = array_reverse($this->breadCrumb);
                        $breadHTML = "<nav class=\"breadcrumbs\"><ul>";


                        foreach($this->breadCrumb as $bread){
                                $breadHTML.= $bread;
                        }

                        $breadHTML .= "</ul></nav>";

                        $this->breadHTML = $breadHTML;
						
						return  $this->breadHTML;
                }

        public function makeOthersBreadCrumb($id){
                        $result = $this->DB->selectQuery("SELECT f.nome as nome,sf.id_master as id_master from subfolder sf,folder f where f.id_folder = sf.id_master and sf.id_slave =".$id);

					
                        if($this->DB->queryCount() == 1){
                                array_push($this->breadCrumb,"<li><a onClick=\"loadFiles(".$result[0]['id_master'].");\" href=\"#\">".$result[0]['nome']."</a></li>");
                                $this->makeOthersBreadCrumb($result[0]['id_master']);

                        }
					}	
					
		public function updateFolder($id_U,$name,$id_categorie,$permission,$permission_granular){
			if($this->DB->Command("update folder set nome='".addslashes($name)."',id_categorie=".$id_categorie.",permission=".$permission.",permission_granular='".$permission_granular."' where id_folder=".$id_U)){
				return true;
			}else{
				return false;
			}	
		}
		
		public function updateFile($id_U,$name,$id_categorie,$permission,$permission_granular){
			if($this->DB->Command("update files set description='".addslashes($name)."',id_categorie=".$id_categorie.",permission=".$permission.",permission_granular='".$permission_granular."' where id_file=".$id_U)){
				return true;
			}else{
				return false;
			}	
		}
		
		public function getInfofolder($idfolder){
			$folder = $this->DB->selectQuery("SELECT * from folder where id_folder=".$idfolder);
			return $folder[0];
		}			
		
		public function getInfoFile($idfile){
			$file = $this->DB->selectQuery("SELECT * from files where id_file=".$idfile);
			return $file[0];
		}
		
		public function getGroupsPermissionGranular(){
			
			
		}
		
	}
?>