<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	
	require_once( WBS_DIR."/published/PM/pm.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "WL";
	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );
	
	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	
	
	do {
		foreach ($ids as $id) {
			$workData = array ("P_ID" => $projectId, "PW_ID" => $id);
			
			if (PEAR::isError($error = pm_deleteWork( prepareArrayToStore($workData), $pmStrings, $language, $kernelStrings)))
				break;
		}
		
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	} else {
		$ajaxRes = array ("success" => true);
	}
	
	print $json->encode($ajaxRes);	
?>