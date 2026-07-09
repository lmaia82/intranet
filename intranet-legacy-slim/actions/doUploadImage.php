<?php
	class doUploadImage{
		private $FileMan;
		function __construct(){
			$this->FileMan = new DiamondFileManager();
		}
		
		function run(){
			if($this->FileMan->uploadImage($_FILES['uploadFile']['tmp_name'],$_FILES['uploadFile']['name'],$_FILES['uploadFile'])){
				echo "A Imagem foi Enviada com sucesso. Use o endereço : images/".$_FILES['uploadFile']['name'];
			}else{
				echo "Error";
			}
		}
	
	}
?>