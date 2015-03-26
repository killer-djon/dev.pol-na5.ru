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

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	$commonRedirParams = array();
	$commonRedirParams[OPENER] = base64_encode(PAGE_DD_RECYCLED);

	switch ($btnIndex) {
		case 0 :
				$res = dd_setEmailSettingsParams( prepareArrayToStore($emailSettings), $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

		case 1 :
				redirectBrowser( PAGE_DD_RECYCLED, array('curScreen'=>3) );
	}


	switch (true) {
		case true :

				if ( !isset($edited) ) {
					$emailMode = null;
					$emailName=  null;
					$emailAddress = null;
					dd_getEmailSettingsParams( $emailMode, $emailName, $emailAddress, $kernelStrings );

					$emailSettings = array();
					$emailSettings['mode'] = $emailMode;
					$emailSettings['name'] = $emailName;
					$emailSettings['email'] = $emailAddress;
				}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['emp_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_SETEMAILPARAMS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "emailSettings", $emailSettings );
	}

	$preproc->display( "setemailparams.htm" );
?>