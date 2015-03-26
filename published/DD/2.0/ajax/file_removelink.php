<?php
	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$json = new Services_JSON();
	try {
		$fileObj = $foldersTree->getFile(WebQuery::getParam("id"));
		$fileObj->removeShareLink(true);
		$result = array ("success" => true);
	} catch (Exception $ex) {
		$result = array ("success" => false, "errorStr" => $ex->getMessage());		
	}
	
	print $json->encode($result);
?>