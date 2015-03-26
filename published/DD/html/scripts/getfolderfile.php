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

	$fileName = $fileData->DL_FILENAME;

	$fileSize = $fileData->DL_FILESIZE;
	$fileType = $fileData->DL_MIMETYPE;
	$diskFileName = $fileData->DL_DISKFILENAME;

	if( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL )
		$attachmentPath = dd_getFolderDir( $fileData->DF_ID )."/".$diskFileName;
	elseif ( $fileData->DL_STATUSINT == TREE_DLSTATUS_DELETED )
		$attachmentPath = dd_recycledDir()."/".$diskFileName;

	$silentMode = 1;

	if ( !file_exists($attachmentPath) || is_dir($attachmentPath) )
		die( "Error: file not found" );
	
	if (preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])) {
	    if (preg_match("/[а-я]/ui", $fileName)) {
    	    $fileName = iconv("UTF-8", "Windows-1251", $fileName);
	    } else {
	        $fileName = rawurlencode($fileName);
	    }
	}
		
	$metric = metric::getInstance();
	$metric->addAction($DB_KEY, $currentUser, 'DD', 'DOWNLOAD', 'ACCOUNT', $fileSize);

	//default open images in browser (for mf iexplorer)
	if (strpos($fileType, 'img') !== false || strpos($fileType, 'image') !== false || strpos($fileType, 'pdf') !== false) { 
		$ContentDisposition  =  'Content-Disposition: inline; filename="' . $fileName . '"';
	} else {
		$ContentDisposition  = "Content-disposition: attachment; filename=\"".$fileName."\"";
	}
	//when force-download, images open in 'save dialog'
	if ($_GET['force'] == 'download') {
	    $fileType = 'octet/stream';
    	$ContentDisposition  = "Content-disposition: attachment; filename=\"".$fileName."\"";
  	}
  
	@session_write_close();
	if (onWebasystServer()) {
		$path = "/data" . substr($attachmentPath, strlen(WBS_DATA_DIR));
		
		header("Content-type: " . $fileType);
		header($ContentDisposition);
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
	header($ContentDisposition);
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

	@fclose($fp);
?>