<?php
	class doDownload{
			private $DB;
			private $fman;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->fman = new DiamondFileManager();
		}
		function run(){
				$imgTyp = "";
				if(isset($_GET["imgtype"])){
					$imgTyp = "thumb_";
				}
				
				$arquivo = $_GET["key"];
 				if(isset($arquivo)){
					$query = "select * from files where key_file='".addslashes($arquivo)."';";
					$rslt = $this->DB->selectQuery($query);
					
					$rper = $this->fman->getFilesByDownload($rslt[0]['id_file']);
					if(count($rper) == 0){
						header("Location: index.php?module=error&type=ErrorPermission");
					}
						
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
						
					}
					
					$checkPer = false;
				switch($permission){
					case 1:
							$checkPer = true;
						break;
					case 2:
						if($cat == $_COOKIE["setor"]){
							$checkPer = true;
						}
						break;
					
					case 3:
							$permis = explode("|",$per_gra);
							$permission_valid = false;
							foreach($permis as $prm){
								if($prm == $_COOKIE["setor"]){
									$checkPer = true;
									$permission_valid = true;
								}
							}
							if($permission_valid==false){
								if($cat == $_COOKIE["setor"]){
									$checkPer = true;
								}
							}
						break;
				 
				 }
				 
					if(!$checkPer){
						header("Location: index.php?module=error&type=ErrorPermission");
					}
					$arquivoNome = $arquivo; // nome do arquivo que será enviado p/ download
					$arquivoLocal = DIAMOND_MINE.'/uploads/'.$imgTyp.$arquivoNome."_".$novoNome; // caminho absoluto do arquivo

					// faz o teste se a variavel não esta vazia e se o arquivo realmente existe
     				 switch($ext){ // verifica a extensão do arquivo para pegar o tipo
        				 case "pdf": $tipo="application/pdf"; break;
        				 case "exe": $tipo="application/octet-stream"; break;
       					 case "zip": $tipo="application/zip"; break;
       					 case "doc": $tipo="application/msword"; break;
						 case "docx": $tipo="application/msword"; break;
        				 case "xls": $tipo="application/vnd.ms-excel"; break;
         				 case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
						 case "xlsx": $tipo="application/vnd.ms-excel"; break;
         				 case "pptx": $tipo="application/vnd.ms-powerpoint"; break;
       				     case "gif": $tipo="image/gif"; break;
        				 case "png": $tipo="image/png"; break;
         				 case "jpg": $tipo="image/jpg"; break;
       				     case "mp3": $tipo="audio/mpeg"; break;
        				 case "php": // deixar vazio por seurança
         				 case "htm": // deixar vazio por seurança
        				 case "html": // deixar vazio por seurança
						 case "JPG": $tipo="image/jpg"; break;
						 case "JPEG": $tipo="image/jpg"; break;
						 case "GIF": $tipo="image/gif"; break;
						 case "PNG": $tipo="image/png"; break;
						 
    				  }
					
					// Configuramos os headers que serão enviados para o browser
					header('Content-Description: File Transfer');
					header('Content-Disposition: inline; filename="'.$novoNome.'"');
					header('Content-Type:'.$tipo);
					header('Content-Length:'.filesize($arquivoLocal));
					header('Content-Transfer-Encoding: binary');

					// Envia o arquivo para o cliente
					readfile($arquivoLocal);
			
			
		}
	
	

	}
}	

?>
