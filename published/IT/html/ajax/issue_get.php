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

				$worksTotal = it_listActiveProjectUserWorks( $P_ID, $currentUser, null );
				if ( PEAR::isError($worksTotal) ) {
					$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

					$fatalError = true;
					break;
				}

				if ( !count($worksTotal) ) {
					$errorStr = $itStrings['ami_noopentasks_message'];

					$fatalError = true;
					break;
				}

				$works = array();
				foreach( $worksTotal[$P_ID] as $key=>$data )
					if ( !$data['CLOSED'] )
						$works[$key] = sprintf( "%s: %s", $key, strTruncate( $data['PW_DESC'], 45 ) );

				if ( !count($works) ) {
					$errorStr = $itStrings['ami_noopentasks_message'];

					$fatalError = true;
					break;
				}

				if ( !isset($edited) || !$edited ) {
					$PAGE_ATTACHED_FILES = null;
					$PAGE_DELETED_FILES = null;

					if ( isset($PW_ID) && $PW_ID == "GBT" )
						unset($PW_ID);

					if ( !isset($PW_ID) ) {
						$defPW_ID = it_getProjectDefaultTask( $currentUser, $P_ID );
						$work_keys = array_keys($works);

						if ( !strlen($defPW_ID) || !in_array($defPW_ID, $work_keys) )
							$PW_ID = $work_keys[0];
						else
							$PW_ID = $defPW_ID;
					}

					if ( $action == ACTION_NEW ) {
						$issuedata = it_initIssue( $P_ID, $PW_ID, $kernelStrings, $itStrings, $ITS_Data, $currentUser );
						if ( PEAR::isError( $issuedata ) ) {
							$errorStr = $issuedata->getMessage();

							$fatalError = true;
							break;
						}

						$issuedata["U_ID_SENDER"] = $currentUser;
						$issuedata["I_STATUSCURRENT"] = base64_encode($issuedata["I_STATUSCURRENT"]);
						$RECORD_FILES = null;
					} elseif ( $action == ACTION_EDIT ) {
						$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$I_ID), $issuedata, true );
						if ( PEAR::isError( $res ) ) {
							$errorStr = $itStrings[IT_ERR_LOADISSUEDATA];

							$fatalError = true;
							break;
						}

						$issuedata["I_STARTDATE"] = convertToDisplayDate( $issuedata["I_STARTDATE"], true );

						$RECORD_FILES = $issuedata["I_ATTACHMENT"];

						$P_ID = $issuedata["P_ID"];
						$PW_ID = $issuedata["PW_ID"];
						$issuedata["I_STATUSCURRENT"] = base64_encode($issuedata["I_STATUSCURRENT"]);
					}
				}

				$issuedata["DISPLAY_SENDER"] = getUserName($issuedata["U_ID_SENDER"], true);

				if ( $action == ACTION_EDIT )
					$issuedata["DISPLAY_NUM"] = sprintf( "%s.%s", $PW_ID, $issuedata["I_NUM"] );

				if ( (isset($edited) && $edited && $action == ACTION_NEW) || $action == ACTION_EDIT ) {
					$curStatus = base64_decode($issuedata["I_STATUSCURRENT"]);

					$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $curStatus );
					if ( PEAR::isError($ITS_Data) || is_null($ITS_Data) ) {
						$errorStr = $itStrings[IT_ERR_LOADITS];

						$fatalError = true;
						break;
					}
				}

				if ( $action == ACTION_NEW ) {
					if ( !$userIsProjman ) {
						$allowedStatusNames = it_getIssueAllowedTransitions( null, $P_ID, $PW_ID );
						if ( PEAR::isError($allowedStatusNames) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

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

						$allowedStatusNames = $res;
						array_shift($allowedStatusNames);
					}

					$allowedStatusIDs = array();
					foreach( $allowedStatusNames as $statusName )
						$allowedStatusIDs[] = base64_encode($statusName);
				}

				// Check user rights for this issue
				//
				if ( !isset($I_ID) )
					$I_ID = null;

				$res = it_getUserITRights( $P_ID, $PW_ID, $currentUser, $I_ID );
				if ( PEAR::isError($res) ) {
					$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

					$fatalError = true;
					break;
				}

				if ( !$res ) {
					$errorStr = $itStrings[IT_ERR_ISSUERIGHTS];

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

				$priority_ids = array_reverse( array_keys( $it_issue_priority_names ) );

				foreach( array_reverse($it_issue_priority_names) as $id => $value ) {
					$priority_names[] = $itStrings[$value];
				}
				
				$attachmentsData = listAttachedFiles( base64_decode($issuedata["I_ATTACHMENT"]) );
				$attachedFiles = array();
				if ( count($attachmentsData) ) {
					for ( $i = 0; $i < count($attachmentsData); $i++ ) {
						$fileData = $attachmentsData[$i];
						$fileName = $fileData["name"];
						$fileSize = formatFileSizeStr( $fileData["size"] );

						$params = array( "I_ID"=>$I_ID, "fileName"=>base64_encode($fileName) );
						$fileURL = prepareURLStr( PAGE_IT_GETISSUEFILE, $params );

						$attachedFiles[] = array ("name" => $fileData["screenname"], "delname" => base64_encode($fileData["name"]), "url" => $fileURL, "size" => $fileSize);
					}
				}
		}
	}

	
	//
	// Form handling
	//

	
	
	
	if ( isset($issuedata) ) {
		$issuedata = prepareArrayToDisplay( $issuedata, array( "I_SUMMARY", "I_DESC" ), isset($edited) && $edited );
		$issuedata["COMBO_PW_ID"] = $issuedata["PW_ID"];
	}

	
	//$issuedata["desc"] = "Hasd";
	$ajaxRes = array ("success" => true, "data" => $issuedata, "assignments" => $assignArray, "files" => $attachedFiles);
	
	print $json->encode($ajaxRes);	
?>