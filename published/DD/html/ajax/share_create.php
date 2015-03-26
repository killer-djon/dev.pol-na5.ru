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
		
		if (empty($subtype)) {
			$error = PEAR::raiseError("Empty subtype");
			break;
		}
		
		$widgetData = array ("WT_ID" => "DDList", "WST_ID" => $subtype, "WG_DESC" => $wgName, "WG_LANG" => $language);
		
		//if (PEAR::isError ($error = $wgId))
		//	break;		
		
		$params = $widgetData;
		//$params["UID"] = $currentUser;
		if (!empty($files) && is_array($files))
			$params["FILES"] = join (",", $files);
		if (!empty($folder))
			$params["FOLDERS"] = $folder;
		$params["VIEW_MODE"] = $viewMode;
		
		$wgId = $widgetManager->add (prepareArrayToStore($params));
		if (PEAR::isError ($error = $wgId))
			break;
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["wgId"] = $wgId;
		if ($subtype == "Link")
			$ajaxRes["embType"] = "link";
		else
			$ajaxRes["embType"] = "inplace";
		$ajaxRes["name"] = prepareStrToStore($wgName);
	}	
	
	print $json->encode ($ajaxRes);
		
?>