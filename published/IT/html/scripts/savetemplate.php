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
					$res = it_saveTemplateSchema( prepareArrayToStore($templatedata), $P_ID, $PW_ID, $kernelStrings, $itStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						if ( $res->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $res->getUserInfo();

						break;
					}

					redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );
		}
		case 1 : redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, OPENER=>$opener ) );

	}

	switch ( true ) {
		case true : {

		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['svt_page_title'] );
	$preproc->assign( FORM_LINK, prepareURLStr( PAGE_IT_SAVETEMPLATE, array() ) );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "P_ID", $P_ID ); 
	$preproc->assign( "PW_ID", $PW_ID ); 
	$preproc->assign( OPENER, $opener );

	$preproc->assign( HELP_TOPIC, "transitionschema.htm");

	if ( !$fatalError ) {
		if ( isset($templatedata) )
			$preproc->assign( "templatedata", prepareArrayToDisplay($templatedata, null, isset($edited) && $edited) ); 

		
	}

	$preproc->display( "savetemplate.htm" );

?>