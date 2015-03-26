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
	if ( $fatalError ) {
		$fatalError = false;
		$errorStr = null;
		$SCR_ID = "RB";

		pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	}

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	define( 'ACTION_RESTORE', 'restore' );

	$admin = base64_decode($opener) == PAGE_DD_RECYCLED;

	$btnIndex = getButtonIndex( array( BTN_CANCEL, BTN_SAVE ), $_POST );

	$reloadParent = false;
	switch ($btnIndex) {
		case 0 : 
				redirectBrowser( base64_decode($opener), array() );
		case 1 : 
				$documents = unserialize( base64_decode( $doclist ) );
				$folders = unserialize( base64_decode( $folderlist ) );
				$DF_ID = base64_decode($curDF_ID);

				if ( count($documents) && $DF_ID == TREE_AVAILABLE_FOLDERS ) {
					$errorStr = $ddStrings['sv_screen_errdest_message'];
					break;
				}

				if ( count($documents) ) {
					$res = dd_deleteRestoreDocuments( $documents, DD_RESTOREDOC, $currentUser, $kernelStrings, $ddStrings, $DF_ID, $admin );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}
				}

				if ( count($folders) ) {
					$res = dd_restoreFolders( $folders, $currentUser, $kernelStrings, $ddStrings, $DF_ID, $admin );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}
					$readOnly = false;
					$dd_treeClass->setUserDefaultFolder( $currentUser, $DF_ID, $kernelStrings, $readOnly );
					$reloadParent = true;
					break;
				}

				redirectBrowser( PAGE_DD_RECYCLED, array() );
	}

	switch (true) {
		case true : 
					$documents = unserialize( base64_decode( $doclist ) );
					$folders = unserialize( base64_decode( $folderlist ) );

					$user = ($admin) ? null : $currentUser;
					$addavailableFolders = true;

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$suppress_ID = null;
					$suppressIDChildren = false;
					$suppressParent = null;

					if ( $admin )
						$dd_treeClass->checkRights = false;

					$folders = $dd_treeClass->listFolders( $user, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, 
												$hierarchy, $deletable, TREE_WRITEREAD, $suppress_ID, 
												$suppressIDChildren, $suppressParent, $addavailableFolders );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					if ( !count( $folders ) ) {
						$fatalError = true;
						$errorStr = $ddStrings['app_nofolders_message'];

						break;
					}

					foreach ( $folders as $DF_ID=>$folderData ) {
						$encodedID = base64_encode($DF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);
						
						if ($admin) 
							$folderData->TREE_ACCESS_RIGHTS = TREE_WRITEREAD;
						else 
							if ( !isset($folderData->TREE_ACCESS_RIGHTS) || !UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_WRITEREAD ) )
								$folderData->TREE_ACCESS_RIGHTS = TREE_NOACCESS;

						if ( $DF_ID == TREE_AVAILABLE_FOLDERS )
							$folderData->NAME = $kernelStrings['app_treeroot_name'];

						$folders[$DF_ID] = $folderData;
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['r_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_DD_RESTORE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( OPENER, $opener );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "doclist", $doclist );
	$preproc->assign( "folderlist", $folderlist );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "reloadParent", $reloadParent );

	if ( !$fatalError ) {
		$preproc->assign( "folders", $folders );
		$preproc->assign( "hierarchy", $hierarchy );
	}

	$preproc->display( "restore.htm" );
?>