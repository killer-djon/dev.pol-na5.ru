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

	$btnIndex = getButtonIndex( array(BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
		case 0 :
				redirectBrowser( $opener, array("currentPage"=>$currentPage, "P_ID"=>$P_ID) );
	}

	switch ( true ) {
		case ( true ) :
				$P_ID = base64_decode( $P_ID );

				$projectData = pm_getProjectData( $P_ID, $kernelStrings );
				if ( PEAR::isError($projectData) ) {
					$fatalError = true;
					$errorStr = $projectData->getMessage();

					break;
				}

				$projectData['P_MODIFYDATETIME'] = convertToDisplayDate( $projectData['P_MODIFYDATETIME'], false, true, true );
				$projectData['P_MANAGER'] = getUserName( $projectData['U_ID_MANAGER'], true );

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$preproc->assign( PAGE_TITLE, $pmStrings['amp_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_PM_VIEWPROJECT );
	$preproc->assign( ACTION, $action );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( OPENER, $opener );
	$preproc->assign( "P_ID", $P_ID );
	$preproc->assign( "currentPage", $currentPage );

	if ( isset($projectData) )
		$preproc->assign( "projectData", $projectData );
	else
		$preproc->assign( "projectData", null );

	$preproc->display("viewproject.htm");
?>