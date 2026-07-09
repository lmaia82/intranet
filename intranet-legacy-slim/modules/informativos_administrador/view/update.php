<? 
$result = $this->articles->getInformationArticle($this->getParameterUrl("id")); 
$resultctg = $this->categories->getCategoriesById($result[0]["id_categorie"]);
$setores = $this->categories->getCategoriesOnArray($this->getCookie("administrative"));
?>

<form id="frm-update-informe" action="index.php?action=updateArticle" method="POST">
		<fieldset>
			<legend>Atualizar Informativo</legend>
				
				<label>Titulo</label>
				<div class="input-control text" data-role="input-control">
                    <input type="text" placeholder="Titulo" name="title" value="<? echo $result[0]["title"]; ?>">
                </div>
			<label>Setor</label>
			<div class="input-control select">
				<select name="id_categorie">
					<?foreach($setores as $sec){
						$selc = "";
						if($result[0]["id_categorie"] == $sec['id']){
							$selc = " selected";
						}
					?>
						<option value="<? echo $sec['id']; ?>" <? echo $selc; ?>><? echo $sec['desc']; ?></option>
					<?}?>
				</select>
			</div>	
			
			<label>Permissionamento</label>
			<div class="input-control select">
				<select name="id_permission">
						<?
							$privado = "";
							$public = "";
							if($result[0]["permission"]==1){
								$privado = "selected";
							}else{
								$public = "selected";
							}
						 ?>
						<option value='1' <? echo $privado; ?>>Restrito</option>
						<option value='2' <? echo $public; ?>>Público</option>
					</select>
			</div>
			<label>Tipo</label>
			<div class="input-control select">
					<select name="id_tipo_informe" id="id_tipo_informe">
						<?
							$inform = "";
							$gfotos = "";
							if($result[0]["tipo"]==1){
								$inform = "selected";
							}else{
								$gfotos = "selected";
							}
						 ?>
						<option value='1' <? echo $inform;?>>Informe</option>
						<option value='2' <? echo $gfotos;?>>Galeria de Fotos</option>
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
						<?
				if($result[0]["tipo"]==2){
						$arrimg = get_object_vars(json_decode($result[0]["foto_gallery"]));
						foreach($arrimg['fotos'] as $img){
							$fototmp = get_object_vars($img);
						?>
							<tr>
								<td>
									<? echo $fototmp['nome']; ?>
								</td>
								<td>
									<img height="75" width="75" src="index.php?action=doDownload&key=<? echo $fototmp['id']; ?>&imgtype=thumb"/>
								</td>
								<td>
									<i onclick="removeItens(this);" style="cursor:pointer;" class="icon-remove"></i>
									<input type="hidden" name="id_url[]" value="<? echo $fototmp['id']; ?>"/></td>
							</tr>			
						<?
						}
				}				
				?>
					</tbody>
				</table>
			</div>
			 <label>Informativo</label>
				<textarea class="ckeditor" id="txteditor" name="content">
				<? echo $result[0]["content"]; ?>
				</textarea>
		</fieldset>
		<input type="hidden" name="idUp" value="<? echo $this->getParameterUrl("id"); ?>"/>
		<br/>
		<input type="submit" class="primary large" value="Salvar Informativo">
	</form>