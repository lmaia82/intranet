<?php
	class writeText{
		private $nome;
		function __construct(){
			$this->nome = DIAMOND_MINE;
		}
		
		function run(){
			echo $this->nome;
		}
	}
?>