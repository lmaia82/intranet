<? $idtoload = $this->getParameterUrl("id"); ?>
<? $result = $this->articles->getInformationArticle($idtoload); ?>
<?

$infctg = $this->categories->getCategoriesById($result[0]['id_categorie']);
?>

<h2><? echo $result[0]["title"]; ?></h2>
<h3>Área Responsável: <? echo $infctg[0]["description"]; ?></h3>
<p>
	<? echo $result[0]["content"]; ?>
</p>
<? if($result[0]["tipo"] == 2){?>
<script type="text/javascript">
			document.write('<style>.noscript { display: none; }</style>');
		</script>
	
	<div class="galleriffic"> 
		<div id="page">
			<div id="container">
				<!-- Start Advanced Gallery Html Containers -->
				
				<div id="gallery" class="content">
					<div id="controls" class="controls"></div>
					<div class="slideshow-container">
						<div id="loading" class="loader"></div>
						<div id="slideshow" class="slideshow"></div>
					</div>
					<div id="caption" class="caption-container"></div>
				</div>
				<div id="thumbs" class="navigation">
					<ul class="thumbs noscript">
					
						<?
							$arrfotos =  get_object_vars(json_decode($result[0]["foto_gallery"]));
							foreach($arrfotos['fotos'] as $foto){
								$ftptemp =  get_object_vars($foto);
						?>
							<li>
							<a class="thumb"  <? echo "href=\"index.php?action=doDownload&key=".$ftptemp['id']."\" " ?> <? echo "alt=\"".$ftptemp['nome']."\" " ?>>
								<img  <? echo "src=\"index.php?action=doDownload&key=".$ftptemp['id']."&imgtype=thumb\" " ?> style="width: 75px;height:75px;" <? echo "alt=\"".$ftptemp['nome']."\" " ?> />
							</a>
							<div class="caption">
								<div class="download">
									<a  <? echo "href=\"index.php?action=doDownload&key=".$ftptemp['id']."\" " ?>>Baixar Foto</a>
								</div>
								<div class="image-title"><? echo $ftptemp['nome']; ?></div>
								<!--div class="image-desc">Description</div-->
							</div>
						</li>
							
						<?}?>
	
					</ul>
				</div>
				<!-- End Advanced Gallery Html Containers -->
				<div style="clear: both;"></div>
			</div>
		</div>
		
	</div>
<?}?>
		
		
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				// We only want these styles applied when javascript is enabled
				$('div.navigation').css({'width' : '300px', 'float' : 'left'});
				$('div.content').css('display', 'block');

				// Initially set opacity on thumbs and add
				// additional styling for hover effect on thumbs
				var onMouseOutOpacity = 0.67;
				$('#thumbs ul.thumbs li').opacityrollover({
					mouseOutOpacity:   onMouseOutOpacity,
					mouseOverOpacity:  1.0,
					fadeSpeed:         'fast',
					exemptionSelector: '.selected'
				});
				
				// Initialize Advanced Galleriffic Gallery
				var gallery = $('#thumbs').galleriffic({
					delay:                     2500,
					numThumbs:                 15,
					preloadAhead:              10,
					enableTopPager:            true,
					enableBottomPager:         true,
					maxPagesToShow:            7,
					imageContainerSel:         '#slideshow',
					controlsContainerSel:      '#controls',
					captionContainerSel:       '#caption',
					loadingContainerSel:       '#loading',
					renderSSControls:          true,
					renderNavControls:         true,
					playLinkText:              'Começar Apresentação',
					pauseLinkText:             'Para Apresentação',
					prevLinkText:              '&lsaquo; Foto Anterior',
					nextLinkText:              'Proxima Foto &rsaquo;',
					nextPageLinkText:          'Prox &rsaquo;',
					prevPageLinkText:          '&lsaquo; Ant',
					enableHistory:             false,
					autoStart:                 false,
					syncTransitions:           true,
					defaultTransitionDuration: 900,
					onSlideChange:             function(prevIndex, nextIndex) {
						// 'this' refers to the gallery, which is an extension of $('#thumbs')
						this.find('ul.thumbs').children()
							.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
							.eq(nextIndex).fadeTo('fast', 1.0);
					},
					onPageTransitionOut:       function(callback) {
						this.fadeTo('fast', 0.0, callback);
					},
					onPageTransitionIn:        function() {
						this.fadeTo('fast', 1.0);
					}
				});
			});
		</script>
