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
	
	$folderData["MMF_NAME"] = $newName;
	$folderData["MMF_ID"] = $folderID;
	$admin = true;
	$callbackParams = array( 'mmStrings'=>$mmStrings, 'kernelStrings'=>$kernelStrings );
	$folderID = $mm_treeClass->addmodFolder( ACTION_EDIT, $currentUser, "ROOT", prepareArrayToStore($folderData),
										$kernelStrings, $admin, 'mm_onCreateFolder', $callbackParams, true, true );
	
	if ( PEAR::isError( $folderID ) ) {
		$errorStr = $folderID->getMessage();
	}
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, name: '$newName', newID : '$folderID', encNewID: '" . base64_encode($folderID) . "'}";
	}
	
?>