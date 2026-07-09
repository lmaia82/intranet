<?php
ini_set('max_execution_time', 300);
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

define('DIAMOND_SECURE', 1 );

if (!defined('DIAMOND_MINE'))
{
		define('DIAMOND_MINE', __DIR__);
		require_once DIAMOND_MINE . '/lib/Framework.php';
}

/**
if($_SERVER["SERVER_PORT"] != '443' && !isset($_REQUEST['application'])){
	$url = 'https://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	header("Location: ".$url);
}
**/


$App = new App();
$App->Render();
?>
