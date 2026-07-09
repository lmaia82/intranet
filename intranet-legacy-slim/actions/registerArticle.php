<?php
	class registerArticle{
		private $DB;
		private $Article;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Article = new DiamondArticle();
		}
		
		function run(){
			if($_POST['id_tipo_informe']==2){
				if(isset($_POST['id_url'])){
					$arrid = $_POST['id_url'];
					$arrnome = $_POST['id_name'];
					$index = 0;
					$arrayFOTOS = array('fotos' => array());
						foreach($arrid as $idd){
							$arrtemp = array('nome' => $arrnome[$index],
											 'id'   => $idd);
							$arrayFOTOS['fotos'][] = $arrtemp;
							$index++;
						}
				}
			}
			$jso = json_encode($arrayFOTOS);
			if($this->Article->createArticle(addslashes($_POST['content']),addslashes($_POST['id_categorie']),addslashes($_POST['title']),$_POST['id_permission'],$_POST['id_tipo_informe'],$jso)){
				header("Location: index.php?module=informativos_administrador&view=list");
			}else{
				header("Location: index.php?module=informativos_administrador&view=list");
			}
			
		}
	}
?>