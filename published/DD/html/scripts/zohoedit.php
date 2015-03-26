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

	$FILE_DATA = (array)$fileData;
	list($proto, $ver) = split('/', $_SERVER['SERVER_PROTOCOL']);
	if(strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['SERVER_PORT'] == 443) {
		$proto = 'https'; 
	} else {
		$proto = 'http';
	}

	$FILE_DATA['SRC'] = $_GET['DL_ID'];


	/**
	* COLLECT DATA FOR ZOHO
	*/
	$trsnfVars['DL_ID'] = $FILE_DATA['DL_ID'];
	$trsnfVars['DL_CURRENT_USER'] = $currentUser;
	$trsnfVars['DL_FVERSION'] = dd_getFileVersion( $FILE_DATA['DL_ID'], $kernelStrings );
	$b64scfd = base64_encode(gzcompress(serialize($trsnfVars),9));
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );
	
	$writer = array ('doc', 'txt', 'odt',);
	$sheet = array ('xls', 'ods', );

	$secretkey=dd_getzohoKey($kernelStrings);
	if (in_array($FILE_DATA['DL_FILETYPE'], $writer))
		$preproc->assign("REMOTE", "https://export.writer.zoho.com/remotedoc.im?output=editor&apikey=". $secretkey );
	elseif (in_array($FILE_DATA['DL_FILETYPE'], $sheet))
		$preproc->assign("REMOTE", "https://sheet.zoho.com/remotedoc.im?output=editor&apikey=". $secretkey );
	
	$preproc->assign("FILE", $FILE_DATA);
	$preproc->assign("DBKEY", base64_encode(trim(strtoupper($_SESSION['wbs_dbkey']))));
	$preproc->assign("HOST", strtolower($proto).'://'.$_SERVER['HTTP_HOST'] . dirname($_SERVER["REQUEST_URI"]));
//	$preproc->assign("HOST", 'http://'.$_SERVER['HTTP_HOST'] . dirname($_SERVER["REQUEST_URI"]));
	$preproc->assign("B64SCFD", $b64scfd);
	$preproc->assign("hasZohoKey", $secretkey != '');
	$preproc->assign("message_zohokey_not_found", $ddStrings['sv_zohokey_not_found'] );

	$preproc->assign("FID", md5_file($attachmentPath));
	$preproc->display( "zohoedit.htm" );
?>