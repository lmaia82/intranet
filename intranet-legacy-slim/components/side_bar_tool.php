<?
	class side_bar_tool{
		private $vars;
		private $parameters;
		//private $forecast;
		private $dateD;
		
		function __construct($vars = ""){
			$this->vars = $vars;
			//$this->forecast = new DiamondForecast();
			$this->dateD = new DiamondDate();
			
		}
		
		public function loadCom(){
			//$information = $this->forecast->Information();
			
			$output = "<a class='tile double vertical menu-maroto live' data-role='live-tile' data-effect='slideRight'>
				<div class='tile-content'>
                    <div class='padding10'>
					   <h1 class='fg-white ntm'>".$this->dateD->getDay()."</h1>
                       <h3 class='fg-white ntm'>".$this->dateD->getLiteralMonth()." de ".$this->dateD->getYear()."</h3>
                       
                    </div>

                </div>";
				
				//Over HTTPS Yahoo Weather Throw Error, Instead used a HTML file and Crontab to Refresh information after 2 hours
				$fors = file_get_contents("/usr/share/intranet/forec.html");	
				$output .= $fors;
				
				/**<div class='tile-content'>
					 <div class='padding10'>
						 <h1 class='fg-white ntm'>".$information['temperature']." C°".$information['img']."</h1>
					     <h4 class='fg-white no-margin'>".$information['msg']."</h4>
					 </div>
                </div>**/

					
			$output .= "</a>";

			$output .= "<a id='maps-btn' class='tile double vertical menu-maroto live' data-role='live-tile' data-effect='slideRight'>
				<div class='tile-content'>
                    <div class='padding10'>
					   <h2 class='fg-white ntm'>Trânsito em Tempo Real <span class='icon-compass-3 place-right' style='font-size:50px;'></span></h2>
                    </div>

                </div>
				<div class='tile-content image'>
					 <img src='media/img/mapping.PNG'/>
					 <div class='brand bg-dark opacity'>
						<span class='text'>
							Clique Aqui para Visualizar
						</span>
					</div>
                </div>

				</a>";	
			$output .= "<a href='index.php?action=doDownload&key=1f9a6abb3ec88e1102842a3d59ff329b' target='_blank'><div class='tile  menu-maroto'>
							<div class='tile-content icon'>
								<span class='icon-libreoffice'></span>
								<div class='brand bg-dark opacity'>
									<span class='text'>
										Guia do Usuário
									</span>
								</div>
							</div>
						</div></a>";
						
			$output .= "<a href='http://diseg.pu.ufrj.br/' target='_blank'><div class='tile  menu-maroto'>
							<div class='tile-content icon'>
								<span class='icon-camera-2'></span>
								<div class='brand bg-dark opacity'>
									<span class='text'>
										CET Fundão
									</span>
								</div>
							</div>
						</div></a>";
						/*<div class='tile half menu-maroto'>
							<div class='tile-content icon'>
								<span class='icon-upload-2'></span>
							</div>
						</div>
						<div class='tile half menu-maroto'>
							<div class='tile-content icon'>
								<span class='icon-phone'></span>
							</div>
						</div>
						<div class='tile half menu-maroto'>
							<div class='tile-content icon'>
								<span class='icon-calendar'></span>
							</div>
						</div>";*/
			
			echo $output;
			
		}
		
		public function setParameters($name,$value){
			
		}
	}					
?>