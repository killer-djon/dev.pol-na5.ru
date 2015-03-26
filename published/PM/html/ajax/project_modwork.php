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
	$ajaxRes = array ("success" => true);
	
	do {
		
		$workData["P_ID"] = $projectId;
		$workData["PW_ID"] =  $id ;
		
		$workData = db_query_result( $qr_pm_select_work, DB_ARRAY, $workData);
		if ( PEAR::isError($workData) || !$workData) {
			$error = PEAR::raiseError(sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] ));
			break;
		}
		if ($workData["PW_STARTDATE"])
			$workData["PW_STARTDATE"] = convertToDisplayDateNT($workData["PW_STARTDATE"]);
		if ($workData["PW_DUEDATE"])
			$workData["PW_DUEDATE"] = convertToDisplayDateNT($workData["PW_DUEDATE"]);
		if ($workData["PW_ENDDATE"])
			$workData["PW_ENDDATE"] = convertToDisplayDateNT($workData["PW_ENDDATE"]);
		$workData[$field] = $value;
		$workData["P_STATUS"] = pm_compareWithCurDate( $workData["P_ENDDATE"] ) | $workData["U_STATUS"];
		$workData["PW_STATUS"] = pm_compareWithCurDate( $workData["PW_ENDDATE"] ) | $workData["U_STATUS"];
		$workData["PW_COSTESTIMATE"] = formatFloat($workData["PW_COSTESTIMATE"], 2, ".");
		
		if ($workData["PW_COSTESTIMATE"] && !$workData["PW_COSTCUR"]) {
			$workData["PW_COSTCUR"] = $defaultCur;
			$ajaxRes["setCur"] = $defaultCur;
		}
		
		if (!empty($workData["ASGN"]))
			$workData["ASSIGNED"] = split (",", rawurldecode($workData["ASGN"]));
		
		if (!empty($asgn))
			$workData["ASSIGNED"] = split(",", $asgn);
		
		if ($field == "PW_ENDDATE") {
			if ($value)
				$res = pm_closeWork( $workData, $pmStrings, $kernelStrings, $language, $currentUser);
			else
				$res = pm_reopenWork( $workData, $pmStrings, $kernelStrings);
		} else {
			$res = pm_addmodWork( PM_ACTION_MODIFY, prepareArrayToStore($workData), $pmStrings, $kernelStrings, $language );
		}
		if ( PEAR::isError($res) ) {
			$error = $res;
			break;
		}
	} while (false);
	
	if (PEAR::isError($error)) {
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	} 
	
	print $json->encode($ajaxRes);	
?>