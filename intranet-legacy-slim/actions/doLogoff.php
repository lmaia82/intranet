<?php 
class doLogoff{
		function run(){
			if (isset($_SERVER['HTTP_COOKIE'])) {
				$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
				foreach($cookies as $cookie) {
						$parts = explode('=', $cookie);
						$name = trim($parts[0]);
						setcookie($name, '', time()-1000);
						setcookie($name, '', time()-1000, '/');
			}
		}
			session_start();
			session_destroy();
			header("Location: index.php?module=autenticar");	 
		}
	
	}
?>