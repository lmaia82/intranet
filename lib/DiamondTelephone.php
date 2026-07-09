<?php
	class DiamondTelephone{
		private $DB;
		function __construct(){
			$this->DB = new DiamondDataBase();
		}
		
		public function buildCards(){
			return $this->DB->selectQuery("select DISTINCT(SUBSTRING(nome,1,1)) as ininame from telefones order by nome;");
		}
			
		public function getRamalByLetter($ltr){
			return $this->DB->selectQuery("select t.id_telefone as id_telefone,t.nome as nome,t.telefone as telefone,t.email as email,c.description as setor,t.cargo as cargo from telefones t,categories c  where c.id_categorie = t.id_setor and t.nome like '".$ltr."%' order by t.nome;");
		}
		
		public function getRamal($initial){
			$rslt_user_telefone = $this->DB->selectQuery("select t.id_telefone as id_telefone,t.nome as nome,t.telefone as telefone,s.descricao as setor,t.cargo as cargo from telefones t,setor s  where s.id_setor = t.setor and t.nome like '".$initial."%' order by t.nome;");
			$rms = "";		
			foreach($rslt_user_telefone as $ctg_user_telefone) 
				{
		
				$rms .= "<address>
					<h4>".$ctg_user_telefone['nome']."</h4>
					<strong>Setor:</strong>".$ctg_user_telefone['setor']."<br />
					<strong>Ramal:</strong>".$ctg_user_telefone['telefone']."<br />
				</address>";
				
			
				}
				$arr = array ('ine' => $rms);
				return json_encode($arr);
		}
		
		public function insertTelefone($nome,$telefone,$setor,$email){
			$result = $this->DB->insertQuery("insert into telefones(nome,telefone,id_setor,email)values('".$nome."','".$telefone."',".$setor.",'".$email."')");
			if($result){
				return true;
			}else{
				return false;
			}
		}
		
		public function updateTelefone($idUp,$nome,$telefone,$setor,$email){
			$result = $this->DB->insertQuery("update telefones set nome='".$nome."',telefone='".$telefone."',id_setor=".$setor.",email='".$email."' where id_telefone=".$idUp);
			if($result){
				return true;
			}else{
				return false;
			}
		}
		
		public function deleteTelefone($id){
			foreach($id as $idchk){
				$result = $this->DB->Command("delete from telefones where id_telefone=".$idchk);
			}
				return true;
				
		}
		
		public function getList($id){
				if($id == 0){
					$rslt_user_telefone = $this->DB->selectQuery("select t.id_telefone as id_telefone,t.nome as nome,t.telefone as telefone,t.email as email,c.description as setor from telefones t,categories c  where c.id_categorie = t.id_setor order by t.nome;");
				}else{
					$rslt_user_telefone = $this->DB->selectQuery("select t.id_telefone as id_telefone,t.nome as nome,t.telefone as telefone,t.email as email,c.description as setor from telefones t,categories c  where c.id_categorie = t.id_setor and t.id_setor=".$id." order by t.nome;");
				}
				return $rslt_user_telefone;
		}
		
		public function getInformation($id){
			$result = $this->DB->selectQuery("select * from telefones where id_telefone=".$id);
			return $result;
		}
	}
?>