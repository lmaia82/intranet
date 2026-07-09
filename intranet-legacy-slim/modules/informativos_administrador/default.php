<? $this->component->loadComponent("CheckPermissionAdm")->loadCom(); 
$setor = $this->getCookie("setor");
?>
<!DOCTYPE html>
	<html lang="pt-br">
		<head>
			<title>CETEM | INTRANET (BETA V.0.1)</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">  
			<meta charset="UTF-8">
			<? $this->component->loadComponent("Header_teste")->loadCom(); ?>
			
				<style>
				.table {
					text-align: left;
				}
				.repositorio-icon {
					font-size: 20px !important;
				}
				.remover-item {
					cursor: pointer;
				}
				.container-fotos{
					width:764px;
					height:120px;
					white-space:nowrap;
					overflow:auto;
				}
				#container-fotos ul
				{
					margin: 0;
					padding: 0;
					list-style-type: none;
					text-align: center;
				}

				#container-fotos ul li { display: inline; }

				#container-fotos ul li img
				{
					width:95px;
					height: 95px;
					padding: 15px 10px;
					
				}

				.thumbnail-imagelist:hover
				{
					border-style: solid;
					border-bottom-width: 4px;
					border-top-width: 4px;
					border-right-width: 4px;
					border-left-width: 4px;
					border-color: red;
				}
				.thumbnail-imagelist{
						cursor: pointer;
					}
			</style>
			<script>
				function closeDialog(){
						 $.Dialog.close();
					}
				function acceptYes(){
					$("#listinf").submit();
				}	
				
				function removeItens(obj){
							$($(obj).parent()).parent().remove();	
				}
			
				function removeItensOfList(obj){
							$(obj).parent().remove();	
				}
				
				function addtotable(){
					$("#ulcontent li").each(function(index){
						var img = $(this).find("img");
						var input = $(this).find("input");
						
						var innhtml = "";
						innhtml = "<tr>";
							innhtml += "<td>";
								innhtml += $(img).attr("alt");
							innhtml += "</td>";
							innhtml += "<td>";
								innhtml += "<img height='75' width='75' src='index.php?action=doDownload&key=" + $(input).val() + "&imgtype=thumb'/>";
							innhtml += "</td>";
							innhtml += "<td>";
								innhtml += "<i onclick='removeItens(this);' style='cursor:pointer; ' class='icon-remove'></i>";
							innhtml += "<input type='hidden' name='id_name[]' value='" + $(img).attr("alt") + "'/></td>";	
							innhtml += "<input type='hidden' name='id_url[]' value='" + $(input).val() + "'/></td>";
						innhtml += "</tr>";
						$("#tabela-imagens").append(innhtml);
					});
					closeDialog();
				}
				
				$(document).ready(function(){
					
					$("#list-images-div").hide();
					
					if($("#id_tipo_informe").val() == 2){
						$("#list-images-div").show();
					}
					
					$("#id_tipo_informe").change(function(){
						if($(this).val()== 2){
							$("#list-images-div").show();
						}else{
							$("#list-images-div").hide();
						}
					});
					
					$("#alterButton").click(function(){
						var url = "";
						$("input[type='checkbox']").each(function(index){
							if($(this).is(":checked")){
								url = "index.php?module=informativos_administrador&view=update&id=" + $(this).val();
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
        					   content: '<p>Deseja Realmente Excluir o(s) Informativo(s) selecionado(s) ?</p><br/><button id="btnyes" onclick="acceptYes();" class="primary">Sim</button>&nbsp;<button class="danger" onclick="closeDialog();" id="btnno">Não</button>'
    					});
					});
					
					$("#calliframe-images").click(function(){
  						  $.Dialog({
     						   shadow: true,
        					   overlay: true,
      						   icon: '<span class="icon-upload-2"></span>',
       						   title: 'CETEM - Selecione a Pasta.',
       						   width: 500,
							   height:300,
       						   padding: 0,
        					   content: '<iframe id="imagelist" name="imagelist" src="index.php?action=iframe_list_images" scrolling=\'yes\' width=\'780px\' height=\'500px\'></iframe><br/><div id="container-fotos" class="container-fotos"><ul id="ulcontent"></ul></div><br/><center><button id="btnaddtotable" onclick="addtotable();" class="primary">Adicionar</button></center><br/>'
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
						<h2>Informes</h2>
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


