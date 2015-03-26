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
	$error = null;
	
	do {
		$folderInfo = $dd_treeClass->getFolderInfo( $folderId, $kernelStrings );
		$specialStatus = $folderInfo["DF_SPECIALSTATUS"];
		
		$admin = false;
		$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, "language" => $language ,'U_ID'=>$currentUser);
		if ($specialStatus) {
			$error = $dd_treeClass->deleteFolder( $folderId, $currentUser, $kernelStrings, true,
																"dd_onDeleteFolder", $callbackParams, false );
		} else {
			$error = $dd_treeClass->recycleFolder( $folderId, $currentUser, $kernelStrings, $ddStrings,
											false, "dd_onDeleteFolder", array('ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, 'U_ID'=>$currentUser), false );
		}
		if ( PEAR::isError($error) )
			break;
		
	} while (false);
	
	if (PEAR::isError($error)) {
		print "{'success': false, errorStr: '" . $error->getMessage() . "'}";
	} else {
		print "{'success': true}";
	}
	
?>