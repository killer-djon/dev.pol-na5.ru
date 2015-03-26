<?php

	$allow_page_caching = false;

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	$SCR_ID = "QP";
	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	if ( $fatalError )
		die( $locStrings[ERR_GENERALACCESS] );

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];

	$fileName = base64_decode($fileName);
	$fileDiskName = base64_decode($file);
	$archivePath = WBS_TEMP_DIR."/".$fileDiskName;

	$fileSize = filesize($archivePath);
	$fileType = 'application/x-zip-compressed';
	$diskFileName = $archivePath;

	$silentMode = 1;

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