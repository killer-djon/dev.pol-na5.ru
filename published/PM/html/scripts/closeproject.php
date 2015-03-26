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
			redirectBrowser( PAGE_PM_WORKLIST, array("folder"=>$folder, OPENER=>$opener, ACTION=>PM_ACTION_MODIFY, SORTING_COL=>$sorting, "P_ID"=>base64_encode($projectData["P_ID"]), "currentPage"=>$currentPage) );
			//redirectBrowser( PAGE_PM_ADDMODPROJECT, array("folder"=>$folder, OPENER=>$opener, ACTION=>PM_ACTION_MODIFY, SORTING_COL=>$sorting, "P_ID"=>base64_encode($projectData["P_ID"]), "currentPage"=>$currentPage) );

			break;
		}
		case 1 : {
			$projectData["P_MODIFYUSERNAME"] = getUserName( $currentUser, true );

			if ( !isset($closeMode) )
				$closeMode = 0;

			$res = pm_closeProject( prepareArrayToStore($projectData), $pmStrings, $kernelStrings, $language, $currentUser, $closeMode );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				$errorCode = $res->getCode();

				if ( in_array($errorCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDDATE, PM_ERRCODE_STARTEXCEND, PM_ERRCODE_ENDEXCCURR)) )
					$invalidField = $res->getUserInfo();

				break;
			}

			redirectBrowser( $opener, array("folder"=>$folder, SORTING_COL=>$sorting, "currentPage"=>$currentPage, 'P_ID'=>base64_encode($projectData["P_ID"])) );
		}
	}

	switch( true ) {
		case ( true ) : {
			if ( !isset($edited) || !$edited ) {
				$projectData["P_ID"] = base64_decode( $P_ID );

				$res = pm_projectExists( $projectData );
				if ( PEAR::isError($res) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

					$fatalError = true;
					break;
				}

				if ( !$res ) {
					$errorStr = sprintf( $pmStrings['cmp_errproj_message'] );

					$fatalError = true;
					break;
				}

				$closeMode = 0;

				$projectData["P_ENDDATE"] = displayDate( convertTimestamp2Local( time() ) );
			}

			// Check if there is open works
			//
			$openWorksExists = db_query_result($qr_pm_select_project_active_works_count, DB_FIRST, $projectData );
			if ( PEAR::isError( $openWorksExists ) ) {
				$fatalError = true;

				$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
				break;
			}

			// Check if there is other application records linked to this project
			//
			$params = array( "P_ID"=>$projectData["P_ID"] );

			if ( PEAR::isError( $otherAppComments = handleEvent( $PM_APP_ID, "onCompleteRequest", $params, $language, true, true ) ) ) {
				$fatalError = true;
				$errorStr = $otherAppComments->getMessage();

				break;
			}
		}
	}

	$preproc = new php_preprocessor($templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );
	$preproc->assign( PAGE_TITLE, $pmStrings['cmp_page_title']);
	$preproc->assign( FORM_LINK,  PAGE_PM_CLOSEPROJECT );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( OPENER, $opener );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "folder", $folder );
	$preproc->assign( "currentPage", $currentPage );

	if ( !$fatalError ) {
		$preproc->assign( "projectData", $projectData );
		$preproc->assign( "openWorksExists", $openWorksExists );

		$preproc->assign( "otherAppComments", $otherAppComments );
		$preproc->assign( "otherAppCommentsNum", count($otherAppComments) );

		if ( $openWorksExists )
			$preproc->assign( "closeMode", $closeMode );
	}

	$preproc->display("closeproject.htm");
?>