<? $this->component->loadComponent("CheckPermissionAdm")->loadCom(); ?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
				<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			
			<style>
				.repositorio-icon {
					font-size: 20px !important;
				}
			</style>
			<script>
			
				function closeDialog(){
						 $.Dialog.close();
					}
				function acceptYes(){
					$("#lista_itens").submit();
				}

				
				
				function fhiframe(){
					$("#parent_id").val($("#valuenode").val());
					$("#nome_folder").val($("#pasta_name").html());
					closeDialog();
						
				}
				
				$(document).ready(function(){
					$("#treew_view").hide();
					$( "#upform" ).attr( "enctype", "multipart/form-data" ).attr( "encoding", "multipart/form-data" );
					$(".item-perm").each(function(i){
						if($(this).is(":selected")){
							if($(this).val()== 3){
								$("#permissao_granular").show();
							}else{
								$("#permissao_granular").hide();
							}
						}
					});
					
					$("#permission").change(function(){
						if($(this).val()== 3){
							$("#permissao_granular").show();
						}else{
							$("#permissao_granular").hide();
						}
					});
					
					$("#tipo").change(function(){
						if($(this).val()== 1){
							$("#treew_view").show();
						}else{
							$("#treew_view").hide();
						}
					});
					
					$("#categorias").perfectScrollbar({
       					   wheelSpeed: 20,
          				   wheelPropagation: false
        			});

					/**var bar = $('.bar');
   				    var percent = $('.percent');
   				    var status = $('#status');
					var pb = $("#bar-progresso").progressbar();
					
   				    $('#upform').ajaxForm({
        				beforeSend: function() {
						  status.empty();
           				  var percentVal = 0;
            			  pb.progressbar('value', percentVal);
       					 },
       					 uploadProgress: function(event, position, total, percentComplete) {
            				var percentVal = percentComplete;
           				  	pb.progressbar('value', percentVal);
        				 },
       					 complete: function() {
           					 window.location = 'index.php?module=repositorio_administrador&view=list&id=' + $("#destiny").val();
       					 }
   					 });
					**/
					$("#alterButton").click(function(){
						var url = "";
						$("input[type='checkbox']").each(function(index){
							if($(this).is(":checked")){
								url = "index.php?module=telefones_administrador&view=update&id=" + $(this).val();
							}
						});
						window.location = url;
					});
					
					$("#btnalter").click(function(){
						var url = "";
						$("input[type='checkbox']").each(function(index){
							if($(this).is(":checked")){
								if($(this).attr("tp_orientation") == "folder"){
									url = "index.php?module=repositorio_administrador&view=update_folder&idfolder=" + $("#idprnt").val()+"&id="+$(this).val();
								}else{
									url = "index.php?module=repositorio_administrador&view=update_file&idfolder=" + $("#idprnt").val()+"&id="+$(this).val();
								}
							}
						});
						window.location = url;
					});
					
					
					
					$("#btndelete").click(function(){
  						  $.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-rocket"></span>',
       						   title: 'CETEM - Dialogo',
       						   width: 300,
       						   padding: 10,
        					   content: '<p>Deseja Realmente Excluir o(s) Itens(s) selecionado(s) ?</p><br/><button id="btnyes" onclick="acceptYes();" class="primary">Sim</button>&nbsp;<button class="danger" onclick="closeDialog();" id="btnno">Não</button>'
    					});
					});
					
				
					
					$("#calliframe").click(function(){
  						  $.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-upload-2"></span>',
       						   title: 'CETEM - Selecione a Pasta.',
       						   width: 530,
							   height:500,
       						   padding: 0,
        					   content: '<iframe id="treev" name="treev" src="index.php?action=iframe_treeview" scrolling=\'yes\' width=\'515px\' height=\'450px\'></iframe><br/><input type="button" class="primary" value="Selecionar" onclick="fhiframe();" id="btnselect"/><input type=\"hidden\" id=\"valuenode\"/></div><b>&nbsp;Pasta Alvo:<span id="pasta_name" name="pasta_name"></span></b>'
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