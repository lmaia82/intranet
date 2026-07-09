<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	
	class iframe_list_images{
		private $DB;
		private $HTML = false;
		private $event;
		private $fman;
		private $component;
		private $appg;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->event = new DiamondEvents();
			$this->component = new DiamondComponent();
			$this->fman = new DiamondFileManager();
			$this->appg = new App();
		}
		
		function run(){		   
		if($this->appg->getParameterUrl("id") == "notset"){
			$id_fo = 0;
		}else{
			$id_fo = $this->appg->getParameterUrl("id");
		}
	
	
	$listFolders = $this->fman->getFolders($id_fo);
	$listFiles = $this->fman->getFilesImages($id_fo);
	
	$setor = $this->appg->getCookie("setor");
	$infctg = $this->appg->categories->getCategoriesById($setor);		

	
echo "<!DOCTYPE html>
	<html lang=\"pt-br\">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  
			<meta charset=\"UTF-8\">";
			
				$this->component->loadComponent("Header_teste")->loadCom();
				
			echo "<script>";
			echo "function selectImg(id,callobj,nome){
					var obj = parent.document.getElementById(\"ulcontent\");
					obj.value = '';
					var htmltable = nome;
						htmltable = \"<li>\";
							htmltable = htmltable + \"<img onclick='removeItensOfList(this);' alt='\" + nome + \"' class='thumbnail-imagelist'  src='index.php?action=doDownload&key=\" + id + \"&imgtype=thumb'/><input type='hidden' name='id_url[]' value='\" + id + \"' />\";
					htmltable = htmltable +  '</li>';
					
					$(obj).append(htmltable);
					}";
			echo "</script>";
				
echo "</head>
		<body class=\"metro\">";
			
echo "<div class=\"grid fluid\">    
		  <div class=\"row\">
		  		<div class=\"span12\">
		  			<div class=\"row\">";
					
echo "<table class=\"table\">
        <thead>
          <tr>
          	<th>Nome</th>
            <th>Tipo</th>
            <th>Visualização</th>
          </tr>
        </thead>
        <tbody>";
        if($id_fo != 0){
        	$res = $this->fman->getBack($id_fo);
				echo "<tr onclick=\"javascript: window.location ='index.php?action=iframe_list_images&id=".$res[0]["id_master"]."';\" style=\"cursor: pointer;\">
        			<td><i class=\"icon-arrow-left repositorio-icon\"></i>&nbsp;&nbsp;&nbsp;Voltar</td>
        			<td></td>
        			<td></td>
        		</tr>";
         }
			foreach($listFolders as $folder){ 
				
        	echo "<tr onclick=\"javascript: window.location ='index.php?action=iframe_list_images&id=".$folder["id_folder"]."';\" style=\"cursor: pointer;\">
        			<td><i class=\"icon-folder-2 repositorio-icon\"></i>&nbsp;&nbsp;&nbsp;".$folder["nome"]."</td>
        			<td>Pasta</td>
        			<td>00/00/0000</td>
        		</tr>";
        	 }
        	foreach($listFiles as $file){
        	echo "<tr onclick=\"selectImg('".$file["key_file"]."',this,'".$file["description"]."');\" style=\"cursor: pointer;\">
        			<td style=\"vertical-align:middle\">".$this->fman->returnIcon($file["ext"])."&nbsp;&nbsp;&nbsp;".$file["description"]."</td>
        			<td style=\"vertical-align:middle\">Image</td>
        			<td><img height=\"75\" width=\"75\" src=\"index.php?action=doDownload&key=".$file["key_file"]."&imgtype=thumb\"/></td>
        		</tr>";
        	} 
        	
       echo  "</tbody>
      </table>";	


 echo  "</div>
	 </div>
	
	</body>
</html>";		   
						   
	}
}
?>
