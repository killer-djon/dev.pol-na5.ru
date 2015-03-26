<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
		case 1 : redirectBrowser( PAGE_IT_ISSUELIST, array() );
	}

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	switch ( true ) {
		case true :
					$issues_decoded = unserialize( base64_decode($issues) );
					$selectedNum = count($issues_decoded);

					$printMode = 0;
					if ( !$selectedNum )
						$printMode = 1;

					$params = array();
					$params['printMode'] = $printMode;
					$params['issues'] = $issues;
					$params['P_ID'] = $P_ID;
					$params['curPW_ID'] = $PW_ID;

					$printURL = prepareURLStr( sprintf( "../../reports/%s", PAGE_IT_ISSUELIST ), $params );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['pr_page_title'] );
	$preproc->assign( FORM_LINK, $printURL );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "selectedNum", $selectedNum );
		$preproc->assign( "P_ID", $P_ID );
		$preproc->assign( "PW_ID", $PW_ID );
		$preproc->assign( "printMode", $printMode );
		$preproc->assign( "issues", $issues );
	}

	$preproc->display( "print.htm" );

?>