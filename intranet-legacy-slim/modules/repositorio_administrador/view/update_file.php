<?
	if($this->getParameterUrl("idfolder") == "notset"){
		$id_fo = 0;
	}else{
		$id_fo = $this->getParameterUrl("idfolder");
	}
	//$setor = $this->getCookie("setor");
	$catg = $this->categories->getCategoriesToPermission();
	
	$id_fou = $this->getParameterUrl("id");
	$file_update = $this->fileman->getInfoFile($id_fou);
	
	$setores = $this->categories->getCategoriesOnArray($this->getCookie("administrative"));
?>
<form action="index.php?action=updateFile" method="POST">
		<fieldset>
			<legend>Alterar Informações - Arquivo</legend>
		
				<h4>Nome do Arquivo</h4>
				<div class="input-control text span5 block" data-role="input-control">
                    <input type="text" placeholder="Nome" name="nome" value="<? echo $file_update['description']; ?>">
                </div>
				
				<h4>Setor</h4>
				<div class="input-control select span3 block">
					<select name="id_categorie">
						<?foreach($setores as $sec){
							$selc = "";
							if($file_update["id_categorie"] == $sec['id']){
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
						<? 
						switch($file_update['permission']){
							case 1:
								$pub = " selected ";
								$rest = " ";
								$Pers = " ";
								break;
							case 2:
								$pub = " ";
								$rest = " selected ";
								$Pers = " ";
								break;
							case 3:
								$pub = " ";
								$rest = " ";
								$Pers = " selected ";
								break;
						}
					?>
						<option class="item-perm" value="1" <? echo $pub;?>>Público</option>
       			    	<option class="item-perm" value="2" <? echo $rest;?>>Restrito</option>
        				<option class="item-perm" value="3" <? echo $Pers;?>>Personalizado</option>
						
					</select>
				</div>	
				<div id="permissao_granular" style="display: none;">
				<h4>Selecione os Setores</h4>
					<div id="categorias" style="height: 100px; width:30%; overflow: auto;">
						<? 
							if($file_update['permission'] ==3){
								$granular = explode("|",$file_update['permission_granular']);
							}
						?>
						<? foreach($catg as $ctg){ 
							$sty = "";
							if($file_update['permission'] ==3){
								if(in_array($ctg["id_categorie"],$granular)){
									$sty = " checked";
								}
							}
						?>
						
						<div class="input-control checkbox">
						 <label>
							<input type="checkbox" name="checks[]" value="<? echo $ctg["id_categorie"]; ?>" <? echo $sty; ?>/>
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
		<input type="hidden" value="<? echo $id_fou; ?>" name="id_U"/>
		
		<br/>
		<input type="submit" class="primary large" value="Salvar Alterações">
	</form>
	
	