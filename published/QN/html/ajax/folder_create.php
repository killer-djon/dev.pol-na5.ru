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
	$saveBtnPressed = false;
	$accessInheritance = "COPY";
	
	$name = "New Folder";	
	$folderData["QNF_NAME"] = $name;
	$admin = false;
	$parentFolderId = ($parentId == TREE_AVAILABLE_FOLDERS) ? "ROOT" : $parentId;
	$callbackParams = array( 'qnStrings'=>$qnStrings, 'kernelStrings'=>$kernelStrings );
	
	$folderId = $qn_treeClass->addmodFolder( ACTION_NEW, $currentUser, $parentFolderId, prepareArrayToStore($folderData),
														$kernelStrings, $admin );
	if ( PEAR::isError( $folderId ) ) {
		$errorStr = $folderId->getMessage();
	}
	
	do {
		$userAccessRights[UR_REAL_ID] = $folderId;
		$saveResult =  $UR_Manager->SaveItem( $userAccessRights );
		if ( PEAR::isError( $saveResult ) )
		{
			$errorStr = $saveResult->getMessage();
			break;
		}

		$groupAccessRights[UR_REAL_ID] = $folderId;
		$saveResult =  $UR_Manager->SaveItem( $groupAccessRights );
		if ( PEAR::isError( $saveResult ) )
		{
			$errorStr = $saveResult->getMessage();
			break;
		}
	} while (false);
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, name: '$name', newID : '$folderId', parentId: '$parentId', encNewID: '" . base64_encode($folderId) . "'}";
	}
	
?>