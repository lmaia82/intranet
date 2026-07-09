<?php
	class UrlControl{
		private $urlMode;
		private $id;
		private $sector;
		private $module;
		private $view;
		private $DB;
		private $action;
		private $parameter;
		private $application;
		
		function __construct(){
			$this->DB = new DiamondDataBase();
			
			if(isset($_REQUEST['module'])){
				$this->module = $_REQUEST['module'];  
			}else{
				$this->module = "notset";
			}
			
			if(isset($_REQUEST['view'])){
				$this->view = $_REQUEST['view'];  
			}else{
				$this->view = "notset";
			}
		
			
			if(isset($_REQUEST['action'])){
				$this->action = $_REQUEST['action'];
			}else{
				$this->action = "notset";
			}
			
			if(isset($_REQUEST['application'])){
				$this->application = $_REQUEST['application'];
			}else{
				$this->application = "notset";
			}
			
			
			
		}
		
		function getModule(){
			return $this->module;
		}
		
		function getAction(){
			return $this->action;
		}
		
		function getView(){
			return $this->view;
		}
		
		function getApplication(){
			return $this->application;
		}
		
		function existParameter($prm){
			if(isset($_REQUEST[$prm])){
				return true;
			}else{
				return false;
			}
		}
		
	}
?>