<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "CT";
	$ajaxRes = array ("success" => false, "errorStr" => "no result");

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	
	do {
		$widgetManager = getWidgetManager ();
		if (PEAR::isError ($error = $widgetManager))
			break;
		
		if (empty($wgName)) {
			$error = PEAR::raiseError("Empty name");
			break;
		}
		if (empty($folderId)) {
			$error = PEAR::raiseError("Empty folder id");
			break;
		}
		
		$widgetData = array ("WT_ID" => "DDUploader", "WST_ID" => "Inplace", "WG_DESC" => $wgName, "WG_LANG" => $language);
		$wgId = $widgetManager->add (prepareArrayToStore($widgetData));
		
		if (PEAR::isError ($error = $wgId))
			break;		
		
		$params = array ("FOLDER" => $folderId, "TITLE" => $wgName);
		/*$params = array ("UID" => $currentUser);
		if (!empty($files) && is_array($files))
			$params["FILES"] = join (",", $files);
		if (!empty($folder))
			$params["FOLDERS"] = $folder;
		$params["VIEW_MODE"] = $viewMode;*/
		
		$res = $widgetManager->setWidgetParams ($wgId, prepareArrayToStore($params));
		if (PEAR::isError ($error = $res))
			break;
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["wgId"] = $wgId;
		$ajaxRes["name"] = prepareStrToStore($wgName);
	}	
	
	print $json->encode ($ajaxRes);
		
?>