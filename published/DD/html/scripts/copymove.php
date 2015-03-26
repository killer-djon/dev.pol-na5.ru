<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
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
					$destDF_ID = base64_decode($curDF_ID);
					
					$callbackParams = array( 'ddStrings'=>$ddStrings, 'U_ID'=>$currentUser );
					$res = $dd_treeClass->copyMoveDocuments( $documents, $destDF_ID, $operation, $currentUser, $kernelStrings, 
																"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", $callbackParams,
																true, true, "dd_onFinishCopyMoveFiles" );

					if ( PEAR::isError($res) )
						$errorStr = $res->getMessage();
					else {
						$dd_treeClass->setUserDefaultFolder( $currentUser, $destDF_ID, $kernelStrings );
						redirectBrowser( PAGE_DD_CATALOG, array() );
					}
				} else 
					if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER ) {
						$destDF_ID = base64_decode($curDF_ID);
						$srcDF_ID = base64_decode($DF_ID);
						$callbackParams = array( 'ddStrings'=>$ddStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$srcDF_ID, 'U_ID'=>$currentUser );

						if ( $operation == TREE_MOVEFOLDER )
							$newDF_ID = $dd_treeClass->moveFolder( $srcDF_ID, $destDF_ID, $currentUser, $kernelStrings, 
																	"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", "dd_onCreateFolder", "dd_onDeleteFolder",
																	$callbackParams, "dd_onFinishMoveFolder", true, true, $accessInheritance );
						else {
							$callbackParams["copy"] = true;
							$newDF_ID = $dd_treeClass->copyFolder( $srcDF_ID, $destDF_ID, $currentUser, $kernelStrings, 
																	"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", "dd_onCreateFolder", 
																	$callbackParams, "dd_finishCopyFolder", $accessInheritance );
						}

						if ( PEAR::isError($newDF_ID) )
							$errorStr = $newDF_ID->getMessage();
						else {
							$dd_treeClass->setUserDefaultFolder( $currentUser, $newDF_ID, $kernelStrings );
							redirectBrowser( PAGE_DD_CATALOG, array() );
						}
					}

				break;
		case 1 : 
				redirectBrowser( PAGE_DD_CATALOG, array() );
	}


	switch (true) {
		case true :
					if ( !isset($edited) )
						$accessInheritance = ACCESSINHERITANCE_COPY;

					$supressID = $DF_ID = base64_decode($DF_ID);

					if ( strlen($DF_ID) ) {
						$curFolderData = $dd_treeClass->getFolderInfo( $DF_ID, $kernelStrings );
						if ( PEAR::isError($curFolderData) ) {
							$fatalError = true;
							$errorStr = $curFolderData->getMessage();

							break;
						}

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER ) {
							$parentFolderData = $dd_treeClass->getFolderParentInfo( $DF_ID, $kernelStrings, true );
							if ( PEAR::isError($parentFolderData) ) {
								$fatalError = true;
								$errorStr = $parentFolderData->getMessage();

								break;
							}

							$folderName = $parentFolderData['DF_NAME'];
						} else
							$folderName = $curFolderData['DF_NAME'];
					} else
						$folderName = $ddStrings['dd_sreen_searchresult_title'];

					if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC ) {
						$minimalRights = array( TREE_WRITEREAD, TREE_READWRITEFOLDER );
						$supressID = null;
						$showRootFolder = false;
					} else 
						if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER ) {
							$minimalRights = array( TREE_READWRITEFOLDER );
							$showRootFolder = $dd_treeClass->isRootIdentity( $currentUser, $kernelStrings );
						}

					$supressChildren = $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER;
					$suppressParent = false;
					if ( $operation == TREE_MOVEFOLDER )
						$suppressParent = $curFolderData['DF_ID_PARENT'];

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $dd_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, 
															$access, $hierarchy, $deletable, $minimalRights, $supressID,
															$supressChildren, $suppressParent, $showRootFolder );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					foreach ( $folders as $fDF_ID=>$folderData ) {
						$encodedID = base64_encode($fDF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$params = array();

						if ( $operation == TREE_MOVEFOLDER)
							if ( $fDF_ID == $curFolderData['DF_ID_PARENT'] || $fDF_ID == $curFolderData['DF_ID'] )
								$folderData->TREE_ACCESS_RIGHTS = TREE_NOACCESS; 

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER )
							if ( $fDF_ID == TREE_AVAILABLE_FOLDERS )
								$folderData->NAME = $kernelStrings['app_treeroot_name'];

						// Prevert file copy/move operations for current folder
						//
						if ( ($operation == TREE_COPYDOC || $operation == TREE_MOVEDOC) && $fDF_ID == $DF_ID )
							$folderData->TREE_ACCESS_RIGHTS = -2; 

						$folders[$fDF_ID] = $folderData;
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

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$titles = array( TREE_COPYDOC=>'cm_screen_copy_title', TREE_MOVEDOC=>'cm_screen_move_title', 
						TREE_COPYFOLDER=>'cm_screen_copyfolder_title', TREE_MOVEFOLDER=>'cm_screen_movefolder_title' );
	$saveCaptions = array( TREE_COPYDOC=>'cm_screen_copy_btn', TREE_MOVEDOC=>'cm_screen_move_btn', 
						TREE_COPYFOLDER=>'cm_screen_copy_btn', TREE_MOVEFOLDER=>'cm_screen_move_btn' );

	$title = $ddStrings[$titles[$operation]];
	$saveCaption = $ddStrings[$saveCaptions[$operation]];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_DD_COPYMOVE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "operation", $operation );
	$preproc->assign( "searchString", $searchString );

	$preproc->assign( "DF_ID", base64_encode($DF_ID) );

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