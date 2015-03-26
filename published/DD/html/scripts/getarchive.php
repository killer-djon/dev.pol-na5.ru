<?php

	$allow_page_caching = false;

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$metric = metric::getInstance();
	
	$SCR_ID = "CT";
	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	if ( $fatalError )
		die( $locStrings[ERR_GENERALACCESS] );

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];

	$fileName = base64_decode($fileName);
	$fileDiskName = base64_decode($file);
	$archivePath = WBS_TEMP_DIR."/".$fileDiskName;

	$fileSize = filesize($archivePath);
	$fileType = 'application/x-zip-compressed';
	$diskFileName = $archivePath;

	$silentMode = 1;
	$metric->addAction($DB_KEY, $currentUser, 'DD', 'DOWNLOAD-ZIP', 'ACCOUNT', $fileSize);
	if ( !file_exists($archivePath) || is_dir($archivePath) )
		die( "Error: file not found" );

	@ini_set( 'async_send', 1 );

	header("Content-type: $fileType");
	header('Content-Disposition: inline; filename="' . $fileName . '"');
	header("Accept-Ranges: bytes");
	header("Content-Length: $fileSize");
	header("Expires: 0");
	header("Cache-Control: private");
	header("Pragma: public");
	header("Connection: close");

	$fp = @fopen($archivePath, 'rb');

	while (!feof($fp)) {
		print @fread($fp, 1048576 );
		@ob_flush();
	}

	@fclose($fp)
?>