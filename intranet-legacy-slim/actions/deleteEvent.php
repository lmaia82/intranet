<?php
	if (!defined('DIAMOND_SECURE'))
	{
		echo "Access Denied";
	}
	class deleteEvent{
		private $DB;
		private $Event;
		function __construct(){
			$this->DB = new DiamondDataBase();
			$this->Event = new DiamondEvents();
		}
		
		function run(){
			
			if($this->Event->deleteEvents($_POST['checks'])){
				header("Location: index.php?module=eventos&view=list");
			}
			
		}
	}
?>