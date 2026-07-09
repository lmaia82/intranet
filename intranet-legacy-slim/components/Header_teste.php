<?php
	class Header_teste{
		private $vars;
		private $parameters;
		
		function __construct($vars = ""){
			$this->vars = $vars;
		}
		
		public function loadCom(){
			//DO SOMETHING
			$output = "<meta name='viewport' content='width=device-width, initial-scale=1.0'>  
			<meta charset='UTF-8'>
		  
			<!-- MetroUICSS -->
			<link rel='stylesheet' href='media/muicss/css/metro-bootstrap.css'>
			<link rel='stylesheet' href='media/muicss/css/metro-bootstrap-responsive.css'>
			<link rel='stylesheet' href='media/bootstrap_muicss/css/bootstrap.css'>
			<link rel='stylesheet' href='media/css/custom.diamond.css'>
			<link rel='stylesheet' href='media/scrollbar/perfect-scrollbar.css'>
			<link rel='stylesheet' href='media/pageslide/jquery.pageslide.css'>
			
			
			<!-- jQuery -->
			<script src='media/jquery-1.10.1.js'></script>
			<script src='media/jquery-ui.js'></script>
			<script src='media/muicss/js/metro/metro-core.js'></script>
			<script src='media/muicss/js/metro/metro-touch-handler.js'></script>
			<script src='media/muicss/js/metro/metro-accordion.js'></script>
			<script src='media/muicss/js/metro/metro-button-set.js'></script>
			<script src='media/muicss/js/metro/metro-date-format.js'></script>
			<script src='media/muicss/js/metro/metro-calendar.js'></script>
			<script src='media/muicss/js/metro/metro-datepicker.js'></script>
			<script src='media/muicss/js/metro/metro-carousel.js'></script>
			<script src='media/muicss/js/metro/metro-countdown.js'></script>
			<script src='media/muicss/js/metro/metro-dropdown.js'></script>
			<script src='media/muicss/js/metro/metro-input-control.js'></script>
			<script src='media/muicss/js/metro/metro-live-tile.js'></script>
			<script src='media/muicss/js/metro/metro-progressbar.js'></script>
			<script src='media/muicss/js/metro/metro-rating.js'></script>
			<script src='media/muicss/js/metro/metro-slider.js'></script>
			<script src='media/muicss/js/metro/metro-tab-control.js'></script>
			<script src='media/muicss/js/metro/metro-table.js'></script>
			<script src='media/muicss/js/metro/metro-times.js'></script>
			<script src='media/muicss/js/metro/metro-dialog.js'></script>
			<script src='media/muicss/js/metro/metro-notify.js'></script>
			<script src='media/muicss/js/metro/metro-listview.js'></script>
			<script src='media/muicss/js/metro/metro-treeview.js'></script>
			<script src='media/muicss/js/metro/metro-fluentmenu.js'></script>
			<script src='media/muicss/js/metro/metro-hint.js'></script>
			<script src='media/scrollbar/jquery.mousewheel.js' ></script>
			<script src='media/scrollbar/perfect-scrollbar.js' ></script>
			<script src='media/scrollbar/jquery-scrollspy.js'></script>
			<script src='media/jquery.form.js'></script>
			<script type='text/javascript' src='media/ckeditor/ckeditor.js'></script>
			<script src='media/scrollbar/perfect-scrollbar.js' ></script>
			
			<link rel='stylesheet' href='media/galleriffic/css/galleriffic-3.css' type='text/css' />
			<script type='text/javascript' src='media/galleriffic/js/jquery.galleriffic.js'></script>
			<script type='text/javascript' src='media/galleriffic/js/jquery.opacityrollover.js'></script>
			<script type='text/javascript' src='media/js_order.js'></script>
			<script type='text/javascript' src='media/sidr-package-1.2.1/jquery.sidr.min.js'></script>
			
			 
			<style>
				
				.table {
					text-align: left;
				}
				
			
				/** Quick Tool **/
				.quicktool {
					padding: 10px 0px 10px 0px;
				}

				.quicktool > img {
 					 margin: 5px;
 					 display: inline-block;
				}
				.quicktool .image-container {
  					margin: 10px;
 			     	display: inline-block;
  					vertical-align: middle;
				}
			
			</style>
			<style>
			.login {
					
					overflow: hidden;
					margin-bottom: 15px;
					margin-top:90px;
					width: 50%;
					margin-left:150px;
				}
				.login .logoti{
					float: left;
					margin-right: 1px;
					padding-top: 65px;
					padding-right: 10px;
				}
				.login form{
					overflow: hidden;
					padding: 5px 0px 15px 50px;
				}

				.metro .tab-control .tabs>li>a {
					border: 1px #eee solid;
					display: block;
					float: left;
					padding: 5px 10px;
					z-index: 10;
					top: 0;
					left: 0;
					color: inherit;
					background-color: #FFF;
				}
				.metro .tab-control .tabs>li:hover a {
					background-color: #8D8D8D;
					color: #fff;
				}
				
					#modal { 
						display: none; 
						background-color:#333;
						font: 10px/14px 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;
					}
					
					#modal  h2 {
						padding: 1em;
						border-bottom: 1px solid #0C0C0C;
						color: whitesmoke;
					}
					
					#modal  a:hover {
						background: #aaa;
					} 
					
					#pageslide{
						padding: 0px;
						overflow:scroll;
						
					}
					.cbp-spmenu-vertical {
						height: 100%;
						top: 0;
						z-index: 1000;
					}
					
					.cbp-spmenu {
						background: #47a3da;
						overflow:auto;
					}
					
					.cbp-spmenu-vertical a {
						border-bottom: 1px solid #0C0C0C;
						padding: 1em;
					}
					.cbp-spmenu a {
						display: block;
						color: #fff;
						font-size: 1.1em;
						font-weight: 300;
					}
					
					#modal  a {
						color: #f0f0f0;
						text-decoration: none;
					}
			</style>	
			 <div id='modal' class='cbp-spmenu-vertical cbp-spmenu' style='display:none;'>
            <h2>Links Úteis</h2>
			<a target='_blank' href='http://www.agu.gov.br/sistemas/site/templatesitehome.aspx'>AGU-Advocacia Geral da União</a>
			<a target='_blank' href='https://contas.tcu.gov.br/cadicon/procura'>CADICON</a>
			<a target='_blank' href='http://www010.dataprev.gov.br/cws/contexto/cnd/cnd.html'>Consulta à Certidão CND e CPD-EN</a>
			<a target='_blank' href='http://www.portaltransparencia.gov.br/ceis/Consulta.seam'>Consulta Portal da Transparência</a>
			<a target='_blank' href='https://www.sifge.caixa.gov.br/Cidadao/Crf/FgeCfSCriteriosPesquisa.asp'>Consulta Regularidade do Empregador</a>
			<a target='_blank' href='http://www.cgu.gov.br/'>CGU-Controladoria-Geral da União</a>
			<a target='_blank' href='http://portal.in.gov.br/'>Imprensa Nacional</a>
			<a target='_blank' href='http://www.planejamento.gov.br/'>Ministério do Planejamento</a>
			<a target='_blank' href='http://www.mcti.gov.br/'>MCTI</a>
			<a target='_blank' href='http://www.servidor.gov.br/index.asp'>Portal do Servidor</a>
			<a target='_blank' href='http://www2.planalto.gov.br/presidencia/legislacao'>Planalto.gov.br</a>
			<a target='_blank' href='http://www.receita.fazenda.gov.br/Grupo2/Certidoes.htm'>Receita Federal - Certidões</a>
			<a target='_blank' href='https://www2.scdp.gov.br/novoscdp/home.xhtml'>SCDP</a>
			<a target='_blank' href='http://www.siapenet.gov.br/'>Siape Net</a>
			<a target='_blank' href='https://www1.siapenet.gov.br/orgao/Login.do?method=inicio'>Siape Net - Orgão</a>
			<a target='_blank' href='http://www.tst.jus.br/certidao'>Tribunal Superior do Trabalho</a>
			<a target='_blank' href='http://portal2.tcu.gov.br/portal/page/portal/TCU'>TCU-Tribunal de Contas da União</a>
        </div>
			";
			
			echo $output;
			
		}
		
		public function setParameters($name,$value){
			
		}
	}
?>