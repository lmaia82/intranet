<?php 
	set_time_limit(30);
	error_reporting(E_ALL);
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors',1);
	class doLogin{
		private $ldap;
		
		function __construct(){
			$this->ldap = new DLdap($_POST["usuario"],$_POST["pass"]);
		}
		
		function run(){
			if($this->ldap->login()){
				header("Location: ".$_POST["redir"]);
			}else{
				header("Location: index.php?module=autenticar");
			}
		}
	
	}
?>