<?php

	require_once( "../../common/reports/reportsinit.php" );

	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Issue list print report
	//
	if ( !isset( $template_id ) )
		$template_id = "default";


	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	reportUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];

	$searchMode = isset($searchString) && strlen($searchString);

	switch ( true ) {
		case true :
					$currentFolder = base64_decode($curQNF_ID);

					$visibleColumns = null;
					$viewMode = null;
					$recordsPerPage = null;
					$showSharedPanel = null;
					$contentLimit = null;
					qn_getViewOptions( $currentUser, $visibleColumns, $viewMode, $recordsPerPage, $showSharedPanel, $contentLimit, $kernelStrings, $readOnly );

					$sorting = getAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_DOCUMENT_SORTING', null, $readOnly );
					if ( !strlen($sorting) )
						$sorting = "QN_SUBJECT asc";
					else
						$sorting = base64_decode( $sorting );

					$sortData = parseSortStr( $sorting );
					if ( $sortData['field'] == 'QNF_NAME' && $searchString == "" || ( !in_array($sortData['field'], $visibleColumns) ) )
						$sorting = "QN_SUBJECT asc";

					if ( $printMode == 0 ) {
						$documents = unserialize( base64_decode( $doclist ) );
						$contacts = qn_listSelectedNotes( $documents, $currentUser, $sorting, $kernelStrings, "qn_processFileListEntry" );

						$folders = array();
						foreach( $contacts as $key=>$data ) {
							$folders[$data->QNF_ID]['LIST'][$key] = $data; 

							if ( !isset($folders[$data->QNF_ID]['NAME']) ) {
								$folderData = $qn_treeClass->getFolderInfo( $data->QNF_ID, $kernelStrings );
								$folders[$data->QNF_ID]['NAME'] = $folderData['QNF_NAME']; 
							}

							if ( !array_key_exists('docnum', $folders[$data->QNF_ID]) )
								$folders[$data->QNF_ID]['docnum'] = 0;

							$folders[$data->QNF_ID]['docnum']++;
						}
					} elseif ( $printMode == 1 ) {
						$folders = array();
						$notes = $qn_treeClass->listFolderDocuments( $currentFolder, $currentUser, $sorting, $kernelStrings, "qn_processFileListEntry" );

						$folderData = $qn_treeClass->getFolderInfo( $currentFolder, $kernelStrings );

						$folders[$folderData['QNF_ID']]['NAME'] = $folderData['QNF_NAME']; 
						$folders[$folderData['QNF_ID']]['docnum'] = count( $notes );
						$folders[$folderData['QNF_ID']]['LIST'] = array();

						foreach( $notes as $key=>$data )
							$folders[$data->QNF_ID]['LIST'][$key] = $data;
					} else {
						$access = null;
						$hierarchy = null;
						$deletable = null;
						$folderList = $qn_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, 
																$access, $hierarchy, $deletable, null, null,
																false, null );
						if ( PEAR::isError($folderList) ) {
							$fatalError = true;
							$errorStr = $folderList->getMessage();

							break;
						}

						$folders = array();
						foreach( $folderList as $QNF_ID=>$folderData ) {
							$notes = $qn_treeClass->listFolderDocuments( $QNF_ID, $currentUser, $sorting, $kernelStrings, "qn_processFileListEntry" );

							$folders[$folderData->QNF_ID]['NAME'] = $folderData->QNF_NAME;
							$folders[$folderData->QNF_ID]['docnum'] = count( $notes );
							$folders[$folderData->QNF_ID]['LIST'] = array();

							foreach( $notes as $key=>$data )
								$folders[$folderData->QNF_ID]['LIST'][$key] = $data;
						}
					}

					foreach( $folders as $QNF_ID=>$folderData ) {
						foreach( $folderData['LIST'] as $QN_ID=>$data ) {
							$data = (array)$data;

							$folderData['LIST'][$QN_ID] = $data;
						}
						$folders[$QNF_ID] = $folderData;
					}

					$visibleColumns = array_merge( array( QN_COLUMN_SUBJECT ), $visibleColumns );

					$columnKeys = array();
					foreach ( $visibleColumns as $key=>$columnName )
						$columnKeys[$columnName] = 1;

					$numVisibleColumns = count($visibleColumns)+1;

					if ( !isset($cutNotes) )
						$cutNotes = 0;

					setAppUserCommonValue( $QN_APP_ID, $currentUser, 'QN_CUTPRINTNOTES', $cutNotes, $kernelStrings, $readOnly );

					if ( $template_id != "default" )
					{
						$tpl = qn_getTemplate( $template_id, $currentUser, $kernelStrings );
						if ( PEAR::isError( $tpl ) )
						{
							$template_id = "default";
							break;
						}
					
						$tpl_strs = array();

						foreach( $folders as $folder )
						{
							foreach( $folder['LIST'] as $key=>$value )
							{
								$note = $value;
								$note['FOLDER_NAME'] = $folder['NAME'];

								$tpl_strs[] = qn_applyTemplate( $note, $tpl['QNT_HTML'] );
							}
						}
					}
					
	}


	$preprocessor = new print_preprocessor( $QN_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );
	$preprocessor->assign( "qnStrings", $qnStrings );
	$preprocessor->assign( 'pageTitle', $qnStrings['qn_screen_long_name'] );

	if ( !$fatalError ) {
		$preprocessor->assign( "folders", $folders );
		$preprocessor->assign( "viewMode", $viewMode );
		$preprocessor->assign( "contentLimit", $contentLimit );
		$preprocessor->assign( "visibleColumns", $visibleColumns );
		$preprocessor->assign( "numVisibleColumns", $numVisibleColumns );
		$preprocessor->assign( "qn_columnNames", $qn_columnNames );
		$preprocessor->assign( "columnKeys", $columnKeys );
		$preprocessor->assign( "cutNotes", $cutNotes );
	}

	if ( $template_id == "default" )
	{
		$preprocessor->display( "quicknotes.htm" );
	}
	else
	{
		$preprocessor->assign( "templated_strings", $tpl_strs );
		$preprocessor->display( "templated.htm" );
	}

	
?>