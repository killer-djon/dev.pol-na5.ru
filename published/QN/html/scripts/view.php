<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
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

					$contentLimit = trim( $contentLimit );

					if ( strlen( $contentLimit ) && ( !isIntStr($contentLimit) || !ereg("^[0-9]+$", $contentLimit ) ) )
					{
						$errorStr = sprintf( $kernelStrings[ERR_INVALIDNUMFORMAT], $contentLimit );
						$invalidField = 'CONTENT_LIMIT';
						break;
					}

					qn_setViewOptions( $currentUser, $visibleColumnsIDs, null, $recordsPerPage, null, $contentLimit, $kernelStrings, $readOnly );

		case 1 :
					redirectBrowser( PAGE_QN_QUICKNOTES, $returnParams);
	}

	switch (true) {
		case true :
					if ( !isset($edited) ) {
						$visibleColumnsIDs = null;
						$viewMode = null;
						$recordsPerPage = null;
						$showSharedPanel = null;
						$contentLimit = null;
						qn_getViewOptions( $currentUser, $visibleColumnsIDs, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $readOnly );
					}

					if ( !isset($visibleColumnsIDs) )
						$visibleColumnsIDs = array();

					$hiddenColumnsIDs = array_diff( $qn_columns, $visibleColumnsIDs );

					$hiddenColumnsNames = array();
					$visibleColumnsNames = array();

					foreach( $hiddenColumnsIDs as $ID )
						$hiddenColumnsNames[] = $qnStrings[$qn_columnNames[$ID]];

					foreach( $visibleColumnsIDs as $ID )
						$visibleColumnsNames[] = $qnStrings[$qn_columnNames[$ID]];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qnStrings['cv_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QN_VIEW );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qnStrings", $qnStrings );

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