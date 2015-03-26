<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Authorization
	//

	$SCR_ID = "WL";
	$errorStr = null;
	$fatalError = false;

	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	$invalidField = null;

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_PM_ADDMODWORK, array(SORTING_COL=>$sorting, ACTION=>PM_ACTION_MODIFY, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "opener"=>$opener, "list_action"=>$list_action) );

			break;
		}
		case 1 : {
			$res = pm_closeWork( prepareArrayToStore($workData), $pmStrings, $kernelStrings, $language, $currentUser );
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				$errCode = $res->getCode();

				if ( in_array( $errCode, array( ERRCODE_INVALIDFIELD, ERRCODE_INVALIDDATE, PM_ERRCODE_STARTEXCEND, PM_ERRCODE_ENDEXCCURR ) ) )
					$invalidField = $res->getUserInfo();

				break;
			}

			redirectBrowser( $opener, array(SORTING_COL=>$sorting, "P_ID"=>base64_encode($workData["P_ID"]), "PW_ID"=>base64_encode($workData["PW_ID"]), "firstIndex"=>$firstIndex, "list_action"=>$list_action) );
		}
	}

	switch ( true ) {
		case ( true ) :
			if ( !isset($edited) || !$edited ) {
				$workData["P_ID"] = base64_decode( $P_ID );
				$workData["PW_ID"] = base64_decode( $PW_ID );

				$res = pm_workExists( $workData );
				if ( PEAR::isError($res) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

					$fatalError = true;
					break;
				}

				if ( !$res ) {
					$errorStr = $pmStrings['ct_notask_message'];

					$fatalError = true;
					break;
				}

				$workData["PW_ENDDATE"] = displayDate( convertTimestamp2Local( time() ) );
			}

			// Check if there is other application records linked to this project
			//
			$params = array( "P_ID"=>$workData["P_ID"], "PW_ID"=>$workData["PW_ID"], );

			if ( PEAR::isError( $otherAppComments = handleEvent( $PM_APP_ID, "onCompleteTaskRequest", $params, $language, true, true ) ) ) {
				$fatalError = true;
				$errorStr = $otherAppComments->getMessage();

				break;
			}
	}

	$preproc = new php_preprocessor ($templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$preproc->assign( PAGE_TITLE, $pmStrings['ct_page_title']);
	$preproc->assign( FORM_LINK, PAGE_PM_FINISHWORK );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "opener", $opener );
	$preproc->assign( "firstIndex", $firstIndex );

	if ( isset($list_action) )
		$preproc->assign( "list_action", $list_action );
	else
		$preproc->assign( "list_action", null );

	if ( !$fatalError ) {
		$preproc->assign( "workData", $workData );

		$preproc->assign( "otherAppComments", $otherAppComments );
		$preproc->assign( "otherAppCommentsNum", count($otherAppComments) );
	}

	$preproc->display("completework.htm");
?>