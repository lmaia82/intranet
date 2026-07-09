<form action="index.php?action=registerEvent" method="POST">
		<fieldset>
			<legend>Cadastro de Eventos</legend>
				
				<h4>Nome</h4>
				<div class="input-control text span7 block" data-role="input-control">
                    <input type="text" placeholder="Nome do Evento" name="nome">
                </div>
                <h4>Local</h4>
				<div class="input-control text span4 block" data-role="input-control">
                    <input type="text" placeholder="Local do Evento" name="local">
                </div>
				
				<h4>Mensagem</h4>
				<div class="input-control textarea">
					<textarea name="intro"></textarea>
				</div>
				
                <div class="grid">
                	<div class="row">
                		<div class="span2"/>
                	<h4>Data Inicio</h4>
					<div class="input-control text span2" data-role="datepicker" data-format="dd/mm/yyyy">
   						 <input type="text" name="dinicio">
    					<button type="button" class="btn-date"></button>
					</div>
					</div>
					<div class="span2"/>
					<h4>Hora Inicio</h4>
					<div class="input-control text span1">
   						 <input type="text" name="hinicio">
					</div>
					</div>
						</div>
			</div>
			<div class="grid">
                	<div class="row">
					<div class="span2">
                	<h4>Data Término</h4>
						<div class="input-control text span2" data-role="datepicker" data-format="dd/mm/yyyy">
   					 		<input type="text" name="dtermino">
    						<button type="button" class="btn-date"></button>
						</div>
					</div>	
					<div class="span2"/>
					<h4>Hora Término</h4>
						<div class="input-control text span1">
   						 <input type="text" name="htermino">
					</div>
					</div>
				</div>
			</div>
		</fieldset>
		<br/>
		<input type="submit" class="primary large" value="Salvar Evento">
	</form>
	
	