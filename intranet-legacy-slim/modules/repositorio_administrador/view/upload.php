<?
	if($this->getParameterUrl("idfolder") == "notset"){
		$id_fo = 0;
	}else{
		$id_fo = $this->getParameterUrl("idfolder");
	}
	//$setor = $this->getCookie("setor");
	$catg = $this->categories->getCategoriesToPermission();
	//$id_fou = $this->getParameterUrl("id");
	
	//$file_update = $this->fileman->getInfoFile($id_fou);
	$setores = $this->categories->getCategoriesOnArray($this->getCookie("administrative"));
?>

<form id="upform" action="index.php?action=doUpload" method="POST">
		<fieldset>
			<legend>Upload de Arquivos</legend>
				
				<div id="barra_progresso">
					<div id="bar-progresso" class="progress-bar large" data-role="progress-bar"></div>
				</div>
				<h4>Nome do Arquivo</h4>
				<div class="input-control text span5 block" data-role="input-control">
                    <input type="text" placeholder="Nome" name="nome">
                </div>
                <h4>Arquivo</h4>
				<div class="input-control file span5 block">
   					 <input type="file" name="uploadFile" />
    				<button class="btn-file"></button>
				</div>
				<input type="hidden" name="MAX_FILE_SIZE" value="80000" />
				<h4>Setor</h4>
				<div class="input-control select span3 block">
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
            	<h4>Permissionamento</h4>
				<div class="input-control select span3 block">
					<select id="permission"  name="permission">
						<option value="1">Público</option>
       			    	<option value="2">Restrito</option>
        				<option value="3">Personalizado</option>
					</select>
				</div>	
				<div id="permissao_granular" style="display: none;">
					<h4>Selecione os Setores</h4>
						<div id="categorias" style="height: 100px; width:30%; overflow: auto;">
							<? foreach($catg as $ctg){ ?>
							<div class="input-control checkbox">
							 <label>
								<input type="checkbox" name="checks[]" value="<? echo $ctg["id_categorie"]; ?>"/>
								<span class="check"></span>
								<? echo $ctg["description"]; ?>
								 <label>
							</div>	
								<br/>
							<? } ?>
						</div>
				</div>
		</fieldset>
		
		<input id="destiny" type="hidden" value="<? echo $id_fo; ?>" name="destiny"/>
		<br/>
		<input type="submit" class="primary large" value="Fazer Upload">
	</form>
	
	