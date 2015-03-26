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
	
	$folderData["QNF_NAME"] = $newName;
	$folderData["QNF_ID"] = $folderID;
	$admin = true;
	$callbackParams = array( 'qnStrings'=>$qnStrings, 'kernelStrings'=>$kernelStrings );
	$folderID = $qn_treeClass->addmodFolder( ACTION_EDIT, $currentUser, "ROOT", prepareArrayToStore($folderData),
										$kernelStrings, $admin, 'qn_onCreateFolder', $callbackParams, true, true );
	
	if ( PEAR::isError( $folderID ) ) {
		$errorStr = $folderID->getMessage();
	}
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true, name: '$newName', newID : '$folderID', encNewID: '" . base64_encode($folderID) . "'}";
	}
	
?>