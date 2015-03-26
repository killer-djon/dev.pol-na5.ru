<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );

	require_once( WBS_DIR."/published/IT/it.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

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
	

	switch ( true ) {
		case true : {
				if ( $fatalError )
					break;

				// Check if user a project manager
				//
				$projmanData = it_getProjManData( $P_ID );
				if ( PEAR::isError($projmanData) ) {
					$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];

					$fatalError = true;
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
				
				if ( !$userIsProjman ) {
					$allowedStatusNames = it_getIssueAllowedTransitions( null, $P_ID, $PW_ID );
				} else {
					$allowedStatusNames = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings, false, true );
					array_shift($allowedStatusNames);
				}
				$statusesArray = array ();
				foreach ($allowedStatusNames as $cKey => $cValue) {
					$statusesArray[] = array ($cValue);
				}
				
				$workData = it_getWorkData($P_ID, $PW_ID, $kernelStrings);
				if ( PEAR::isError($error = $workData) )
					break;
				
				$workData["assignments"] = it_getWorkAssignments ($P_ID, $PW_ID);
				$canManageUsers = ($P_ID == 0) ? aa_canManageUsers( $currentUser ) : $userIsProjman;
		}
	}
	
	//$issuedata["desc"] = "Hasd";
	if (!PEAR::isError($error)) {
		$ajaxRes = array ("success" => true, "assignments" => $assignArray, "statuses" => $statusesArray, "data" => $ITS_Data, "workData" => $workData, "canManageUsers" => $canManageUsers);
	} else {
		$ajaxRes = array ("success" => false, "errorStr" => $error->getMessage());
	}
	
	print $json->encode($ajaxRes);	
?>