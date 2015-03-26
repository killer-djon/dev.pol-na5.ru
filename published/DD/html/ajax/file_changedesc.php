<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );
	require_once( "../../../common/html/includes/ajax.php" );	

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
	global $dd_treeClass;
	
		
	do {
		$docData = dd_getDocumentData( $id, $kernelStrings );
		if ( PEAR::isError($docData) ) return $docData;
    
		$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $docData->DF_ID, $kernelStrings );
		if ( PEAR::isError($error = $rights))	break;

		if ($rights < TREE_WRITEREAD) {
			$error = PEAR::raiseError ($ddStrings['add_screen_norights_message']);
			break;
		}
		
		$params = prepareArrayToStore($_POST);
		
		$newDesc = strip_tags($params["description"]);
		$res = dd_updateFileDescription($currentUser, $params["id"], $newDesc, &$kernelStrings);
		 
		if (PEAR::isError($error = $res)) break;
		
	} while (false);
	
	
	if (PEAR::isError($error)) {
		$ajaxRes["success"] = false;
		$ajaxRes["errorStr"] = $error->getMessage ();
	} else {
		$ajaxRes["success"] = true;
		$ajaxRes["newDesc"] = $newDesc;
	}	
	
	print $json->encode ($ajaxRes);		
?>