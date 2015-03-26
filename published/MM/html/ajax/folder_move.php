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
	$saveBtnPressed = false;
	$accessInheritance = "COPY";
	
	if (empty($parentFolderID) || $parentFolderID == TREE_AVAILABLE_FOLDERS )
		$parentFolderID = TREE_AVAILABLE_FOLDERS;
	
	$callbackParams = array( 'mmStrings'=>$mmStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$folderID, 'U_ID'=>$currentUser );

	if ($action == "copy") {
		$folderID = $mm_treeClass->copyFolder( $folderID, $parentFolderID, $currentUser, $kernelStrings, 
																	"mm_onAfterCopyMoveMessage", "mm_onCopyMoveNote", null, 
																	$callbackParams, null, $accessInheritance);
	} else {
		$folderID = $mm_treeClass->moveFolder( $folderID, $parentFolderID, $currentUser, $kernelStrings, 
																		"mm_onAfterCopyMoveMessage", "mm_onCopyMoveNote", null, null,
																		$callbackParams, "mm_onFinishMoveFolder", true, true, $accessInheritance );		
	}
	
	if ( PEAR::isError( $folderID ) ) {
		$errorStr = $folderID->getMessage();
	}
		if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, newID : '$folderID', encNewID: '" . base64_encode($folderID) . "'}";
	}
	
?>