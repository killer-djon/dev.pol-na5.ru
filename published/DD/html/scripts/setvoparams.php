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
				$vcSettings['maxVersionNum'] = trim($vcSettings['maxVersionNum']);
				$enabled = strlen($vcSettings['maxVersionNum']);

				$res = dd_setVersionOverrideParams( $enabled, $vcSettings['maxVersionNum'], $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

		case 1 :
				redirectBrowser( PAGE_DD_RECYCLED, array('curScreen'=>2) );
	}


	switch (true) {
		case true :

				if ( !isset($edited) ) {
					$enabled = false;
					$versionNum = 0;

					$res = dd_getVersionOverrideParams( $enabled, $versionNum, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						$fatalError = true;

						break;
					}

					$vcSettings = array();
					if ( $enabled )
						$vcSettings['maxVersionNum'] = $versionNum;
				}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['vc_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_SETVEROVERRIDEPARAMS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "vcSettings", $vcSettings );
	}

	$preproc->display( "setvoparams.htm" );
?>