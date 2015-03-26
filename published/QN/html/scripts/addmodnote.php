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

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$metric = metric::getInstance();
	$curQNF_ID = base64_decode( $QNF_ID );
	$curFolderID = $curQNF_ID;

	$btnIndex = getButtonIndex( array( BTN_ATTACH, BTN_SAVE, BTN_CANCEL, BTN_DELETEFILES, "deleteNoteBtn", 'saveaddbtn' ), $_POST );

	switch ($btnIndex) {
		case 5 :
		case 0 :
		case 1 : 	// Move new attached file
					//
					if ( isset($_FILES['notefile']) ) {
						$res = add_moveAttachedFile( $_FILES['notefile'],
														base64_decode($PAGE_ATTACHED_FILES),
														WBS_TEMP_DIR, $locStrings, true, "qn" );
						if ( PEAR::isError( $res ) ) {
							$errorStr = $res->getMessage();

							break;
						}
						if ($_FILES['notefile']['size']>0)
						$metric->addAction($DB_KEY, $currentUser, 'QN', 'ATTACH', 'ACCOUNT', $_FILES['notefile']['size']);
						
						$PAGE_ATTACHED_FILES = base64_encode($res);
					}

					if ( $btnIndex == 0 )
						break;

					// Make note attachments list
					//
					$res = makeRecordAttachedFilesList( base64_decode($RECORD_FILES),
														base64_decode($PAGE_DELETED_FILES),
														base64_decode($PAGE_ATTACHED_FILES),
														$locStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					$noteData["QN_ATTACHMENT"] = base64_encode($res);
					$noteData["QNF_ID"] = $curFolderID;

					$QN_ND = qn_addModNote( $action, $currentUser, prepareArrayToStore($noteData), $locStrings, $qnStrings );
					if ( PEAR::isError( $QN_ND ) ) {
						$errorStr = $QN_ND->getMessage();

						$errCode = $QN_ND->getCode();
						if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
							$invalidField = $QN_ND->getUserInfo();

						break;
					} else {
						if ($action == ACTION_NEW)
							$metric->addAction($DB_KEY, $currentUser, 'QN', 'ADDNOTE', 'ACCOUNT');
					}
					// Apply attachments
					//
					$attachmentsPath = qn_getNoteAttachmentsDir( $QN_ND );
					$res = applyPageAttachments( base64_decode($PAGE_ATTACHED_FILES),
													base64_decode($PAGE_DELETED_FILES),
													$attachmentsPath, $locStrings, $QN_APP_ID );
					if ( PEAR::isError($res) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					if ( $btnIndex == 1 )
						redirectBrowser( PAGE_QN_QUICKNOTES, array() );
					else {
						$params = array();
						$params[ACTION] = ACTION_NEW;
						$params['QNF_ID'] = $QNF_ID;
						redirectBrowser( PAGE_QN_ADDMODNOTE, $params );
					}
		case 2 :
					redirectBrowser( PAGE_QN_QUICKNOTES, array() );
		case 3 :
					$pageFiles = base64_decode($PAGE_ATTACHED_FILES);
					$delFiles = base64_decode($PAGE_DELETED_FILES);

					if ( !isset($cbdeletenewfile) ) $cbdeletenewfile = array();
					if ( !isset($cbdeleterecordfile) ) $cbdeleterecordfile = array();

					$res = deleteAttachedFiles( base64_decode($RECORD_FILES), $delFiles, $pageFiles,
												$cbdeletenewfile, $cbdeleterecordfile, $locStrings );

					if ( PEAR::isError( $res ) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					$PAGE_ATTACHED_FILES = base64_encode( $pageFiles );
					$PAGE_DELETED_FILES = base64_encode( $delFiles );

					break;
		case 4 :
					$res = qn_deleteNote( $currentUser, $noteData["QN_ID"], $curFolderID, $locStrings, $qnStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						$fatalError = true;

						break;
					}

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

					if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
					{
						$fatalError = true;
						$errorStr = $qnStrings['amn_screen_norights_message'];

						break;
					}

					if ( !isset($edited) || !$edited ) {
						$PAGE_ATTACHED_FILES = null;
						$PAGE_DELETED_FILES = null;

						if ( $action == ACTION_NEW )
							$RECORD_FILES = null;
						else {
							$noteData = $qn_treeClass->getDocumentInfo( $QN_ID, $locStrings );
							if ( PEAR::isError($noteData) ) {
								$fatalError = true;
								$errorStr = $noteData->getMessage();

								break;
							}

							$RECORD_FILES = $noteData["QN_ATTACHMENT"];
						}
					}
	}

	//
	// Generating attached files lists
	//
	if ( !$fatalError )
		$attachedFiles = makeAttachedFileList( base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($PAGE_ATTACHED_FILES),
												"cbdeletenewfile",
												"cbdeleterecordfile" );
	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QN_APP_ID );

	$title = ( $action == ACTION_NEW ) ? $qnStrings['amn_screen_add_title'] : $qnStrings['amn_screen_modify_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QN_ADDMODNOTE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( "QNF_ID", $QNF_ID );

	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	if ( !$fatalError ) {
		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "attachedFiles", $attachedFiles );

		if ( isset($noteData) ) {
			if ( isset($edited) )
				$noteData["QN_CONTENT"] = stripSlashes( $noteData["QN_CONTENT"] );
			$preproc->assign( "noteData", prepareArrayToDisplay( $noteData, array( "QN_CONTENT" ), isset($edited) && $edited ) );
		}

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );
	}

	$preproc->display( "addmodnote.htm" );
?>
