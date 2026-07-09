<?
	
	//$setor = $this->getCookie("administrative");
	$setores = $this->categories->getCategoriesOnArray($this->getCookie("administrative"));
	//$infctg = $this->categories->getCategoriesById($setor);
?>
<form action="index.php?action=registerArticle" id="frm-create-informe" method="POST">
		<fieldset>
			<legend>Criação Informe</legend>
				
				<label>Titulo</label>
				<div class="input-control text" data-role="input-control">
                    <input type="text" placeholder="Titulo" name="title">
                </div>
			<label>Setor</label>
			<div class="input-control select">
				<select name="id_categorie">
					<?foreach($setores as $sec){?>
						<option value="<? echo $sec['id']; ?>"><? echo $sec['desc']; ?></option>
					<?}?>
				</select>
			</div>	
			
			<label>Permissionamento</label>
			<div class="input-control select">
					<select name="id_permission">
						<option value='1'>Restrito</option>
						<option value='2'>Público</option>
					</select>
			</div>
			
			<label>Tipo</label>
			<div class="input-control select">
					<select name="id_tipo_informe" id="id_tipo_informe">
						<option value='1'>Informe</option>
						<option value='2'>Galeria de Fotos</option>
					</select>
			</div>
			
			<div id="list-images-div">
			<br/>
			<input type="button" id="calliframe-images" class="primary large" value="Abrir Lista de Imagens"/>
			
				<table class="table">
					<thead>
						<tr>
							<th>Nome</th>
							<th>Miniatura</th>
							<th>Remover</th>
						</tr>
					</thead>
					<tbody id="tabela-imagens">
						
					</tbody>
				</table>
			</div>
			 <label>Informe</label>
				<textarea class="ckeditor" id="txteditor" name="content">
				</textarea>
		</fieldset>
		<br/>
		<input type="submit" class="primary large" value="Salvar Informe">
	</form>
	
	