<?php
		
	class DLdap {
		private $ldap_user;
		private $ldap_password;
		private $ldap_conn;
		private $ldap_server;
		private $ldap_tree;
		private $ldap_bind;
		private $DB;
		private $arrayADM;
		function __construct($user = "",$pass = ""){
			$tmp = new DiamondConfig();
			$arr_Temp = get_object_vars($tmp);
			$this->ldap_server = $arr_Temp['ldap_server'];
			$this->ldap_tree = $arr_Temp['ldap_tree'];
			$this->ldap_user = $user;
			$this->ldap_password = $pass;
			$this->DB = new DiamondDataBase();
			$this->arrayADM = array();
		}
		
		private function connectLDAP(){
			
			
			$this->ldap_conn = ldap_connect($this->ldap_server)or die("Error ao Conectar com o Servidor LDAP.");
		
			if($this->ldap_conn)
			{		
					return true;
					
			}else{
					return false;
			}
			
		}
		
		private function bindLDAP(){
			$this->ldap_bind = @ldap_bind($this->ldap_conn,"MINERAL\\".$this->ldap_user,$this->ldap_password);
			if($this->ldap_bind)
			{
					
					return true;
			}else{
					
					return false;
			}
			
		}
		
		private function checkCredentials()
		{
			if($this->connectLDAP()){
				if($this->bindLDAP()){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		private function getInformations(){
			if($this->connectLDAP()){
				if($this->bindLDAP()){
					$result = ldap_search($this->ldap_conn,$this->ldap_tree, "(sAMAccountName=".$this->ldap_user.")") or die ("Error in search query: ".ldap_error($this->ldap_conn));
					$data = ldap_get_entries($this->ldap_conn, $result);
					return $data;
				}
			}
			
			
		}
		
		private function setPermissions(){
			if($this->checkCredentials()){
				$result = $this->getInformations();
				$itsADM = false;
				$itsSector = false;
				$groups = array();
				setcookie("nome", $result[0]["displayname"][0],time()+3600,'/');
				
				for($ii=0;$ii<$result[0]["memberof"]["count"];$ii++)
				{
					//setcookie("groups[".$ii."]", $result[0]["memberof"][$ii],time() + (10 * 365 * 24 * 60 * 60),'/');
					$groups[$ii] = $result[0]["memberof"][$ii];
					
					$this->setAdministrator($result[0]["memberof"][$ii]);
					
					if(!$itsSector){
						$itsSector = $this->setSector($result[0]["memberof"][$ii]);
					}
					
					
				}
				
				$this->verifyAllAdm();
				
				session_start();
				$_SESSION["groups"] = $groups;
				
				return true;
			}else{
				return false;
			}
		}
		
		private function setSector($ldap_cfg){
			$result = $this->DB->selectQuery("select id_categorie from categories where ldap_config='".$ldap_cfg."' and type_of=0");
			if($this->DB->queryCount() == 1){
				setcookie("setor",$result[0]["id_categorie"],time()+3600,'/');
				return true;
			}
		}
		

		
		private function setAdministrator($ldap_cfg){
			$result = $this->DB->selectQuery("select id_categorie from categories where ldap_administrator='".$ldap_cfg."'");
			if($this->DB->queryCount() == 1){
				$this->arrayADM[] = $result[0]["id_categorie"];
				//setcookie("administrative",$result[0]["id_categorie"],time() + (10 * 365 * 24 * 60 * 60),'/');
				//return true;
				//}else{
				//setcookie("administrative",0,time() + (10 * 365 * 24 * 60 * 60),'/');
				//return false;
			}
		}
		
		private function verifyAllAdm(){
			for($x = 0;$x < count($this->arrayADM);$x++){
				setcookie("administrative[".$x."]",$this->arrayADM[$x],time()+3600,'/');
			}
		}
		
		function login(){
			if($this->setPermissions()){
				return true;
			}else{
				return false;
			}
		}
		
		function verify_permission(){
			
		}
		
		
			
	}
?>
