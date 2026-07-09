<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	
	class loadAgenda{
		private $DB;
		private $HTML = false;
		private $event;
		private $component;
		
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->event = new DiamondEvents();
			$this->component = new DiamondComponent();
		}
		
		function run(){
				$idagenda = $_GET['id_a'];
				$resul = $this->event->getInformation($idagenda);
				
				$title = $resul[0]['title'];
				$local = $resul[0]['local'];
				$informacoes = $resul[0]['informacoes'];
				
				 
				$dtinicio_arr = explode("-",$resul[0]["dt_start"]); 
				$dtinicio = $dtinicio_arr[2]."/".$dtinicio_arr[1]."/".$dtinicio_arr[0];
										
				$dtfinal_arr = explode("-",$resul[0]["dt_end"]); 
				$dtfinal = $dtfinal_arr[2]."/".$dtfinal_arr[1]."/".$dtfinal_arr[0];
										
						
											   
						   
						   
echo "<!DOCTYPE html>
	<html lang=\"pt-br\">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">  
			<meta charset=\"UTF-8\">";
			
				$this->component->loadComponent("Header_teste")->loadCom();
			
				
echo "</head>
		<body class=\"metro\">";
			
echo "<div class=\"grid fluid\">    
		  <div class=\"row\">
		  		<div class=\"span8 offset2\">
		  			<div class=\"row\">
					";
					
echo "<h2>".$title."</h2>
	  <h3>".$local."</h3>
	  <h5>Início:".$dtinicio." - ".$resul[0]["tm_start"]."</h5>
	  <h5>Término".$dtfinal." - ".$resul[0]["tm_end"]."</h5>
	  <br/>
	  <br/>
	  <br/>
      <p>".$informacoes."</p>";					


 echo  "</div>
		</div>
	 </div>
	
	</body>
</html>";		   
						   
	}
}
?>


