<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

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
	$assignmentListIDs = null;
	$assignmentListNames = null;

	$transdata["P_ID"] = $P_ID;
	$transdata["PW_ID"] = $PW_ID;

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Check if work exists
						//
						$res = it_workExists( $P_ID, $PW_ID );
						if ( PEAR::isError($res) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						if ( !$res ) {
							$errorStr = $itStrings[IT_ERR_WORKNOTFOUND];
							$fatalError = true;

							break;
						}

						if ( (!isset($edited) || !$edited) )
							if ( $action == ACTION_EDIT ) {
								$transdata["ITS_NUM"] = $ITS_NUM;

								if ( PEAR::isError( exec_sql($qr_it_selectissueworktransition, $transdata, $transdata, true) ) ) {
									$errorStr = $itStrings['dws_errloadtrans_message'];
									$fatalError = true;

									break;
								}

								if ( PEAR::isError( exec_sql($qr_it_selectnextissueworktransitionnum, $transdata, $outdata, true) ) ) {
									$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
									$fatalError = true;

									break;
								}

								if ( isset($outdata["ITS_NUM"]) && strlen($outdata["ITS_NUM"]) )
									$transdata["ITS_PREVNUM"] = $outdata["ITS_NUM"];
								else
									$transdata["ITS_PREVNUM"] = IT_END_STATUS;

								$transdata["PREV_PREVNUM"] = $transdata["ITS_PREVNUM"];
								$transdata['ITS_DEFAULT_DEST_ENCODED'] = base64_encode($transdata['ITS_DEFAULT_DEST']);
							} else {
								$transdata["ITS_PREVNUM"] = IT_END_STATUS;
								$transdata['ITS_DEFAULT_DEST_ENCODED'] = null;
							}

						// Load project and work information
						//
						$projName = it_getProjectName( $P_ID, null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
						if ( PEAR::isError($projName) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						$workDescr = it_getWorkDescription( $P_ID, $PW_ID );
						if ( PEAR::isError($workDescr) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						// "Place before" list
						//
						if ( $action == ACTION_NEW )
							$qr = db_query( $qr_it_selectschematransitions, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
						else
							$qr = db_query( $qr_it_selectschematransitionsexcl, $transdata );

						if ( PEAR::isError( $qr ) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						$prevnum_ids = array();
						$prevnum_names = array();

						while ( $row = db_fetch_array( $qr ) ) {
							if ( $row["ITS_NUM"] == IT_FIRST_STATUS )
								continue;

							$prevnum_ids[count($prevnum_ids)] = $row["ITS_NUM"];
							$prevnum_names[count($prevnum_names)] = $row["ITS_STATUS"];
						}

						db_free_result( $qr );

						if ( (!isset($edited) || !$edited) )
							if ( isset( $placeBefore ) )
								$transdata["ITS_PREVNUM"] = $prevnum_ids[$placeBefore];
							elseif ( $action == ACTION_NEW && count($prevnum_ids) )
								$transdata["ITS_PREVNUM"] = $prevnum_ids[count($prevnum_ids)-1];

						// Assignment option
						//
						$aopt_ids = array();
						$aopt_names = array();

						foreach( $it_assignment_options as $ao_key => $ao_value ) {
							$aopt_ids[count($aopt_ids)] = $ao_key;
							$aopt_names[count($aopt_names)] = sprintf( "&lt;%s&gt;", $itStrings[$ao_value] );
						}

						// Transition color
						//
						$color_ids = array();
						$color_names = array();

						foreach( $it_styles as $cl_key => $cl_value ) {
							$color_ids[] = $cl_key;
							$color_names[] = $itStrings[$cl_value[2]];
						}

						// Assignments list
						//
						$assignmentListIDs[] = null;
						$assignmentListNames[] = $itStrings['dws_notassigned_item'];

						$assignmentListIDs[] = IT_SENDER_OPTION;
						$assignmentListNames[] = sprintf( "&lt;%s&gt;", $itStrings['dws_sender_item'] );

						$assignmentListQr = db_query( $qr_it_selectworkassignments, array( 'P_ID'=>$P_ID, 'PW_ID'=>$PW_ID ) );
						if ( PEAR::isError($assignmentListQr) ) {
							$errorStr = $itStrings[IT_ERR_LOADWORKASSIGNMENTS];
							$fatalError = true;

							break;
						}

						while ( $row = db_fetch_array($assignmentListQr) ) {
							$assignmentListIDs[] = $row["U_ID"];
							$assignmentListNames[] = getArrUserName($row, true);
						}

						db_free_result( $assignmentListQr );

						// Allowed transition list
						//
						$currentNum = ( $action != ACTION_NEW ) ? $transdata['ITS_NUM'] : null;

						$possibleTransitions = it_getPossibleAllowedTransitionList( $P_ID, $PW_ID, $currentNum, $itStrings );
						if ( PEAR::isError($possibleTransitions) ) {
							$errorStr = $possibleTransitions->getMessage();
							$fatalError = true;

							break;
						}

						if ( !isset($edited) || !$edited ) {
							if ( $action == ACTION_EDIT ) {
								$allowedTransitions = $transdata['ITS_ALLOW_DEST'];
								$allowedTransitions = explode(IT_TRANSITIONS_SEPARATOR, $allowedTransitions );
							} else
								$allowedTransitions = array();
						} else {
							if ( !isset($allowedTransitions) || !is_array($allowedTransitions) )
								$allowedTransitions = array();
							else
								foreach( $allowedTransitions as $key=>$value )
									$allowedTransitions[$key] = base64_decode($value);
						}

						$htmlSafeTransitions = array();
						foreach( $possibleTransitions as $key=>$value ) {
							$value["ITS_COLOR"] = it_getIssueHTMLStyle( $value["ITS_COLOR"] );
							$value["ITS_STATUS_ENCODED"] = base64_encode( $value["ITS_STATUS"] );
							$key = prepareStrToDisplay( $key, true );

							$value["CHECKED"] = in_array( $value["ITS_STATUS"], $allowedTransitions );

							$htmlSafeTransitions[$key] = $value;
						}

						$possibleTransitions = $htmlSafeTransitions;

						$isEndState = false;

						if ( $action == ACTION_EDIT ) {
							$isEndState = it_isEndState( $P_ID, $PW_ID, $transdata["ITS_NUM"], $itStrings );
							if ( PEAR::isError($isEndState) ) {
								$errorStr = $isEndState->getMessage();

								$fatalError = true;
								break;
							}
						}
		}
	}

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, "deletebtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
					redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$transdata["P_ID"],
										"PW_ID"=>$transdata["PW_ID"], ACTION=>ACTION_EDIT, "searchCriteria"=>$searchCriteria, OPENER=>$opener ) );

					break;
		}
		case 1 : {
					$transdata = trimArrayData( $transdata );

					$transdata['ITS_ALLOW_DEST'] = implode( IT_TRANSITIONS_SEPARATOR, $allowedTransitions );

					if ( !isset( $transdata['ITS_DEFAULT_DEST_ENCODED'] ) )
						$transdata['ITS_DEFAULT_DEST'] = null;
					else
						$transdata['ITS_DEFAULT_DEST'] = base64_decode( $transdata['ITS_DEFAULT_DEST_ENCODED'] );

					$res = it_addmodIssueTransition( $action, prepareArrayToStore($transdata, array("ITS_ALLOW_DEST", "ITS_DEFAULT_DEST")), $kernelStrings, $itStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						if ( $res->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $res->getUserInfo();

						break;
					}

					redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$transdata["P_ID"],
										"PW_ID"=>$transdata["PW_ID"], ACTION=>ACTION_EDIT, "searchCriteria"=>$searchCriteria, OPENER=>$opener ) );
		}
		case 2 : {
					$stateIssues = it_issueWithStatusExists( $transdata["P_ID"], $transdata["PW_ID"], $transdata["ITS_NUM"] );
					if ( PEAR::isError($stateIssues) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						$fatalError = true;
						break;
					}

					if ( $stateIssues )
						redirectBrowser( PAGE_IT_DELETETRANSITION, array( "P_ID"=>$transdata["P_ID"],
											"PW_ID"=>$transdata["PW_ID"], "ITS_NUM"=>$transdata["ITS_NUM"], ACTION=>ACTION_EDIT, OPENER=>$opener ) );

					$res = it_deleteIssueTransition( $transdata, $kernelStrings, $itStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						break;
					}

					redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$transdata["P_ID"],
										"PW_ID"=>$transdata["PW_ID"], ACTION=>ACTION_EDIT, "searchCriteria"=>$searchCriteria, OPENER=>$opener ) );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['dws_page_title'] );
	$preproc->assign( ACTION, $action );
	$preproc->assign( FORM_LINK, PAGE_IT_ADDMODTRANSITION );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( "PW_ID", $PW_ID );
	$preproc->assign( OPENER, $opener );

	$preproc->assign( HELP_TOPIC, "modifytransition.htm");

	if ( !$fatalError ) {
		$preproc->assign( "prevnum_ids", $prevnum_ids );
		$preproc->assign( "prevnum_names", $prevnum_names );

		$preproc->assign( "aopt_ids", $aopt_ids );
		$preproc->assign( "aopt_names", $aopt_names );

		$preproc->assign( "color_ids", $color_ids );
		$preproc->assign( "color_names", $color_names );

		$preproc->assign( "projName", prepareStrToDisplay($projName, true) );
		$preproc->assign( "workDescr", prepareStrToDisplay($workDescr, true) );

		$preproc->assign( "assignmentListIDs", $assignmentListIDs );
		$preproc->assign( "assignmentListNames", $assignmentListNames );

		$preproc->assign( "prevListItemCount", count($prevnum_ids) );

		$transdata = prepareArrayToDisplay( $transdata, null, isset($edited) && $edited );

		$preproc->assign( "transdata", $transdata );
		$preproc->assign( "allowDelete", $action == ACTION_EDIT );

		$preproc->assign( "possibleTransitions", $possibleTransitions );
		$preproc->assign( "allowedTransitions", $allowedTransitions );

		$preproc->assign( "isEndState", $isEndState );

		if ( isset($allowedTransitionsPacked) )
			$preproc->assign( "allowedTransitionsPacked", $allowedTransitionsPacked );
	}

	$preproc->display( "addmodtransition.htm" );
?>