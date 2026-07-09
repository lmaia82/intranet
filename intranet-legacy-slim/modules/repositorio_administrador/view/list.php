<?	
	
	if($this->getParameterUrl("id") == "notset"){
		$id_fo = 0;
	}else{
		$id_fo = $this->getParameterUrl("id");
	}
	
	$listFolders = $this->fileman->getFoldersAdministrator($id_fo);
	$listFiles = $this->fileman->getFilesAdministrator($id_fo);
	
	$setor = $this->getCookie("setor");
	$infctg = $this->categories->getCategoriesById($setor);
?>
<button onclick="javascript: window.location ='index.php?module=repositorio_administrador&view=upload&idfolder=<? echo $id_fo; ?>';" class="primary"><i class="icon-upload-2 fg-white"></i>&nbsp;Upload Arquivo</button>
&nbsp;
<button onclick="javascript: window.location ='index.php?module=repositorio_administrador&view=create_folder&idfolder=<? echo $id_fo; ?>';" class="info" ><i class="icon-plus fg-white"></i>&nbsp;Criar Pasta</button>
&nbsp;
<button class="warning" id="btnalter"><i class="icon-cycle fg-white"></i>&nbsp;Alterar</button>
&nbsp;
<button class="danger" id="btndelete"><i class="icon-minus fg-white"></i>&nbsp;Excluir</button>
<br/>
<br/>

<table class="table">
        <thead>
          <tr>
          	<th>Nome</th>
            <th>Tipo</th>
            <th>Data Criação</th>
			<th>Selecionar</th>
          </tr>
        </thead>
        <tbody>
		<form method="POST" id="lista_itens" action="index.php?action=deleteRepositorio">
        <? if($id_fo != 0){
        	$res = $this->fileman->getBack($id_fo);
        ?>
        	<tr onclick="javascript: window.location ='index.php?module=repositorio_administrador&view=list&id=<? echo $res[0]["id_master"];?>';" style="cursor: pointer;">
        			<td><i class="icon-arrow-left repositorio-icon"></i>&nbsp;&nbsp;&nbsp;Voltar</td>
        			<td></td>
        			<td></td>
        		</tr>
        <? } ?>
        	<? foreach($listFolders as $folder){ 
					$arr_date = explode(" ",$folder['date_creation']);
					$datea	  = explode("-",$arr_date[0]);
					$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
				
			?>
				
        		<tr class="itemfile">
					<td onclick="javascript: window.location ='index.php?module=repositorio_administrador&view=list&id=<? echo $folder["id_folder"]; ?>';" style="cursor: pointer;">
						<i class="icon-folder-2 repositorio-icon"></i>&nbsp;&nbsp;&nbsp;<? echo $folder["nome"]; ?>						
					</td>
        			<td>Pasta</td>
        			<td><? echo $dtinicio; ?></td>
					<td><div class="input-control checkbox">
							<label>
								<input type="checkbox" name="checks_folder[]" tp_orientation="folder" value="<? echo $folder["id_folder"]; ?>"/>
								<input type="hidden" name="tipo_operacao_<? echo $folder["id_folder"] ?>" value="0"/>
								<span class="check"></span>
							</label>
						</div></td>
        		</tr>
        	<? } ?>
        	<? foreach($listFiles as $file){
					$arr_date = explode(" ",$file['date_creation']);
					$datea	  = explode("-",$arr_date[0]);
					$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
			?>
        		<tr class="itemfile">
        			<td  onclick="javascript: window.location ='index.php?action=doDownload&key=<? echo $file["key_file"];?>';" style="cursor: pointer;"><? echo $this->fileman->returnIcon($file["ext"]); ?>&nbsp;&nbsp;&nbsp;<? echo $file["description"]; ?></td>
        			<td>Arquivo</td>
        			<td><? echo $dtinicio; ?></td>
					<td><div class="input-control checkbox">
							<label>
								<input type="checkbox" name="checks_files[]" tp_orientation="file" value="<? echo $file["id_file"]; ?>"/>
								<input type="hidden" name="tipo_operacao_<? echo $file["id_file"]; ?>" value="1"/>
								
								<span class="check"></span>
							</label>
						</div></td>
        		</tr>
        	<? } ?>
			  <input type="hidden" id="idprnt" name="currento" value="<? echo $id_fo; ?>"/>
        	</form>
        </tbody>
      </table>
    
    