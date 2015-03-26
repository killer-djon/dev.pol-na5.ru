<?php
	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$json = new Services_JSON();
	try {
		$fileObj = $foldersTree->getFile(WebQuery::getParam("id"));
		$versions = $fileObj->getVersionsData();
		foreach ($versions as &$cRow) {
			unset($cRow["DLH_DISKFILENAME"]);
			$cRow["DOWNLOAD_URL"] = WebQuery::getUrl("gethistoryfile.php", array ("DL_ID" => base64_encode($fileObj->DL_ID),"DLH_VERSION" => base64_encode($cRow["DLH_VERSION"])));
			$dtm = $cRow["date"];
			$cRow["DLH_DATETIME"] = $dtm->display();
			$cRow["DLH_USERNAME"] = $cRow["username"];
		}
		$result = $versions;
	} catch (Exception $ex) {
		$result = array ("success" => false, "errorStr" => $ex->getMessage());		
	}
	
	print $json->encode($result);
?>