<?php

	$allow_page_caching = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/MM/mm.php" );

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "MM";

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	$locStrings = $loc_str[$language];
	$mm_locStrings = $mm_loc_str[$language];

	if ( $fatalError )
		die( $locStrings[ERR_GENERALACCESS] );


	$MMF_ID = base64_decode($MMF_ID);

	// Check user rights for this note
	//
	$rights = $mm_treeClass->getIdentityFolderRights( $currentUser, $MMF_ID, $locStrings );
	if ( PEAR::isError($rights) )
		die( $locStrings[ERR_GENERALACCESS] );

	if ( $rights < 0 || !strlen($rights) )
		die( $mm_locStrings[38] );

	$noteData = $mm_treeClass->getDocumentInfo( $MMM_ID, $locStrings );
	if ( PEAR::isError($noteData) )
		die( $noteData->getMessage() );

	$RECORD_FILES = $noteData["MMM_ATTACHMENT"];

	$attachmentsPath = mm_getNoteAttachmentsDir( $MMM_ID );

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