<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	require_once( "../../../common/html/includes/ajax.php" );	

	//
	// Authorization
	//

	$fatalError = false;
	$error = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	
	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	global $dd_treeClass;
	
		
	do {
		if (!isset($versions))
			break;
		
		$res = dd_deleteFileVersions( $DL_ID, $versions, $kernelStrings );
		
		if (PEAR::isError($error = $res)) 
			break;
		
	} while (false);
	
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
	}	
	
	print $json->encode ($ajaxRes);		
?>