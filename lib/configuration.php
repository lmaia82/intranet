<?php
class DiamondConfig{
	public $server_db = "127.0.0.1";
	public $database = "intranet";
	public $intranetName = "CETEM - Intra";
	public $user = "root";
	public $password = "__CHANGE_ME__";
	public $friendly_url = false;
	public $database_type = 0; //0 = MySql 1 = PostGreSQL
#comentado em 08042015//	public $ldap_server = "mineral.cetem";
	public $ldap_server = "__CHANGE_ME__";
	public $ldap_tree = "OU=CETEM,DC=mineral,DC=cetem";
}
?>
