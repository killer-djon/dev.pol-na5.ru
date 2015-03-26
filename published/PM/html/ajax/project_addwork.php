<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	
	require_once( WBS_DIR."/published/PM/pm.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "WL";
	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );
	$metric = metric::getInstance();
	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	
	
	do {
		$workData = $fields;
		$workData["ASSIGNED"] = split(",", $assignedStr);
		
		$userRights = $PMRightsManager->evaluateUserProjectRights( $currentUser, $workData["P_ID"], $kernelStrings );
		if ( $userRights < PM_RIGHT_READWRITE )
		{
			$error = PEAR::raiseError($pmStrings["pm_noaddmodtaskrights_message"]);
			break;
		}
		
		if ($id) $workData["PW_ID"] = $id;
		$action = ($action == "edit") ? PM_ACTION_MODIFY : PM_ACTION_NEW;
		if (!empty($workData["PW_BILLABLE"])) $workData["PW_BILLABLE"] = 1;
		$workData = prepareArrayToStore($workData);
		
		if (!$workData["PW_COSTESTIMATE"])
			$workData["PW_COSTCUR"] = "";
		$res = pm_addmodWork( $action, 	$workData, $pmStrings, $kernelStrings, $language );
		if ( PEAR::isError($res) ) {
			$error = $res;
			break;
		}
		$metric->addAction($DB_KEY, $currentUser, 'PM', 'ADDTASK', 'ACCOUNT');
		if ($workData["PW_ENDDATE"])
			$res = pm_closeWork( $workData, $pmStrings, $kernelStrings, $language, $currentUser);
		else
			$res = pm_reopenWork( $workData, $pmStrings, $kernelStrings);			
		
		if ( PEAR::isError($res) ) {
			$error = $res;
			break;
		}
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	} else {
		$ajaxRes = array ("success" => true, "PW_ID" => $workData["PW_ID"]);
	}
	
	print $json->encode($ajaxRes);	
?>
