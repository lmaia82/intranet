<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	
	class iframe_treeview{
		private $DB;
		private $HTML = false;
		private $event;
		private $fman;
		private $component;
		
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->event = new DiamondEvents();
			$this->component = new DiamondComponent();
			$this->fman = new DiamondFileManager();
		}
		
		function run(){		   
						   
echo "<!DOCTYPE html>
	<html lang=\"pt-br\">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  
			<meta charset=\"UTF-8\">";
			
				$this->component->loadComponent("Header_teste")->loadCom();
			echo "<script>";
			echo "function selectNode(id,callobj){
					var obj = parent.document.getElementById(\"valuenode\");
					obj.value = id;
					
					var objname = parent.document.getElementById(\"pasta_name\");
					objname.innerHTML = callobj.innerHTML;
				}";
			echo "</script>";
				
echo "</head>
		<body class=\"metro\">";
			
echo "<div class=\"grid fluid\">    
		  <div class=\"row\">
		  		<div class=\"span8 offset2\">
		  			<div class=\"row\">
					";
					
echo "<ul class=\"treeview\" data-role=\"treeview\">";
echo $this->fman->buildTreeViewShortcut(0);
echo "</ul>";			


 echo  "
		</div>
	 </div>
	
	</body>
</html>";		   
						   
	}
}
?>


