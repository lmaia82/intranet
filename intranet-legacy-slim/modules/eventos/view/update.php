<? 
$result = $this->events->getInformation($this->getParameterUrl("id")); 

            	
$dtinicio_arr = explode("-",$result[0]["dt_start"]); 
$dtinicio = $dtinicio_arr[2]."/".$dtinicio_arr[1]."/".$dtinicio_arr[0];

$dtfinal_arr = explode("-",$result[0]["dt_end"]); 
$dtfinal = $dtfinal_arr[2]."/".$dtfinal_arr[1]."/".$dtfinal_arr[0];
?>
<form action="index.php?action=updateEvent" method="POST">
		<fieldset>
			<legend>Cadastro de Eventos</legend>
				
				<h4>Nome</h4>
				<div class="input-control text span7 block" data-role="input-control">
                    <input type="text" placeholder="Nome do Evento" name="nome" value="<? echo $result[0]["title"]; ?>">
                </div>
                <h4>Local</h4>
				<div class="input-control text span4 block" data-role="input-control">
                    <input type="text" placeholder="Local do Evento" name="local" value="<? echo $result[0]["local"]; ?>">
                </div>
				<h4>Mensagem</h4>
				<div class="input-control textarea">
					<textarea name="intro"><? echo $result[0]["informacoes"]; ?></textarea>
				</div>
                <div class="grid">
                	<div class="row">
                		<div class="span2"/>
                	<h4>Data Inicio</h4>
					<div class="input-control text span2" data-role="datepicker" data-format="dd/mm/yyyy">
   						 <input type="text" name="dinicio" value="<? echo $dtinicio; ?>">
    					<button type="button" class="btn-date"></button>
					</div>
					</div>
					<div class="span2"/>
					<h4>Hora Inicio</h4>
					<div class="input-control text span1">
   						 <input type="text" name="hinicio" value="<? echo $result[0]["tm_start"]; ?>">
					</div>
					</div>
						</div>
			</div>
			<div class="grid">
                	<div class="row">
					<div class="span2">
                	<h4>Data Término</h4>
						<div class="input-control text span2" data-role="datepicker" data-format="dd/mm/yyyy">
   					 		<input type="text" name="dtermino" value="<? echo $dtfinal; ?>">
    						<button type="button" class="btn-date"></button>
						</div>
					</div>	
					<div class="span2"/>
					<h4>Hora Término</h4>
						<div class="input-control text span1">
   						 <input type="text" name="htermino" value="<? echo $result[0]["tm_end"]; ?>">
					</div>
					</div>
				</div>
			</div>
		</fieldset>
		<br/>
		<input type="hidden" name="idUp" value="<? echo $this->getParameterUrl("id")?>" />
		<input type="submit" class="primary large" value="Salvar Evento">
	</form>
	
	