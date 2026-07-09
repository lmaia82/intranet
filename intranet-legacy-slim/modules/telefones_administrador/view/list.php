<?	
	$listaTelefones= $this->telefones->getList(0);
?>
<button onclick="javascript: window.location ='index.php?module=telefones_administrador&view=create';" class="primary"><i class="icon-plus fg-white"></i>&nbsp;Inserir</button>
&nbsp;
<button class="info" id="alterButton"><i class="icon-pencil fg-white"></i>&nbsp;Alterar</button>
&nbsp;
<button class="danger" id="btndelete"><i class="icon-minus fg-white"></i>&nbsp;Excluir</button>
<br/>
<br/>

<table class="table">
        <thead>
          <tr>
          	<th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Setor</th>
          </tr>
        </thead>
        <tbody>
        <form id="listtel" action="index.php?action=deleteTelefone" method="POST">
        <? foreach($listaTelefones as $telefone) { ?>
          <tr>
          	<td>
          		<div class="input-control checkbox">
   			 		<label>
        				<input type="checkbox" name="checks[]" value="<? echo $telefone["id_telefone"]; ?>"/>
        				<span class="check"></span>
        				<? echo $telefone["nome"]; ?>
    				</label>
				</div>
          	</td>
            <td>
            	<? echo $telefone["email"]; ?>
            </td>
            <td>
            	<? echo $telefone["telefone"]; ?>
            </td>
            <td>
            	<? echo $telefone["setor"]; ?>
            </td>
          </tr>
          <? } ?>
          </form>
        </tbody>
      </table>