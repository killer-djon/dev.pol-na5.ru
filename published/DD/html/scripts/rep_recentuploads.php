<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$reportGenerated = false;

	$btnIndex = getButtonIndex( array( BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
					redirectBrowser( PAGE_DD_REPORTS, array() );
	}

	switch (true) {
		case true :
					if ( !isset($edited) ) {
						$repSettings = array();
						$repSettings['type'] = 'days';
						$repSettings['days'] = 10;
					}

					$total = 0;
					$reportData = dd_repRecentUploads( $repSettings, $kernelStrings, $total );
					if ( PEAR::isError($reportData) ) {
						$errorStr = $reportData->getMessage();
						$invalidField = $reportData->getUserInfo();

						break;
					}

					$reportGenerated = true;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['rep_recentuploads_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_REP_RECENTUPLOADS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "reportData", $reportData );
		$preproc->assign( "repSettings", $repSettings );
		$preproc->assign( "reportDataCount", count($reportData) );
		$preproc->assign( "reportGenerated", $reportGenerated );
		$preproc->assign( "total", $total );
	}

	$preproc->display( "rep_recentuploads.htm" );
?>