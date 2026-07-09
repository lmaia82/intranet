<?
	$setor = $this->getCookie("setor");
	if($setor != "notset"){
				header("Location: index.php?module=minha_area");
	}
?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			<script>
				$(document).ready(function(){
					/**secform.onFail(function(){
						//TRIGGER ONFAIL
						$.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-rocket"></span>',
       						   title: 'CETEM - Intranet - Error',
       						   width: 280,
       						   padding: 10,
        					   content: '<p>Campos preenchidos incorretamentes.</p><br/><center><button class="danger" onclick="$.Dialog.close();">Ok</button></center>'
    					});
					});**/
				});
			</script>
			<style type="text/css">
				body {
						background-image: url('media/img/frente_cr.png');
						background-attachment: fixed;
						background-repeat: no-repeat;
						background-position: center center;
					}
			</style>	
		</head>
		<body class="metro">
		<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom($this->module); ?>
			
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
		  		<div class="span8 offset2">
		  			<div class="row">
						<div class="span8 offset2">
						  <div class="row">
							<div class="login">
									<!--div class="logoti">
										<img src="media/img/logo-intra.png" class="rounded polaroid shadow" />
									</div-->
									
									<form id="frm-outh" action="index.php?action=doLogin" method="POST" style="background-color: white;" class="rounded polaroid shadow">
										<fieldset>
											<h4>Usuário</h4>
												<div class="input-control text block span10" data-role="input-control">
													<input type="text" name="usuario" placeholder="Usuario">
												</div>
												<h4>Senha</h4>
												<div class="input-control password block span10" data-role="input-control">
													<input type="password" name="pass" placeholder="Senha">
												</div>
												<br/>
												<? 
													$towhat = "";
													if($this->getParameterUrl("redir") != "notset"){
														$towhat = $this->getParameterUrl("redir");
													}else{
														$towhat = "index.php?module=autenticar";
													}
												?>
											<input type="hidden" name="redir" value="<?echo $towhat; ?>"/>
										<fieldset>
										<center style="
    padding-left: 70px;
"><button  type="submit" class="primary place-left">Entrar</button></center>
									</form>
								</div>
							</div>
						</div>	
		  			</div>
				</div>
			</div>
		</div>
	</body>
</html>