<?php
	class getInfoFiles{
			private $DB;
		function __construct(){
			$this->DB = new DiamondDataBase();
		}
		function run(){
				$arquivo = $_GET["id"];
 				if(isset($arquivo)){
					$query = "select * from files where id_file=".$arquivo.";";
					$rslt = $this->DB->selectQuery($query);
					
					foreach($rslt as $fil) {
						$ext = $fil["ext"];
						$novoNome =  $fil["name"];
						$key = $fil["key_file"];
						$desc = $fil["description"];
					}
					
					$arr = array ('name' => $novoNome,
								  'desc' => $desc,
								  'key'  =>  $key,
								  'ext'  => $ext
									);
					echo json_encode($arr);
					
			
		}
	
	

	}
}	

?>