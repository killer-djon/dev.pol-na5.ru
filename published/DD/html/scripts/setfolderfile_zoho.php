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

	pageUserAuthorization( "CT", $DD_APP_ID, true, true );

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];

	//GET DATA FROM ZOHO
	$FILE_DATA = unserialize( gzuncompress(base64_decode($_POST['id'])) );
	$currentUser = $FILE_DATA['DL_CURRENT_USER'];
	$DL_ID = $FILE_DATA['DL_ID'];
	
	//GET FILE INFO FROM DB
	$fileData = dd_getDocumentData( $DL_ID, $kernelStrings );
	if( PEAR::isError($fileData) )
		 die( $res->getMessage() );

	//CHECK RIGHTS
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

	//GET FULL FILENAME PATH
	$diskFileName = $fileData->DL_DISKFILENAME;

	if( $fileData->DL_STATUSINT == TREE_DLSTATUS_NORMAL )
		$attachmentPath = dd_getFolderDir( $fileData->DF_ID )."/".$diskFileName;
	elseif ( $fileData->DL_STATUSINT == TREE_DLSTATUS_DELETED )
		$attachmentPath = dd_recycledDir()."/".$diskFileName;

	function getFileVersion($DL_ID) {
		$SQL = "SELECT MAX( DLH_VERSION ) as MAX FROM `DOCLISTHISTORY` WHERE `DL_ID`='".$DL_ID."'";
		$rez = db_query($SQL);
		$row = db_fetch_array($rez, MYSQL_NUM);
		return (int)$row['MAX'];
	}
	
	$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
	
	$tmpFName = $_FILES['content']['tmp_name'];
	$newFSize = $_FILES['content']['size'];
	$thsFVers = (int)$FILE_DATA['DL_FVERSION'];
	
	##version control DISABLED##

	if (!$versionControlEnabled) {
		$res = dd_updateFile($DL_ID, $tmpFName, $attachmentPath, $newFSize);
	} else {
		
	##version control ENABLED###
		
		$fver = dd_getFileVersion( $DL_ID, $kernelStrings );
		//check new version existance
		$debug[] = 'recv '.$thsFVers;
		$debug[] = 'curr '.$fver;
		$debug[] = 'dl_id '.$DL_ID;
		
		if ($thsFVers != $fver) {
			//if new version was created, update only
			$res = dd_updateFile($DL_ID,$tmpFName,$attachmentPath,$newFSize);
		} else {
			//if new version doesn't exist, create and update
			$tmpFileName 	= uniqid( TMP_FILES_PREFIX );
			$destPath 		= WBS_TEMP_DIR."/".$tmpFileName;
			$srcName 		= $_FILES['content']['tmp_name'];
			@move_uploaded_file( $srcName, $destPath );
			$fileObj = new dd_fileDescription();
			$fileObj->DL_FILENAME = $fileData->DL_FILENAME;
			$fileObj->DL_FILESIZE = $_FILES['content']['size'];
			$fileObj->DL_DESC = $fileData->DL_DESC;
			$fileObj->DL_MIMETYPE = $fileData->DL_MIMETYPE;
			$fileObj->DL_VERSIONCOMMENT = 'Auto version. Created at '.date('d/m/Y H:i:s',time());
			$fileObj->sourcePath = $destPath;
			$fileList[] = $fileObj;
			$resultStatistics = null;
			$DF_ID = $fileData->DF_ID;
			$err = dd_addFiles(
							$fileList,
							$DF_ID,
							$currentUser,
							$kernelStrings,
							$ddStrings,
							$messageStack,
							$lastFileName,
							$resultStatistics,
							true,
							$existingFileOperation
						);
			if (PEAR::isError($err))
				return false;
			else
				return true;

		}
	}

	print ($res === true)?'Document saved':'ERROR! NOT SAVED!';

?>