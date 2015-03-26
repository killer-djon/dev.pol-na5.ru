<?php

	$allow_page_caching = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	if ( $fatalError )
		die( $kernelStrings[ERR_GENERALACCESS] );

	// Check user rights for this issue
	//
	$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$I_ID), $issuedata, true );
	if ( PEAR::isError( $res ) ) 
		die( $itStrings[IT_ERR_LOADISSUEDATA] );

	$res = it_getUserITRights( $issuedata["P_ID"], $issuedata["PW_ID"], $currentUser, $I_ID );
	if ( PEAR::isError($res) )
		die( $kernelStrings[ERR_QUERYEXECUTING] );

	if ( !$res )
		die( $itStrings[IT_ERR_ISSUERIGHTS] );

	$attachmentsPath = it_getIssueAttachmentsDir( $issuedata["P_ID"], $issuedata["PW_ID"], $I_ID );

	$fileData = getAttachedFileInfo( base64_decode($issuedata["I_ATTACHMENT"]), base64_decode($fileName) );
	if ( is_null($fileData) ) {
		header("HTTP/1.0 404 Not Found");
		die();
	}

	$fileName = $fileData["name"];
	$diskFileName = $fileData["diskfilename"];
	$fileType = $fileData["type"];

	$fileSize = $fileData["size"];
	$filePath = sprintf( "%s/%s", $attachmentsPath, $diskFileName );

	if ( !file_exists($filePath) ) {
		header("HTTP/1.0 404 Not Found");
		die();
	}

	@ini_set( 'async_send', 1 );

	$silentMode = 1;

	header('Cache-Control: no-cache, must-revalidate');
	header("Accept-Ranges: bytes");
	header("Content-Length: $fileSize");
	header('Connection: close');
	header("Content-type: $fileType"); 
	header("Content-Disposition: inline; filename=$fileName;");

	$fp = @fopen($filePath, 'rb');

	while (!feof($fp)) {
		print @fread($fp, 1048576 );
		@ob_flush();
	}

	@fclose($fp)

?>