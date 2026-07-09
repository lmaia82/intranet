<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			<style>
				.metro .grid.fluid .row .span1 {
					width: auto !important;
				}
				.metro .grid.fluid .row .span2 {
					width: auto !important;
				}
			</style>
			<script>
				function closeDialog(){
						 $.Dialog.close();
					}
				function acceptYes(){
					$("#listeve").submit();
				}	
				$(document).ready(function(){
					$("#alterButton").click(function(){
						var url = "";
						$("input[type='checkbox']").each(function(index){
							if($(this).is(":checked")){
								url = "index.php?module=eventos&view=update&id=" + $(this).val();
							}
						});
						window.location = url;
					});
					
					
					
					$("#btndelete").click(function(){
  						  $.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-rocket"></span>',
       						   title: 'CETEM - Intranet Confirmação Exclusão',
       						   width: 300,
       						   padding: 10,
        					   content: '<p>Deseja Realmente Excluir o(s) Eventos(s) selecionado(s) ?</p><br/><button id="btnyes" onclick="acceptYes();" class="primary">Sim</button>&nbsp;<button class="danger" onclick="closeDialog();" id="btnno">Não</button>'
    					});
					});
				});
			</script>
		</head>
	<body class="metro">
			<!-- NAV -->
			<? $this->component->loadComponent("header_page")->loadCom(); ?>
			<? $this->component->loadComponent("navBar")->loadCom(); ?>
		<!-- Main -->
		<div class="grid fluid">    
		  <div class="row">
			<div class="span8 offset2">
		  			<div class="row">
						<h2>Arquivos</h2>
						<? $this->loadView(); ?>
					
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