<?
	include_once("../../../../system/wainit_ajax.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$json = new Services_JSON();
	try {
		$fileObj = $foldersTree->getFile(WebQuery::getParam("id"));
		$link = $fileObj->getShareLinkUrl(true);
		$result = array ("success" => true, "link" => $link);
	} catch (Exception $ex) {
		$result = array ("success" => false, "error" => $ex->getMessage());		
	}
	
	print $json->encode($result);
?>