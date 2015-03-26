<?php
	define('PUBLIC_AUTHORIZE', true);
	
	include_once("_screen.init.php");
	Kernel::incAppFile("DD", "dd_app");

	include_once(SYSTEM_PATH . "/packages/data_model/JsTreeWrapper.php");
	
	Wbs::publicAuthorize();
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	$folder = $foldersTree->getNodeBySlug(WebQuery::getParam("sl"));
	$filesCollection = $folder->getRecords(null, array("column" => "DL_FILENAME ASC", "direction" => "ASC"));
	
	$noPictures = true;
	$allPictures = true;
	
	// calculate default view
	$records = $filesCollection->getRecords();
	foreach ($records as $cFile) {
		if ($cFile->isImage())
			$noPictures = false;
		else
			$allPictures = false;			
	}
	
	if ($noPictures)
		$defaultView = "list"; 
	elseif (!$allPictures)
		$defaultView = "thumb_list"; 
	else
		$defaultView = "tiles"; 		
		
	
	$view = WebQuery::getParam("view", $defaultView);
	waLocale::loadFile($app->getPath("localization", true), "dd_template_backend");	
	waLocale::loadFile($app->getPath("localization", true), "dd_public");	
	$views = $app->getLinkViews();
	
	
	$preproc = new Preproc("DD");
	$preproc->assign("folder", $folder);
	$preproc->assign("title", $folder->Name);
	$preproc->assign("files", $records);
	$preproc->assign("views", $views);
	$preproc->assign("view", $view);
	$preproc->assign("is_hosted", Kernel::isHosted());
	$preproc->display ("folder_link.html");	
?>