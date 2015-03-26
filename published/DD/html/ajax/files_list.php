<?
	include_once("../../../../system/wainit_ajax.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	CurrentUser::getInstance()->setSetting("DDLASTFOLDER", WebQuery::getParam("id"));
	
	if (!in_array(Wbs::getDbkeyObj()->getDbkey(), array("VW1151", "VT2065", "AJAX", "VT2033"))) {
		print "Wrong dbkey";
		exit;
	}
	
	
	$node = $foldersTree->getNode(WebQuery::getParam("id"));
	$limitParams = array ("offset" => WebQuery::getParam("offset"), "limit" => WebQuery::getParam("limit"));
	$filesCollection = $node->getRecords(array(),$limitParams);
	$fileInfos = array ();
	foreach ($filesCollection["data"] as $file)
		$fileInfos[] = $file->asArray ();
	
	$folderData = $node->asArray ();
	
	$json = new Services_JSON();
	print $json->encode(array ("files" => $fileInfos, "total" => $filesCollection["total"], "folder" => $folderData));
?>