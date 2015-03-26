<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( "../../../common/html/includes/ajax.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Add/Modify Issue page script
	//

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$invalidField = null;
	$ITS_Data = null;

	$DATA = $_POST;
	
	$res = it_logAddComment(prepareArrayToStore($DATA), $kernelStrings, $itStrings, $currentuser);
	

	if (!PEAR::isError($res)) {
		$res['ITL_DATETIME'] = convertToUserFriendlyDateTime( $res['ITL_DATETIME'], $kernelStrings );
		$res['U_ID_SENDER'] = getUserName($res['U_ID_SENDER']);
		$res['ITL_OLDCONTENT'] = nl2br(htmlspecialchars($res['ITL_OLDCONTENT']));
		$res["success"] = true;
	} else {
		$res = array ("success" => false, "errorStr" => $res->getMessage());
	}

	print $json->encode($res);	

?>