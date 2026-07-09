<?	
	$listaEventos= $this->events->getList();
?>
<button onclick="javascript: window.location ='index.php?module=eventos&view=create';" class="primary"><i class="icon-plus fg-white"></i>&nbsp;Inserir</button>
&nbsp;
<button class="info" id="alterButton"><i class="icon-pencil fg-white"></i>&nbsp;Alterar</button>
&nbsp;
<button class="danger" id="btndelete"><i class="icon-minus fg-white"></i>&nbsp;Excluir</button>
<br/>
<br/>

<table class="table">
        <thead>
          <tr>
          	<th>Titulo</th>
            <th>Local</th>
            <th>Inicio</th>
            <th>Término</th>
          </tr>
        </thead>
        <tbody>
        <form id="listeve" action="index.php?action=deleteEvent" method="POST">
        <? foreach($listaEventos as $event) { ?>
          <tr>
          	<td>
          		<div class="input-control checkbox">
   			 		<label>
        				<input type="checkbox" name="checks[]" value="<? echo $event["id_event"]; ?>"/>
        				<span class="check"></span>
        				<? echo $event["title"]; ?>
    				</label>
				</div>
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
          </form>
        </tbody>
      </table>