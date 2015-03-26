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

	$btnIndex = getButtonIndex( array(BTN_CANCEL), $_POST );

	switch ($btnIndex) {
		case 0 :
			redirectBrowser( PAGE_DD_CATALOG, array() );
	}

	switch (true) {
		case true :

				$fileList = unserialize( base64_decode($doclist) );
				$checkedOutFiles = array();
				$invalidFiles = array();

				foreach ( $fileList as $DL_ID ) {
					$res = dd_changeFileCheckStatus( $currentUser, $DL_ID, DD_CHECK_OUT, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}

					if ( $res )
						$checkedOutFiles[] = dd_getDocumentData( $DL_ID, $kernelStrings );
					else
						$invalidFiles[] = dd_getDocumentData( $DL_ID, $kernelStrings );
				}

				foreach ( $checkedOutFiles as $key=>$value ) {
					$value = dd_processFileListEntry( $value );
					$checkedOutFiles[$key] = $value;
				}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['co_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_CHECKOUT );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "doclist", $doclist );

		$preproc->assign( "checkedOutFiles", $checkedOutFiles );
		$preproc->assign( "showCheckedOutFiles", count($checkedOutFiles) );
		$preproc->assign( "invalidFiles", $invalidFiles );
		$preproc->assign( "showInvalidFiles", count($invalidFiles) );
	}

	$preproc->display( "checkout.htm" );
?>