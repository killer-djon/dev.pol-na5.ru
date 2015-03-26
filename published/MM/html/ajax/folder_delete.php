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
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	
	do {
		$params = array();
		$params['U_ID'] = $currentUser;
		$params['kernelStrings'] = $kernelStrings;

		$res = $mm_treeClass->deleteFolder( $folderId, $currentUser, $kernelStrings, false, "mm_onDeleteFolder", $params );
		if ( PEAR::isError($res) )
			$errorStr = $res->getMessage();
	} while (false);
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true}";
	}
	
?>