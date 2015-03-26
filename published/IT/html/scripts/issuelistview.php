<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;	
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$reportData = array();

	switch ( true ) {
		case true : 
					//$it_columns = array_keys( $it_list_columns_names );

					if ( !isset($edited) || !$edited ) {
						$viewdata = it_loadIssueListViewData( $currentUser, $kernelStrings );
						//$visibleColumnsIDs = $viewdata[IT_LV_COLUMNS];
						//$hiddenColumnsIDs = array_diff( $it_columns, $visibleColumnsIDs );

					}
					
					/*

					if ( !isset($visibleColumnsIDs) )
						$visibleColumnsIDs = array();

					if ( !isset($hiddenColumnsIDs) )
						$hiddenColumnsIDs = array();

					$hiddenColumnsNames = array();
					$visibleColumnsNames = array();

					foreach( $hiddenColumnsIDs as $ID )
						$hiddenColumnsNames[] = $itStrings[$it_list_columns_names[$ID]];

					foreach( $visibleColumnsIDs as $ID )
						$visibleColumnsNames[] = $itStrings[$it_list_columns_names[$ID]];*/

					$recsPerPage = array( 10, 20, 30, 40, 50 );
	}

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array( BTN_CANCEL, BTN_SAVE ), $_POST );

	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_IT_ISSUELIST, array("P_ID"=>$P_ID) );
			
			break;
		}
		case 1 : {
			if ( !isset($visibleColumnsIDs) )
				$visibleColumnsIDs = array();

			$viewdata[IT_LV_COLUMNS] = $visibleColumnsIDs;

			$res = it_saveIssueListViewData( $currentUser, $viewdata, $kernelStrings );
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();

				if ( $res->getCode() == ERRCODE_INVALIDFIELD )
					$invalidField = $res->getUserInfo();

				break;
			}

			redirectBrowser( PAGE_IT_ISSUELIST, array("P_ID"=>$P_ID) );
		}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( PAGE_TITLE, $itStrings['cv_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_IT_ILVIEW );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( HELP_TOPIC, "issuelistview.htm");
	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( "P_ID", $P_ID );

	if ( !$fatalError ) {
		$preproc->assign( "viewdata", $viewdata );

		/*$preproc->assign( "hiddenColumnsIDs", $hiddenColumnsIDs );
		$preproc->assign( "visibleColumnsIDs", $visibleColumnsIDs );
		$preproc->assign( "hiddenColumnsNames", $hiddenColumnsNames );
		$preproc->assign( "visibleColumnsNames", $visibleColumnsNames );
		$preproc->assign( "visibleColumnsNames", $visibleColumnsNames );*/
		$preproc->assign( "recsPerPage", $recsPerPage );
	}

	$preproc->display( "issuelistview.htm" );
?>