<?php
	define('PUBLIC_AUTHORIZE', true);
	
	include_once("_screen.init.php");
	Kernel::incAppFile("DD", "dd_app");
	
	
	$fp = Env::get("fp");
	if (empty($fp))
		die("Not setted widget");
	$errorStr = null;
	
	$view = "list";
	try {
  		// get widget data
	    $widgets_model = new WidgetsModel();
	    $widgetData = $widgets_model->getByCode($fp);
		
		//print_r($widgetData);
		if (!($widgetData["WT_ID"] == "DDList" && $widgetData["WST_ID"] == "Link")) {
			die("Wrong widget type");
		}
		// get widget params
		$widget_params_model = new WidgetParamsModel();
		$params = $widget_params_model->getByWidget($widgetData["WG_ID"]);
		
		$widgetViewModes = array (0 => "grid", "1" => "list", "2" => "thumb_list", "3" => "tiles");
		if (isset($params["VIEW_MODE"]))
			$view = $widgetViewModes[$params["VIEW_MODE"]];
		
		
		$app = DDApplication::getInstance();
		$foldersTree = $app->getFoldersTree();
		
		$filesData = array ();

		if (!empty($params["FILES"])) {
			$ids = explode(",", $params["FILES"]);
			$filesCollection = $foldersTree->getFilesByIds($ids);
			$filesData = $filesCollection["data"];
		}
		if (!empty($params["FOLDERS"])) {
			$folder = $foldersTree->getNode($params["FOLDERS"]);
			$filesCollection = $folder->getRecords(null, array('column' => 'DL_FILENAME', 'direction' => 'ASC'));
			$filesData = $filesCollection->getRecords();
		}
	} catch (Exception $exception) {
		$errorStr = $exception->getMessage();
		echo $exception;
	}
	
	waLocale::loadFile($app->getPath("localization", true), "dd_template_backend");	
	waLocale::loadFile($app->getPath("localization", true), "dd_public");	

	$preproc = new Preproc("DD");
	$preproc->assign("title", $widgetData["WG_DESC"]);
	$preproc->assign("files", $filesData);
	if (isset($views)) {
		$preproc->assign("views", $views);
	}
	$preproc->assign("view", $view);
	$preproc->assign("errorStr", $errorStr);
	$preproc->assign("is_hosted", Kernel::isHosted());
	$preproc->display ("folder_link.html");	
?>