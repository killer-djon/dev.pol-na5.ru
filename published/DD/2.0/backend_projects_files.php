<?php
	$start = microtime(true);
	include_once("_screen.init.php");
	Wbs::authorizeUser("DD");
	Kernel::incAppFile("DD", "dd_app");
	
	$app = DDApplication::getInstance();
	
	// Load old files localization
	waLocale::loadFile($app->getPath("localization", false), "dd");
	
	$preproc = new Preproc("DD");
	$preproc->displayScreen ("backend_projects_root.html");
?>