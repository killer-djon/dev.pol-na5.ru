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

	if ( !isset($searchString) )
		$searchString = null;

	$btnIndex = getButtonIndex( array(), $_POST );

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 :
				if ( ($operation == TREE_COPYDOC || $operation == TREE_MOVEDOC) ) {
					$documents = unserialize( base64_decode( $doclist ) );
					$destQNF_ID = base64_decode($curQNF_ID);

					$callbackParams = array( 'qnStrings'=>$qnStrings );
					$res = $qn_treeClass->copyMoveDocuments( $documents, $destQNF_ID, $operation, $currentUser, $kernelStrings,
																"qn_onAfterCopyMoveNote", "qn_onCopyMoveNote", $callbackParams,
																true, true );

					if ( PEAR::isError($res) )
						$errorStr = $res->getMessage();
					else {
						$qn_treeClass->setUserDefaultFolder( $currentUser, $destQNF_ID, $kernelStrings );
						redirectBrowser( PAGE_QN_QUICKNOTES, array() );
					}
				} else
					if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER ) {
						$destQNF_ID = base64_decode($curQNF_ID);
						$srcQNF_ID = base64_decode($QNF_ID);

						$callbackParams = array( 'qnStrings'=>$qnStrings, "kernelStrings"=>$kernelStrings );

						if ( $operation == TREE_MOVEFOLDER )
							$newQNF_ID = $qn_treeClass->moveFolder( $srcQNF_ID, $destQNF_ID, $currentUser, $kernelStrings,
																	"qn_onAfterCopyMoveNote", "qn_onCopyMoveNote", null, null,
																	$callbackParams, null, true, true, $accessInheritance );
						else
							$newQNF_ID = $qn_treeClass->copyFolder( $srcQNF_ID, $destQNF_ID, $currentUser, $kernelStrings,
																	"qn_onAfterCopyMoveNote", "qn_onCopyMoveNote", null,
																	$callbackParams, null, $accessInheritance);

						if ( PEAR::isError($newQNF_ID) )
							$errorStr = $newQNF_ID->getMessage();
						else {
							$qn_treeClass->setUserDefaultFolder( $currentUser, $newQNF_ID, $kernelStrings );
							redirectBrowser( PAGE_QN_QUICKNOTES, array() );
						}
					}

				break;
		case 1 :
				redirectBrowser( PAGE_QN_QUICKNOTES, array() );
	}


	switch (true) {
		case true :
					$supressID = $QNF_ID = base64_decode($QNF_ID);

					if ( !isset($edited) )
						$accessInheritance = ACCESSINHERITANCE_COPY;

					if ( strlen($QNF_ID) ) {
						$curFolderData = $qn_treeClass->getFolderInfo( $QNF_ID, $kernelStrings );
						if ( PEAR::isError($curFolderData) ) {
							$fatalError = true;
							$errorStr = $curFolderData->getMessage();

							break;
						}

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER ) {
							$parentFolderData = $qn_treeClass->getFolderParentInfo( $QNF_ID, $kernelStrings, true );
							if ( PEAR::isError($parentFolderData) ) {
								$fatalError = true;
								$errorStr = $parentFolderData->getMessage();

								break;
							}

							$folderName = $parentFolderData['QNF_NAME'];
						} else
							$folderName = $curFolderData['QNF_NAME'];
					} else
						$folderName = $qnStrings['qn_sreen_searchresult_title'];

					if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC ) {
						$minimalRights = TREE_WRITEREAD;
						$supressID = null;
						$showRootFolder = false;
					} else
						if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER ) {
							$minimalRights = TREE_READWRITEFOLDER;
							$showRootFolder = $qn_treeClass->isRootIdentity( $currentUser, $kernelStrings );
						}

					$supressChildren = $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER;
					$suppressParent = false;
					if ( $operation == TREE_MOVEFOLDER )
						$suppressParent = $curFolderData['QNF_ID_PARENT'];

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $qn_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable, $minimalRights, $supressID,
															$supressChildren, $suppressParent, $showRootFolder );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					foreach ( $folders as $fQNF_ID=>$folderData ) {
						$encodedID = base64_encode($fQNF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$params = array();

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_MOVEDOC ) {
							if ( $fQNF_ID == $curFolderData['QNF_ID_PARENT'] || $fQNF_ID == $curFolderData['QNF_ID'] )
								$folderData->TREE_ACCESS_RIGHTS = TREE_NOACCESS;
						}

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER )
							if ( $fQNF_ID == TREE_AVAILABLE_FOLDERS )
								$folderData->NAME = $kernelStrings['app_treeroot_name'];

						// Prevert file copy/move operations for current folder
						//
						if ( ($operation == TREE_COPYDOC || $operation == TREE_MOVEDOC) && $fQNF_ID == $QNF_ID )
							$folderData->TREE_ACCESS_RIGHTS = -2;

						$folders[$fQNF_ID] = $folderData;
					}

					$docNum = null;
					if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC ) {
						$documents = unserialize( base64_decode( $doclist ) );
						$docNum = count( $documents );
					}

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$titles = array( TREE_COPYDOC=>'cm_screen_copy_title', TREE_MOVEDOC=>'cm_screen_move_title', TREE_COPYFOLDER=>'cm_screen_copyfolder_title', TREE_MOVEFOLDER=>'cm_screen_movefolder_title' );
	$saveCaptions = array( TREE_COPYDOC=>'cm_screen_copy_btn', TREE_MOVEDOC=>'cm_screen_move_btn', TREE_COPYFOLDER=>'cm_screen_copy_btn', TREE_MOVEFOLDER=>'cm_screen_move_btn' );

	$title = $qnStrings[$titles[$operation]];
	$saveCaption = $qnStrings[$saveCaptions[$operation]];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QN_COPYMOVE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "operation", $operation );
	$preproc->assign( "searchString", $searchString );

	$preproc->assign( "QNF_ID", base64_encode($QNF_ID) );

	if ( !$fatalError ) {
		if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC )
			$preproc->assign( "doclist", $doclist );

		$preproc->assign( "folders", $folders );
		$preproc->assign( "folderCount", count($folders) );
		$preproc->assign( "hierarchy", $hierarchy );
		$preproc->assign( "saveCaption", $saveCaption );
		$preproc->assign( "folderName", $folderName );
		$preproc->assign( "docNum", $docNum );

		$preproc->assign( "accessInheritance", $accessInheritance );
	}

	$preproc->display( "copymove.htm" );
?>