<? $catg = $this->categories->getCategoriesAll(); ?>
<form action="index.php?action=registerTelefone" method="POST">
		<fieldset>
			<legend>Cadastro Telefone</legend>
				
				<h4>Nome</h4>
				<div class="input-control text span5 block" data-role="input-control">
                    <input type="text" placeholder="Nome" name="nome">
                </div>
                <h4>Telefone</h4>
				<div class="input-control text span3 block" data-role="input-control">
                    <input type="text" placeholder="Telefone" name="telefone">
                </div>
                <h4>Email</h4>
				<div class="input-control text span3 block" data-role="input-control">
                    <input type="text" placeholder="Email" name="email">
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
		<br/>
		<input type="submit" class="primary large" value="Salvar Telefone">
	</form>
	
	