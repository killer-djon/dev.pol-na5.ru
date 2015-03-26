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
	
	do {
		$operation = ($action == "move") ? TREE_MOVEDOC : TREE_COPYDOC;
		
		$callbackParams = array( 'ddStrings'=>$ddStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$folderId, 'U_ID'=>$currentUser );

		$res = $dd_treeClass->copyMoveDocuments( $documents, $folderId, $operation, $currentUser, $kernelStrings, 
				"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", $callbackParams,
				true, true, "dd_onFinishCopyMoveFiles" );
		
		if ( PEAR::isError( $res) ) {
			$errorStr = $res->getMessage();
			break;
		}
	} while (false);
	
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true}";
	}
	
?>