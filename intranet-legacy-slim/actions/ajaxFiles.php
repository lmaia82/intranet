<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	
	class ajaxFiles{
		private $FileMan;
		function __construct(){
			$this->FileMan = new DiamondFileManager();
		}
		
		function run(){
				$idd = $_GET['id_f'];
				$listFolders = $this->FileMan->getFolders($idd);
				$listFiles = $this->FileMan->getFiles($idd);
				
				$bread = $this->FileMan->makeBreadCrumb($idd);
				
				$outputHTML = $bread."<table class=\"table\">
       							 <thead>
         						 <tr>
          							<th>Nome</th>
           						    <th>Tipo</th>
           							<th>Data de Criação</th>
									<th>Compartilhe</th>
         						 </tr>
        						</thead>
								<tbody>";
							
								foreach($listFolders as $folder){
									if($folder["type"]==0){
											$iconfolder = " icon-folder-2 ";
										}else{
											$iconfolder = " icon-new-tab ";
									}
									$arr_date = explode(" ",$folder['date_creation']);
									$datea	  = explode("-",$arr_date[0]);
									$dtiniciof = $datea[2]."/".$datea[1]."/".$datea[0];
									
									$outputHTML .= "<tr onclick=\"loadFiles(".$folder["id_folder"].");\" style=\"cursor: pointer;\">
											<td><i class=\"".$iconfolder." repositorio-icon\"></i>&nbsp;&nbsp;&nbsp;".$folder["nome"]."</td>
											<td>Pasta</td>
											<td>".$dtiniciof."</td>
											<td></td>
										</tr>";
									} 
									
									$urlk = explode("/",$_SERVER["REQUEST_URI"]);
									$narr = array_pop($urlk);
									$urlb = implode("/",$urlk);
									
									foreach($listFiles as $file){ 
										$arr_date = explode(" ",$file['date_creation']);
										$datea	  = explode("-",$arr_date[0]);
										$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
															$outputHTML .= "<tr>
											<td onclick=\"javascript: window.location ='index.php?action=doDownload&key=".$file["key_file"]."';\" style=\"cursor: pointer;\">".$this->FileMan->returnIcon($file["ext"])."&nbsp;&nbsp;&nbsp;".$file["description"]."</td>
											<td>Arquivo</td>
											<td>".$dtinicio."</td>
											<td ><i onclick=\"javascript: showURL('".'https://'.$_SERVER["SERVER_NAME"].$urlb."/index.php?action=doDownload&key=".$file["key_file"]."');\" style=\"cursor: pointer;\" class='icon-mail repositorio-icon'></i></td>
										</tr>";
									}
									
					$outputHTML .= "</tbody>
							  </table>";
							  
					echo $outputHTML;	  
		
		}
		
	}
?>