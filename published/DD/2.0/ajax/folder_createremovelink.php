<?php
	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$json = new Services_JSON();
	try {
		$folderObj = $foldersTree->getNode(WebQuery::getParam("id"));
		$action = WebQuery::getParam("action");
		
		if ($action == "create") {
			$link = $folderObj->getShareLinkUrl(true);
			$result = array ("success" => true, "link" => $link);
		} elseif ($action == "remove") {
			$folderObj->removeShareLink(true);
			$result = array ("success" => true);
		} else {
			$result = array ("success" => false, "errorStr" => "Wrong action: $action");
		}
	} catch (Exception $ex) {
		$result = array ("success" => false, "errorStr" => $ex->getMessage());		
	}
	
	print $json->encode($result);
?>