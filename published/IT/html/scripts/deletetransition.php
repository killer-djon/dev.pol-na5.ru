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

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
					$res = it_transferIssues( $P_ID, $PW_ID, $ITS_NUM, $deletedata["ITS_NUM"], $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}

					$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>$ITS_NUM );

					$res = it_deleteIssueTransition( $params, $kernelStrings, $itStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						break;
					}

					redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "searchCriteria"=>null ) );
		}
		case 1 : redirectBrowser( PAGE_IT_ADDMODTRANSITION, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>$ITS_NUM, ACTION=>ACTION_EDIT, "searchCriteria"=>null ) );
	}

	switch ( true ) {
		case true : {
					$stateIssues = it_issueWithStatusExists( $P_ID, $PW_ID, $ITS_NUM );
					if ( PEAR::isError($stateIssues) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						$fatalError = true;
						break;
					}

					$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>$ITS_NUM );

					$transdata = db_query_result( $qr_it_select_transition_byitsnum, DB_ARRAY, $params );
					if ( PEAR::isError($transdata) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

					$htmlStatus = it_getIssueHTMLStyle( $transdata["ITS_COLOR"] );
					$messageText = sprintf( $itStrings['dt_form_text'], $stateIssues, $htmlStatus.prepareStrToDisplay($transdata["ITS_STATUS"], true)."</b></font>" );

					$transitionSchema = it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings );
					if ( PEAR::isError($transitionSchema) ) {
						$errorStr = $transitionSchema->getMessage();

						$fatalError = true;
						break;
					}

					$statusNumber = count($transitionSchema);
					$stateIDs = array();
					$stateNames = array();

					for ( $i = 1; $i < $statusNumber; $i++ ) {
						if ( $ITS_NUM == $transitionSchema[$i]["ITS_NUM"] )
							continue;

						$stateIDs[] = $transitionSchema[$i]["ITS_NUM"];
						$stateNames[] = $transitionSchema[$i]["ITS_STATUS"];
					}

		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['df_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_DELETETRANSITION );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "P_ID", $P_ID ); 
	$preproc->assign( "PW_ID", $PW_ID ); 
	$preproc->assign( "ITS_NUM", $ITS_NUM ); 

	$preproc->assign( HELP_TOPIC, null );

	if ( !$fatalError ) {
		$preproc->assign( "messageText", $messageText ); 
		$preproc->assign( "stateIDs", $stateIDs ); 
		$preproc->assign( "stateNames", $stateNames ); 
	}

	$preproc->display( "deletetransition.htm" );
?>