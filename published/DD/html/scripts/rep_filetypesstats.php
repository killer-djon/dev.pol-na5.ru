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

	$btnIndex = getButtonIndex( array( BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
					redirectBrowser( PAGE_DD_REPORTS, array() );
	}

	switch (true) {
		case true :

			$totals = null;
			$reportData = dd_repFileTypesStats( $kernelStrings, $ddStrings, $totals );
			if ( PEAR::isError($reportData) ) {
				$errorStr = $reportData->getMessage();
				$fatalError = true;

				break;
			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['rep_filetypestats_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_REP_FILETYPESSTATS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "reportData", $reportData );
		$preproc->assign( "reportDataCount", count($reportData) );
		$preproc->assign( "totals", $totals );
	}

	$preproc->display( "rep_filetypesstats.htm" );
?>