<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Issue page script
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
	$attachedFiles = null;

	if ( !isset($action) )
		$action = null;

	define( "ACTION_FORWARD", "forward" );
	define( "ACTION_RETURN", "return" );

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Load issue data
						//
						$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$I_ID), $issuedata, true );
						if ( PEAR::isError( $res ) ) {
							$errorStr = $itStrings[IT_ERR_LOADISSUEDATA];

							$fatalError = true;
							break;
						}

						// Load ITS Data
						//
						$ITS_Data = it_loadITSData( $issuedata["P_ID"], $issuedata["PW_ID"], $issuedata["I_STATUSCURRENT"]	 );
						if ( PEAR::isError($ITS_Data) ) {
							$errorStr = $itStrings[IT_ERR_LOADITS];

							$fatalError = true;
							break;
						}

						// Load project manager ID
						//
						$projmanData = it_getProjManData( $issuedata["P_ID"] );
						if ( PEAR::isError($projmanData) ) {
							$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];

							$fatalError = true;
							break;
						}

						$projMan = isset( $projmanData["U_ID_MANAGER"] ) ? $projmanData["U_ID_MANAGER"] : null;
						$userIsProjman = is_array($projmanData) && $projMan == $currentUser || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $P_ID, $kernelStrings ), UR_TREE_FOLDER  );

						// Load work data
						//
						$res = it_workIsClosed( $issuedata["P_ID"], $issuedata["PW_ID"], $kernelStrings );
						if ( PEAR::isError($res) ) {
							$errorStr = $res->getMessage();

							$fatalError = true;
							break;
						}

						$workIsClosed = $res;

						$issuedata["I_STARTDATE"] = convertToDisplayDate( $issuedata["I_STARTDATE"], true );
						$issuedata["I_CLOSEDATE"] = convertToDisplayDate( $issuedata["I_CLOSEDATE"], true );

						$P_ID = $issuedata["P_ID"];
						$PW_ID = $issuedata["PW_ID"];
						$issuedata["I_PRIORITY"] = $itStrings[$it_issue_priority_names[$issuedata["I_PRIORITY"]]];
						$issuedata["STATUS_COLOR"] = base64_encode(it_getIssueHTMLStyle( $ITS_Data["ITS_COLOR"] ));
						$issuedata["DISPLAY_NUM"] = sprintf( "%s.%s", $PW_ID, $issuedata["I_NUM"] );

						if ( strlen( $issuedata["U_ID_ASSIGNED"] ) )
							$issuedata["U_ID_ASSIGNED"] = base64_encode( getUserName( $issuedata["U_ID_ASSIGNED"], true ) );
						else
							$issuedata["U_ID_ASSIGNED"] = base64_encode( $itStrings['is_notassigned_text'] );

						if ( strlen( $issuedata["U_ID_SENDER"] ) )
							$issuedata["U_ID_SENDER"] = base64_encode( getUserName( $issuedata["U_ID_SENDER"], true ) );
						else
							$issuedata["U_ID_SENDER"] = base64_encode( $itStrings['is_notassigned_text'] );

						// Load a list of allowed transitions
						//
						if ( !$userIsProjman )
							$allowedTransitions = it_getIssueAllowedTransitions( $I_ID );
						else
							$allowedTransitions = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings );

						if ( PEAR::isError($allowedTransitions) ) {
							$errorStr = $itStrings[IT_ERR_LOADISSUEALLOWEDTRANSITIONS];

							$fatalError = true;
							break;
						}

						if ( $userIsProjman ) {
							$allowedTransitions = array_keys( $allowedTransitions );
							array_shift($allowedTransitions);
						}

						$allowedTransitions = it_getStateNames( $P_ID, $PW_ID, $allowedTransitions, $kernelStrings );
						if ( PEAR::isError($allowedTransitions) ) {
							$errorStr = $allowedTransitions->getMessage();

							$fatalError = true;
							break;
						}

						$sendURL = null;
						if ( count($allowedTransitions) ) {
							$issues = array( $issuedata["I_ID"] );
							$issues = base64_encode( serialize($issues) );
							$URLParams = array("issues"=>$issues, "P_ID"=>$issuedata["P_ID"], "PW_ID"=>$issuedata["PW_ID"], OPENER=>PAGE_IT_ISSUE );

							$sendURL = prepareURLStr( PAGE_IT_SENDISSUE, $URLParams );
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

						// Check user rights for this issue
						//
						$rights = it_getUserITRights( $P_ID, $PW_ID, $currentUser, $I_ID );
						if ( PEAR::isError($rights) ) {
							$errorStr = $itStrings[IT_ERR_LOADITRIGHTS];

							$fatalError = true;
							break;
						}

						// Load transition log
						//
						$logData = array();

						$qr = db_query( $qr_it_issuetranslog, $issuedata );
						if ( PEAR::isError($qr) ) {
							$errorStr = $itStrings[IT_ERR_LOADITL];

							$fatalError = true;
							break;
						}

						while ( $row = db_fetch_array( $qr ) ) {
							$currentRecord = $row;

							if ( strlen( $currentRecord["U_ID_ASSIGNED"] ) ) {
								$assignedNameParts = array( "C_LASTNAME"=>$row["A_LASTNAME"], "C_MIDDLENAME"=>$row["A_MIDDLENAME"], "C_FIRSTNAME"=>$row["A_FIRSTNAME"], "C_EMAILADDRESS"=>$row["A_EMAILADDRESS"] );
								$currentRecord["U_ID_ASSIGNED"] = getArrUserName($assignedNameParts, true);
							} else
								$currentRecord["U_ID_ASSIGNED"] = "&lt;<font color=red>".$itStrings['is_na_text']."</font>&gt;";

							$senderNameParts = array( "C_LASTNAME"=>$row["S_LASTNAME"], "C_MIDDLENAME"=>$row["S_MIDDLENAME"], "C_FIRSTNAME"=>$row["S_FIRSTNAME"], "C_EMAILADDRESS"=>$row["S_EMAILADDRESS"] );
							$currentRecord["U_ID_SENDER"] = getArrUserName($senderNameParts, true);
							$currentRecord["MODIFYRECORD"] = strlen( $currentRecord["ITL_OLDCONTENT"] );

							$currentRecord["ITL_DATETIME"] = convertToDisplayDateTime($row["ITL_DATETIME"], false, true, true );

							if ( !$currentRecord["MODIFYRECORD"] ) {
									$currentRecord["STATUS_COLOR"] = base64_encode( it_getIssueHTMLStyle( $row["ITS_COLOR"] ) );
									$currentRecord["DISPLAY_STATUS"] = $currentRecord["ITL_STATUS"];
							}
							else
								$currentRecord["DISPLAY_STATUS"] = sprintf( $itStrings['is_modified_text'] );

							$attachmentsData = listAttachedFiles( base64_decode($currentRecord["ITL_ATTACHMENT"]) );
							$attachedFiles = array();
							if ( count($attachmentsData) ) {
								for ( $i = 0; $i < count($attachmentsData); $i++ ) {
									$fileData = $attachmentsData[$i ];
									$params = array( "I_ID"=>$I_ID, "ITL_ID"=>$currentRecord["ITL_ID"], "fileName"=>base64_encode($fileData["name"]) );
									$fileURL = prepareURLStr( PAGE_IT_GETTRANSITIONFILE, $params );

									$fileSize = formatFileSizeStr( $fileData["size"] );
									$attachedFiles[] = sprintf( "<a href=\"%s\" target=\"_blank\">%s</a> %s",
																		$fileURL, $fileData["screenname"], $fileSize );
								}
								$currentRecord["FILE_DATA"] = sprintf( "<b>%s:</b> %s", $itStrings['is_files_label'], implode( ", ", $attachedFiles ) );
							}

							$logData[] = $currentRecord;
						}
						db_free_result( $qr );

						$count = count($logData);
						for ( $i = 0; $i<count($logData); $i++ )
							if ( $i < ($count-1) )
								$logData[$i]["ACTION"] = $itStrings['is_sentby_label'];
							else
								$logData[$i]["ACTION"] = $itStrings['is_openedby_label'];

						// Load attachments
						//
						$attachmentsData = listAttachedFiles( base64_decode($issuedata["I_ATTACHMENT"]) );
						$attachedFiles = array();
						if ( count($attachmentsData) ) {
							for ( $i = 0; $i < count($attachmentsData); $i++ ) {
								$fileData = $attachmentsData[$i];
								$fileName = $fileData["name"];
								$fileSize = formatFileSizeStr( $fileData["size"] );

								$params = array( "I_ID"=>$I_ID, "fileName"=>base64_encode($fileName) );
								$fileURL = prepareURLStr( PAGE_IT_GETISSUEFILE, $params );

								$attachedFiles[] = sprintf( "<a href=\"%s\" target=\"_blank\">%s (%s)</a>", $fileURL, $fileData["screenname"], $fileSize );
							}
						}
						if ( !count($attachedFiles) )
							$attachedFiles = null;
						else
							$attachedFiles = implode( ", ", $attachedFiles );
		}
	}

	$btnIndex = getButtonIndex( array(BTN_CANCEL, "modifybtn", "deletebtn", "sendbtn", "sendbackbtn"), $_POST );

	if ( $action == ACTION_FORWARD )
		$btnIndex = 3;
	elseif ( $action == ACTION_RETURN )
		$btnIndex = 4;

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_IT_ISSUELIST, array("P_ID"=>$P_ID), sprintf( "I%s", $I_ID ) );

		case 1 : redirectBrowser( PAGE_IT_ADDMODISSUE, array(ACTION=>ACTION_EDIT, "I_ID"=>$I_ID, "P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );

		case 2 : {
					$res = it_deleteIssue( $I_ID, $kernelStrings, $itStrings, $currentUser );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					redirectBrowser( PAGE_IT_ISSUELIST, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
				}

		case 3 : redirectBrowser( PAGE_IT_SENDISSUE, array("I_ID"=>$I_ID, "direction"=>IT_NEXT_TRANSITION, "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>PAGE_IT_ISSUE) );

		case 4 : redirectBrowser( PAGE_IT_SENDISSUE, array("I_ID"=>$I_ID, "direction"=>IT_PREV_TRANSITION, "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>PAGE_IT_ISSUE) );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$issuedata = prepareArrayToDisplay( $issuedata );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['is_page_title'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FORM_LINK, PAGE_IT_ISSUE );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "I_ID", $I_ID );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( "PW_ID", $PW_ID );
	$preproc->assign( OPENER, PAGE_IT_ISSUE );

	$preproc->assign( HELP_TOPIC, "issuelog.htm");

	if ( !$fatalError ) {
		$preproc->assign( "rights", $rights );
		$preproc->assign( "workName", prepareStrToDisplay($workName, true) );
		$preproc->assign( "projName", prepareStrToDisplay($projName, true) );

		$preproc->assign( "issuedata", $issuedata );
		$preproc->assign( "attachedFiles", $attachedFiles );

		$preproc->assign( "ALLOW_DELETE", $userIsProjman || ($ITS_Data["ITS_ALLOW_DELETE"] && !$workIsClosed ? 1 : 0) );
		$preproc->assign( "ALLOW_MODIFY", $userIsProjman || ($ITS_Data["ITS_ALLOW_EDIT"] && !$workIsClosed ? 1 : 0) );

		$preproc->assign( "sendURL", $sendURL );

		$preproc->assign( "logData", $logData );
	}

	$preproc->display( "issue.htm" );
?>