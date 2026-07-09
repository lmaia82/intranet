<? 
$result = $this->telefones->getInformation($this->getParameterUrl("id")); 
$catg = $this->categories->getCategoriesToPermission();
?>
<form action="index.php?action=updateTelefone" method="POST">
		<fieldset>
			<legend>Cadastro Telefone</legend>
				
				<h4>Nome</h4>
				<div class="input-control text span5 block" data-role="input-control">
                    <input type="text" placeholder="Nome" name="nome" value="<? echo $result[0]["nome"]; ?>">
                </div>
                <h4>Telefone</h4>
				<div class="input-control text span3 block" data-role="input-control">
                    <input type="text" placeholder="Telefone" name="telefone" value="<? echo $result[0]["telefone"]; ?>">
                </div>
                <h4>Email</h4>
				<div class="input-control text span3 block" data-role="input-control">
                    <input type="text" placeholder="Email" name="email" value="<? echo $result[0]["email"]; ?>" >
                </div>
                
			<h4>Setor</h4>
			<div class="input-control select span3 block">
				<select name="id_setor">
						<? foreach($catg as $ctg){ ?>
						<option value="<? echo $ctg["id_categorie"]; ?>"><? echo $ctg["description"]; ?></option>
        				
						<? } ?>
				</select>
			</div>	
		</fieldset>
		<input type="hidden" name="idUp" value="<? echo $this->getParameterUrl("id"); ?>"/>
		<br/>
		<input type="submit" class="primary large" value="Salvar Telefone">
	</form>
	
	