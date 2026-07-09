<?	
	$setor = $this->getCookie("setor");
	//$listaInformativos = $this->articles->getList($setor,0);
	$listaInformativos = $this->articles->getListAdm();
?>
<script>
	$(document).ready(function(){
					
				});
</script>
<button onclick="javascript: window.location ='index.php?module=informativos_administrador&view=create';" class="primary"><i class="icon-plus fg-white"></i>&nbsp;Inserir</button>
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
            <th>Permissão</th>
			<th>Setor</th>
          </tr>
        </thead>
        <tbody>
        <form id="listinf" action="index.php?action=deleteArticle" method="POST">
		
        <? foreach($listaInformativos as $informativo) { ?>
			
          <tr>
          	<td>
          		<div class="input-control checkbox">
   			 		<label>
        				<input type="checkbox" name="checks[]" value="<? echo $informativo["id_article"]; ?>"/>
        				<span class="check"></span>
        				<? echo $informativo["title"]; ?>
    				</label>
				</div>
          	</td>
            <td>
            	<? if($informativo["permission"]==1){
            		echo "Privado";
            	}else{
            		echo "Publico";
            	}?>
            </td>
			<td>
				<? 
				$nm = $this->categories->getCategoriesById($informativo["id_categorie"]); 
				echo $nm[0]['description'];
				?>
			</td>
          </tr>
          <? } ?>
          </form>
        </tbody>
      </table>