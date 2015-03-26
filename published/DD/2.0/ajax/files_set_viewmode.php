<?php
	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$json = new Services_JSON();
	try {
		$foldersTree->setUserFolderViewmode (CurrentUser::getInstance(), WebQuery::getParam("folderId"), WebQuery::getParam("mode"));			
	} catch (Exception $ex) {
		print $json->encode(array ("success" => false, "errorStr" => $ex->getMessage()));
		exit;
	}
	
	print $json->encode(array ("success" => true));
?>