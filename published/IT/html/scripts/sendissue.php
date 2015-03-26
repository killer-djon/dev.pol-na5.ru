<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Add/Modify Issue page script
	//

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
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

	switch ( true ) {
		case true : {
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

						// Load page parameters and database records
						//
						if ( isset($STATUS) )
							$STATUS = base64_decode($STATUS);

						if ( $fatalError )
							break;

						$issueList = unserialize( base64_decode($issues) );
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
						if ( !$userIsProjman ) {
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

							if ( in_array( $issuedata['I_STATUSCURRENT'], $res ) && !$userIsProjman ) {
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
							if ( !$userIsProjman )
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

						foreach( $transition_descs as $key=>$value )
							$transition_ids[] = base64_encode($value);

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

						$label = sprintf( $itStrings['fwi_selectedissuesnum_text'], $iCnt );
		}
	}

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL, BTN_ATTACH, BTN_DELETEFILES), $_POST );

	switch ( $btnIndex ) {
		case 2 :
		case 0 : {
					// Move new attached file
					//
					$res = add_moveAttachedFile( $_FILES['transitionfile'],
													base64_decode($PAGE_ATTACHED_FILES),
													WBS_TEMP_DIR, $kernelStrings, true, "is" );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					$PAGE_ATTACHED_FILES = base64_encode($res);

					if ( $btnIndex == 2 )
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

					$senddata["ITL_ATTACHMENT"] = base64_encode($res);

					$senddata["I_ID"] = $I_ID;
					$senddata["U_ID_SENDER"] = $currentUser;
					$senddata["ITL_STATUS"] = $STATUS;

					// Add ITL record
					//
					$data = prepareArrayToStore($senddata, array("ITL_STATUS"));
					$ITL_IDS = it_senMultiIssueStatus( $issueList, $data, $kernelStrings, $itStrings, $currentUser, $ITS_Data, $STATUS );

					if ( PEAR::isError( $ITL_IDS ) ) {
						$errorStr = $ITL_IDS->getMessage();

						$errCode = $ITL_IDS->getCode();
						if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
							$invalidField = $ITL_IDS->getUserInfo();

						break;
					}

					// Apply attachments
					//
					$index = 0;
					$last = count($ITL_IDS) - 1;
					foreach( $ITL_IDS as $I_ID=>$ITL_ID ) {
						$attachmentsPath = it_getITLAttachmentsDir( $P_ID, $PW_ID, $I_ID, $ITL_ID );
						$res = applyPageAttachments( base64_decode($PAGE_ATTACHED_FILES),
														base64_decode($PAGE_DELETED_FILES),
														$attachmentsPath, $kernelStrings, $IT_APP_ID, ($index == $last) );
						if ( PEAR::isError($res) ) {
							$errorStr =  $res->getMessage();

							break;
						}
						$index++;
					}

					redirectBrowser( PAGE_IT_ISSUELIST, array( "P_ID"=>$P_ID ), sprintf( "I%s", $issuedata["I_ID"] ) );

					break;
				}
		case 1 : redirectBrowser( $opener, array( "I_ID"=>$issuedata["I_ID"], "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "listPW_ID"=>$listPW_ID ) );
		case 3 : {
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

	if ( !$fatalError ) {
		//
		// Generating attached files lists
		//
		$attachedFiles = makeAttachedFileList( base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($PAGE_ATTACHED_FILES),
												"cbdeletenewfile",
												"cbdeleterecordfile" );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['fwi_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_SENDISSUE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "I_ID", $I_ID );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( "PW_ID", $PW_ID );
	$preproc->assign( "issues", $issues );
	$issuedata = prepareArrayToDisplay( $issuedata );
	$preproc->assign( "issuedata", $issuedata );
	$preproc->assign( OPENER, $opener );

	$preproc->assign( HELP_TOPIC, "forwardissue.htm");

	if ( !$fatalError ) {
		$senddata = prepareArrayToDisplay( $senddata );

		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "workName", prepareStrToDisplay($workName, true) );
		$preproc->assign( "projName", prepareStrToDisplay($projName, true) );

		$preproc->assign( "iCnt", $iCnt );
		$preproc->assign( "label", $label );

		$preproc->assign( "nextStatusColor", $nextStatusColor );
		$preproc->assign( "assignment_ids", $assignment_ids );
		$preproc->assign( "assignment_names", $assignment_names );

		$preproc->assign( "transition_ids", $transition_ids );
		$preproc->assign( "transition_descs", $transition_descs );

		$preproc->assign( "senddata", $senddata );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		$preproc->assign( "noAssigneeAllowed", $noAssigneeAllowed );
		$preproc->assign( "STATUS", base64_encode($STATUS) );
		$preproc->assign( "attachedFiles", $attachedFiles );

		$preproc->assign( "STATUS", base64_encode($STATUS) );
	}
	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $kernelStrings )) );

	$preproc->display( "sendissue.htm" );
?>