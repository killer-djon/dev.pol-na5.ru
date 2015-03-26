<?php

	$allow_page_caching = false;

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	$SCR_ID = "CT";
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	if ( $fatalError ) {
		$errorStr = null;
		$fatalError = false;

		$SCR_ID = "RB";
		pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );
	}

	if ( $fatalError )
		die( $locStrings[ERR_GENERALACCESS] );

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];

	$fileData = dd_getDocumentData( base64_decode($DL_ID), $kernelStrings );
	if( PEAR::isError( $fileData ) )
		die( $res->getMessage() );

	if ( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL ) { 
		$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $fileData->DF_ID, $kernelStrings );
		if( PEAR::isError( $rights ) )
			die( $locStrings[ERR_GENERALACCESS] );

		if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) || !strlen($rights) )
			die( $ddStrings['dd_screen_norights_message'] );
	} else {
		$hasAccessToRecycled = checkUserAccessRights( $currentUser, "RB", $DD_APP_ID, false );

		if ( !$hasAccessToRecycled )
			if ( $fileData->DL_DELETE_U_ID != $currentUser )
				die( $ddStrings['dd_screen_norights_message'] );
	}

	$historyRecord = dd_getHistoryRecord( base64_decode($DL_ID), base64_decode($DLH_VERSION), $kernelStrings );
	if( PEAR::isError( $historyRecord ) )
		die( $locStrings[ERR_GENERALACCESS] );

	$diskFileName = $historyRecord['DLH_DISKFILENAME'];
	$attachmentPath = DD_HISTORY_DIR."/".$diskFileName;
	if ( !file_exists($attachmentPath) || is_dir($attachmentPath) )
		die( "Error: file not found" );

	$fileName = $fileData->DL_FILENAME;

	$fileSize = filesize($attachmentPath);
	$fileType = $fileData->DL_MIMETYPE;

	$silentMode = 1;

	@ini_set( 'async_send', 1 );

	header("Content-type: $fileType");
	header('Content-Disposition: inline; filename="' . $fileName . '"');
	header("Accept-Ranges: bytes");
	header("Content-Length: $fileSize");
	header("Expires: 0");
	header("Cache-Control: private");
	header("Pragma: public");
	header("Connection: close");

	$fp = @fopen($attachmentPath, 'rb');

	while (!feof($fp)) {
		print @fread($fp, 1048576 );
		@ob_flush();
	}

	@fclose($fp)
?>