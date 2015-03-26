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

	$btnIndex = getButtonIndex( array(PM_BTN_CLOSE, PM_BTN_REOPENPROJECT, PM_BTN_DELETEPROJECT), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( $opener, array("folder"=>RS_DELETED, SORTING_COL=>$sorting, "currentPage"=>$currentPage, "P_ID"=>base64_encode($projectData["P_ID"])) );
			break;
		}

		case 1 : {
			$projectData["P_MODIFYUSERNAME"] = getUserName( $currentUser, true );

			$res = pm_reopenProject( prepareArrayToStore($projectData), $pmStrings, $kernelStrings );
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				break;
			}

			redirectBrowser( PAGE_PM_ADDMODPROJECT, array("folder"=>RS_DELETED, OPENER=>$opener, ACTION=>PM_ACTION_MODIFY, SORTING_COL=>$sorting, "P_ID"=>base64_encode($projectData["P_ID"]),  "currentPage"=>$currentPage) );
			break;
		}

		case 2 : {
			$res = pm_deleteProject( $projectData, $pmStrings, $language, $kernelStrings );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				break;
			}

			redirectBrowser( $opener, array("folder"=>RS_DELETED, "P_ID"=>base64_encode($projectData["P_ID"]), SORTING_COL=>$sorting, "currentPage"=>$currentPage) );
			break;
		}
	}

	switch( true ) {
		case( true ) : {
				if ( !isset($projectData["P_ID"]) )
					$projectData["P_ID"] = base64_decode( $P_ID );

				$res = exec_sql( $qr_pm_select_project, $projectData, $projectData, true );
				if ( PEAR::isError($res) ) {
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

					$fatalError = true;
					break;
				}

				$projectData = prepareArrayToDisplay( $projectData );

				$projectData["P_STARTDATE"] = convertToDisplayDate( $projectData["P_STARTDATE"] );
				$projectData["P_ENDDATE"] = convertToDisplayDate( $projectData["P_ENDDATE"] );

				$projectData["P_MODIFYDATETIME"] = convertToDisplayDateTime( $projectData["P_MODIFYDATETIME"], false, true, true );

				$projectData["P_MANAGER"] = getArrUserName( $projectData, true );

				$works_count = db_query_result( $qr_pm_count_project_works, DB_FIRST, $projectData );
				if ( PEAR::isError($works_count) )
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

				$confirmation_string = pm_makeConfirmationString( $works_count, $language, $pmStrings );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor($templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$preproc->assign( PAGE_TITLE, $pmStrings['amp_closedpage_title']);
	$preproc->assign( FORM_LINK,  PAGE_PM_REOPENPROJECT);
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( OPENER, $opener );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "currentPage", $currentPage );

	if ( !$fatalError ) {
		$preproc->assign( "projectData", $projectData );
		$preproc->assign( "confirmation_string", $confirmation_string );
		$preproc->assign( "works_count", $works_count );
	}

	$preproc->display("projectinformation_closed.htm");
?>