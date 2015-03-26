<?php

	//
	// Document Depot DMBS-independent application functions
	//

	function dd_addFiles( $fileList, $DF_ID, $U_ID, $kernelStrings, $ddStrings, &$messageStack, &$lastFileName, &$resultStatistics, $generateThumbnail = true, $existingFilesOperation = DD_REPLACE_FILES, $removeFilesAfterCopy = true, $fromWidget = false, $fromFlash = false)
	//
	// Adds files to folders
	//
	//		Parameters:
	//			$fileList - array of dd_fileDescription objects
	//			$DF_ID - folder identifier
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//			$messageStack - message stack
	//			$lastFileName - last processed file name
	//			$resultStatistics - file adding statistics
	//			$generateThumbnail - generate thumbnails for image files
	//			$existingFilesOperation - operation to perform on existing files
	//			$fromWidget - adding widget name
	//
	//		Returns null information or PEAR_Error
	//
	{
		global $qr_dd_insertFile;
		global $qr_dd_updateFile;
		global $qr_dd_getMaxDL_ID;
		global $dd_treeClass;
		global $DD_APP_ID;
		global $dd_knownImageFormats;
		
		$filesLimitErrorCode = 502;
		$spaceLimitErrorCode = 503;
		$userQuotaErrorCode = 504;
		$fileCheckedErrorCode = 505;
	
		$resultStatistics = array();

		$resultStatistics['filesAdded'] = 0;
		$resultStatistics['imagesAdded'] = 0;
		$resultStatistics['imageErrors'] = 0;

		// Check user rights
		//
		if (!$fromWidget) {
			$rights = $dd_treeClass->getIdentityFolderRights( $U_ID, $DF_ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
				return PEAR::raiseError( $ddStrings['add_screen_norights_message'], ERRCODE_APPLICATION_ERR );
		}

		// Evaluate version control status
		//
		$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
		if ( $versionControlEnabled ) {
			$versionOverrideEnabled = null;
			$maxVersionsNum = null;
			dd_getVersionOverrideParams( $versionOverrideEnabled, $maxVersionsNum, $kernelStrings );
		}

		// Make folder directories
		//
		$res = dd_makeFolderDirs( $DF_ID, $ddStrings );
		if ( PEAR::isError($res) )
			return $res;

		// Check if thumbnail generation is enabled
		//
		$thumbnailEnabled = readApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBENABLED, $kernelStrings );
		$thumbnailEnabled = $thumbnailEnabled == DD_THUMBENABLED && dd_thumbnailsSupported();

		$resultFileList = array();

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $DD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		$InitialTotalSpace = $TotalUsedSpace;
		$InitialUserSpace = $UserUsedSpace;

		foreach ( $fileList as $fileDescription ) {
			// Check size limitations
			//
			$fileSize = $fileDescription->DL_FILESIZE;

			$lastFileName = $fileDescription->DL_FILENAME;

			$UserUsedSpace = $InitialUserSpace + $QuotaManager->GetSpaceUsageAdded();

			$TotalUsedSpace = $InitialTotalSpace + $QuotaManager->GetSpaceUsageAdded();

			// Check if the user disk space quota is not exceeded
			//
			if (!$fromWidget) {
				if ( $QuotaManager->UserApplicationQuotaExceeded( $UserUsedSpace + $fileSize, $U_ID, $DD_APP_ID, $kernelStrings ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $kernelStrings['app_usersizelimit_message'], ERRCODE_APPLICATION_ERR, null, null, $userQuotaErrorCode );
				}
			}			
			
			// Check if the system disk space quota is not exceeded
			//
			if ( $QuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) ) {
				$QuotaManager->Flush( $kernelStrings );
				$error = $QuotaManager->ThrowNoSpaceError( $kernelStrings );
				$error->userinfo = $spaceLimitErrorCode;
				return $error;
			}

			$res = dd_documentAddingPermitted( $kernelStrings, $ddStrings );
			if ( PEAR::isError($res) ) {
				$QuotaManager->Flush( $kernelStrings );
				$res->userinfo = $filesLimitErrorCode;
				return $res;
			}

			// Process version control
			//
			$createNewFileRecord = true;

			if ( $versionControlEnabled ) {
				$fileInfo = dd_getFileByName( $fileDescription->DL_FILENAME, $DF_ID, $kernelStrings );
				if ( PEAR::isError($fileInfo) ) {
					$QuotaManager->Flush( $kernelStrings );
					return $fileInfo;
				}

				if ( !is_null($fileInfo) ) {

					// Skip existing file if user selected the skip option
					//
					if ( $existingFilesOperation == DD_SKIP_FILES )
						continue;

					$createNewFileRecord = false;

					// File already exists, check if it is not checked out by another user
					//
					if ( $fileInfo['DL_CHECKSTATUS'] == DD_CHECK_OUT ) {

						// File is checked out, check owner
						//
						if ( $fileInfo['DL_CHECKUSERID'] == $U_ID ) {

							// Checked out by this user
							//
							$res = dd_createFileHistoryRecord( $U_ID, $fileDescription->DL_DESC, $fileInfo['DL_ID'], $ddStrings, $kernelStrings, $versionOverrideEnabled, $maxVersionsNum, $QuotaManager, $fromWidget );
							if ( PEAR::isError($res) ) {
								$QuotaManager->Flush( $kernelStrings );
								return $res;
							}
						} else {

							// Checked out by another user. Skip this file
							//
							if ( !is_null($messageStack) )
								$messageStack[] = sprintf ( $ddStrings['add_screen_upload_locked'],
															$fileDescription->DL_FILENAME,
															getUserName($fileInfo['DL_CHECKUSERID'], true) );
							if ($fromFlash)
								return PEAR::raiseError ($messageStack[sizeof($messageStack)-1], null, null, null, $fileCheckedErrorCode);

							continue;
						}
					} else {

						// File is not checked out
						//
						$res = dd_createFileHistoryRecord( $U_ID, $fileDescription->DL_DESC, $fileInfo['DL_ID'], $ddStrings, $kernelStrings, $versionOverrideEnabled, $maxVersionsNum, $QuotaManager, $fromWidget );
						if ( PEAR::isError($res) ) {
							$QuotaManager->Flush( $kernelStrings );
							return $res;
						}
					}
				}
			}

			// Move file to folder directory
			//
			if ( $createNewFileRecord )
				$diskFileName = dd_generateUniqueDiskFilename( $fileDescription->DL_FILENAME, $DF_ID );
			else
				$diskFileName = $fileInfo['DL_DISKFILENAME'];

			$destPath = sprintf( "%s/%s", dd_getFolderDir( $DF_ID ), $diskFileName );

			if ( !@copy( $fileDescription->sourcePath, $destPath ) ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $ddStrings['app_copyerr_message'] );
			}

			if ( $removeFilesAfterCopy )
				@unlink( $fileDescription->sourcePath );

			if ( $createNewFileRecord ) {
				$DL_ID = db_query_result( $qr_dd_getMaxDL_ID, DB_FIRST, array() );
				if ( PEAR::isError($DL_ID) ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				$DL_ID = incID($DL_ID);
			} else {
				$DL_ID = $fileInfo['DL_ID'];
			}

			$params = array();
			$params['DF_ID'] = $DF_ID;
			$params['DL_ID'] = $DL_ID;
			$params['DL_DESC'] = $fileDescription->DL_DESC;
			$params['DL_FILENAME'] = $fileDescription->DL_FILENAME;
			$params['DL_FILETYPE'] = dd_getFileType( $fileDescription->DL_FILENAME );
			$params['DL_FILESIZE'] = $fileDescription->DL_FILESIZE;
			$params['DL_UPLOADUSERNAME'] = getUserName( $U_ID, true );
			if ($fromWidget)
				$params['DL_UPLOADUSERNAME'] = $fromWidget;
			$params['DL_MIMETYPE'] = $fileDescription->DL_MIMETYPE;
			$params['DL_DISKFILENAME'] = $diskFileName;
			$params['DL_STATUSINT'] = TREE_DLSTATUS_NORMAL;
			$params['DL_CHECKSTATUS'] = DD_CHECK_IN;
			if (!$fromWidget) {
				$params['DL_MODIFYUSERNAME'] = $U_ID;
				$params['DL_CHECKUSERID'] = $U_ID;
				$params['DL_OWNER_U_ID'] = $U_ID;
			} else
				$params['DL_MODIFYUSERNAME'] = $fromWidget;
			$params['DL_VERSIONCOMMENT'] = $fileDescription->DL_VERSIONCOMMENT;
			
			// Generate thumbnail
			//
			$originalFileInfo = pathinfo( $fileDescription->DL_FILENAME );
			$thumbGenerated = false;
			$thumbPath = null;
			$ext = trim(strtolower($originalFileInfo['extension']));

			if ( $thumbnailEnabled && isset($originalFileInfo['extension']) && $generateThumbnail ) {

				if ( !$createNewFileRecord ) {
					$ext1 = null;
					$srcThumbFile = findThumbnailFile( $destPath, $ext1 );
					if ( $srcThumbFile )
						$QuotaManager->AddDiskUsageRecord( $U_ID, $DD_APP_ID, -1*filesize($srcThumbFile) );
				}

				$thumbPath = makeThumbnail( $destPath, $destPath, $ext, 96, $kernelStrings );
				if ( !PEAR::isError($thumbPath) )
					if ( $thumbPath )
						$thumbGenerated = true;
			}

			clearstatcache();

			// Insert file record
			//
			$resultFileList[$DF_ID][] = $params;

			if ( $createNewFileRecord )
				$res = db_query( $qr_dd_insertFile, $params );
			else
				$res = db_query( $qr_dd_updateFile, $params );
			
			if ( PEAR::isError($res) ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			// Update disk usage
			//
			$QuotaManager->AddDiskUsageRecord( $U_ID, $DD_APP_ID, $fileDescription->DL_FILESIZE );

			if ( $thumbGenerated )
				$QuotaManager->AddDiskUsageRecord( $U_ID, $DD_APP_ID, filesize($thumbPath) );

			$resultStatistics['filesAdded'] = $resultStatistics['filesAdded'] + 1;
			if ( in_array($ext, $dd_knownImageFormats) )
				$resultStatistics['imagesAdded'] = $resultStatistics['imagesAdded'] + 1;

			if ( !$thumbGenerated && PEAR::isError($thumbPath) )
				$resultStatistics['imageErrors'] = $resultStatistics['imageErrors'] + 1;

			if ( !is_null($messageStack) ) {
				$messageStack[] = sprintf ( $ddStrings['add_screen_upload_success'], $fileDescription->DL_FILENAME );

				if ( $thumbGenerated )
					$messageStack[] = sprintf( $ddStrings['add_screen_upload_info'], $fileDescription->DL_FILENAME, $kernelStrings['app_thumbcreated_message'] );
				else
					if ( PEAR::isError($thumbPath) )
						$messageStack[] = sprintf( $ddStrings['add_screen_upload_info'], $fileDescription->DL_FILENAME, $thumbPath->getMessage() );
			}
		}

		$QuotaManager->Flush( $kernelStrings );

		if (!$fromFlash)
			dd_sendNotifications( $resultFileList, $U_ID, DD_ADDDOC, $kernelStrings );

		return null;
	}

	function dd_documentAddingPermitted( &$kernelStrings, &$ddStrings )
	//
	// Checks whether adding document is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		$limit = getApplicationResourceLimits( 'DD' );
		if ( $limit === null )
			return null;

		$sql = "SELECT COUNT(*) FROM DOCLIST"; 

		$res = db_query_result( $sql, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $res >= $limit )
		{
			if ( hasAccountInfoAccess($currentUser) )
				return PEAR::raiseError( sprintf( $ddStrings['app_doclimit_message'], $limit )." ".getUpgradeLink($kernelStrings), ERRCODE_APPLICATION_ERR );
			else
				return PEAR::raiseError( sprintf( $ddStrings['app_doclimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'], ERRCODE_APPLICATION_ERR );
		}
	}

	function dd_checkFilesExistence( $fileList, $DF_ID, $U_ID, $kernelStrings, $ddStrings, &$filesFound )
	//
	// Check if files already exists in the folder
	//
	//		Parameters:
	//			$fileList - array of dd_fileDescription objects
	//			$DF_ID - folder identifier
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//			$filesFound - sets to true if any files already exists
	//
	//		Returns array with file data or PEAR_Error
	//
	{
		$filesFound = false;

		$result = array();

		foreach ( $fileList as $fileDescription ) {
			$fileInfo = dd_getFileByName( $fileDescription->DL_FILENAME, $DF_ID, $kernelStrings );
			if ( PEAR::isError($fileInfo) )
				return $fileInfo;

			if ( !count($fileInfo) || is_null($fileInfo) )
				continue;

			if ( !is_null($fileInfo) )
				if ( $fileInfo['DL_CHECKSTATUS'] == DD_CHECK_OUT && $fileInfo['DL_CHECKUSERID'] != $U_ID )
					$fileInfo['LOCKED'] = 1;
				else
					$fileInfo['LOCKED'] = 0;

			$result[] = $fileInfo;
			if ( !is_null($fileInfo) )
				$filesFound = true;
		}

		return $result;
	}

	function dd_createFileHistoryRecord( $U_ID, $fileDescription, $DL_ID, &$ddStrings, &$kernelStrings, $versionOverrideEnabled, $maxVersionNum, &$QuotaManager, $fromWidget = false )
	//
	// Creates file history record
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$fileDescription - file description
	//			$DL_ID - file identifier
	//			$ddStrings - Document Depot localization strings
	//			$kernelStrings - Kernel localization strings
	//			$QuotaManager - DiskQuotaManager object
	//			$fromWidget - if added from widget
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		global $qr_dd_selectMaxHistoryID;
		global $qr_dd_insertHistory;
		global $qr_dd_selectOldFileVersions;
		global $qr_dd_deleteFileVersion;

		if ( $versionOverrideEnabled ) {
			// Delete old verions
			//
			$params = array( 'DL_ID'=>$DL_ID );

			$staleVersions = array();

			$qr = db_query( sprintf( $qr_dd_selectOldFileVersions, $maxVersionNum-1 ), $params );
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array($qr) )
				$staleVersions[] = $row;

			db_free_result($qr);

			foreach ( $staleVersions as $staleVersionData ) {
				$destPath = DD_HISTORY_DIR."/".$staleVersionData['DLH_DISKFILENAME'];
				if ( file_exists($destPath) )
					@unlink($destPath);

				$QuotaManager->AddDiskUsageRecord( $staleVersionData['DL_OWNER_U_ID'], $DD_APP_ID, -1*$staleVersionData['DLH_SIZE'] );

				db_query( $qr_dd_deleteFileVersion, $staleVersionData );
			}
		}

		$fileData = dd_getDocumentData( $DL_ID, $kernelStrings );
		if ( PEAR::isError($fileData) )
			return $fileData;

		// Generate unique file name and copy file to the history directory
		//
		$fileName = uniqid("HIS");

		$errStr = null;
		if ( !file_exists(DD_HISTORY_DIR) )
			forceDirPath( DD_HISTORY_DIR, $errStr );

		$srcPath = dd_getFolderDir( $fileData->DF_ID )."/".$fileData->DL_DISKFILENAME;
		$destPath = DD_HISTORY_DIR."/".$fileName;

		$res = @copy( $srcPath, $destPath );
		if ( !$res )
			return PEAR::raiseError( $ddStrings['app_copyerr_message'] );

		// Create database record
		//
		$params = array();
		$params['DL_ID'] = $DL_ID;

		$DLH_VERSION = db_query_result( $qr_dd_selectMaxHistoryID, DB_FIRST, $params );
		if ( PEAR::isError($DLH_VERSION) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$DLH_VERSION = incID($DLH_VERSION);

		$params = array();
		$params['DL_ID'] = $DL_ID;
		$params['DLH_VERSION'] = $DLH_VERSION;
		$params['DLH_USERNAME'] = getUserName( $U_ID, true );
		if ($fromWidget)
			$params['DLH_USERNAME'] = $fromWidget;
		$params['DLH_DISKFILENAME'] = $fileName;
		$params['DLH_DESC'] = $fileData->DL_VERSIONCOMMENT;
		$params['DLH_SIZE'] = filesize($destPath);

		$res = db_query( $qr_dd_insertHistory, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function dd_getFileByName( $fileName, $DF_ID, &$kernelStrings )
	//
	// Returns file data by file name
	//
	//		Parameters:
	//			$fileName - file name
	//			$DF_ID - file folder
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or null PEAR::Error
	//
	{
		global $qr_dd_selectFileByName;

		$params = array();
		$params['DL_FILENAME'] = strtolower(trim($fileName));
		$params['DF_ID'] = $DF_ID;

		$res = db_query_result( $qr_dd_selectFileByName, DB_ARRAY, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !is_array($res) || !count($res) )
			return null;

		if ( !strlen($res['DL_FILENAME']) )
			return null;

		return $res;
	}

	function dd_getDocumentData( $DL_ID, $kernelStrings )
	//
	// Returns document data (object containing record from DOCLIST table)
	//
	//		Parameters:
	//			$DL_ID - document identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns object or PEAR_Error
	//
	{
		global $qr_dd_selectFile;

		$res = db_query_result( $qr_dd_selectFile, DB_ARRAY, array('DL_ID'=>$DL_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return (object)$res;
	}
	
	function dd_getDocumentDataBySlug( $DL_LINK_SLUG, $kernelStrings )
	//
	// Returns document data (object containing record from DOCLIST table)
	//
	//		Parameters:
	//			$DL_LINK_SLUG - document identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns object or PEAR_Error
	//
	{
		global $qr_dd_selectFileBySlug;

		$res = db_query_result( $qr_dd_selectFileBySlug, DB_ARRAY, array('DL_LINK_SLUG'=>$DL_LINK_SLUG) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return (object)$res;
	}

	function dd_getUserName( $U_ID )
	//
	// Returns user name for using in the messages
	//
	//		Parameters:
	//			$U_ID - User identifier
	//
	//		Returns string or PEAR_Error
	//
	{
		$userName = getUserName($U_ID, true);

		return $userName;
	}

	function dd_changeFileCheckStatus( $U_ID, $DL_ID, $status, $kernelStrings )
	//
	// Checks file in
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$DL_ID - file identifier
	//			$status - file check status
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_dd_updateFileCheckStatus;

		$res = dd_checkUserCheckOperationRights( $U_ID, $DL_ID, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		if ( !$res )
			return false;

		$params = array();
		$params['DL_ID'] = $DL_ID;
		$params['DL_CHECKUSERID'] = $U_ID;
		$params['DL_CHECKSTATUS'] = $status;

		$res = db_query( $qr_dd_updateFileCheckStatus, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return true;
	}

	function dd_checkUserCheckOperationRights( $U_ID, $DL_ID, &$kernelStrings )
	//
	// Checks if user has rights to check in or out file
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$DL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $dd_treeClass;

		$docData = dd_getDocumentData( $DL_ID, $kernelStrings );
		if ( PEAR::isError($docData) )
			return $docData;

		$rights = $dd_treeClass->getIdentityFolderRights( $U_ID, $docData->DF_ID, $kernelStrings );
		if ( PEAR::isError($rights) )
			return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
			return false;

		if ( UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
			return true;

		if ( $docData->DL_CHECKSTATUS == DD_CHECK_OUT )
			if ( $docData->DL_CHECKUSERID == $U_ID )
				return true;
			else
				return false;

		return true;
	}

	function dd_deleteRestoreDocuments( $documentList, $operation, $U_ID, $kernelStrings, $ddStrings, $destDF_ID = null, $admin = false, $fromWidget = false )
	//
	// Moves document to recycle bin or back to folder
	//
	//		Parameters:
	//			$documentList - array of document identifiers
	//			$operation - file operation - DD_DELETEDOC, DD_RESTOREDOC
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//			$destDF_ID - destination folder for restore operation
	//			$admin - do not perform rights checking
	//			$fromWidget - if method called from widget - name of widget
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_dd_updateFileStatus;
		global $qr_dd_updateFileLocationStatus;
		global $dd_treeClass;

		$versionControlEnabled = dd_versionControlEnabled($kernelStrings);

		// Check user rights for restore operation
		//
		if ( $operation == DD_RESTOREDOC ) {
			if ( is_null($destDF_ID) )
				return PEAR::raiseError( $ddStrings['sv_screen_nodest_message'], ERRCODE_APPLICATION_ERR );

			if ( !$admin ) {
				$rights = $dd_treeClass->getIdentityFolderRights( $U_ID, $destDF_ID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, array(TREE_WRITEREAD, TREE_READWRITEFOLDER) ) )
					return PEAR::raiseError( $ddStrings['r_screen_norights_message'], ERRCODE_APPLICATION_ERR );
			}
		}

		$resultFileList = array();

		foreach( $documentList as $DL_ID ) {
			$docData = dd_getDocumentData( $DL_ID, $kernelStrings );
			if ( PEAR::isError($docData) )
				return $docData;

			if ( $versionControlEnabled && $operation == DD_RESTOREDOC ) {
				// Delete existing file in the destination folder
				//
				$fileInfo = dd_getFileByName( $docData->DL_FILENAME, $destDF_ID, $kernelStrings );
				if ( PEAR::isError($fileInfo) )
					return $fileInfo;

				if ( !is_null($fileInfo) ) {
					if ( $fileInfo['DL_CHECKSTATUS'] == DD_CHECK_OUT )
						if ( $fileInfo['DL_CHECKUSERID'] != $U_ID ) {
							$userName = dd_getUserName( $fileInfo['DL_CHECKUSERID'] );

							return PEAR::raiseError( sprintf($ddStrings['cm_replace_error'], $docData->DL_FILENAME, $userName), ERRCODE_APPLICATION_ERR );
						}

					dd_deleteRestoreDocuments( array($fileInfo['DL_ID']), DD_DELETEDOC, $U_ID, $kernelStrings, $ddStrings );
				}
			}

			// Check user rights for delete operation
			//
			if ( $operation == DD_DELETEDOC && !$admin ) {
				$rights = $dd_treeClass->getIdentityFolderRights( $U_ID, $docData->DF_ID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;
				
				if ($fromWidget)
					$rights = TREE_WRITEREAD;

				if ( !UR_RightsObject::CheckMask( $rights, array(TREE_WRITEREAD, TREE_READWRITEFOLDER) ) )
					return PEAR::raiseError( $ddStrings['sv_screen_nodelrights_message'], ERRCODE_APPLICATION_ERR );

				$fileIsLocked = ( $docData->DL_CHECKSTATUS == DD_CHECK_OUT && ($docData->DL_CHECKUSERID != $U_ID || $fromWidget));

				if ( $versionControlEnabled && $fileIsLocked && !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) ) {
					$userName = dd_getUserName( $docData->DL_CHECKUSERID );
					$errorStr = ($fromWidget) ? $ddStrings['dd_screen_deletelocked_widget_error'] : $ddStrings['dd_screen_deletelocked_error'];
					return PEAR::raiseError( sprintf($errorStr, $docData->DL_FILENAME, $userName), ERRCODE_APPLICATION_ERR );
				}
			}

			if ( $operation == DD_DELETEDOC ) {
				$sourcePath = sprintf( "%s/%s", dd_getFolderDir( $docData->DF_ID ), $docData->DL_DISKFILENAME );
				$diskFileName = dd_generateUniqueDiskFilename( $docData->DL_FILENAME, $docData->DF_ID, true );
				$destPath = sprintf( "%s/%s", dd_recycledDir(), $diskFileName );

				$res = dd_makeRecycledDir( $ddStrings );
				if ( PEAR::isError($res) )
					return $res;
			} else {
				$sourcePath = sprintf( "%s/%s", dd_recycledDir(), $docData->DL_DISKFILENAME );
				$diskFileName = dd_generateUniqueDiskFilename( $docData->DL_FILENAME, $destDF_ID );
				$destPath = sprintf( "%s/%s", dd_getFolderDir($destDF_ID), $diskFileName );

				$destDir = dd_getFolderDir($destDF_ID);
				$errStr = null;
				if ( !file_exists($destDir) )
					forceDirPath($destDir, $errStr);
			}

			if ( file_exists($sourcePath) ) {

				// Copy thumbnail file
				//
				$ext = null;
				$srcThumbFile = findThumbnailFile( $sourcePath, $ext );
				if ( $srcThumbFile ) {
					$destThumbFile = $destPath.".$ext";

					if ( !@copy( $srcThumbFile, $destThumbFile ) )
						return PEAR::raiseError( $ddStrings['app_copyerr_message'] );

					if ( !@unlink($srcThumbFile) )
						return PEAR::raiseError( $ddStrings['app_delerr_message'] );
				}

				// Copy original file
				//
				if ( !@copy( $sourcePath, $destPath ) )
					return PEAR::raiseError( $ddStrings['app_copyerr_message'] );

				if ( !@unlink($sourcePath) )
					return PEAR::raiseError( $ddStrings['app_delerr_message'] );

			}

			$params = array();
			$params['DL_DISKFILENAME'] = $diskFileName;

			if ( $operation == DD_DELETEDOC ) {
				$params['DL_DELETE_U_ID'] = $U_ID;
				$params['DL_DELETE_DATETIME'] = convertToSqlDate( time() );
				if ($fromWidget)
					$params['DL_DELETE_USERNAME'] = $fromWidget;
				else
					$params['DL_DELETE_USERNAME'] = getUserName($U_ID, true);
			} else {
				$params['DL_DELETE_U_ID'] = null;
				$params['DL_DELETE_DATETIME'] = null;
				$params['DL_DELETE_USERNAME'] = null;
			}

			$params['DL_STATUSINT'] =  ($operation == DD_DELETEDOC) ? TREE_DLSTATUS_DELETED : TREE_DLSTATUS_NORMAL;
			$params['DL_ID'] = $DL_ID;

			if ( $operation == DD_DELETEDOC ) {
				$docInfo = $dd_treeClass->getDocumentInfo( $DL_ID, $kernelStrings );
				$resultFileList[$docInfo['DF_ID']][] = $docInfo;

				$params['DF_ID'] = null;
				$res = db_query( $qr_dd_updateFileLocationStatus, $params );
			} else {
				$docInfo = $dd_treeClass->getDocumentInfo( $DL_ID, $kernelStrings );
				$params['DF_ID'] = $destDF_ID;
				$resultFileList[$destDF_ID][] = $docInfo;

				$res = db_query( $qr_dd_updateFileLocationStatus, $params );
			}

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		if ( $operation == DD_DELETEDOC )
			dd_sendNotifications( $resultFileList, $U_ID, DD_DELETEDOC, $kernelStrings );
		else
			dd_sendNotifications( $resultFileList, $U_ID, DD_ADDDOC, $kernelStrings );

		return null;
	}

	function dd_restoreFolders( $folderList, $U_ID, $kernelStrings, $ddStrings, $destDF_ID = null, $admin = false )
	//
	// Restores deleted folders
	//
	//		Parameters:
	//			$folderList - array of folder identifiers
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//			$destDF_ID - destination folder for restore operation
	//			$admin - do not perform rights checking
	//
	//		Returns null or PEAR_Error
	//
	{
		global $dd_treeClass;
		global $qr_dd_updateFolderStatus;
		global $qr_dd_updateFodlerUpdateStatusFields;

		// Check user rights for restore operation
		//
		if ( is_null($destDF_ID) )
			return PEAR::raiseError( $ddStrings['sv_screen_nodest_message'], ERRCODE_APPLICATION_ERR );

		if ( !$admin ) {
			$rights = $dd_treeClass->getIdentityFolderRights( $U_ID, $destDF_ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
				return PEAR::raiseError( $ddStrings['r_screen_norights_message'], ERRCODE_APPLICATION_ERR );
		}

		foreach( $folderList as $DF_ID ) {
			$callbackParams = array( 'ddStrings'=>$ddStrings, "kernelStrings"=>$kernelStrings, "restore" => true, "folder"=>$DF_ID, 'U_ID'=>$U_ID );

			$dd_treeClass->moveFolder( $DF_ID, $destDF_ID, $U_ID, $kernelStrings,
										"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", "dd_onCreateFolder", "dd_onDeleteFolder",
										$callbackParams, "dd_onFinishMoveFolder", false, true, ACCESSINHERITANCE_INHERIT );
		}
	}

	function dd_getFileVersion( $DL_ID, $kernelStrings )
	//
	// Returns current file version
	//
	//		Parameters:
	//			$DL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns integer or PEAR_Error
	//
	{
		global $qr_dd_selectMaxHistoryID;

		$version = db_query_result( $qr_dd_selectMaxHistoryID, DB_FIRST, array('DL_ID'=>$DL_ID) );
		if ( PEAR::isError($version) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$version = incID($version);

		return $version;
	}

	function dd_getHistoryRecord( $DL_ID, $DLH_VERSION, $kernelStrings )
	//
	// Returns file version record
	//
	//		Parameters:
	//			$DL_ID - file identifier
	//			$DLH_VERSION - version number
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectHistoryRecord;

		$res = db_query_result( $qr_dd_selectHistoryRecord, DB_ARRAY, array('DL_ID'=>$DL_ID, 'DLH_VERSION'=>$DLH_VERSION) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function dd_onFinishMoveFolder( $params )
	//
	// Callback function, on folder move
	//
	//		Parameters:
	//			$params - parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		extract($params);

		global $qr_dd_updateFolderStatus;
		global $qr_dd_updateFodlerUpdateStatusFields;
		
		if ( !$suppressNotifications ) {
			$deletedFolderData['folderUsers'] = $folderUsers;
			$objectList = array( $deletedFolderData['DF_ID_PARENT']=>$deletedFolderData );
			dd_sendNotifications( $objectList, $U_ID, DD_DELETEFOLDER, $kernelStrings );

			$objectList = array( $newFolderData['DF_ID_PARENT']=>$newFolderData );
			dd_sendNotifications( $objectList, $U_ID, DD_ADDFOLDER, $kernelStrings );
		}

		if ( isset($restore) ) {
			$params = array();
			$params['DF_ID'] = $folder;
			$params['DF_STATUS'] = TREE_FSTATUS_NORMAL;

			db_query( $qr_dd_updateFolderStatus, $params );
		}

		$userName = getUserName( $U_ID, true );
		$sqlParams = array( 'DF_MODIFYUSERNAME'=>$userName, 'DF_ID'=>$folder );

		db_query( $qr_dd_updateFodlerUpdateStatusFields, $sqlParams );
	}

	function dd_finishCopyFolder( $kernelStrings, $U_ID, $operation, $callbackParams )
	//
	//  Callback function, executes after files copied or moved
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - user identifier
	//			$operation - operation: TREE_COPYFOLDER, TREE_MOVEFOLDER
	//			$callbackParams - other parameters array
	//
	//		Returns null
	//
	{
		extract( $callbackParams );

		global $dd_treeClass;

		$folderInfo = $dd_treeClass->getFolderInfo( $newID, $kernelStrings );
		$objectList = array( $destID=>$folderInfo );

		dd_sendNotifications( $objectList, $U_ID, DD_ADDFOLDER, $kernelStrings );

		global $qr_dd_updateFolderCreateStatusFields;

		$specialStatus = $folderInfo["DF_SPECIALSTATUS"];
		$userName = getUserName( $U_ID, true );
		$sqlParams = array( 'DF_CREATEUSERNAME'=>$userName, 'DF_MODIFYUSERNAME'=>$userName, 'DF_ID'=>$folder, "DF_SPECIALSTATUS" => $specialStatus );
		
		db_query( $qr_dd_updateFolderCreateStatusFields, $sqlParams );
	}

	function dd_onCreateFolder( $DF_ID, $params )
	//
	// Callback function, on folder create
	//
	//		Parameters:
	//			$DF_ID - new folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		extract($params);
		
		// Make folder directories
		//
		$res = dd_makeFolderDirs( $DF_ID, $ddStrings );
		if ( PEAR::isError($res) )
			return $res;
		
		global $dd_treeClass;

		$folderInfo = $dd_treeClass->getFolderInfo( $DF_ID, $kernelStrings );

		$objectList = array( $ID_PARENT=>$folderInfo );
		
		$origFolderInfo = (array) $params['originalData'];
		if (isset($copy))
			$specialStatus = 0;
		else
			$specialStatus = (isset($origFolderInfo["DF_SPECIALSTATUS"])) ? $origFolderInfo["DF_SPECIALSTATUS"] : 0;
		
		global $qr_dd_updateFolderCreateStatusFields;
		global $qr_dd_updateFolderCreateMoveStatusFields;

		$userName = getUserName( $U_ID, true );
		
		if ( !isset($move) ) {
			$sqlParams = array( 'DF_CREATEUSERNAME'=>$userName, 'DF_MODIFYUSERNAME'=>$userName, 'DF_ID'=>$DF_ID, "DF_SPECIALSTATUS" => $specialStatus );
			db_query( $qr_dd_updateFolderCreateStatusFields, $sqlParams );
		} else {
			$sqlParams = array( 'DF_CREATEUSERNAME'=>$originalData['DF_CREATEUSERNAME'],
								'DF_MODIFYUSERNAME'=>$originalData['DF_MODIFYUSERNAME'],
								'DF_ID'=>$DF_ID, 'DF_CREATEDATETIME'=>$originalData['DF_CREATEDATETIME'],'DF_SPECIALSTATUS' => $specialStatus);
			db_query( $qr_dd_updateFolderCreateMoveStatusFields, $sqlParams );
		}

		if ( !$suppressNotifications )
			dd_sendNotifications( $objectList, $U_ID, DD_ADDFOLDER, $kernelStrings );
		
		global $qr_dd_updateFolderWidgets;
		if (isset($deletedFolderData) && is_array($deletedFolderData)) {
			$wgUpdateParams = array ("OLD_FOLDER_ID" =>  $deletedFolderData["DF_ID"], "NEW_FOLDER_ID" => $DF_ID);
			$res = db_query( $qr_dd_updateFolderWidgets, $wgUpdateParams );
		}

		return null;
	}

	function dd_deleteFolderFilesHistory( $U_ID, $DF_ID, &$QuotaManager, &$kernelStrings )
	//
	// Deletes folder files history records
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$DF_ID - folder identifier
	//			$QuotaManager - DiskQuotaManager object
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		global $qr_dd_selectFolderFilesHistory;
		global $qr_dd_deleteFileHistory;

		$qr = db_query( $qr_dd_selectFolderFilesHistory, array('DF_ID'=>$DF_ID) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr) ) {
			$destPath = DD_HISTORY_DIR."/".$row['DLH_DISKFILENAME'];
			if ( @file_exists($destPath) )
				@unlink($destPath);

			$QuotaManager->AddDiskUsageRecord( $row['DL_OWNER_U_ID'], $DD_APP_ID, -1*$row['DLH_SIZE'] );

			db_query( $qr_dd_deleteFileHistory, $row );
		}

		db_free_result($qr);

		return null;
	}

	function dd_onDeleteFolder( $DF_ID, $params )
	//
	// Callback function, on folder delete
	//
	//		Parameters:
	//			$DF_ID - folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;
		global $qr_dd_selectFolderFiles;
		
		extract($params);
		$params["DF_ID"] = $DF_ID;
		
		if ( PEAR::isError( $res = handleEvent( $DD_APP_ID, "onDeleteFolder", $params, $params["language"] ) ) )
			return $res;
		
		if ( !isset($physicallyDelete) )
			$physicallyDelete = true;

		$QuotaManager = new DiskQuotaManager();

		if ( $physicallyDelete ) {
			// Delete folder files
			//
			$destPath = dd_getFolderDir( $DF_ID );

			// Delete folder files history
			//
			dd_deleteFolderFilesHistory( $U_ID, $DF_ID, $QuotaManager, $kernelStrings );

			if ( realpath(DD_FILES_DIR) != realpath($destPath) ) {

				$qr = db_query( $qr_dd_selectFolderFiles, array('DF_ID'=>$DF_ID) );

				while ( $row = db_fetch_array($qr) ) {
					$ext = 0;
					$fileDestPath = sprintf( "%s/%s", dd_getFolderDir( $DF_ID ), $row['DL_DISKFILENAME'] );
					$srcThumbFile = findThumbnailFile( $fileDestPath, $ext );

					$QuotaManager->AddDiskUsageRecord( $row['DL_OWNER_U_ID'], $DD_APP_ID, -1*$row['DL_FILESIZE'] );

					if ( file_exists($srcThumbFile) ) {
						$QuotaManager->AddDiskUsageRecord( $row['DL_OWNER_U_ID'], $DD_APP_ID, -1*filesize($srcThumbFile) );
					}
				}

				db_free_result($qr);

				removeDir( $destPath );
			}
			
			$QuotaManager->Flush( $kernelStrings );
		}
		
		// Send notification
		//
		if ( !$suppressNotifications ) {
			$objectList = array( $deletedFolderData['DF_ID_PARENT']=>$deletedFolderData );
			dd_sendNotifications( $objectList, $U_ID, DD_DELETEFOLDER, $kernelStrings );
		}

		if ( $physicallyDelete ) {
			// Delete folder's local view settings
			//
			dd_deleteFolderVewSettings( $DF_ID, $kernelStrings );
		} else {
			// Update status fields
			//
			global $qr_dd_updateFodlerUpdateStatusFields;

			$userName = getUserName( $U_ID, true );
			$sqlParams = array( 'DF_MODIFYUSERNAME'=>$userName, 'DF_ID'=>$DF_ID );

			db_query( $qr_dd_updateFodlerUpdateStatusFields, $sqlParams );
		}
		
		// If system folder - check other services

		return null;
	}

	function dd_removeDocuments( $documentList, $U_ID, $kernelStrings, $ddStrings, $admin = false )
	//
	// Completely deletes document
	//
	//		Parameters:
	//			$documentList - array of document identifiers
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//			$admin - do not perform rights checking
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;
		global $dd_treeClass;
		global $qr_dd_deleteFile;
		global $qr_dd_selectFileHistory;
		global $qr_dd_deleteFileHistory;

		$QuotaManager = new DiskQuotaManager();

		foreach( $documentList as $DL_ID ) {
			$docData = dd_getDocumentData( $DL_ID, $kernelStrings );
			if ( PEAR::isError($docData) )
				return $docData;

			if ( !$admin )
				if ( $docData->DL_DELETE_U_ID != $U_ID )
					return PEAR::raiseError( $ddStrings['sv_screen_nodelrights_message'], ERRCODE_APPLICATION_ERR );

			// Remove original file
			//
			$fileSize = 0;

			$filePath = sprintf( "%s/%s", dd_recycledDir(), $docData->DL_DISKFILENAME );
			if ( file_exists($filePath) ) {
				$fileSize = filesize($filePath);
				if ( !@unlink($filePath) )
					return PEAR::raiseError( $ddStrings['app_delerr_message'] );
			}

			// Remove thumbnail file
			//
			$ext = null;
			$thumbSize = 0;
			$srcThumbFile = findThumbnailFile( $filePath, $ext );
			if ( file_exists($srcThumbFile) ) {
				$thumbSize = filesize($srcThumbFile);
				@unlink($srcThumbFile);
			}

			// Delete database record
			//
			$res = db_query( $qr_dd_deleteFile, array('DL_ID'=>$DL_ID) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			// Find and delete history records
			//
			$qr = db_query( $qr_dd_selectFileHistory, array('DL_ID'=>$DL_ID) );
			while ( $row = db_fetch_array($qr) ) {
				$destPath = DD_HISTORY_DIR."/".$row['DLH_DISKFILENAME'];
				if ( @file_exists($destPath) ) {
					$QuotaManager->AddDiskUsageRecord( $docData->DL_OWNER_U_ID, $DD_APP_ID, -1*filesize($destPath) );
					@unlink($destPath);
				}
			}
			db_free_result( $qr );


			db_query( $qr_dd_deleteFileHistory, array('DL_ID'=>$DL_ID) );

			$QuotaManager->AddDiskUsageRecord( $docData->DL_OWNER_U_ID, $DD_APP_ID, -1*$docData->DL_FILESIZE );
			if ( $thumbSize )
				$QuotaManager->AddDiskUsageRecord( $docData->DL_OWNER_U_ID, $DD_APP_ID, -1*$thumbSize );
		}

		$QuotaManager->Flush( $kernelStrings );

		return null;
	}

	function dd_getFileHistory( $DL_ID, $kernelStrings )
	//
	// Returns file history as array
	//
	//		Parameters:
	//			$DL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_ERROR
	//
	{
		global $qr_dd_selectFileHistory;

		$qr = db_query( $qr_dd_selectFileHistory, array('DL_ID'=>$DL_ID) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$result[] = $row;
		}

		db_free_result( $qr );

		$result = array_reverse($result, true);

		return $result;
	}

	function dd_deleteFileVersions( $DL_ID, $deleteVersion, &$kernelStrings )
	//
	// Deletes file history records
	//
	//		Parameters:
	//			$DL_ID - file identifier
	//			$deleteVersion - list of versions to delete
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;
		global $qr_dd_deleteFileVersion;

		$QuotaManager = new DiskQuotaManager();

		foreach ( $deleteVersion as $version ) {
			$params = array( 'DL_ID'=>$DL_ID, 'DLH_VERSION'=>$version );

			$record =  dd_getHistoryRecord( $DL_ID, $version, $kernelStrings );
			if ( PEAR::isError($record) )
				return $record;

			$destPath = DD_HISTORY_DIR."/".$record['DLH_DISKFILENAME'];
			if ( file_exists($destPath) )
				@unlink($destPath);

			$res = db_query( $qr_dd_deleteFileVersion, $params );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$QuotaManager->AddDiskUsageRecord( $record['DL_OWNER_U_ID'], $DD_APP_ID, -1*$record['DLH_SIZE'] );
		}

		$QuotaManager->Flush( $kernelStrings );
	}

	function dd_onAfterCopyMoveFile( $kernelStrings, $U_ID, $docData, $operation, $params )
	//
	//	Completes file copy/move process
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$docData - file data, record from DOCLIST table as array
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_dd_insertFile;
		global $qr_dd_updateFileLocation;

		extract( $params );

		if ( $operation == TREE_COPYDOC ) {
			$docData['DL_UPLOADUSERNAME'] = getUserName( $U_ID, true );
			$docData['DL_OWNER_U_ID'] = $U_ID;
			$docData['DL_DISKFILENAME'] = $diskFileName;

			$res = dd_documentAddingPermitted( $kernelStrings, $ddStrings );
			if ( PEAR::isError($res) )
				return $res;

			$res = db_query( $qr_dd_insertFile, $docData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			$docData['DL_DISKFILENAME'] = $diskFileName;

			$res = db_query( $qr_dd_updateFileLocation, $docData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function dd_fileExists( $fileName, $DL_ID, $DF_ID, $kernelStrings )
	//
	// Checks if file with the given name exists in the given folder
	//
	//		Parameters:
	//			$fileName - file name to check
	//			$DL_ID - file identifier to skip
	//			$DF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_dd_selectFileNameCount;

		$params = array();
		$params['DL_ID'] = $DL_ID;
		$params['DF_ID'] = $DF_ID;
		$params['DL_FILENAME'] = $fileName;

		$res = db_query_result( $qr_dd_selectFileNameCount, DB_FIRST, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}


	function dd_modifyFile( $DL_ID, $U_ID, $fileData, $kernelStrings, $ddStrings )
	//
	// Modifies file name and description
	//
	//		Parameters:
	//			$DL_ID - file identifier
	//			$U_ID - user identifier
	//			$fileData - array containing file data
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $dd_treeClass;
		global $qr_dd_updateFileNameDescription;
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		// Check required fields and user rights
		//
		$docData = dd_getDocumentData( $DL_ID, $kernelStrings );
		if ( PEAR::isError($docData) )
			return $docData;

		$rights = $dd_treeClass->getIdentityFolderRights( $U_ID, $docData->DF_ID, $kernelStrings );
		if ( PEAR::isError($rights) )
			return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
			return PEAR::raiseError( $ddStrings['mf_screen_nomodrights_message'], ERRCODE_APPLICATION_ERR );

		$requiredFields = array( "DL_FILENAME" );
		if ( PEAR::isError( $invalidField = findEmptyField($fileData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		if ( ereg( "\\/|\\\|\\?|:|<|>|\\*", $fileData["DL_FILENAME"] ) || !(strpos($fileData["DL_FILENAME"], "\\") === FALSE) )
			return PEAR::raiseError( $ddStrings['mf_screen_invchars_message'], ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
												$_PEAR_default_error_options, "DL_FILENAME" );

		$fileData['DL_ID'] = $DL_ID;

		// Check if file name is not exists yet
		//
		$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
		if ( PEAR::isError($versionControlEnabled) )
			return $versionControlEnabled;

		if ( $versionControlEnabled ) {
			$res = dd_fileExists( $fileData["DL_FILENAME"], $DL_ID, $docData->DF_ID, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			if ( $res ) {
				return PEAR::raiseError( $ddStrings['mf_fileexists_message'],
											ERRCODE_INVALIDFIELD,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											"DL_FILENAME" );
			}
		}

		// Update database record
		//
		$res = db_query( $qr_dd_updateFileNameDescription, $fileData );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function dd_searchFiles( $searchString, $U_ID, $sortStr, $kernelStrings, $entryProcessor = null )
	//
	// Searches files
	//
	//		Parameters:
	//			$searchString - text to find
	//			$U_ID - user identifier
	//			$sortStr - sorting string
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array of file objects or PEAR_Error
	//
	{
		global $qr_dd_findFiles;
		global $qr_dd_findGroupFiles;
		global $qr_dd_findFilesGlobal;
		global $UR_Manager;

		$pattern = "/\"(?P<groups>[^\"]*)\"/ui";
		preg_match_all( $pattern, $searchString, $matches );

		$quotedWords = $matches['groups'];

		foreach ( $quotedWords as $group )
			$searchString = str_replace( '"'.$group.'"', "", $searchString );

		$totalWords = array_merge( $quotedWords, explode( " ", $searchString ) );

		$groupsToFind = array();

		foreach ( $totalWords as $value )
			if ( strlen(trim($value)) )
				$groupsToFind[] = $value;

		$fileNameConstraints = array();
		$fileDescConstraints = array();

		foreach ( $groupsToFind as $index=>$group ) {
			$group = strtolower($group);
			$fileNameConstraints[] = sprintf( "LOWER(DL_FILENAME) LIKE '!group%s!'", $index );
			$fileDescConstraints[] = sprintf( "LOWER(DL_DESC) LIKE '!group%s!'", $index );
		}

		$fileNameConstraints = implode( " AND ", $fileNameConstraints );
		$fileDescConstraints = implode( " AND ", $fileDescConstraints );

		$params = array( 'U_ID'=>$U_ID );

		foreach ( $groupsToFind as $index=>$group ) {
			$params[sprintf('group%s', $index)] = "%".strtolower($group)."%";
		}

		$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );
		$sql = $globalAdmin ? $qr_dd_findFilesGlobal : $qr_dd_findFiles;

		$qr = db_query( sprintf($sql, $fileNameConstraints, $fileDescConstraints, $sortStr), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
			if ( !is_null($entryProcessor) )
				$result[$row->DL_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->DL_ID] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	function dd_sendNotifications( $objectList, $U_ID, $operation, $kernelStrings )
	//
	// Sends email notifications about file and folder operations
	//
	//		Parameters:
	//			$objectList - list of objects being updated: array( DF_ID1=>array( object1, object2...),...  )
	//			$U_ID - identificator of user caused operation
	//			$srcFolderList - list of source folders
	//			$operation - operation: DD_ADDDOC, DD_DELETEDOC, DD_ADDFOLDER, DD_DELETEFOLDER
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $dd_treeClass;
		global $DD_APP_ID;
		global $dd_loc_str;

		$fileOperations = array( DD_ADDDOC, DD_DELETEDOC );
		$folderOperations = array( DD_ADDFOLDER, DD_DELETEFOLDER );

		$fileOperation = in_array( $operation, $fileOperations );
		$folderOperation = in_array( $operation, $folderOperations );

		if ( !$fileOperation && !$folderOperation )
			return null;

		if ( !count($objectList) )
			return null;

		$affectedUsers = array();
		$bodyParts = array();
		$folderLinks = array();

		foreach ( $objectList as $DF_ID=>$folderObjects ) {
			// List of users assigned to this folder
			//
			$parentFolder = $DF_ID;

			if ( $folderOperation )
				$DF_ID = $folderObjects['DF_ID'];

			if ( !isset( $folderObjects['folderUsers'] ) ) {
				$folderUsers = $dd_treeClass->listFolderUsers( $DF_ID, $kernelStrings );
				if ( PEAR::isError($folderUsers) )
					return $folderUsers;
			} else {
				$folderUsers = $folderObjects['folderUsers'];
			}
			
			$affectedUsers = array_merge( $affectedUsers, $folderUsers );
			
			foreach ( $affectedUsers as $user=>$userData )
				$folderLinks[$user][$DF_ID] = 1;

			$bodyPart = array();

			// Folder name
			//
			$folderInfo = $dd_treeClass->getFolderInfo( $parentFolder, $kernelStrings, true );
			if ( PEAR::isError($folderInfo) )
				return $folderInfo;

			$bodyPart['DF_NAME'] = $folderInfo['DF_NAME'];

			// Path to folder
			//
			if ( $parentFolder != TREE_ROOT_FOLDER ) {
				$path = $dd_treeClass->getPathToFolder( $parentFolder, $kernelStrings );
				if ( PEAR::isError($path) )
					return $path;
			} else
				$path = array( $parentFolder => $folderInfo['DF_NAME'] );

			if ( $fileOperation )
				$path = array_merge( $path, array( $DF_ID=>$folderInfo['DF_NAME'] ) );

			$path = implode( '->', $path );

			$bodyPart['PATH'] = $path;

			// Object list
			//
			$objects = array();
			if ( $fileOperation ) {
				foreach( $folderObjects as $key => $data ) {
					$info = sprintf( "&nbsp;&nbsp;&nbsp;%s", $data['DL_FILENAME'] );
					$objects[] = $info;
				}

				$objects = implode( "<br>", $objects );
			} else
				$objects = $folderObjects;

			$bodyPart['OBJECTS'] = $objects;
			$bodyPart['OBJECT_NUM'] = count($folderObjects);

			$bodyParts[$DF_ID] = $bodyPart;
		}

		$operationTexts = array( DD_ADDDOC=>'app_mail_addoperation', DD_DELETEDOC=>'app_mail_deleteoperation',
									DD_ADDFOLDER=>'app_mail_addfldoperation', DD_DELETEFOLDER=>'app_mail_delfldoperation' );

		foreach( $affectedUsers as $userID => $data ) {
			if ( $userID == $U_ID )
				continue;

			$language = readUserCommonSetting( $userID, LANGUAGE );
			$userDDStrings = $dd_loc_str[$language];
			
			$messageHeader = sprintf($userDDStrings['app_mail_title'], getUserName( $userID, true ) );
			$messageBody = sprintf( "%s<br><br>", $userDDStrings['app_mail_info'] );
			
			$messageSubject = $userDDStrings['app_mail_subject'];
			$userFolders = $folderLinks[$userID];

			$index = 0;

			foreach( $userFolders as $DF_ID=>$value ) {
				$folderData = $bodyParts[$DF_ID];

				$folderHeader = $userDDStrings['app_mail_foldername'];

				$operationTextIndex = $operationTexts[$operation];
				$operationDescription = $userDDStrings[$operationTextIndex];

				if ( $fileOperation )
					$operationDescription = sprintf($operationDescription, $folderData['OBJECT_NUM'] );

				if ( $index > 0 )
					$messageBody .= "<br><br>";

				if ( $fileOperation )
					$messageBody .= sprintf( "%s: <b>%s</b><br><br>%s:<br>%s", $folderHeader, $folderData['PATH'], $operationDescription, $bodyPart['OBJECTS'] );
				else
					$messageBody .= sprintf( "%s: <b>%s</b><br><br>%s: %s", $folderHeader, $folderData['PATH'], $operationDescription, $bodyPart['OBJECTS']['DF_NAME'] );
				$index++;
			}

			$res = sendWBSMail( $userID, null, $U_ID, $messageSubject, 1, $messageBody, $kernelStrings, DD_MAIL_NOTIFICATION, $DD_APP_ID, $messageHeader );
		}
	}

	function dd_getViewOptions( $U_ID, &$visibleColumns, &$viewMode, &$recordsPerPage,
								&$showSharedPanel, &$displayIcons, &$folderViewMode, &$restrictDescLen, $DF_ID, $kernelStrings,
								$useCookies = false )
	//
	//	Returns view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (DD_GRID_VIEW, DD_LIST_VIEW, DD_THUMBLIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$displayIcons - display icons
	//			$folderViewMode - folder view mode (DD_FLDVIEW_GLOBAL, DD_FLDVIEW_LOCAL)
	//			$restrictDescLen - maximum description length to display
	//			$DF_ID - folder identifier
	//			$kernelStrings - Kernel localization string
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $dd_columns;
		global $DD_APP_ID;
		global $UR_Manager;

		$visibleColumns = array();

		$folderViewMode = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_FOLDERVIEWOPT', null, $useCookies );
		if ( !strlen($folderViewMode) )
			$folderViewMode = DD_FLDVIEW_LOCAL;

		$columns = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_VISIBLECOLUMNS', $useCookies );
		if ( $columns != "none" ) {
			if ( strlen($columns) )
				$visibleColumns = explode( ";", $columns );
			else
				$visibleColumns = array( DD_COLUMN_FILETYPE, DD_COLUMN_FILESIZE, DD_COLUMN_UPLOADDATE, DD_COLUMN_UPLOADUSER );
		} else
			$visibleColumns = array();

		// Try to load local view settings
		//
		dd_getLocalViewSettings( $U_ID, $DF_ID, $viewMode );

		// Load global view settings
		//
		//if ( is_null($viewMode) )
		//	$viewMode = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_VIEWMODE', null, $useCookies );

		if ( !strlen($viewMode) )
			$viewMode = DD_LIST_VIEW;

		$recordsPerPage = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_RECORDPERPAGE', null, $useCookies );
		if ( !strlen($recordsPerPage) )
			$recordsPerPage = 30;

		$showSharedPanel = $UR_Manager->CheckMask($UR_Manager->GetUserRightValue( $U_ID, "/ROOT/DD/FOLDERS/VIEWSHARES" ), UR_BOOL_TRUE );

		$displayIcons = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_DISPLAYICONS', null, $useCookies );
		if ( !strlen($displayIcons) )
			$displayIcons = 1;

		$restrictDescLen = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_RESTRICTDESCLEN', null, $useCookies );

		return null;
	}

	function dd_setViewOptions( $U_ID, $visibleColumns, $viewMode, $recordsPerPage,
								$showSharedPanel, $displayIcons, $folderViewMode, $restrictDescLen, $DF_ID,
								$kernelStrings, $useCookies = false )
	//
	//	Saves view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (DD_GRID_VIEW, DD_LIST_VIEW, DD_THUMBLIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$displayIcons - display icons
	//			$folderViewMode - folder view mode (DD_FLDVIEW_GLOBAL, DD_FLDVIEW_LOCAL)
	//			$restrictDescLen - maximum description length to display
	//			$DF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $DD_APP_ID;

		if ( is_null($folderViewMode) ) {
			$folderViewMode = getAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_FOLDERVIEWOPT', null, $useCookies );
			if ( !strlen($folderViewMode) )
				$folderViewMode = DD_FLDVIEW_LOCAL;
		} else
			setAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_FOLDERVIEWOPT', $folderViewMode, $kernelStrings, $useCookies );

		if ( $folderViewMode == DD_FLDVIEW_GLOBAL )
			$DF_ID = null;

		// Save global view settings
		//
		if ( !is_null($visibleColumns) ) {
			$visibleColumns = implode( ";", $visibleColumns );

			if ( !strlen($visibleColumns) )
				$visibleColumns = "none";

			setAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_VISIBLECOLUMNS', $visibleColumns, $kernelStrings, $useCookies );
		}

		if ( $folderViewMode == DD_FLDVIEW_GLOBAL )
			if ( !is_null($viewMode) )
				setAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_VIEWMODE', $viewMode, $kernelStrings, $useCookies );

		if ( !is_null($recordsPerPage) )
			setAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_RECORDPERPAGE', $recordsPerPage, $kernelStrings, $useCookies );

		if ( !is_null($showSharedPanel) ) {
			if ( !$showSharedPanel )
				$showSharedPanel = 0;
			setAppUserCommonValue( $DD_APP_ID, $U_ID, $DD_APP_ID.TREE_SHOWWHAREDPANEL, $showSharedPanel, $kernelStrings, $useCookies );
		}

		if ( !is_null($displayIcons) ) {
			if ( !$displayIcons )
				$displayIcons = 0;
			setAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_DISPLAYICONS', $displayIcons, $kernelStrings, $useCookies );
		}

		if ( !is_null($restrictDescLen) ) {
			if ( !$restrictDescLen )
				$restrictDescLen = 0;
			setAppUserCommonValue( $DD_APP_ID, $U_ID, 'DD_RESTRICTDESCLEN', $restrictDescLen, $kernelStrings, $useCookies );
		}

		// Set local view settings
		//
		dd_applyLocalViewSettings( $U_ID, $DF_ID, $folderViewMode, $viewMode, $kernelStrings );
	}

	function dd_applyLocalViewSettings( $U_ID, $DF_ID, $folderViewMode, $viewMode, $kernelStrings )
	//
	// Applies local view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$DF_ID - folder identifier
	//			$folderViewMode - folder view mode (DD_FLDVIEW_GLOBAL, DD_FLDVIEW_LOCAL)
	//			$viewMode - view mode (DD_GRID_VIEW, DD_LIST_VIEW, DD_THUMBLIST_VIEW)
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;
		global $dd_treeClass;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return $kernelStrings[ERR_XML];

		$result = array();

		$appNode = getElementByTagname( $settingsElement, $DD_APP_ID );
		if ( !$appNode )
			$appNode = @create_addElement( $dom, $settingsElement, $APP_ID );

		$foldersViewNode = getElementByTagname( $appNode, 'FOLDERSVIEW' );
		if ( !$foldersViewNode )
			$foldersViewNode = @create_addElement( $dom, $appNode, 'FOLDERSVIEW' );

		$xpath = xpath_new_context($dom);

		// Apply settings to the DF_ID folder
		//
		if ( $folderViewMode == DD_FLDVIEW_LOCAL ) {
			if ( !is_null($DF_ID) ) {
				$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$DF_ID']", $foldersViewNode );

				// Create element for DF_ID folder if it doesn't exists already
				//
				if ( !count($folderElement->nodeset) ) {
					$folderNode = @create_addElement( $dom, $foldersViewNode, 'FOLDER' );
					$folderNode->set_attribute( 'ID', $DF_ID );
				} else
					$folderNode = $folderElement->nodeset[0];

				if ( !is_null($viewMode) ) $folderNode->set_attribute( 'VIEWMODE', $viewMode );
			}
		}

		// Apply settings to all other folders
		//
		if ( $folderViewMode == DD_FLDVIEW_GLOBAL ) {
			$access = null;
			$hierarchy = null;
			$deletable = null;
			$dd_folders = $dd_treeClass->listFolders( $U_ID, TREE_ROOT_FOLDER, $kernelStrings, 0, true,
														$access, $hierarchy, $deletable,
														null, null, false, null, true );
			if ( PEAR::isError($dd_folders) )
				return $dd_folders;

			foreach ( $dd_folders as $ID=>$data ) {

				$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$ID']", $foldersViewNode );

				// Create element for folder if it doesn't exists already
				//
				if ( !count($folderElement->nodeset) ) {
					$folderNode = @create_addElement( $dom, $foldersViewNode, 'FOLDER' );
					$folderNode->set_attribute( 'ID', $ID );
				} else
					$folderNode = $folderElement->nodeset[0];

				if ( !is_null($viewMode) ) $folderNode->set_attribute( 'VIEWMODE', $viewMode );
			}
		}

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function dd_getLocalViewSettings( $U_ID, $DF_ID, &$viewMode )
	//
	// Returns folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$DF_ID - folder identifier
	//			$viewMode - view mode (DD_GRID_VIEW, DD_LIST_VIEW, DD_THUMBLIST_VIEW)
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		if ( is_null($DF_ID) )
			return null;

		$viewMode = null;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return null;

		$result = array();

		$appNode = getElementByTagname( $settingsElement, $DD_APP_ID );
		if ( !$appNode )
			return null;

		$foldersViewNode = getElementByTagname( $appNode, 'FOLDERSVIEW' );
		if ( !$foldersViewNode )
			return null;

		$xpath = xpath_new_context($dom);

		$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$DF_ID']", $foldersViewNode );
		if ( !$folderElement || !count($folderElement->nodeset)  )
			return null;

		$folder = $folderElement->nodeset[0];

		$viewMode = $folder->get_attribute( 'VIEWMODE' );

		return null;
	}

	function dd_deleteFolderVewSettings( $DF_ID, $kernelStrings )
	//
	// Deletes folder local view settings
	//
	//		Parameters:
	//			$DF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $dd_treeClass;

		// Get folder user list
		//
		$users = $dd_treeClass->listFolderUsers( $DF_ID, $kernelStrings );
		if ( PEAR::isError($users) )
			return $users;

		// Delete view settings for each user
		//
		foreach ( $users as $key=>$value )
			dd_deleteUserFolderViewSettings( $key, $DF_ID, $kernelStrings );

		return null;
	}

	function dd_deleteUserFolderViewSettings( $U_ID, $DF_ID, $kernelStrings )
	//
	// Deletes user folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$DF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return $kernelStrings[ERR_XML];

		$result = array();

		$appNode = getElementByTagname( $settingsElement, $DD_APP_ID );
		if ( !$appNode )
			return null;

		$xpath = xpath_new_context($dom);

		$foldersViewNode = getElementByTagname( $appNode, 'FOLDERSVIEW' );
		if ( !$foldersViewNode )
			return null;

		$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$DF_ID']", $foldersViewNode );
		if ( !$folderElement || !count($folderElement->nodeset)  )
			return null;

		$folder = $folderElement->nodeset[0];
		$folder->unlink_node();

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	//
	// System event handlers
	//

	function dd_onDeleteUser( $params )
	//
	// Handler of application AA onDeleteUser event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		require $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		switch ($CALL_TYPE) {
			case CT_APPROVING : {

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				return null;
			}
		}
	}


	function dd_transferFilesToSystem( $U_ID )
	/**
	 * Changes files ownership from $U_ID to $SYSTEM account. Also modificates quotas.
	 *
	 *
	 * @param string $U_ID user id
	 * @return null or PEAR::Error if errors would occure
	 */
	{
		global $qr_dd_transferFilesToSystem;
		global $kernelStrings;
		global $DD_APP_ID;

		$res = db_query( $qr_dd_transferFilesToSystem, array("U_ID"=>$U_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $DD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$QuotaManager->AddDiskUsageRecord( $U_ID, $DD_APP_ID, -1*$UserUsedSpace );
		$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $DD_APP_ID, $UserUsedSpace );

		$QuotaManager->Flush($kernelStrings);

		return null;
	}

	function dd_onRemoveUser( $params )
	//
	// Handler of application AA onRemoveUser event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		require $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/dd_queries_cmn.php") );

		global $dd_loc_str;
		global $loc_str;

		$ddStrings = $dd_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$vars = get_defined_vars();
				saveVariables( $vars, "qr_dd" );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {

				return (  dd_transferFilesToSystem( $U_ID ) );
			}
		}
	}

	function dd_onDeleteCurrency( $params )
	//
	// Handler of application AA onDeleteCurrency event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );

		switch ($CALL_TYPE) {
			case CT_APPROVING : {

				return EVENT_APPROVED;
			}
			case CT_ACTION : {

				return null;
			}
		}
	}
	
	
	function dd_onDeleteProject( $params )
	//
	// Handler of application PM onDeleteProject event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		/*extract( $params );
		
		if ($CALL_TYPE == CT_ACTION) {
			$projectData = pm_getProjectData($P_ID, $kernelStrings);
			
			if ($projectData["DF_ID"]) {
				@require $appScriptPath;
				eval( prepareFileContent( $appScriptPath ) );
				eval( prepareFileContent($appDirPath."/dd_queries_cmn.php") );
				
				global $dd_treeClass;;
				global $currentUser;
				$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, "language" => $language ,'U_ID'=>$currentUser);
				
				$res = $dd_treeClass->deleteFolder( $folderId, $currentUser, $kernelStrings, true,
																	"dd_onDeleteFolder", $callbackParams, false );
				if (PEAR::isError($res))
					return $res;
			}
		}*/
		return true;
	}

	function dd_disableThumbnails( $kernelStrings, $ddStrings )
	//
	// Disables thumbnail generation in Document Depot
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - document depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;
		global $dd_treeClass;

		// Write database value
		//
		$res = writeApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBDISABLED, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;


		return null;
	}

	function dd_enableThumbnails( $kernelStrings, $ddStrings )
	//
	// Enables thumbnail generation in Document Depot
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - document depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		// Write database value
		//
		$res = writeApplicationSettingValue( $DD_APP_ID, DD_THUMBNAILSTATE, DD_THUMBENABLED, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function dd_updateThumbnails( $kernelStrings, $ddStrings, &$messageStack )
	//
	// Updates thumbnails
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - document depot localization strings
	//			$messageStack - stack to put errors and messages
	//
	//		Returns number of files processed or PEAR_Error
	//
	{
		global $dd_treeClass;
		global $DD_APP_ID;

		@set_time_limit(0);

		$messageStack = array();
		$totalFilesProcessed = 0;

		// Generate thumbnails
		//
		$access = null;
		$hierarchy = null;
		$deletable = null;
		$folders = $dd_treeClass->listFolders( null, TREE_ROOT_FOLDER, $kernelStrings, 0, true,
												$access, $hierarchy, $deletable,
												null, null, false, null, false );
		if ( PEAR::isError($folders) ) {
			$fatalError = true;
			$errorStr = $folders->getMessage();

			break;
		}

		$QuotaManager = new DiskQuotaManager();

		foreach ( $folders as $key=>$value ) {

			$files = $dd_treeClass->listFolderDocuments( $key, null, "DL_ID asc", $kernelStrings, null, true );
			if ( PEAR::isError($files) )
				return $files;

			$fileList = array();

			$thumbsCreatedInFolder = 0;

			foreach( $files as $data ) {

				if( $data->DL_STATUSINT == TREE_DLSTATUS_NORMAL )
					$attachmentPath = dd_getFolderDir( $data->DF_ID )."/".$data->DL_DISKFILENAME;
				elseif ( $data->DL_STATUSINT == TREE_DLSTATUS_DELETED )
					$attachmentPath = dd_recycledDir()."/".$data->DL_DISKFILENAME;

				$ext = null;
				$thumbPath = findThumbnailFile( $attachmentPath, $ext );

				if ( is_null($thumbPath) ) {
					$originalFileInfo = pathinfo( $data->DL_FILENAME );

					$ext = $data->DL_FILETYPE;
					if ( strlen($ext) ) {
						$thumbPath = makeThumbnail( $attachmentPath, $attachmentPath, $ext, 96, $kernelStrings );
						if ( PEAR::isError($thumbPath) ) {
							$totalFilesProcessed++;
							$thumbsCreatedInFolder++;
							$fileList[$data->DL_FILENAME] = $thumbPath->getMessage();
						}	else
								if ( $thumbPath ) {
									$totalFilesProcessed++;
									$thumbsCreatedInFolder++;
									$fileList[$data->DL_FILENAME] = $kernelStrings['app_thumbcreated_message'];

									$QuotaManager->AddDiskUsageRecord( $data->DL_OWNER_U_ID, $DD_APP_ID, @filesize($thumbPath) );
								}
					}
				}
			}

			if ( $thumbsCreatedInFolder ) {
				$messageStack[$key] = array( 'name'=>$value->DF_NAME, 'offset'=>$value->OFFSET_STR );
				$messageStack[$key]['files'] = $fileList;
			}
		}

		$QuotaManager->Flush( $kernelStrings );

		return $totalFilesProcessed;
	}

	function dd_getUserEmail( $U_ID, &$kernelStrings )
	//
	// Returns user name and email address
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_selectUser;

		$userData = db_query_result( $qr_selectUser, DB_ARRAY, array('U_ID'=>$U_ID) );
		if ( PEAR::isError($userData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();
		$result['name'] = getArrUserName( $userData );
		$result['email'] = $userData["C_EMAILADDRESS"];

		return $result;
	}

	function dd_getTotalImagesNum( $kernelStrings )
	//
	// Returns total number of images in the database
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization string
	//
	//		Returns integer or PEAR_Error
	//
	{
		global $qr_dd_imageNum;

		$res = db_query_result( $qr_dd_imageNum, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function dd_enableVersionControl( $kernelStrings, $ddStrings )
	//
	// Enables Document Depot version control
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		$duplicateFiles = dd_listDuplicateFlies( $kernelStrings );
		if ( PEAR::isError($duplicateFiles) )
			return $duplicateFiles;

		if ( count($duplicateFiles) )
			return PEAR::raiseError( $ddStrings['sv_duplicatefilesfound_message'], ERRCODE_APPLICATION_ERR );

		writeApplicationSettingValue( $DD_APP_ID, DD_VERSIONCONTROLSTATE, DD_VCENABLED, $kernelStrings );
	}

	function dd_disableVersionControl( $kernelStrings )
	//
	// Disables Document Depot version control
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		writeApplicationSettingValue( $DD_APP_ID, DD_VERSIONCONTROLSTATE, DD_VCDISABLED, $kernelStrings );
	}

	function dd_listDuplicateFlies( $kernelStrings )
	//
	// Returns information about duplicate files required by the Service screen
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectDuplicateFilenames;
		global $qr_dd_selectFileByName;
		global $dd_treeClass;

		$result = array();

		$qr = db_query( $qr_dd_selectDuplicateFilenames, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$files_qr = db_query( $qr_dd_selectFileByName, $row );
			if ( PEAR::isError($files_qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$folderPath = $dd_treeClass->getPathToFolder( $row['DF_ID'], $kernelStrings );
			if ( PEAR::isError($folderPath) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$folderPath = implode( "/", $folderPath );

			if ( !isset($result[$row['DF_ID']]) )
				$result[$row['DF_ID']] = array( 'PATH'=>$folderPath, 'files'=>array() );

			if ( !isset($result[$row['DF_ID']]['files'][$row['DL_FILENAME']]) )
				$result[$row['DF_ID']]['files'][$row['DL_FILENAME']] = array();

			while ( $fileRow = db_fetch_array($files_qr) ) {
				$result[$row['DF_ID']]['files'][$row['DL_FILENAME']][] = $fileRow;
			}

			db_free_result($files_qr);
		}

		db_free_result($qr);

		return $result;
	}

	function dd_getDbFileName( $DL_ID, &$kernelStrings )
	//
	// Returns a file name
	//
	//		Parameters:
	//			$DL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_dd_selectFileName;

		$result = db_query_result( $qr_dd_selectFileName, DB_FIRST, array('DL_ID'=>$DL_ID) );
		if ( PEAR::isError($result) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $result;
	}

	//
	// Report functions
	//

	function dd_repSpaceByUsers( &$kernelStrings, &$total )
	//
	// Returns data for the Storage space usage by file owners report
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$total - size total value
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectStorageByUser;
		global $qr_dd_selectUserNameChunk;

		$sql = sprintf( $qr_dd_selectStorageByUser, $qr_dd_selectUserNameChunk );

		$total = 0;

		$qr = db_query( $sql, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$total += $row['DU_SIZE'];
			$row['DU_SIZE'] = formatFileSizeStr( $row['DU_SIZE'] );
			$row['UDQ_SIZE'] = formatFileSizeStr( $row['UDQ_SIZE'] );
			if ( strlen($row['RATIO']) )
				$row['RATIO'] = round( $row['RATIO'], 2 )."%";

			$result[$row['USERNAME']] = $row;
		}

		db_free_result( $qr );

		$total = formatFileSizeStr( $total );

		return $result;
	}

	function dd_repFileTypesStats( &$kernelStrings, &$ddStrings, &$total )
	//
	// Returns data for the File type statistics report
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$ddStrings - Document Depot localization strings
	//			$total - totals array
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectFileTypeStats;

		$total = array( 'size'=>0, 'files'=>0 );

		$qr = db_query( $qr_dd_selectFileTypeStats, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$total['size'] += $row['SIZE'];
			$total['files'] += $row['CNT'];
			$row['SIZE'] = formatFileSizeStr( $row['SIZE'] );
			if ( !strlen($row['DL_FILETYPE']) )
				$row['DL_FILETYPE'] = $ddStrings['rep_ftsunknown_label'];

			$result[$row['DL_FILETYPE']] = $row;
		}

		db_free_result( $qr );

		$total['size'] = formatFileSizeStr( $total['size'] );

		return $result;
	}

	function dd_formatHours( $seconds )
	//
	// Converts seconds to hours and minutes (hh:mm)
	//
	//		Parameters
	//			$seconds - seconds as integer
	//
	//		Returns string
	//
	{
		$hours = (int)($seconds/3600);
		$minutes = (int)(($seconds % 3600) / 60);

		return sprintf( "%02d:%02d", $hours, $minutes );
	}

	function dd_repRecentUploads( $settings, &$kernelStrings, &$total )
	//
	// Returns data for the Recent Uploads report
	//
	//		Parameters:
	//			$settings - report settings
	//			$kernelStrings - Kernel localization strings
	//			$total - size total value
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectRecentFilesByDate;
		global $qr_dd_selectRecentFilesByDateRange;

		$validator = new dd_reportDataRangeValidator();

		$res = $validator->loadFromArray( $settings, $kernelStrings, true, array( s_datasource=>s_form, 'kernelStrings'=>$kernelStrings ) );
		if ( PEAR::isError($res) )
			return $res;

		if ( $validator->type == 'days' )
			$sql = sprintf( $qr_dd_selectRecentFilesByDate, $validator->days );
		else {
			$fromDate = ($validator->from).' 00:00:00';
			$toDate = ($validator->to).' 23:59:59';
			$sql = sprintf( $qr_dd_selectRecentFilesByDateRange, $fromDate, $toDate );
		}

		$qr = db_query( $sql, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$total = 0;

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$total += $row['DL_FILESIZE'];
			$row['DL_FILESIZE'] = formatFileSizeStr( $row['DL_FILESIZE'] );
			$row['DL_UPLOADDATETIME'] = convertToDisplayDateTime( $row['DL_UPLOADDATETIME'],false, true, true );

			$result[] = $row;
		}

		db_free_result( $qr );

		$total = formatFileSizeStr( $total );

		return $result;
	}

	function dd_repCompareFolders( $folder1, $folder2 )
	//
	// Helper function for the dd_repFoldesSummary function
	//
	{
		$parts1 = explode( '.', $folder1['DF_ID'] );
		$parts2 = explode( '.', $folder2['DF_ID'] );

		foreach ( $parts1 as $index=>$part1 ) {
			if ( array_key_exists($index, $parts2) ) {
				if ( $parts2[$index] != $parts1[$index] ) {
					if ( $parts2[$index] > $parts1[$index] )
						return -1;
					else
						return 1;
				}
			} else
				return 1;
		}

		return 0;
	}

	function dd_repFoldesSummary( &$kernelStrings, &$total )
	//
	// Returns data for the Folders Summary report
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$total - totals array
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectFolderSummaryStats;

		$total = array( 'size'=>0, 'files'=>0 );

		$qr = db_query( $qr_dd_selectFolderSummaryStats, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$cache = array();

		while ( $row = db_fetch_array($qr) ) {
			$total['size'] += $row['SIZE'];
			$total['files'] += $row['CNT'];
			$row['SIZE'] = formatFileSizeStr( $row['SIZE'] );
			$row['OFFSET'] = substr_count( $row['DF_ID'], '.' )-1;

			$cache[] = $row;
		}

		db_free_result( $qr );

		$total['size'] = formatFileSizeStr( $total['size'] );

		usort( $cache, 'dd_repCompareFolders' );

		return $cache;
	}

	function dd_repFreqUpdFilesReport( $settings, &$kernelStrings )
	//
	// Returns data for the Frequently updated files report
	//
	//		Parameters:
	//			$settings - report settings
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_dd_selectFreqFilesStatsByDate;
		global $qr_dd_selectFreqFilesStatsByDateRange;

		$validator = new dd_reportDataRangeValidator();

		$res = $validator->loadFromArray( $settings, $kernelStrings, true, array( s_datasource=>s_form, 'kernelStrings'=>$kernelStrings ) );
		if ( PEAR::isError($res) )
			return $res;

		if ( $validator->type == 'days' )
			$sql = sprintf( $qr_dd_selectFreqFilesStatsByDate, $validator->days, $validator->days );
		else {
			$fromDate = ($validator->from).' 00:00:00';
			$toDate = ($validator->to).' 23:59:59';
			$sql = sprintf( $qr_dd_selectFreqFilesStatsByDateRange, $fromDate, $toDate, $fromDate, $toDate );
		}

		$qr = db_query( $sql, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$result[] = $row;
		}

		db_free_result( $qr );

		return $result;
	}
	
		function dd_updateFileDescription($U_ID, $PL_ID, $PL_DESC, &$kernelStrings) {

		global $qr_pd_updateFileDescr;
		$params = array(
			"DL_DESC" => $PL_DESC,
			"DL_MODIFYUSERNAME" => $U_ID,
			"DL_ID" => $PL_ID
		);

		$qr = db_query( $qr_pd_updateFileDescr, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		db_free_result($qr);

		return TRUE;
	}
	/**
	* Update uploaded file and  filesize
	* @bool
	* $DL_ID - id $_fileOld
	* $_fileNew - full path to NEW uploaded file (with name)
	* $_fileOld - full path to OLD existing file (with name)
	* $size		- new file size 
	*/
	function dd_updateFile ($DL_ID, $_fileNew, $_fileOld, $size) {
		global $qr_dd_updateUploadedFile;
		if (!isset($_fileNew)) {
			return PEAR::raiseError( $ddStrings['updlg_file_error_unknown'] );
		}

		if (!unlink($_fileOld)) {
			return PEAR::raiseError( $ddStrings['updlg_file_error_unknown'] );
		}

		$status = move_uploaded_file($_fileNew,$_fileOld);
		if ($status === FALSE) {
			return PEAR::raiseError( $ddStrings['updlg_file_error_unknown'] );
		}

		$param = array(
			'DL_FILESIZE' => (int)$size,
			'DL_ID' => $DL_ID,
		);
		$res = db_query($qr_dd_updateUploadedFile, $param);
		if (PEAR::isError($res)) {
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return true;
	}

?>