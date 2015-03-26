<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	
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

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_ATTACH, BTN_SAVE, BTN_CANCEL, BTN_DELETEFILES, 'saveaddbtn'), $_POST );

	switch ( $btnIndex ) {
		case 2 : {
					if ( $action == ACTION_NEW )
						redirectBrowser( PAGE_IT_ISSUELIST, array() );
					else
						redirectBrowser( PAGE_IT_ISSUE, array( "I_ID"=>$issuedata["I_ID"], "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "listPW_ID"=>$listPW_ID ) );

					break;
				}
	}

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

						$priority_ids = array_reverse( array_keys( $it_issue_priority_names ) );

						foreach( array_reverse($it_issue_priority_names) as $id => $value ) {
							$priority_names[] = $itStrings[$value];
						}
		}
	}


	//
	// Form handling
	//

	switch ( $btnIndex ) {
		case 0 :
		case 4 :
		case 1 : {
					// Move new attached file
					//
					$res = add_moveAttachedFile( $_FILES['issuefile'],
													base64_decode($PAGE_ATTACHED_FILES),
													WBS_TEMP_DIR, $kernelStrings, true, "is" );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					$PAGE_ATTACHED_FILES = base64_encode($res);

					if ( $btnIndex == 0 )
						break;

					// Make issue attachments list
					//
					$res = makeRecordAttachedFilesList( base64_decode($RECORD_FILES),
														base64_decode($PAGE_DELETED_FILES),
														base64_decode($PAGE_ATTACHED_FILES),
														$kernelStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					$issuedata["I_ATTACHMENT"] = base64_encode($res);

					// Save issue
					//
					$issuedata["P_ID"] = $P_ID;
					$issuedata["PW_ID"] = $PW_ID;

					$recordData = $issuedata;
					$recordData["I_STATUSCURRENT"] = base64_decode($recordData["I_STATUSCURRENT"]);

					print_r($ITS_Data);
					exit;
					$ID = it_addmodIssue( $action, prepareArrayToStore($recordData), $kernelStrings, $itStrings, $ITS_Data, $currentUser );
					if ( PEAR::isError( $ID ) ) {
						$errorStr = $ID->getMessage();

						$errCode = $ID->getCode();
						if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
							$invalidField = $ID->getUserInfo();

						break;
					}

					// Apply attachments
					//
					$attachmentsPath = it_getIssueAttachmentsDir( $P_ID, $PW_ID, $ID );
					$res = applyPageAttachments( base64_decode($PAGE_ATTACHED_FILES),
													base64_decode($PAGE_DELETED_FILES),
													$attachmentsPath, $kernelStrings, $IT_APP_ID );
					if ( PEAR::isError($res) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					// Save user default work
					//
					it_setProjectDefaultTask( $currentUser, $P_ID, $PW_ID, $kernelStrings );

					// Expand work record
					//
					it_saveWorkExpandState( $currentUser, $P_ID, $PW_ID, IT_WORK_EXPANDED, $kernelStrings );

					$anchorName = sprintf( "I%s", $ID );

					if ( $action == ACTION_NEW ) {
						if ( $btnIndex == 1 )
							redirectBrowser( PAGE_IT_ISSUELIST, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "listPW_ID"=>$listPW_ID ), $anchorName );
						else
							if ( $btnIndex == 4 )
								redirectBrowser( PAGE_IT_ADDMODISSUE, array( ACTION=>ACTION_NEW,"P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "listPW_ID"=>$listPW_ID ), $anchorName );
					} else
						redirectBrowser( PAGE_IT_ISSUE, array( "I_ID"=>$issuedata["I_ID"], "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "listPW_ID"=>$listPW_ID ), $anchorName );

					break;
				}
		case 3 : {
					if ( !isset($cbdeletenewfile) )
						$cbdeletenewfile = array();
					if ( !isset($cbdeleterecordfile) )
						$cbdeleterecordfile = array();

					$pageFiles = base64_decode($PAGE_ATTACHED_FILES);
					$delFiles = base64_decode($PAGE_DELETED_FILES);
					$res = deleteAttachedFiles( base64_decode($RECORD_FILES), $delFiles, $pageFiles,
												$cbdeletenewfile, $cbdeleterecordfile, $kernelStrings );

					if ( PEAR::isError( $res ) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					$PAGE_ATTACHED_FILES = base64_encode( $pageFiles );
					$PAGE_DELETED_FILES = base64_encode( $delFiles );

				break;
		}
	}

	//
	// Generating attached files lists
	//
	if ( !$fatalError )
		$attachedFiles = makeAttachedFileList( base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($PAGE_ATTACHED_FILES),
												"cbdeletenewfile",
												"cbdeleterecordfile" );

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	if ( isset($issuedata) )
		$issuedata = prepareArrayToDisplay( $issuedata, array( "I_SUMMARY", "I_DESC" ), isset($edited) && $edited );

	// Assignments list
	//
	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, ($action == ACTION_NEW) ? $itStrings['ami_addissue_title'] : $itStrings['ami_modissue_title'] );
	$preproc->assign( ACTION, $action );
	$preproc->assign( FORM_LINK, PAGE_IT_ADDMODISSUE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "P_ID", $P_ID );

	if ( $action == ACTION_NEW )
		$preproc->assign( HELP_TOPIC, "addissue.htm");
	else
		$preproc->assign( HELP_TOPIC, "modifyissue.htm");

	if ( !$fatalError ) {
		$preproc->assign( "PW_ID", $PW_ID );
		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "workName", prepareStrToDisplay($workName, true) );
		$preproc->assign( "projName", prepareStrToDisplay($projName, true) );

		$preproc->assign( "priority_ids", $priority_ids );
		$preproc->assign( "priority_names", $priority_names );

		$preproc->assign( "assignment_ids", $assignment_ids );
		$preproc->assign( "assignment_names", $assignment_names );

		$preproc->assign( "noAssigneeAllowed", $noAssigneeAllowed );
		$preproc->assign( "action", $action);

		if ( $action == ACTION_NEW ) {
			$preproc->assign( "allowedStatusNames", $allowedStatusNames );
			$preproc->assign( "allowedStatusIDs", $allowedStatusIDs );
		}

		$preproc->assign( "works_ids", array_keys($works) );
		$preproc->assign( "works_names", array_values($works) );

		$preproc->assign( "attachedFiles", $attachedFiles );
		$preproc->assign( "issuedata", $issuedata );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );
	}
	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $kernelStrings )) );

	if ( $action == ACTION_EDIT )
		$preproc->display( "modifyissue.htm" );
	else
		$preproc->display( "addissue.htm" );
?>