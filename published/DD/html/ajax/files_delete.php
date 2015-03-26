<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	
	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$error = null;
	$SCR_ID = "CT";
	
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	
	//
	// Page variables setup
	//
	
	do {
		$kernelStrings = $loc_str[$language];
		$ddStrings = $dd_loc_str[$language];
		
		$res = dd_deleteRestoreDocuments( $documents, DD_DELETEDOC, $currentUser, $kernelStrings, $ddStrings );
		if ( PEAR::isError($error = $res) )
			break;
	} while (false);
	
	$ajaxRes = array ();
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
	}	
	
	print $json->encode ($ajaxRes);
?>