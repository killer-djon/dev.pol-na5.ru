<?php
	include_once("_screen.init.php");
	Wbs::authorizeUser("DD");
	
	$projectId = Env::Request("projectId");
	
	$app = DDApplication::getInstance();
	$foldersTree = $app->getFoldersTree($projectId);
	$foldersTree->loadTree();
	
	class DDJsTreeWrapper extends JsTreeWrapper {
		protected function getNodeParams($node) {
			$params = parent::getNodeParams($node);
			$params[] = isset($node->DF_SPECIALSTATUS) ? $node->DF_SPECIALSTATUS : 0;
			return $params;
		}
	}
	
	$wrapper = new DDJsTreeWrapper();
	$foldersJs = $wrapper->getTreeNodesJs($foldersTree);
	
	$user = CurrentUser::getInstance();
	
	$viewSettings = array ();
	$viewSettings["itemsOnPage"] = $user->getAppSetting("DD", "RECORDPERPAGE", 30);
	$viewSettings["descTruncate"] = $user->getAppSetting("DD", "RESTRICTDESCLEN", 0);
	$viewSettings["viewmodeApplyTo"] = $user->getAppSetting("DD", "FOLDERVIEWOPT", "local");
	
	waLocale::loadCommonTemplatesFile();
	waLocale::loadFile($app->getPath("localization", true), "dd_template_backend");
	
	$canZohoEdit = $app->getSetting("ZOHOEDITSTATE") != "DISABLED";
	$hasZohoKey = $app->getSetting("ZOHOSECRETKEY") != "";
	
	// Load old files localization
	waLocale::loadFile($app->getPath("localization", false), "dd");
	
	/*$date = CDate::now();
	$date->toUtc();
	print_r($date->display());
	$date->toUtc();
	print_r($date->display());
	exit;*/
	
	$currentFolderId = WebQuery::getParam("currentFolderId", $user->getAppSetting("DD", "LASTFOLDER"));
	
	$preproc = new Preproc("DD");
	$preproc->assign("projectId", $projectId);
	$preproc->assign ("foldersJs", $foldersJs);
	$ftp = Wbs::getSystemObj()->files()->getDataPath().DIRECTORY_SEPARATOR.Wbs::getDbkeyObj()->getDbkey().DIRECTORY_SEPARATOR."ftp";
	$preproc->assign("ftpFolder", file_exists($ftp));
	$preproc->assign ("currentFolderId", $currentFolderId);
	$preproc->assign ("viewSettings", $viewSettings);
	$preproc->assign ("currentUser", CurrentUser::getInstance());
	$preproc->assign("lastSearchString", str_replace('"', '\"', $user->getAppSetting("DD", "DD_SEARCHSTRING")));
	$preproc->assign("canTools", $user->getRightValue ("/ROOT/DD/FUNCTIONS", "CANTOOLS"));
	$preproc->assign("canReports", $user->getRightValue ("/ROOT/DD/FUNCTIONS", "CANREPORTS"));
	$preproc->assign("canWidgets", $user->getRightValue ("/ROOT/DD/FUNCTIONS", "CANWIDGETS"));
	$preproc->assign("canCreateRootFolder", $user->getRightValue ("/ROOT/DD/FOLDERS", "ROOT"));
	$preproc->assign("canManageUsers", $user->getRightValue ("/ROOT/UG/SCREENS", "UNG"));
	$preproc->assign("canZohoEdit", $canZohoEdit);
	$preproc->assign("hasZohoKey", $hasZohoKey);
	$preproc->assign("sessionId", session_id());
	$preproc->displayScreen ("backend.html");
	
	//print "<script>document.title='" . (microtime(true) - $start) . "'</script>";
?>