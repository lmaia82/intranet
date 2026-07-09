<?php
	function checkPermission($ldapconfig){
			$col = false;
			if(!empty($_COOKIE['nome']) && isset($_COOKIE['nome']))
			{
				foreach ($_COOKIE['groups'] as $nome) {
						 if($nome == $ldapconfig)
						 {
							$col = true;
						 }
				}
				if($col)
				{
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
function verify_Exists_cookies()
{
	if(!empty($_COOKIE['nome']) && isset($_COOKIE['nome']))
	{
			return true;
	}else{
			return false;
	}
	
	
}
		
		function logoff(){
			setcookie("nome", "", time()-3600,"/");
			setcookie("groups[]", "", time()-3600,"/");
		}
?>