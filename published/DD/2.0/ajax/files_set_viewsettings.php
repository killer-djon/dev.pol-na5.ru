<?php

	include_once("_ajax.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	
	$user = CurrentUser::getInstance();
	
	try {
		if (Env::Post("descTruncate", Env::TYPE_INT, 0)) {
			$user->setSetting("DD", "RESTRICTDESCLEN", Env::Post("descTruncate", Env::TYPE_INT, 0));
		}
		
		if (is_numeric(WebQuery::getParam("itemsOnPage")))
			$user->setSetting("DD", "RECORDPERPAGE", WebQuery::getParam("itemsOnPage"));
		else
			throw new RuntimeException("Items on page value must be numeric");
		
		$viewmodeApplyTo = WebQuery::getParam("viewmodeApplyTo");
		if ($viewmodeApplyTo != "global")
			$viewmodeApplyTo = "local";
		$user->setSetting("DD", "FOLDERVIEWOPT", $viewmodeApplyTo);
		
		
		//$foldersTree->setUserFolderViewmode (CurrentUser::getInstance(), WebQuery::getParam("folderId"), WebQuery::getParam("mode"));			
	} catch (Exception $ex) {
		print json_encode(array ("success" => false, "errorStr" => $ex->getMessage()));
		exit;
	}
	
	print json_encode(array ("success" => true));
?>
