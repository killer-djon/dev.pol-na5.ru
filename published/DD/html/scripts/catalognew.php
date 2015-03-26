<?
	$start = microtime(true);
	include_once("../../../../system/wainit_screen.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	Kernel::incPackageFile("folders_tree", "JsTreeWrapper");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	$foldersTree->loadTree();
	
	$foldersTree->getRootNode();
	$wrapper = new JsTreeWrapper();
	$foldersJs = $wrapper->getTreeNodesJs($foldersTree);
	
	$currentFolder = CurrentUser::getInstance()->getSetting("DDLASTFOLDER");
	$viewParams = array ("currentFolderId" => $currentFolder);
	
	$preproc = new Preproc("DD");
	$preproc->assign ("foldersJs", $foldersJs);
	$preproc->assign ("viewParams", $viewParams);
	$preproc->assign("sessionId", session_id());
	$preproc->displayScreen ("catalognew.htm");
	
	print "<script>document.title='" . (microtime(true) - $start) . "'</script>";
?>