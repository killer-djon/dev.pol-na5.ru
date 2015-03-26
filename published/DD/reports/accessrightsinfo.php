<?php

	require_once( "../../common/reports/reportsinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	reportUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$targetFolder = base64_decode( $DF_ID );

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

					if ( !UR_RightsObject::CheckMask( $thisUserRights, TREE_ONLYREAD ) ) {
						$fatalError = true;
						$errorStr = $ddStrings['app_treenofldviewrights_message'];

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

	$preproc = new print_preprocessor( $DD_APP_ID, $kernelStrings, $language );

	$preproc->assign( REPORT_TITLE, $kernelStrings['app_treeaccessrights_title'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "thisUserRights", $thisUserRights );
		$preproc->assign( "targetFolder", $targetFolder );
		$preproc->assign( "folderName", $folderName );
		$preproc->assign( "folderIsShared", $folderIsShared );

		$preproc->assign( "folderUsers", $folderUsers );
		$preproc->assign( "folderGroups", $folderGroups );

		$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );
		$preproc->assign( "tree_access_mode_long_names", $tree_access_mode_long_names ); 
	}

	$preproc->display( "accessrightsinfo.htm" );
?>