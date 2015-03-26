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
	$invalidField = null;

	$templateIDs = array();
	$templateNames = array();

	switch ( true ) {
		case true : {
						if ( $fatalError )
							break;

						// Check if work exists
						//
						$res = it_workExists( $P_ID, $PW_ID );
						if ( PEAR::isError($res) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];
							$fatalError = true;

							break;
						}

						if ( !$res ) {
							$errorStr = $itStrings[IT_ERR_WORKNOTFOUND];
							$fatalError = true;

							break;
						}

						// Load project and work information
						//
						$projName = it_getProjectName( $P_ID, IT_DEFAILT_MAX_PROJNAME_LEN, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
						if ( PEAR::isError($projName) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						$workDescr = it_getWorkDescription( $P_ID, $PW_ID );
						if ( PEAR::isError($workDescr) ) {
							$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

							$fatalError = true;
							break;
						}

						// Load template list
						//
						$res = db_query( sprintf($qr_it_templatelist, "ITT_NAME") );
						if ( !$res ) {
							$errorStr = $itStrings[IT_ERR_LOADTEMPLATELIST];
							$fatalError = true;

							break;
						}

						while ( $row = db_fetch_array( $res ) ) {
							$templateIDs[] = $row["ITT_ID"];
							$templateNames[] =  $row["ITT_NAME"];
						}

						if ( !count($templateIDs) )
							$templateIDs[] = null;
							$templateNames[] = $itStrings['stt_notemplates_item'];

						db_free_result( $res );
		}
	}

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "searchCriteria"=>$searchCriteria ) );

			break;
		}
		case 1 : {
			$res = it_fillSchemaFromTemplate( $P_ID, $PW_ID, $ITT_ID, $kernelStrings, $itStrings );
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();

				break;
			}

			redirectBrowser( PAGE_IT_ISSUETRANSITIONSCHEMA, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "searchCriteria"=>$searchCriteria ) );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $itStrings['stt_page_title'] );
	$preproc->assign( ACTION, $action );
	$preproc->assign( FORM_LINK, PAGE_IT_FILLFROMTEMPLATE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "P_ID", $P_ID ); 
	$preproc->assign( "PW_ID", $PW_ID ); 
	$preproc->assign( "searchCriteria", $searchCriteria );

	if ( !$fatalError ) {
		$preproc->assign( "templateIDs", $templateIDs ); 
		$preproc->assign( "templateNames", $templateNames ); 

		$preproc->assign( "projName", $projName );
		$preproc->assign( "workDescr", $workDescr );	

		$preproc->assign( "ITT_ID", $ITT_ID ); 
	}

	$preproc->display( "fillfromtemplate.htm" );
?>