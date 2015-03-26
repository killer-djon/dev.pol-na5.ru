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
	$contactCount = 0;

	$targetFolder = base64_decode( $DF_ID );
	
	$returnParams = array ();
	if (!isset($searchString))
		$searchString = "";
		
	if ($searchString)
		$returnParams["searchString"] = $searchString;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :					
					if ( strlen($restrictDescLen) && !isIntStr($restrictDescLen) ) {
						$invalidField = 'restrictDescLen';
						$errorStr = sprintf($kernelStrings[ERR_INVALIDNUMFORMAT], $restrictDescLen);
						break;
					}

					if ( !isset($visibleColumnsIDs) )
						$visibleColumnsIDs = array();

					if ( !isset($displayIcons) )
						$displayIcons = 0;

					dd_setViewOptions( $currentUser, 
										$visibleColumnsIDs, null, 
										$recordsPerPage, 
										null, 
										$displayIcons, 
										$folderViewMode,
										$restrictDescLen,
										$targetFolder, 
										$kernelStrings, 
										$readOnly );
		case 1 :
					redirectBrowser( PAGE_DD_CATALOG, $returnParams );
	}

	switch (true) {
		case true : 
					if ( !isset($edited) ) {
						$visibleColumnsIDs = null;
						$viewMode = null;
						$recordsPerPage = null;
						$showSharedPanel = null;
						$displayIcons = null;
						$folderViewMode = null;
						$restrictDescLen = null;

						dd_getViewOptions( $currentUser, 
											$visibleColumnsIDs, 
											$viewMode, 
											$recordsPerPage, 
											$showSharedPanel, 
											$displayIcons,
											$folderViewMode,
											$restrictDescLen,
											$targetFolder,
											$kernelStrings,
											$readOnly );

						$hiddenColumnsIDs = array_diff( $dd_columns, $visibleColumnsIDs );
					}

					$hiddenColumnsNames = array();
					$visibleColumnsNames = array();

					foreach( $hiddenColumnsIDs as $ID )
						$hiddenColumnsNames[] = $ddStrings[$dd_columnNames[$ID]];

					foreach( $visibleColumnsIDs as $ID )
						$visibleColumnsNames[] = $ddStrings[$dd_columnNames[$ID]];

					if ( !isset($displayIcons) )
						$displayIcons = 0;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['cv_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_VIEW );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "hiddenColumnsIDs", $hiddenColumnsIDs );
		$preproc->assign( "visibleColumnsIDs", $visibleColumnsIDs );
		$preproc->assign( "hiddenColumnsNames", $hiddenColumnsNames );
		$preproc->assign( "visibleColumnsNames", $visibleColumnsNames );

		$preproc->assign( "displayIcons", $displayIcons );
		$preproc->assign( "restrictDescLen", $restrictDescLen );
		$preproc->assign( "searchString", $searchString );

		$preproc->assign( "recordsPerPage", $recordsPerPage );
		$preproc->assign( "recordsPerPageIDs", array( 10, 20, 30, 40, 50 ) );

		$preproc->assign( "folderViewMode", $folderViewMode );
		$preproc->assign( "DF_ID", $DF_ID );
	}

	$preproc->display( "view.htm" );
?>