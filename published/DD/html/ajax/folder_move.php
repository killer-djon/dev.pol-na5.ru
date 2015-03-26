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
	$accessInheritance = ACCESSINHERITANCE_INHERIT;
	
	if (empty($parentFolderID) || $parentFolderID == TREE_AVAILABLE_FOLDERS )
		$parentFolderID = TREE_AVAILABLE_FOLDERS;
	
	do {
		$callbackParams = array( 'ddStrings'=>$ddStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$folderID, 'U_ID'=>$currentUser );

		if ($action == "copy") {
			$folderID = $dd_treeClass->copyFolder( $folderID, $parentFolderID, $currentUser, $kernelStrings, 
																		"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", "dd_onCreateFolder", 
																		$callbackParams, "dd_finishCopyFolder", $accessInheritance );
		} else {
			$folderID = $dd_treeClass->moveFolder( $folderID, $parentFolderID, $currentUser, $kernelStrings, 
																		"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", "dd_onCreateFolder", "dd_onDeleteFolder",
																		$callbackParams, "dd_onFinishMoveFolder", true, true, $accessInheritance );
			
			$dd_treeClass->setUserDefaultFolder( $currentUser, $folderID, $kernelStrings, $readOnly );
		}
		
		if ( PEAR::isError( $folderID ) ) {
			$errorStr = $folderID->getMessage();
			break;
		}
		
		$iconCls = "";
		$folderInfo = $dd_treeClass->getFolderInfo( $folderID, $kernelStrings );
		
		if ( PEAR::isError( $folderInfo ) ) {
			$errorStr = $folderInfo->getMessage();
			break;
		}
		
		if ($folderInfo["DF_SPECIALSTATUS"] >= 2)
			$iconCls = "system-folder";
	} while (false);
	
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, newID : '$folderID', iconCls: '$iconCls', encNewID: '" . base64_encode($folderID) . "'}";
	}
	
?>