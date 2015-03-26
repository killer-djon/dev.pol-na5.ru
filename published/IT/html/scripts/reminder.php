<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;	
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$reportData = array();

	switch ( true ) {
		case true : {
			$issuesUnpacked = unserialize( base64_decode( $issues ) );
			$ids = @array_keys( $issuesUnpacked );
			
			$userIssueData = it_getRemindUserIssueCounts( $ids, $kernelStrings );
			if ( PEAR::isError( $userIssueData ) ) {
				$errorStr = $userIssueData->getMessage();

				$fatalError = true;
				break;
			}

			$users = array_keys( $userIssueData );
		}
	}

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_IT_ISSUELIST, array() );

			break;
		}
		case 1 : {
			$sendData = prepareArrayToStore( $data );
			$comment = $sendData['comment'];
			if ( !isset($headersonly) )
				$headersonly = false;

			$res = it_sendRemindNotification( $ids, $comment, $headersonly, $kernelStrings, $itStrings, $currentUser );
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				break;
			}

			redirectBrowser( PAGE_IT_ISSUELIST, array() );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( PAGE_TITLE, $itStrings['rem_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_REMINDER );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( "issues", $issues );
	$preproc->assign( HELP_TOPIC, "reminder.htm");

	if ( !$fatalError ) {
		$preproc->assign( "userIssueData", $userIssueData );
		if ( isset($headersonly) )
			$preproc->assign( "headersonly", $headersonly );

		if ( isset($data) )
			$preproc->assign( "data", $data );
	}

	$preproc->display( "reminder.htm" );
?>