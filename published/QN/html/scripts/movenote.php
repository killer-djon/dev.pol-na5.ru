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
	$qn_locStrings = $qn_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
					if ( $destFolderID == -1 ) {
						$invalidField = 'DEST_FOLDER';
						break;
					}

					$res = qn_moveNotes( $currentUser, $curFolderID, unserialize(base64_decode($notes)), $destFolderID, $qn_locStrings, $locStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						if ( $res->getCode() == ERRCODE_INVALIDFIELD )
							$invalidField = $res->getUserInfo();

						break;
					}

					$params = array( "curFolderID"=>$curFolderID, PAGES_CURRENT=>$currentPage );
					redirectBrowser( PAGE_QN_QUICKNOTES, $params );
		case 1 :
					$params = array( "curFolderID"=>$curFolderID, PAGES_CURRENT=>$currentPage );
					redirectBrowser( PAGE_QN_QUICKNOTES, $params );
	}

	switch (true) {
		case true : 
					$rights = $qn_treeClass->getIdentityFolderRights( $curFolderID, $currentUser, $locStrings );
					if ( PEAR::isError( $rights ) ) {
						$errorStr = $rights->getMessage();
						$fatalError = true;

						break;
					}

					if ( !strlen($rights) || $rights < QN_READONLY ) {
						$errorStr = $qn_locStrings[16];
						$fatalError = true;

						break;
					}

					// Load folder name
					//
					$folderName = qn_getFolderName( $curFolderID );
					if ( PEAR::isError( $folderName ) ) {
						$errorStr = $folderName->getMessage();
						$fatalError = true;

						break;
					}

					$folderName = prepareStrToDisplay( $folderName, true );

					$folders = qn_listFolders( $locStrings, $currentUser, QN_WRITEREAD );

					if ( isset($folders[$curFolderID]) )
						unset($folders[$curFolderID]);

					$folderIDs = array(-1);
					$folderNames = array(prepareStrToDisplay($qn_locStrings[45]));

					foreach( $folders as $folderID => $folderData ) {
						$folderIDs[] = $folderID;
						$folderNames[] = prepareStrToDisplay($folderData['QNF_NAME'], true);
					}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qn_locStrings[42] );
	$preproc->assign( FORM_LINK, PAGE_QN_MOVENOTE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( PAGES_CURRENT, $currentPage );
	$preproc->assign( "qnStrings", $qn_locStrings );
	$preproc->assign( "curFolderID", $curFolderID );
	$preproc->assign( "notes", $notes );
	$preproc->assign( HELP_TOPIC, "quicknotes.htm");

	if ( !$fatalError ) {
		$preproc->assign( "folderName", $folderName );

		$preproc->assign( "folderIDs", $folderIDs );
		$preproc->assign( "folderNames", $folderNames );
	}

	$preproc->display( "movenote.htm" );
?>