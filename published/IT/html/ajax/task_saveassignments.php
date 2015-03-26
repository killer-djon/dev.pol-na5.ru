<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Add/Modify Issue page script
	//

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$ITS_Data = null;
	
	$action = ACTION_EDIT;	
	

	do {
		if ( $fatalError )
			break;
				
		$res = it_modWorkAssignments ($P_ID, $PW_ID, $assignments, $kernelStrings);
		
		if ( PEAR::isError($res) ) {
			$error = $res;
			break;
		}
		
		
		
		$projMan = isset( $projmanData["U_ID_MANAGER"] ) ? $projmanData["U_ID_MANAGER"] : null;
		$userIsProjman = is_array($projmanData) && $projMan == $currentUser || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $P_ID, $kernelStrings ), UR_TREE_FOLDER  );

		$issuedata["DISPLAY_SENDER"] = getUserName($issuedata["U_ID_SENDER"], true);

		if ( $action == ACTION_EDIT )
			$issuedata["DISPLAY_NUM"] = sprintf( "%s.%s", $PW_ID, $issuedata["I_NUM"] );
		
		$issuedata = it_initIssue( $P_ID, $PW_ID, $kernelStrings, $itStrings, $ITS_Data, $currentUser );
		
		if (!empty($STATUS)) 
			$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $STATUS );

		$assignments = it_listAllowedIssueAssignments( $P_ID, $PW_ID, $itStrings, $kernelStrings, $ITS_Data, $currentUser );
		if ( PEAR::isError($projName) ) {
			$errorStr = $itStrings[IT_ERR_LOADISSUEASSIGNMENTS];

			$fatalError = true;
			break;
		}
		
		foreach( $assignments as $aU_ID=>$aU_NAME )
			if ( $aU_NAME == IT_NOASSIGNMENT )
				$assignments[$aU_ID] = $itStrings['ami_notassigned_item'];

		$noAssigneeAllowed = $ITS_Data["ITS_ASSIGNMENTOPTION"] == IT_ASSIGNMENTOPT_NOTAPPLICABLE;

		$assignment_ids = array_keys( $assignments );
		$assignment_names = array_values( $assignments );
		
		$assignArray = array ();
		foreach ($assignments as $cKey => $cValue) {
			$assignArray[] = array ($cKey, $cValue);
		}
		
		
	} while(false);
	
	if (PEAR::isError($error)) {
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	} else {
		$ajaxRes = array ("success" => true, "assignments" => $assignArray);
	}
	
	print $json->encode($ajaxRes);	
?>