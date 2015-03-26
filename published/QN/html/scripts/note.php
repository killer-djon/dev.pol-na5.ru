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
	$curQNF_ID = base64_decode( $QNF_ID );

	$btnIndex = getButtonIndex( array( BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_QN_QUICKNOTES, array() );
	}

	switch (true) {
		case true :
					$folderInfo = $qn_treeClass->getFolderInfo( $curQNF_ID, $kernelStrings );
					if ( PEAR::isError($folderInfo) ) {
						$fatalError = true;
						$errorStr = $folderInfo->getMessage();

						break;
					}


					$rights = $qn_treeClass->getIdentityFolderRights( $currentUser, $curQNF_ID, $kernelStrings );
					if ( PEAR::isError($rights) ) {
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					if ( $rights == TREE_NOACCESS )
					{
						$fatalError = true;
						$errorStr = $qnStrings['n_screen_rights_message'];

						break;
					}

					$noteData = $qn_treeClass->getDocumentInfo( $QN_ID, $kernelStrings );
					if ( PEAR::isError($noteData) ) {
						$fatalError = true;
						$errorStr = $noteData->getMessage();

						break;
					}

					$noteData["QN_MODIFYDATETIME"] = convertToDisplayDateTime( $noteData["QN_MODIFYDATETIME"], false, true, true );

					$RECORD_FILES = $noteData["QN_ATTACHMENT"];

					$attachmentsData = listAttachedFiles( base64_decode($RECORD_FILES) );
					$attachedFiles = array();
					if ( count($attachmentsData) ) {
						for ( $i = 0; $i < count($attachmentsData); $i++ ) {
							$fileData = $attachmentsData[$i];
							$fileName = $fileData["name"];
							$fileSize = formatFileSizeStr( $fileData["size"] );

							$params = array( "QNF_ID"=>$noteData["QNF_ID"], "QN_ID"=>$noteData["QN_ID"], "fileName"=>base64_encode($fileName) );
							$fileURL = prepareURLStr( PAGE_QN_GETNOTEFILE, $params );

							$attachedFiles[] = sprintf( "<a href=\"%s\" target=\"_blank\">%s (%s)</a>", $fileURL, $fileData["screenname"], $fileSize );
						}
					}
					if ( !count($attachedFiles) )
						$attachedFiles = null;
					else
						$attachedFiles = implode( ", ", $attachedFiles );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qnStrings['n_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QN_NOTE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( "QNF_ID", $QNF_ID );
	$preproc->assign( HELP_TOPIC, "quicknotes.htm");

	if ( !$fatalError ) {
		$preproc->assign( "noteData", $noteData );
		$preproc->assign( "attachedFiles", $attachedFiles );
	}

	$preproc->display( "note.htm" );
?>