<?php
	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$foldersTree->deleteFiles(WebQuery::getParam("documents"));
	
	$json = new Services_JSON();
	print $json->encode(array ("success" => true));
?>