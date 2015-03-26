<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";
	$projectExists = true;

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	//
	// Form handling
	//

	$btnIndex = getButtonIndex( array(BTN_RETURN), $_POST );

	switch ( $btnIndex ) {
		case 0 : redirectBrowser( PAGE_IT_ISSUELIST, array( "P_ID"=>$_SESSION['IT_LIST_PID'] ) );
	}

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	switch ( true ) {
		case true : 
					// Template list
					//
					$qr = db_query( $qr_it_templatelist );
					if ( PEAR::isError( $qr ) ) {
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
						$fatalError = true;

						break;
					}

					$templateList = array();

					while ( $row = db_fetch_array( $qr ) ) {
						$row["ITT_NAME"] = prepareStrToDisplay( $row["ITT_NAME"], true );
						$row["ROW_URL"] = prepareURLStr( PAGE_IT_TEMPLATE, array( "ITT_ID"=>$row["ITT_ID"]) );

						$transitions = it_loadTemplateIssueTransitionSchema( $row["ITT_ID"], $itStrings );
						$schema = array(); 
						foreach ( $transitions as $key=>$data )
							$schema[] = sprintf( "%s%s</b></font>", it_getIssueHTMLStyle( $data['ITTS_COLOR'] ), prepareStrToDisplay($data['ITTS_STATUS'], true) );

						$transitions = implode( ' - ', $schema );
						$row['transitions'] = $transitions;

						$templateList[] = $row;
					}

					db_free_result( $qr );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['tl_screen_short_name'] );
	$preproc->assign( FORM_LINK, prepareURLStr( PAGE_IT_TEMPLATELIST, array() ) );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preproc->assign( "templateList", $templateList );
	}

	$preproc->display( "templatelist.htm" );

?>