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
	$noFolders = false;

	$targetFolder = base64_decode( $DF_ID );

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ($btnIndex) {
		case 0 : 
				$res = $dd_treeClass->PropagateRightsRecursive( $currentUser, $targetFolder, $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}
		case 1 :
				redirectBrowser( PAGE_DD_CATALOG, array() );
	}

	switch (true) {
		case true :
					// Check user access rights
					//
					$thisUserRights = $dd_treeClass->getIdentityFolderRights( $currentUser, $targetFolder, $kernelStrings );
					if ( PEAR::isError($thisUserRights) ) {
						$fatalError = true;
						$errorStr = $thisUserRights->getMessage();

						break;
					}

					if ( !UR_RightsObject::CheckMask( $thisUserRights, TREE_READWRITEFOLDER ) ) {
						$fatalError = true;
						$errorStr = $ddStrings['paa_norights_message'];

						break;
					}

					// Load folder information
					//
					$folderInfo = $dd_treeClass->getFolderInfo( $targetFolder, $kernelStrings );
					if ( PEAR::isError($folderInfo) ) {
						$fatalError = true;
						$errorStr = $folderInfo->getMessage();

						break;
					}

					// Check if folder is shared
					//
					$folderIsShared = $dd_treeClass->folderIsShared( $targetFolder, $currentUser, $kernelStrings );
					if ( PEAR::isError($folderIsShared) ) {
						$fatalError = true;
						$errorStr = $folderIsShared->getMessage();

						break;
					}

					// Check if folder have subfolders
					//
					$minimalRights = array(TREE_ONLYREAD, TREE_WRITEREAD, TREE_READWRITEFOLDER);

					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $dd_treeClass->listFolders( $currentUser, $targetFolder, $kernelStrings, 0, false, 
											$access, $hierarchy, $deletable, $minimalRights );

					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					if ( !count($folders) )
						$noFolders = true;

					// Format form personal rights label
					//
					$folderName = $folderInfo['DF_NAME'];

					// Load folder users
					//
					$folderUsers = $dd_treeClass->listFolderUsers( $targetFolder, $kernelStrings, LFU_UNIFORMLIST, $currentUser );
					if ( PEAR::isError($folderUsers) ) {
						$fatalError = true;
						$errorStr = $folderUsers->getMessage();

						break;
					}

					// Load folder groups
					//
					$folderGroups = $dd_treeClass->listFolderUsers( $targetFolder, $kernelStrings, LFU_GROUPSANDUSERS, $currentUser );
					if ( PEAR::isError($folderGroups) ) {
						$fatalError = true;
						$errorStr = $folderGroups->getMessage();

						break;
					}

					$folderGroups = $folderGroups[LFU_GROUPS];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['paa_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_PROPAGATEACCESSRIGHTS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "thisUserRights", $thisUserRights );
		$preproc->assign( "targetFolder", $targetFolder );
		$preproc->assign( "DF_ID", $DF_ID );
		$preproc->assign( "folderName", $folderName );
		$preproc->assign( "folderIsShared", $folderIsShared );

		$preproc->assign( "noFolders", $noFolders );

		$preproc->assign( "folderUsers", $folderUsers );
		$preproc->assign( "folderGroups", $folderGroups );

		$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
		$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names ); 
	}

	$preproc->display( "propagaterights.htm" );
?>