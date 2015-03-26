<?php

	$allow_page_caching = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	if ( $fatalError )
		die( "You are not authorized to work with this page" );

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	// Load ITL data
	//
	$ITLData = it_getITLData( $I_ID, $ITL_ID );
	if ( PEAR::isError($ITLData) || is_null( $ITLData ) ) 
		die( $itStrings[IT_ERR_LOADITL] );	 

	$P_ID = $ITLData["P_ID"];
	$PW_ID = $ITLData["PW_ID"];

	// Check user rights for this issue
	//
	$res = it_getUserITRights( $P_ID, $PW_ID, $currentUser, $I_ID );
	if ( PEAR::isError($res) )
		die( $kernelStrings[ERR_QUERYEXECUTING] );

	if ( !$res )
		die( $itStrings[IT_ERR_ISSUERIGHTS] );

	$attachmentsPath = it_getITLAttachmentsDir( $P_ID, $PW_ID, $I_ID, $ITL_ID );

	$fileData = getAttachedFileInfo( base64_decode($ITLData["ITL_ATTACHMENT"]), base64_decode($fileName) );
	if ( is_null($fileData) ) {
		header("HTTP/1.0 404 Not Found");
		die();
	}

	$fileName = $fileData["name"];
	$diskFileName = $fileData["diskfilename"];
	$fileType = $fileData["type"];

	$attachmentPath = sprintf( "%s/%s", $attachmentsPath, $diskFileName );

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

	$fp = @fopen($attachmentPath, 'rb');

	while (!feof($fp)) {
		print @fread($fp, 1048576 );
		@ob_flush();
	}

	@fclose($fp)

?>