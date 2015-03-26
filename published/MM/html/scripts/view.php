<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;
	
	$returnParams = array ();
	if (!isset($searchString))
		$searchString = "";
		
	if ($searchString)
		$returnParams["searchString"] = $searchString;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 : 
					if ( !isset( $visibleColumnsIDs ) )
						$visibleColumnsIDs = array();

					if ( !strlen($contentLimit) ) {
						$errorStr = $kernelStrings[ERR_REQUIREDFIELDS];
						$invalidField = 'CONTENT_LIMIT';
						break;
					}

					if ( !isIntStr($contentLimit) ) {
						$errorStr = sprintf( $kernelStrings[ERR_INVALIDNUMFORMAT], $contentLimit );
						$invalidField = 'CONTENT_LIMIT';
						break;
					}

					mm_setViewOptions( $currentUser, $visibleColumnsIDs, null, $recordsPerPage, null, $contentLimit, $kernelStrings, $readOnly );
		case 1 :
					redirectBrowser( PAGE_MM_MAILMASTER, $returnParams );
	}

	switch (true) {
		case true : 
					if ( !isset($edited) ) {
						$visibleColumnsIDs = null;
						$viewMode = null;
						$recordsPerPage = null;
						$showSharedPanel = null;
						$contentLimit = null;
						mm_getViewOptions( $currentUser, $visibleColumnsIDs, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $readOnly );
					}

					if ( !isset($visibleColumnsIDs) )
						$visibleColumnsIDs = array();

					$hiddenColumnsIDs = array_diff( $mm_columns, $visibleColumnsIDs );

					$hiddenColumnsNames = array();
					$visibleColumnsNames = array();

					foreach( $hiddenColumnsIDs as $ID )
						$hiddenColumnsNames[] = $mmStrings[$mm_columnNames[$ID]];

					foreach( $visibleColumnsIDs as $ID )
						$visibleColumnsNames[] = $mmStrings[$mm_columnNames[$ID]];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $mmStrings['cv_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_MM_VIEW );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );

	if ( !$fatalError ) {
		$preproc->assign( "recordsPerPage", $recordsPerPage );
		$preproc->assign( "recordsPerPageIDs", array( 10, 20, 30, 40, 50 ) );

		$preproc->assign( "hiddenColumnsIDs", $hiddenColumnsIDs );
		$preproc->assign( "visibleColumnsIDs", $visibleColumnsIDs );
		$preproc->assign( "hiddenColumnsNames", $hiddenColumnsNames );
		$preproc->assign( "visibleColumnsNames", $visibleColumnsNames );
		$preproc->assign( "contentLimit", $contentLimit );
		
		$preproc->assign( "searchString", $searchString );
	}

	$preproc->display( "view.htm" );
?>