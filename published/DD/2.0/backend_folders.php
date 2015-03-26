<?php
	$start = microtime(true);
	include_once("_screen.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	waLocale::loadFile(Wbs::getSystemObj()->files()->getAppPath("AA", "localization"), "aa");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	$foldersTree->loadTree(true);
	
	$rootNode = $foldersTree->getRootNode();
	$statData = $foldersTree->getStatInfo();
	
	waLocale::loadFile($app->getPath("localization", true), "dd_backend_folders");
	
	$preproc = new Preproc("DD");
	$preproc->assign ("rootNode", $rootNode);
	$preproc->assign ("statData", $statData);
	$preproc->displayScreen ("backend_folders.html");
	
?>