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
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
				$res = it_fillSchemaFromTemplate( $P_ID, $PW_ID, $templatedata["ITT_ID"], $kernelStrings, $itStrings );
				if ( PEAR::isError( $res ) ) {
					$errorStr = prepareStrToDisplay($res->getMessage(), true);
					break;
				}

				redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );
		}
		case 1 : redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );
	}

	switch ( true ) {
		case true : {
						$qr = db_query( $qr_it_templatelist );
						if ( PEAR::isError( $qr ) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						$templateListNames = array();
						$templateListIDs = array();

						while ( $row = db_fetch_array( $qr ) ) {
							$templateListIDs[] = $row["ITT_ID"];
							$templateListNames[] = $row["ITT_NAME"];
						}

						db_free_result( $qr );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['st_page_title'] );
	$preproc->assign( FORM_LINK, prepareURLStr( PAGE_IT_SELECTTEMPLATE, array() ) );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "P_ID", $P_ID ); 
	$preproc->assign( "PW_ID", $PW_ID ); 
	$preproc->assign( OPENER, $opener );

	$preproc->assign( HELP_TOPIC, null );

	if ( !$fatalError ) {
		$preproc->assign( "templateListNames", $templateListNames ); 
		$preproc->assign( "templateListIDs", $templateListIDs ); 

		if ( isset($templatedata) )
			$preproc->assign( "templatedata", $templatedata );
		
	}

	$preproc->display( "selecttemplate.htm" );

?>