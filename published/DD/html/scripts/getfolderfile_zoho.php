<?php

	$allow_page_caching = false;
	$get_key_from_url = true;

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	$SCR_ID = "CT";
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, true, true );

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];

	$fileData = dd_getDocumentData( base64_decode($DL_ID), $kernelStrings );
	if( PEAR::isError( $fileData ) )
		die( $res->getMessage() );

	if ( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL ) {
		$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $fileData->DF_ID, $kernelStrings );
		if( PEAR::isError( $rights ) )
			die( $locStrings[ERR_GENERALACCESS] );

	} else {
		$hasAccessToRecycled = checkUserAccessRights( $currentUser, "RB", $DD_APP_ID, false );

		if ( !$hasAccessToRecycled )
			if ( $fileData->DL_DELETE_U_ID != $currentUser )
				die( $ddStrings['dd_screen_norights_message'] );
	}

	$fileName = $fileData->DL_FILENAME;

	$fileSize = $fileData->DL_FILESIZE;
	$fileType = $fileData->DL_MIMETYPE;
	$diskFileName = $fileData->DL_DISKFILENAME;

	if( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL )
		$attachmentPath = dd_getFolderDir( $fileData->DF_ID )."/".$diskFileName;
	elseif ( $fileData->DL_STATUSINT == TREE_DLSTATUS_DELETED )
		$attachmentPath = dd_recycledDir()."/".$diskFileName;

	if (preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])) {
		if (preg_match("/[а-я]/ui", $fileName)) {
			$fileName = iconv("UTF-8", "Windows-1251", $fileName);
		} else {
			$fileName = rawurlencode($fileName);
		}
	}

if ($_GET['ID'] != md5_file($attachmentPath)) {
	die('Error: file not found');
}
	$silentMode = 1;

	if ( !file_exists($attachmentPath) || is_dir($attachmentPath) )
		die( "Error: file not found" );

	$metric = metric::getInstance();
	$metric->addAction(base64_decode($DB_KEY), $currentUser, 'DD', 'DOWNLOAD', 'LINK', $fileSize);
	
	if (onWebasystServer()) {
		$path = "/data" . substr($attachmentPath, strlen(WBS_DATA_DIR));
		
		header("Content-type: " . $fileType);
		header('Content-Disposition: inline; filename="' . $fileName . '"');
		header("Accept-Ranges: bytes");
		header("Content-Length: $fileSize");
		header("Expires: 0");
		header("Cache-Control: private");
		header("Pragma: public");
		header("Connection: close");
		header("X-Accel-Redirect: " . $path);
		exit;
	}

	@ini_set( 'async_send', 1 );

	header("Content-type: $fileType");
	header('Content-Disposition: inline; filename="' . $fileName . '"');
	header("Accept-Ranges: bytes");
//	header("Content-Length: $fileSize");
	header("Expires: 0");
	header("Cache-Control: private");
	header("Pragma: public");
	header("Connection: close");

	$fp = @fopen($attachmentPath, 'rb');

	while (!feof($fp)) {
		print @fread($fp, 1048576 );
		@ob_flush();
	}

	@fclose($fp);
	exit;
?>