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
	
	function findDefaultTransition( $name, $list )
	{
		if ( in_array( $name, $list ) )
			return $name;
		else
			if ( count($list) )
				return $list[0];
			else
				return null;
	}	

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$ITS_Data = null;
	
	$action = ACTION_EDIT;	
	

	switch ( true ) {
		case true:
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
				$userTaskRights = UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $P_ID, $kernelStrings ), UR_TREE_WRITE  );

				if ( $fatalError )
					break;

				$issueList = $issues;
				$iCnt = count($issueList);
				
				if ( !$iCnt ) {
					$errorStr = $itStrings['fwi_issuesnotfound_message'];
					$fatalError = true;

					break;
				}

				$PW_ID = it_getIssuesTask( $issueList, $kernelStrings, $itStrings, $P_ID );
				if ( PEAR::isError($PW_ID) ) {
					$fatalError = true;
					$errorStr = $PW_ID->getMessage();

					break;
				}
				
				$I_ID = $issueList[0];

				$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$I_ID), $issuedata, true );
				if ( PEAR::isError( $res ) ) {
					$errorStr = $itStrings[IT_ERR_LOADISSUEDATA];
					$fatalError = true;

					break;
				}

				// Check user rights for this issue
				//
				$res = it_getUserITRights( $P_ID, $PW_ID, $currentUser, $I_ID );
				if ( PEAR::isError($res) ) {
					$errorStr = $itStrings[IT_ERR_LOADITRIGHTS];
					$fatalError = true;

					break;
				}

				if ( !$res ) {
					$errorStr = $itStrings[IT_ERR_ISSUERIGHTS];
					$fatalError = true;

					break;
				}

				$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $issuedata["I_STATUSCURRENT"] );
				if ( PEAR::isError($ITS_Data) || is_null($ITS_Data) ) {
					$errorStr = $itStrings[IT_ERR_LOADITS];
					$fatalError = true;

					break;
				}

				$issuedata["STATUS_COLOR"] = base64_encode(it_getIssueHTMLStyle( $ITS_Data["ITS_COLOR"] ));

				// Load allowed transitions
				//
				if ( !($userIsProjman ||  $userTaskRights)) {
					$allowedTransitions = it_getIssueAllowedTransitions( $I_ID );
					if ( PEAR::isError($allowedTransitions) ) {
						$errorStr = $itStrings[IT_ERR_LOADISSUEALLOWEDTRANSITIONS];
						$fatalError = true;

						break;
					}
				} else {
					$res = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings, false, true );
					if ( PEAR::isError($res) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
						$fatalError = true;

						break;
					}

					if ( in_array( $issuedata['I_STATUSCURRENT'], $res ) && !($userIsProjman | $userTaskRights) ) {
						foreach( $res as $key=>$value )
							if ( $value == $issuedata['I_STATUSCURRENT'] ) {
								unset( $res[$key] );
								break;
							}
					}

					$allowedTransitions = $res;
					array_shift($allowedTransitions);
				}

				if ( !count($allowedTransitions) ) {
					$errorStr = $itStrings['fwi_invissuesforbsend_message'];
					$fatalError = true;

					break;
				}
				
				if ( !isset($STATUS) )
					if ( !($userIsProjman || $userTaskRights) )
						$STATUS = findDefaultTransition( $ITS_Data['ITS_DEFAULT_DEST'], $allowedTransitions );
					else {
						$userAllowedTransitions = it_getIssueAllowedTransitions( $I_ID );
						if ( PEAR::isError($userAllowedTransitions) ) {
							$errorStr = $itStrings[IT_ERR_LOADISSUEALLOWEDTRANSITIONS];
							$fatalError = true;

							break;
						}

						if ( count($userAllowedTransitions) )
							$STATUS = findDefaultTransition( $ITS_Data['ITS_DEFAULT_DEST'], $userAllowedTransitions );
						else
							$STATUS = $allowedTransitions[0];
					}

				$senddata["ITL_STATUS"] = $STATUS;

				// Load new status data
				//
				$nextITS_Data = it_loadITSData( $P_ID, $PW_ID, $senddata["ITL_STATUS"] );
				if ( PEAR::isError($nextITS_Data) || is_null($nextITS_Data) ) {
					$errorStr =  $itStrings[IT_ERR_LOADITS];
					$fatalError = true;

					break;
				}

				$workName = it_getWorkDescription( $P_ID, $PW_ID );
				if ( PEAR::isError($workName) ) {
					$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

					$fatalError = true;
					break;
				}

				$projName = it_getProjectName( $P_ID, null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
				if ( PEAR::isError($projName) ) {
					$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

					$fatalError = true;
					break;
				}

				$nextStatusColor = base64_encode(it_getIssueHTMLStyle( $nextITS_Data["ITS_COLOR"] ));

				$transition_descs = array_values( $allowedTransitions );
				$transition_ids = array();

				foreach( $transition_descs as $key=>$value ) {
					$transition_ids[] = base64_encode($value);
					$transitionsArray[] = array ($value);
				}

				// Load allowed assignments list
				//
				if ( !is_null($ITS_Data) ) {
					$assignments = it_listAllowedIssueAssignments( $P_ID, $PW_ID, $itStrings, $kernelStrings, $nextITS_Data, $issuedata["U_ID_SENDER"] );
					if ( PEAR::isError($assignments) ) {
						$errorStr = $itStrings[IT_ERR_LOADISSUEASSIGNMENTS];

						$fatalError = true;
						break;
					}
					foreach( $assignments as $aU_ID=>$aU_NAME )
						if ( $aU_NAME == IT_NOASSIGNMENT )
							$assignments[$aU_ID] = $itStrings['fwi_notassigned_item'];

					$assignment_ids = array_keys( $assignments );
					$assignment_names = array_values( $assignments );
				}

				if ( !isset($edited) || !$edited ) {
					$RECORD_FILES = null;
					$PAGE_ATTACHED_FILES = null;
					$PAGE_DELETED_FILES = null;

					if ( $nextITS_Data["ITS_ASSIGNMENTOPTION"] == IT_ASSIGNMENTOPT_NOTAPPLICABLE )
						$senddata["U_ID_ASSIGNED"] = null;
					else {
						if ( $nextITS_Data["U_ID_ASSIGNED"] == IT_SENDER_OPTION )
							$senddata["U_ID_ASSIGNED"] = $issuedata["U_ID_SENDER"];
						else
							$senddata["U_ID_ASSIGNED"] = $nextITS_Data["U_ID_ASSIGNED"];
					}
				}

				$noAssigneeAllowed = $nextITS_Data["ITS_ASSIGNMENTOPTION"] == IT_ASSIGNMENTOPT_NOTAPPLICABLE;
				
				foreach ($assignments as $cKey => $cValue)
					$assignArray[] = array ($cKey, $cValue);
				
				$label = sprintf( $itStrings['fwi_selectedissuesnum_text'], $iCnt );
	}

	
	//
	// Form handling
	//

	
	
	
	if ( isset($issuedata) ) {
		$issuedata = prepareArrayToDisplay( $issuedata, array( "I_SUMMARY", "I_DESC" ), isset($edited) && $edited );
		$issuedata["COMBO_PW_ID"] = $issuedata["PW_ID"];
	}

	$ajaxRes = array ("success" => true, "data" => $senddata, "assignments" => $assignArray, "transitions" => $transitionsArray );
	
	print $json->encode($ajaxRes);	
?>