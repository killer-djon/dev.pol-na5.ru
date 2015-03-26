<?php

	$allow_page_caching = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/QN/qn.php" );

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	$locStrings = $loc_str[$language];
	$qn_locStrings = $qn_loc_str[$language];

	if ( $fatalError )
		die( $locStrings[ERR_GENERALACCESS] );

	// Check user rights for this note
	//
	$rights = $qn_treeClass->getIdentityFolderRights( $currentUser, $QNF_ID, $locStrings );
	if ( PEAR::isError($rights) )
		die( $locStrings[ERR_GENERALACCESS] );

	if ( $rights < 0 || !strlen($rights) )
		die( $qn_locStrings[38] );

	$noteData = $qn_treeClass->getDocumentInfo( $QN_ID, $locStrings );
	if ( PEAR::isError($noteData) )
		die( $noteData->getMessage() );

	$RECORD_FILES = $noteData["QN_ATTACHMENT"];

	$attachmentsPath = qn_getNoteAttachmentsDir( $QN_ID );

	$fileData = getAttachedFileInfo( base64_decode($RECORD_FILES), base64_decode($fileName) );
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