<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	
	require_once( WBS_DIR."/published/PM/pm.php" );
	
	$fatalError = false;
	$error = null;
	$errorStr = null;
	$SCR_ID = "WL";
	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );
	
	$nodes = array ();
	
	$access = null;
	$hierarchy = null;
	$deletable = null;
	$statisticsMode = false;
	
	$project_works = array();
	
	writeUserCommonSetting( $currentUser, 'showCompleteTasks', $showComplete, $kernelStrings );

	$projectData = array ("P_ID" => $projectId);
	
	$errorStr = null;
	do {
		$query = ($showComplete) ? $qr_pm_select_project_asng : $qr_pm_select_project_asng_notcomplete;
		
		$qr = db_query($query , $projectData);
		
		if ( PEAR::isError($qr) ) {
			$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

			$fatalError = true;
			break;
		}

		while ( $row = db_fetch_array($qr) ) {
			$curRecord = prepareArrayToDisplay( $row );
			$curRecord["PW_STARTDATE"] = convertToDisplayDate($curRecord["PW_STARTDATE"]);
			$curRecord["PW_DUEDATE"] = convertToDisplayDate($curRecord["PW_DUEDATE"]);
			$curRecord["PW_ENDDATE"] = convertToDisplayDate($curRecord["PW_ENDDATE"]);
			$curRecord["PW_BILLABLE"] = $curRecord["PW_BILLABLE"] ? true : false;
			$curRecord["ASGN"] = $curRecord["U_ID"];
			$project_works[] = $curRecord;
		}

		@db_free_result($qr);
	} while (false);
	
	if ($errorStr)
		print $json->encode(array ("success" => false, "errorStr" => $errorStr));	
	else
		print $json->encode($project_works);	
	
?>