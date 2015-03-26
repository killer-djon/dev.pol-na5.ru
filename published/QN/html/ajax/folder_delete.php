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
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	
	do {
		$admin = false;
		
		$params = array();
		$params['U_ID'] = $currentUser;
		$params['kernelStrings'] = $kernelStrings;

		$res = $qn_treeClass->deleteFolder( $folderId, $currentUser, $kernelStrings, false, "qn_onDeleteFolder", $params );
		if ( PEAR::isError($res) )
			$errorStr = $res->getMessage();		
		
		if ( PEAR::isError($res) )
			$errorStr = $res->getMessage();
		
		
	} while (false);
	
	if ($errorStr) {
		print "{'success': false, errorStr: '$errorStr'}";
	} else {
		print "{'success': true}";
	}
	
?>