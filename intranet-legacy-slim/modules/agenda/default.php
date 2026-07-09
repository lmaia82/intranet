<?	
	$listaEventos= $this->events->getList();
?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
				<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			
			<script>
				
				
				$(document).ready(function(){
				
					$(".item-agenda").click(function(){
						var idag = $(this).attr('id-agenda');
						
						
						  $.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-calendar"></span>',
							   width:530,
							   height:500,
       						   title: 'CETEM - Agenda',
        					   content: '<iframe scrolling=\'yes\' width=\'515px\' height=\'500px\' src=\'index.php?action=loadAgenda&id_a='+ idag +'\'></iframe>'
    					});
				});
					

				});
			</script>
				
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("agenda"); ?>
			
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
					
					<h2 id="agenda-btn">Agenda de Eventos Internos</h2>
					<article>
							<table class="table hovered">
								<thead>
								  <tr>
									<th class="text-left">Evento</th>
									<th class="text-left">Local</th>
									<th class="text-left">Inicio</th>
									<th class="text-left">Término</th>
								  </tr>
								</thead>
								<tbody>
						  
								<? foreach($listaEventos as $event) { ?>
								  <tr class="item-agenda" style="cursor: pointer;" id-agenda="<? echo $event["id_event"]; ?>">
									<td>
										<? echo $event["title"]; ?>
									</td>
									<td>
										<? echo $event["local"]; ?>
									</td>
									<td>
										<? 
										
										$dtinicio_arr = explode("-",$event["dt_start"]); 
										$dtinicio = $dtinicio_arr[2]."/".$dtinicio_arr[1]."/".$dtinicio_arr[0];
										
										echo $dtinicio." - ".$event["tm_start"];
										?>
									</td>
									<td>
										<? 
										
										$dtfinal_arr = explode("-",$event["dt_end"]); 
										$dtfinal = $dtfinal_arr[2]."/".$dtfinal_arr[1]."/".$dtfinal_arr[0];
										
										echo $dtfinal." - ".$event["tm_end"];
										?>
									</td>
								  </tr>
								  <? } ?>
								
								</tbody>
							  </table>
			
			
			
						</article>


           			</div>
		  		</div>
		  </div>
		  <div class="row">
			<div class="span8 offset2">
		  			<div class="row">
						<? $this->component->loadComponent("footer")->loadCom(); ?>
					</div>
			</div>
		  </div>
	
	
		<script type='text/javascript' src='media/pageslide/jquery.pageslide.js'></script>	
		<script>
				$(".second").pageslide({ direction: "left", modal: false});
		</script>		
	</body>
	<input type="hidden" id="id_agenda" value="0"/>
</html>