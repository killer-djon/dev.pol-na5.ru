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
	
	if (empty($parentFolderID) || $parentFolderID == TREE_AVAILABLE_FOLDERS )
		$parentFolderID = TREE_AVAILABLE_FOLDERS;
	
	$callbackParams = array( 'qnStrings'=>$qnStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$folderID, 'U_ID'=>$currentUser );

	if ($action == "copy") {
		$newQNF_ID = $qn_treeClass->copyFolder( $folderID, $parentFolderID, $currentUser, $kernelStrings,
																	"qn_onAfterCopyMoveNote", "qn_onCopyMoveNote", null,
																	$callbackParams, null, $accessInheritance);
	} else {
		$folderID = $qn_treeClass->moveFolder( $folderID, $parentFolderID, $currentUser, $kernelStrings, 
																		"qn_onAfterCopyMoveNote", "qn_onCopyMoveNote", null, null,
																		$callbackParams, "qn_onFinishMoveFolder", true, true, $accessInheritance );		
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