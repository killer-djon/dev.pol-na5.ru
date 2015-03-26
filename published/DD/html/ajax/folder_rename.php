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
	
	$folderData["DF_NAME"] = $newName;
	$folderData["DF_ID"] = $folderID;
	$admin = true;
	$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings );
	$folderID = $dd_treeClass->addmodFolder( ACTION_EDIT, $currentUser, "ROOT", prepareArrayToStore($folderData),
										$kernelStrings, $admin, 'dd_onCreateFolder', $callbackParams, true, true );
	
	if ( PEAR::isError( $folderID ) ) {
		$errorStr = $folderID->getMessage();
	}
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, name: '$newName', newID : '$folderID', encNewID: '" . base64_encode($folderID) . "'}";
	}
	
?>