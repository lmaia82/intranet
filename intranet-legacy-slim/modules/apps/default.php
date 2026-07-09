<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			
				<style>
				
			</style>
				
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom("apps"); ?>
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
						<h2>Sistemas</h2>
						  <article>
							<div class="tab-control" data-role="tab-control">
							<ul class="tabs">
								<li class="active"><a href="#sistema_sigtec">SIGTEC</a></li>
								<li><a href="#sistema_sca">SCA</a></li>
								<li><a href="#sistema_srh">SRH</a></li>
								<li><a href="#sistema_sisfat">SISFAT</a></li>
								<li><a href="#sistema_gdact">GDACT Fase 3</a></li>
							</ul>
							 <div class="frames">
								 <div class="frame" id="sistema_sigtec">
								  <!-- SIGTEC -->
									<h4>Sistema de Informações Gerenciais e Tecnológicas - SIGTEC</h4>
									<p>O SIGTEC se destina a apoiar a gestão nas entidades dedicadas à ciência e tecnologia. O auxílio ocorre por meio do registro estruturado das informações gerenciais e tecnológicas, da interação realizada nos ambientes de trabalho e do acompanhamento da concretização dos resultados, que permite a gestão baseada em evidências.</p></br>
									<a href="https://sigtecweb.cetem.gov.br/sigtec" target="_blank"><i class="icon-enter on-left"></i> Acessar</a>
								</div>
								
								 <div class="frame" id="sistema_sca">
									 <!-- SCA -->
									<h4>Sistema para Controle de Análises Minerais - SCA</h4>
									<p>Neste Sistema será registrada toda atividade de contratação, processamento, resultados e apropriação de custos e indicadores gerados na COAM - Coordenação de Análises Minerais. Além da reconhecida atividade de Análise Química, serão também gerenciadas as atividades de Análise de Mercúrio e de Caracterização Tecnológica. Com a aplicação deste Sistema a COAM pretende oferecer um serviço integrado de análise mineralógica através da utilização de diversos procedimentos laboratoriais a serem definidos pelo seu corpo de Consultores. Na medida do possível, as informações geradas por este Sistema serão integradas a outros do tipo SAD, SAP, SISFAT etc. O acesso ao Sistema é de carater restrito.</p>
									</br>
									<a href="http://intranet.cetem.gov.br/index.php?application=sca"><i class="icon-enter on-left"></i> Acessar</a>
								</div>
								
								 <div class="frame" id="sistema_srh">
									  <!-- SRH -->
										<h4>Sistema para Gestão de Recursos Humanos - SRH</h4>
										<p>O SRH não se propõe a substituir os Sistemas do SERPRO para geração de Folha de Pagamento, etc. Seu objetivo é apoiar as atividades de controle do Serviço de Recursos Humanos da Administração do CETEM mantendo um cadastro atualizado de todos os colaboradores Ativos e Inativos, bem como administrar a recepção e o desligamento dos mesmos com relação aos diversos serviços prestados no centro, a saber: Criação de Contas de REDE e de Correio Eletrônico, Registro de empréstimos na Biblioteca, Concessão de Crachás de Indentificação e de Acesso ao Estacionamento, Controle de Planos de Saúde etc. O acesso ao Sistema é de caráter restrito.</p>
										</br>
										<a href="http://intranet.cetem.gov.br/index.php?application=srh"><i class="icon-enter on-left"></i> Acessar</a>
								</div>
								
								 <div class="frame" id="sistema_sisfat">
									<!-- SISFAT -->
									<h4>Sistema de Faturamento - SISFAT</h4>
									<p>Através deste Sistema os Usuários autorizados poderão fazer a Solicitação de Faturamento e acompanhar a sua evolução até a confirmação da Receita. O SEOF deverá utilizar o Sistema para executar todos os procedimentos de Emissão e Controle sobre as Faturas do CETEM. As Receitas de Projetos serão automaticamente transferidas para o Banco de Dados do SAP.</p>
									</br>
									<a href="http://srv-rj-jboss-01.cetem.gov.br:8080/sisfat/login.jsp" target="_blank"><i class="icon-enter on-left"></i> Acessar</a>
								</div>
								 <div class="frame" id="sistema_gdact">
										<!-- GDACT -->
										<h4>Gratificação de Desempenho de Atividade de Ciência e Tecnologia – GDACT</h4>
										<p>Este Programa de Avaliação de Desempenho tem por objetivo promover a avaliação de desempenho institucional e individual para efeito de pagamento da Gratificação de Desempenho de Atividade de Ciência e Tecnologia – GDACT, instituída pela medida provisória nº 2229-43, de 06 de setembro de 2001.</p>
										<p><em>OBS: Caso falte o nome de algum servidor entre o servidores relacionados, ou haja algum nome que não pertença a sua unidade de avaliação, por favor, entre em contato com SERH, através do email: <a href"mailto:adriana@cetem.gov.br"="">adriana@cetem.gov.br</a>.</em></p>
										<p>A Documentação do Uso da Apresentação da Fase 3 se Encontra no Repositório da Intranet no Caminho SERH > GDACT ou direto <a href="index.php?action=doDownload&key=bf8a3852b4615b465ad5686f4f657b38">neste Link</a> </p>
										<p>Qualquer erro ou problema favor enviar um e-mail para <a href="mailto:sein-suporte@cetem.gov.br">sein-suporte@cetem.gov.br</a>
										<!--p>Acesse também a <a href="gdact.php">Página de downloads</a> da documentação da gratificação.</p-->
										</br>
										<a href="http://gdact.cetem.gov.br/" target="_blank"><i class="icon-enter on-left"></i> Acessar</a>
								</div>
						   </div>
						  </div>
						</article>
					</div>
				</div>
			</div>
			 <div class="row">
			<div class="span8 offset2">
		  			<div class="row">
						<? $this->component->loadComponent("footer")->loadCom(); ?>
					</div>
			</div>
		  </div>
		</div>	
		
		<script type='text/javascript' src='media/pageslide/jquery.pageslide.js'></script>	
		<script>
				$(".second").pageslide({ direction: "left", modal: false});
		</script>						
	</body>
</html>
