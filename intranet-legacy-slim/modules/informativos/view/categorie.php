<? 
$categorie = $this->getParameterUrl("id"); 
$infctg = $this->categories->getCategoriesById($categorie);
$pageNum = 1;
if("notset" == $this->getParameterUrl("page")){
		$pageNum = 1;
		$lista_informativo = $this->articles->getAllArticlesByCategorie($categorie,$pageNum);
}else{
		$pageNum = $this->getParameterUrl("page");
		$lista_informativo = $this->articles->getAllArticlesByCategorie($categorie, $this->getParameterUrl("page"));
}
?>

<? echo $infctg[0]["description"];?>
<div class="listview-outlook">
	<? foreach($lista_informativo as $informativo){
			$arr_date = explode(" ",$informativo['date_creation']);
			$datea	  = explode("-",$arr_date[0]);
			$dtinicio = $datea[2]."/".$datea[1]."/".$datea[0];
	?>
														
		<a class="list" onclick="javascript: window.location = 'index.php?module=informativos&view=load&id=<? echo $informativo["id_article"]; ?>';" href="#">
		  <div class="list-content">
			 <span class="list-title"><? echo $informativo["title"]; ?></span>
			 <span class="list-subtitle"><? echo $dtinicio; ?></span>
		  </div>
		</a>
	<? } ?>
</div>
<br/>

<? $page = $this->component->loadComponent("pagination_categories");
   $page->setParameters("id_categorie",$categorie); 
   $page->setParameters("count_articles",$this->articles->getCountArticles($categorie));
   $page->loadCom($pageNum);
?>
