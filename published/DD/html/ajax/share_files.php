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
		
		$widgetData = array ("WT_ID" => "SHDD", "WST_ID" => "SIMPLE", "WG_DESC" => iconv('UTF-8', $html_encoding, $wgName));
		$wgId = $widgetManager->add ($widgetData);
		
		if (PEAR::isError ($error = $wgId))
			break;		
		
		$params = array ("UID" => $currentUser);
		if (!empty($files) && is_array($files))
			$params["FILES"] = join (",", $files);
		if (!empty($folder))
			$params["FOLDERS"] = $folder;
		
		$res = $widgetManager->setWidgetParams ($wgId, $params);
		if (PEAR::isError ($error = $res))
			break;
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["wgId"] = $wgId;
		$ajaxRes["name"] = $wgName;
	}	
	
	print $json->encode ($ajaxRes);
		
?>