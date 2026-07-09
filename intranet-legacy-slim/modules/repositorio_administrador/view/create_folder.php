<?
	if($this->getParameterUrl("idfolder") == "notset"){
		$id_fo = 0;
	}else{
		$id_fo = $this->getParameterUrl("idfolder");
	}
	
	$catg = $this->categories->getCategoriesToPermission();
	//$setor = $this->getCookie("setor");
	$setores = $this->categories->getCategoriesOnArray($this->getCookie("administrative"));
?>

<form action="index.php?action=createFolder" method="POST">
		<fieldset>
			<legend>Criar Pasta</legend>
				
				<h4>Nome da Pasta</h4>
				<div class="input-control text span5 block" data-role="input-control">
                    <input type="text" placeholder="Nome" name="folderName">
                </div>
				<h4>Setor</h4>
				<div class="input-control select span3 block">
					<select name="id_categorie">
						<?foreach($setores as $sec){?>
							<option value="<? echo $sec['id']; ?>"><? echo $sec['desc']; ?></option>
						<?}?>
					</select>
				</div>	
				<h4>Permissionamento</h4>
				<div class="input-control select span3 block">
					<select id="permission" name="permission">
						<option value="1">Público</option>
       			    	<option value="2">Restrito</option>
        				<option value="3">Personalizado</option>
					</select>
				</div>
				<h4>Tipo</h4>
				<div class="input-control select span3 block">
					<select id="tipo" name="tipo">
						<option value="0">Pasta</option>
       			    	<option value="1">Atalho</option>
					</select>
				</div>
				
				<div id="treew_view">
					<h4>Pasta Destino</h4>
					<div class="input-control text span3 block">
						<input type="text" id="nome_folder" />
							<button type="button" id="calliframe" class="btn-file"></button>
						</div>
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
		
		<input type="hidden" value="<? echo $id_fo; ?>" name="destinyFolder"/>
		<input type="hidden" value="0" id="parent_id" name="parent_id" />
		<br/>
		<input type="submit" class="primary large" value="Salvar Pasta">
	</form>
	
	