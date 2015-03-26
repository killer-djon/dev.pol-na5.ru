<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
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
					$destMMF_ID = base64_decode($curMMF_ID);

					$currentFolder = base64_decode(getAppUserCommonValue( $MM_APP_ID, $currentUser, 'CURRENT_FOLDER', null, $readOnly ));
					if($inboxMode || $currentFolder == 0)
						$perFileCheck = false;
					else
						$perFileCheck = true;

					$callbackParams = array( 'mmStrings'=>$mmStrings );

					$res = $mm_treeClass->copyMoveDocuments( $documents, $destMMF_ID, $operation, $currentUser, $kernelStrings,
															"mm_onAfterCopyMoveMessage", "mm_onCopyMoveNote", $callbackParams,
															$perFileCheck, true, null, false, $inboxMode);

					if($inboxMode)
					{
						$del = array();
						foreach( $documents as $doc )
						{
							$row = explode("\t", $doc);
							if(!empty($row[1]))
							  $del[$row[0]][] = $row[1];
						}
						if($del)
						{
							$accs = mm_getAccounts( $currentUser, $kernelStrings );
							if(PEAR::isError($accs))
							  $errorStr = $accs->getMessage();
							else
							{
								foreach($accs as $acc)
								{
									if($acc['MMA_INTERNAL'])
										$acc['MMA_EMAIL'] .= '@'.$acc['MMA_DOMAIN'];
									$accounts[$acc['MMA_EMAIL']] = $acc;
								}
							}
							$res = mm_deleteMail( $del, $mmStrings ); // requires $accounts array
							if ( PEAR::isError( $res ) )
								$errorStr = $res->getMessage();
						}
					}

					if ( PEAR::isError($res) )
						$errorStr = $res->getMessage();
					else {
//						$mm_treeClass->setUserDefaultFolder( $currentUser, $destMMF_ID, $kernelStrings );
//						$box = '';
//						setAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMM_VIRTUALFOLDER', $box, $kernelStrings, $readOnly );
						redirectBrowser( PAGE_MM_MAILMASTER, array() );
					}
				} else
					if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER ) {
						$destMMF_ID = base64_decode($curMMF_ID);
						$srcMMF_ID = base64_decode($MMF_ID);
						$callbackParams = array( 'mmStrings'=>$mmStrings, "kernelStrings"=>$kernelStrings );

						if ( $operation == TREE_MOVEFOLDER )
							$newMMF_ID = $mm_treeClass->moveFolder( $srcMMF_ID, $destMMF_ID, $currentUser, $kernelStrings, 
																	"mm_onAfterCopyMoveMessage", "mm_onCopyMoveNote", null, null,
																	$callbackParams, null, true, true, $accessInheritance );
						else
							$newMMF_ID = $mm_treeClass->copyFolder( $srcMMF_ID, $destMMF_ID, $currentUser, $kernelStrings, 
																	"mm_onAfterCopyMoveMessage", "mm_onCopyMoveNote", null, 
																	$callbackParams, null, $accessInheritance);

						if ( PEAR::isError($newMMF_ID) )
							$errorStr = $newMMF_ID->getMessage();
						else {
							$mm_treeClass->setUserDefaultFolder( $currentUser, $newMMF_ID, $kernelStrings );
							redirectBrowser( PAGE_MM_MAILMASTER, array() );
						}
					}

				break;
		case 1 : 
				redirectBrowser( PAGE_MM_MAILMASTER, array() );
	}


	switch (true) {
		case true :
					$supressID = $MMF_ID = base64_decode($MMF_ID);

					if($inboxMode)
					{
						$documents = unserialize( base64_decode( $doclist ) );
						$docNum = count( $documents );

						$folderName = $mmStrings['app_inbox_root_label'];
					}
					elseif($virtualFolder)
						$folderName = $virtualFolder;
					else
					{
						if ( !isset($edited) )
							$accessInheritance = ACCESSINHERITANCE_COPY;

						if ( strlen($MMF_ID) && $MMF_ID != 0 ) {

							$curFolderData = $mm_treeClass->getFolderInfo( $MMF_ID, $kernelStrings );
							if ( PEAR::isError($curFolderData) ) {
								$fatalError = true;
								$errorStr = $curFolderData->getMessage();

								break;
							}

							if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER ) {
								$parentFolderData = $mm_treeClass->getFolderParentInfo( $MMF_ID, $kernelStrings, true );
								if ( PEAR::isError($parentFolderData) ) {
									$fatalError = true;
									$errorStr = $parentFolderData->getMessage();

									break;
								}

								$folderName = $parentFolderData['MMF_NAME'];
							} else
								$folderName = $curFolderData['MMF_NAME'];
						}
						elseif( $MMF_ID == 0 )
							$folderName = 'Virtual folder';
						else
							$folderName = $mmStrings['mm_sreen_searchresult_title'];
					}

					if ( $operation == TREE_COPYDOC || $operation == TREE_MOVEDOC ) {
						$minimalRights = TREE_WRITEREAD;
						$supressID = null;
						$showRootFolder = false;
					} else 
						if ( $operation == TREE_COPYFOLDER || $operation == TREE_MOVEFOLDER ) {
							$minimalRights = TREE_READWRITEFOLDER; 
							$showRootFolder = $mm_treeClass->isRootIdentity( $currentUser, $kernelStrings );
						}

					$supressChildren = $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER;
					$suppressParent = false;
					if ( $operation == TREE_MOVEFOLDER )
						$suppressParent = $curFolderData['MMF_ID_PARENT'];

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $mm_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false, 
															$access, $hierarchy, $deletable, $minimalRights, $supressID,
															$supressChildren, $suppressParent, $showRootFolder );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					foreach ( $folders as $fMMF_ID=>$folderData ) {
						$encodedID = base64_encode($fMMF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$params = array();

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_MOVEDOC )
							if ( $fMMF_ID == $curFolderData['MMF_ID_PARENT'] || $fMMF_ID == $curFolderData['MMF_ID'] )
								$folderData->RIGHT = TREE_NOACCESS; 

						if ( $operation == TREE_MOVEFOLDER || $operation == TREE_COPYFOLDER )
							if ( $fMMF_ID == TREE_AVAILABLE_FOLDERS )
								$folderData->NAME = $kernelStrings['app_treeroot_name'];

						// Prevert file copy/move operations for current folder
						//
						if ( ($operation == TREE_COPYDOC || $operation == TREE_MOVEDOC) && $fMMF_ID == $MMF_ID )
							$folderData->RIGHT = -2; 

						$folders[$fMMF_ID] = $folderData;
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

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$titles = array( TREE_COPYDOC=>'cm_screen_copy_title', TREE_MOVEDOC=>'cm_screen_move_title', TREE_COPYFOLDER=>'cm_screen_copyfolder_title', TREE_MOVEFOLDER=>'cm_screen_movefolder_title' );
	$saveCaptions = array( TREE_COPYDOC=>'cm_screen_copy_btn', TREE_MOVEDOC=>'cm_screen_move_btn', TREE_COPYFOLDER=>'cm_screen_copy_btn', TREE_MOVEFOLDER=>'cm_screen_move_btn' );

	$title = $mmStrings[$titles[$operation]];
	$saveCaption = $mmStrings[$saveCaptions[$operation]];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_MM_COPYMOVE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "operation", $operation );
	$preproc->assign( "searchString", $searchString );

	$preproc->assign( "MMF_ID", base64_encode($MMF_ID) );

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

		$preproc->assign( "inboxMode", $inboxMode);
	}

	$preproc->display( "copymove.htm" );
?>