<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 : ;
		case 1 : 
					redirectBrowser( PAGE_QN_QUICKNOTES, array() );
	}

	switch (true) {
		case true : 
				$documents = unserialize( base64_decode( $doclist ) );
				$showSelected = count( $documents );

				$access = null;
				$hierarchy = null;
				$deletable = null;
				$folders = $qn_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, 
														$access, $hierarchy, $deletable, null, null,
														false, null );
				if ( PEAR::isError($folders) ) {
					$fatalError = true;
					$errorStr = $folders->getMessage();

					break;
				}

				foreach ( $folders as $fQNF_ID=>$folderData ) {
					$encodedID = base64_encode($fQNF_ID);
					$folderData->curID = $encodedID;
					$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);
					$folders[$fQNF_ID] = $folderData;
				}

				$currentFolder = base64_decode( $QNF_ID );

				if ( $showSelected )
					$printMode = 0;
				else
					if ( $currentFolder != TREE_AVAILABLE_FOLDERS )
						$printMode = 1;
					else
						$printMode = 2;

				$printURL = prepareURLStr( sprintf( "../../reports/%s", PAGE_QN_QUICKNOTES), array() );

				$visibleColumns = null;
				$viewMode = null;
				$recordsPerPage = null;
				$showSharedPanel = null;
				$contentLimit = null;
				qn_getViewOptions( $currentUser, $visibleColumns, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $readOnly );
				$cutText = sprintf( $qnStrings['ps_screen_cut_label'], $contentLimit );

				$cutNotes = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_CUTPRINTNOTES', null, $readOnly );

				$templates_names = array( $qnStrings['ps_screen_default_template'] );
				$templates_ids = array( 'default' );

				$tplList = qn_getTemplateList( $currentUser, $kernelStrings );

				if ( !PEAR::isError(  $tplList )  && count( $tplList) > 0 )
					foreach( $tplList as $value )
					{
						$templates_names[]=$value['QNT_NAME'];
						$templates_ids[] = $value['QNT_ID'];
					}
				
				$current_template = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_CURRENTPRINTTEMPLATE', "default" );

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qnStrings['ps_screen_print_title'] );
	$preproc->assign( FORM_LINK, $printURL );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qnStrings", $qnStrings );

	if ( !$fatalError ) {
		$preproc->assign( "doclist", $doclist );
		$preproc->assign( "showSelected", $showSelected );
		$preproc->assign( "folders", $folders );
		$preproc->assign( "folderCount", count($folders) );
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "QNF_ID", $QNF_ID );
		$preproc->assign( "currentFolder", $currentFolder );
		$preproc->assign( "current_template", $current_template );
		$preproc->assign( "printMode", $printMode );
		$preproc->assign( "cutText", $cutText );
		$preproc->assign( "cutNotes", $cutNotes );
	}

	$preproc->assign( "templates_names", $templates_names );
	$preproc->assign( "templates_ids", $templates_ids );


	$preproc->display( "print.htm" );
?>