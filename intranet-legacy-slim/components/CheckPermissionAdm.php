<?php
	class CheckPermissionAdm{
		private $vars;
		private $parameters;
		
		public function loadCom($parameter = ""){
			//DO SOMETHING
			$status_login_ = false;
			$status_login_adm_ = false;
			if (isset($_COOKIE['nome']) && !empty($_COOKIE['nome']) ) {
				$status_login_ = true;
			}else{
				header("Location: index.php?module=error&type=ErrorPermission");
			}
	
			if(verify_Exists_cookies()){
				//Verify Administrative
				if(isset($_COOKIE['administrative'])){
					if($_COOKIE['administrative']== 0){
						header("Location: index.php?module=error&type=ErrorPermission");
					}else{
						$status_login_adm = true;
					}
				}
					}else{
				header("Location: index.php?module=error&type=ErrorPermission");
				}
				
		}
		private function verify_Exists_cookies()
		{
			if(!empty($_COOKIE['nome']) && isset($_COOKIE['nome']))
			{
				return true;
			}else{
				return false;
			}
		}
		
		public function setParameters($name,$value){
			
		}
	}
?>