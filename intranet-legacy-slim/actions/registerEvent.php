<?php
	class registerEvent{
		private $DB;
		private $Event;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Event = new DiamondEvents();
		}
		
		function run(){
			$dt_inicio_arr = explode("/",$_POST['dinicio']);
			$dt_inicio_formt =  $dt_inicio_arr[2]."-".$dt_inicio_arr[1]."-".$dt_inicio_arr[0];
			
			$dt_final_arr = explode("/",$_POST['dtermino']);
			$dt_final_formt =  $dt_final_arr[2]."-".$dt_final_arr[1]."-".$dt_final_arr[0];
			
			
			
			if($this->Event->insertEvents(addslashes($_POST["local"]),addslashes($_POST["nome"]),addslashes($_POST["intro"]),$dt_inicio_formt,$dt_final_formt,addslashes($_POST["hinicio"]),addslashes($_POST["htermino"]))){
				header("Location: index.php?module=eventos&view=list");
			}
			
		}
	}
?>