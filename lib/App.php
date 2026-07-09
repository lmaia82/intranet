<?php
	ini_set('default_charset','UTF-8');
	error_reporting(0);
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors',0);
	class App
	{
		private $content;
		private $title;
		private $title_page;
		private $id_article;
		private $UrlCont;
		private $DB;
		private $module;
		private $view;
		private $component;
		private $articles;
		private $parameters_url;
		public $categories;
		private $telefones;
		private $events;
		private $fileman;
		private $application;
		
		
		function __construct(){
			$this->UrlCont = new UrlControl();
			$this->DB = new DiamondDataBase();
			$this->component = new DiamondComponent();
			$this->articles = new DiamondArticle();
			$this->categories = new DiamondCategorie();
			$this->telefones = new DiamondTelephone();
			$this->module = $this->UrlCont->getModule();
			$this->fileman = new DiamondFileManager();
			$this->view = $this->UrlCont->getView();
			$this->parameters_url = $_REQUEST;
			$this->events = new DiamondEvents();
			$this->application = new DiamondApplication();
		}
	
		
		function Render()
		{

			$setor = $this->getCookie("setor");
			if($setor=="notset" && $this->parameters_url['module'] != 'autenticar'){
				header("Location: index.php?module=autenticar&redir=".urlencode($this->getUrl()));
			}
			
		

			
			if($this->UrlCont->getModule() <> "notset"){
				if($this->VerifyParameters($this->UrlCont->getModule())){
						$this->LoadModule($this->UrlCont->getModule());
					}else{
						header("Location: index.php?module=error&type=ErrorPage");
					}
			}elseif($this->UrlCont->getModule() == "notset" && $this->UrlCont->getAction() == "notset" && $this->UrlCont->getApplication() == "notset"){
				if (isset($_COOKIE['nome']) && !empty($_COOKIE['nome']) ) {
						header("Location: index.php?module=minha_area");
					}else{
						header("Location: index.php?module=autenticar");
					}	
			}elseif($this->UrlCont->getAction() <> "notset"){
				$this->ExecuteAction($this->UrlCont->getAction());
			}else{
				$this->application->load();
			}
			
		}
		
		public function loadView(){
			if($this->view == "notset"){
				return "notset";
			}else{
				ob_start();
				include(DIAMOND_MINE . "/modules/".$this->module."/view/".$this->view.".php");
				$renderView = ob_get_contents();
				ob_end_clean();
				
				echo $renderView;
				return "set";
			}
			
		}
		public function getCookie($name){
			if(isset($_COOKIE[$name])){
				return $_COOKIE[$name];
			}else{
				return "notset";
			}
		}
		public function getParameterUrl($nameprm){
		if(isset($this->parameters_url[$nameprm])){
				return $this->parameters_url[$nameprm];
			}else{
				return "notset";
			}
		}
		
		public function getUrl() {
			  $url = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
			  $url .= $_SERVER["REQUEST_URI"];
			  return $url;
		}
		
		
		private function LoadModule($name){
				ob_start();
				
				include(DIAMOND_MINE . "/modules/".$name."/default.php");
				$renderPage = ob_get_contents();
				ob_end_clean();
				
				echo $renderPage;
		}
		
		private function ExecuteAction($action){
			require_once(DIAMOND_MINE . "/actions/".$action.".php");
			$actionTmp = new $action();
			$actionTmp->run();
		}
		
		private function VerifyParameters($module){
			if(file_exists(DIAMOND_MINE . "/modules/".$module."/parameters.xml")){
					$xmresul = simplexml_load_file(DIAMOND_MINE . "/modules/".$module."/parameters.xml");
					$json = json_encode($xmresul);
					$arry = json_decode($json,TRUE);
					$process = true;
					$master = $arry['mode'];
					
					if(isset($master['@attributes'])){
						$allright = array("mode"=> array());
						$allright["mode"][] = $master;
						//print_r($allright);	
					}else{
						$allright = $arry;
					}
					
					foreach ($allright['mode'] as $parameter){
						$arraytmp = $parameter['@attributes'];
						if($arraytmp['type'] == "module" && $arraytmp['name'] == $this->UrlCont->getModule()){
								if(isset($parameter['parameter'])){
										
										if(isset($parameter['parameter']['@attributes'])){
											$arrt = $parameter['parameter'];
											$parameter['parameter'] = array();
											$parameter['parameter'][] = $arrt; 	
										}
																			
										foreach($parameter['parameter'] as $pr){										
											$arriegua = $pr['@attributes'];
											if( (string)$arriegua['status'] == "mandatory"){
												if((!$this->UrlCont->existParameter((string)$arriegua['name']))){
														$process = false;	
												}
											}
										}
									}
						}
						
						if($this->view <> "notset"){
							$arraytmp = $parameter['@attributes'];
							if($arraytmp['type'] == "view" && $arraytmp['name'] == $this->view){
									if(isset($parameter['parameter'])){
											
											if(isset($parameter['parameter']['@attributes'])){
												$arrt = $parameter['parameter'];
												$parameter['parameter'] = array();
												$parameter['parameter'][] = $arrt; 	
											}
																				
											foreach($parameter['parameter'] as $pr){										
												$arriegua = $pr['@attributes'];
												if( (string)$arriegua['status'] == "mandatory"){
													if((!$this->UrlCont->existParameter((string)$arriegua['name']))){
															$process = false;	
													}
												}
											}
								}
							}
							
						}
							
						
					}
				return $process;
		
			}else{
				return true;
			
			}
			
		
		}

		
	}
?>
