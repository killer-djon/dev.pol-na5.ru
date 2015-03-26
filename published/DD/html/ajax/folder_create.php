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
	$saveBtnPressed = false;
	$accessInheritance = "COPY";
	
	$name = $ddStrings["dd_new_folder"];	
	$folderData["DF_NAME"] = $name;
	$admin = false;
	$parentFolderId = ($parentId == TREE_AVAILABLE_FOLDERS) ? "ROOT" : $parentId;
	$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings );
	do {
		$folderId = $dd_treeClass->addmodFolder( ACTION_NEW, $currentUser, $parentFolderId, prepareArrayToStore($folderData),
											$kernelStrings, $admin, 'dd_onCreateFolder', $callbackParams, true, true );
		if ( PEAR::isError( $folderId ) ) {
			$errorStr = $folderId->getMessage();
			break;
		}
	
	
		
		$iconCls = "";
		$folderInfo = $dd_treeClass->getFolderInfo( $folderId, $kernelStrings );
		
		if ( PEAR::isError( $folderId ) ) {
			$errorStr = $folderId->getMessage();
			break;
		}
	} while (false);
	
	/*do {
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
	} while (false);*/
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, name: '$name', newID : '$folderId', specialStatus: '{$folderInfo['DF_SPECIALSTATUS']}', parentId: '$parentId', iconCls: '$iconCls', encNewID: '" . base64_encode($folderId) . "'}";
	}
	
?>