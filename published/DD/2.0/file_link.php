<?php
	define('PUBLIC_AUTHORIZE', true);
	
	include_once("_screen.init.php");
	Kernel::incAppFile("DD", "dd_app");
	Wbs::publicAuthorize();
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree();
	$file = $foldersTree->getFileBySlug(WebQuery::getParam("sl"));
	$files = array($file);
	
	$view = ($file->isImage()) ? "thumb_list" : "list";
	
	waLocale::loadFile($app->getPath("localization", true), "dd_template_backend");	
	waLocale::loadFile($app->getPath("localization", true), "dd_public");	
	
	$preproc = new Preproc("DD");
	$preproc->assign("title", $file->DL_FILENAME);
	$preproc->assign("files", $files);
	$preproc->assign("view", $view);
	$preproc->assign("is_hosted", Kernel::isHosted());
	$preproc->display ("folder_link.html");	
?>