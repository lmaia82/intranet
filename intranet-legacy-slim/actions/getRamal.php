<?php
	class getRamal{
		private $DT;
		function __construct(){
			$this->DT = new DiamondTelephone();
		}
		
		function run(){
			echo $this->DT->getRamal($_GET['initial']);
		}
	}
?>