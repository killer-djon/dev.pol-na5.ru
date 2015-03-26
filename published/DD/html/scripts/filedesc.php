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
		case 0 : redirectBrowser( PAGE_DD_CATALOG, array() );
	}

	switch (true) {
		case true : 
					$curDL_ID = base64_decode( $DL_ID );

					$fileData = dd_getDocumentData( $curDL_ID, $kernelStrings );
					if( PEAR::isError( $fileData ) ) {
						$errorStr = $fileData->getMessage();
						$fatalError = true;
						break;
					}

					$fileData = dd_processFileListEntry($fileData);
					$file = (array)$fileData;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['fd_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_MODIFYFILE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "DL_ID", $DL_ID );

	if ( !$fatalError ) {
		$preproc->assign( "file", $file );
	}

	$preproc->display( "filedesc.htm" );
?>