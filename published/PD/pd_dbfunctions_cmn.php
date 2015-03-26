<?php

	//
	// Document Depot DMBS-independent application functions
	//

	function pd_addFiles( $fileList, $PF_ID, $U_ID, $kernelStrings, $pdStrings, &$messageStack, &$lastFileName, &$resultStatistics, $generateThumbnail = true, $existingFilesOperation = PD_REPLACE_FILES, $isFromArchive=false, $fromWidget = false, $fromFlash = false )
	//
	// Adds files to folders
	//
	//		Parameters:
	//			$fileList - array of pd_fileDescription objects
	//			$PF_ID - folder identifier
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//			$messageStack - message stack
	//			$lastFileName - last processed file name
	//			$resultStatistics - file adding statistics
	//			$generateThumbnail - generate thumbnails for image files
	//			$existingFilesOperation - operation to perform on existing files
	//
	//		Returns null information or PEAR_Error
	//
	{
		
		global $qr_pd_insertFile;
		global $qr_pd_updateFile;
		global $qr_pd_getMaxPL_ID;
		global $pd_treeClass;
		global $PD_APP_ID;
		global $pd_knownImageFormats;
		global $qr_pd_maxSort;
		
		$resultStatistics = array();

		$resultStatistics['filesAdded'] = 0;
		$resultStatistics['imagesAdded'] = 0;
		$resultStatistics['imageErrors'] = 0;
		
		$filesLimitErrorCode = 502;
		$spaceLimitErrorCode = 503;
		$userQuotaErrorCode = 504;
		
		// Check user rights
		//
		if (!$fromWidget) {
			$rights = $pd_treeClass->getIdentityFolderRights( $U_ID, $PF_ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITE, TREE_READWRITEFOLDER ) ) )
				return PEAR::raiseError( $pdStrings['add_screen_norights_message'], ERRCODE_APPLICATION_ERR );
		}

		// Make folder directories
		//

		$res = pd_makeFolderDirs( $PF_ID, $pdStrings );

		if ( PEAR::isError($res) )
			return $res;

		// Check if thumbnail generation is enabled
		//
		$thumbnailEnabled = readApplicationSettingValue( $PD_APP_ID, PD_THUMBNAILSTATE, PD_THUMBENABLED, $kernelStrings );
		$thumbnailEnabled = $thumbnailEnabled == PD_THUMBENABLED && pd_thumbnailsSupported();

		$resultFileList = array();

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;
		
		$InitialTotalSpace = $TotalUsedSpace;
		$InitialUserSpace = $UserUsedSpace;

		$modifiedFiles = null;
		$limitPhotosCount = getApplicationResourceLimits($PD_APP_ID);
		$addedFiles = 0;
		//$limitPhotosCount = 1;

		foreach ( $fileList as $fileDescription )
		{
			$sql = "SELECT COUNT(*) FROM PIXLIST";
			$curCountPhotos = db_query_result( $sql, DB_FIRST, array() );

			if ($limitPhotosCount)
			{
				if ($curCountPhotos >= $limitPhotosCount)
				{
					$QuotaManager->Flush( $kernelStrings );

					$userInfo = ($fromFlash) ?
						$filesLimitErrorCode :
						array($addedFiles, $limitSpace, $isFromArchive, $modifiedFiles);
					return PEAR::raiseError(
						$kernelStrings['app_dbsizelimit_message'],
						PD_FILE_UPLOAD_EXC_LIMIT,
						null,
						null,
						$userInfo
					);
				}
			}

			// Check size limitations
			//
			$fileSize = $fileDescription->PL_FILESIZE;
			$lastFileName = $fileDescription->PL_FILENAME;
			$UserUsedSpace = $InitialUserSpace + $QuotaManager->GetSpaceUsageAdded();
			$TotalUsedSpace = $InitialTotalSpace + $QuotaManager->GetSpaceUsageAdded();

			// Check if the user disk space quota is not exceeded
			//
			if ( $QuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) ) {
				$QuotaManager->Flush( $kernelStrings );
				$error = $QuotaManager->ThrowNoSpaceError( $kernelStrings );
				$error->userinfo = $spaceLimitErrorCode;
				return $error;
			}

			// Check if the system disk space quota is not exceeded
			//
			if ( $QuotaManager->UserApplicationQuotaExceeded( $UserUsedSpace + $fileSize, $U_ID, $PD_APP_ID, $kernelStrings ) )
			{
				$QuotaManager->Flush( $kernelStrings );
				$limitSpace = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );
				$userInfo = ($fromFlash) ?
					$userQuotaErrorCode :
					array($addedFiles, $limitSpace, $isFromArchive, $modifiedFiles);
				return PEAR::raiseError(
					$kernelStrings['app_dbsizelimit_message'],
					PD_FILE_UPLOAD_EXC_DB_SIZE_LIMIT,
					null,
					null,
					$userInfo					
				);
			}

			$res = pd_documentAddingPermitted( $kernelStrings );
			if ( PEAR::isError($res) ) {
				$QuotaManager->Flush( $kernelStrings );
				$res->userinfo = $filesLimitErrorCode;
				return $res;
			}

			// Process version control
			//
			$createNewFileRecord = true;

			// Move file to folder directory
			//
			if ( $createNewFileRecord )
				$diskFileName = pd_generateUniqueDiskFilename( $fileDescription->PL_FILENAME, $PF_ID );
			else
				$diskFileName = $fileInfo['PL_DISKFILENAME'];

			$destPath = sprintf( "%s/%s", pd_getFolderDir( $PF_ID ), $diskFileName );			
			$destPathNew = sprintf( "%s/pd/%s/%s", WBS_PUBLIC_ATTACHMENTS_DIR, str_replace(".", "", $PF_ID), $diskFileName );
			

			//copy original 
			if ( !@copy( $fileDescription->sourcePath, $destPath ) )
			{
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $pdStrings['app_copyerr_message'] );
			}

			@unlink( $fileDescription->sourcePath );

			if ( $createNewFileRecord ) {
				$PL_ID = db_query_result( $qr_pd_getMaxPL_ID, DB_FIRST, array() );
				if ( PEAR::isError($PL_ID) ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				$PL_ID = incID($PL_ID);
			} else {
				$PL_ID = $fileInfo['PL_ID'];
			}

			$modifiedFiles[] = $PL_ID;

			// Generate thumbnail
			//
			$originalFileInfo = pathinfo( $fileDescription->PL_FILENAME );
			$thumbGenerated = false;
			$thumbPath = null;
			$ext = trim(strtolower($originalFileInfo['extension']));

			if ( $thumbnailEnabled && isset($originalFileInfo['extension']) && $generateThumbnail )
			{
				// Generate thumbnails

				pd_createThumbnailsForImage($U_ID, $destPath, $ext, $deleteOriginal, $newOriginalPath, $QuotaManager, $destPathNew);
				$thumbGenerated = pd_createThumbnailsForImage($U_ID, $destPath, $ext, $deleteOriginal, $newOriginalPath, $QuotaManager);
				
				//pd_createThumbnailsForImage($U_ID, $destPath, $ext, $deleteOriginal, $newOriginalPath, $QuotaManager);				

				if ($thumbGenerated) {
					if ($newOriginalPath) {
						$diskFileName = basename($newOriginalPath);
						$fileSize = filesize($newOriginalPath);
					}
					else {
						$fileSize = $fileDescription->PL_FILESIZE;
					}

					//$spaceUsed += $fileSize;

					/*if ( DATABASE_SIZE_LIMIT != 0 ) {
						//if ( $QuotaManager->UserApplicationQuotaExceeded( $UserUsedSpace + $fileSize, $U_ID, $PD_APP_ID, $kernelStrings ) ) {
						if ( spaceLimitExceeded( $spaceUsed/MEGABYTE_SIZE ) ) {
							//$QuotaManager->Flush( $kernelStrings );
							return PEAR::raiseError( $kernelStrings['app_dbsizelimit_message'], ERRCODE_APPLICATION_ERR );
						}
					}*/

					//�� ������� ��������
					$deleteOriginal = false;
					if ($deleteOriginal) {
						@unlink($destPath);
					}
				}
			}

			// Cut extention
			pd_getFileNameAndExtention($fileDescription->PL_FILENAME, $cutFileName, $cutFileExt);

			$params = array();
			$params['PF_ID'] = $PF_ID;
			$params['PL_ID'] = $PL_ID;
			$params['PL_DESC'] = $fileDescription->PL_DESC;
			$params['PL_FILENAME'] = $cutFileName;
			$params['PL_FILETYPE'] = pd_getFileType( $fileDescription->PL_FILENAME );
			$params['PL_FILESIZE'] = $fileSize;
			$params['PL_UPLOADUSERNAME'] = getUserName( $U_ID, true );
			$params['PL_MIMETYPE'] = $fileDescription->PL_MIMETYPE;
			$params['PL_DISKFILENAME'] = $diskFileName;
			$params['PL_MODIFYUSERNAME'] = $U_ID;
			$params['PL_STATUSINT'] = TREE_DLSTATUS_NORMAL;

			// Insert file record
			//
			$resultFileList[$PF_ID][] = $params;

			if ( $createNewFileRecord )
			{
				$SORT_ID = db_query_result( $qr_pd_maxSort, DB_FIRST, array( "PF_ID"=> $PF_ID ) );
				$params['PL_SORT'] = $SORT_ID + 1;

				$res = db_query( $qr_pd_insertFile, $params );
			}
			else
				$res = db_query( $qr_pd_updateFile, $params );

			if ( PEAR::isError($res) ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

			// Update disk usage
			//
			if (!$deleteOriginal) {
				$QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, $fileDescription->PL_FILESIZE );
			}

			$resultStatistics['filesAdded'] = $resultStatistics['filesAdded'] + 1;
			if ( in_array($ext, $pd_knownImageFormats) )
				$resultStatistics['imagesAdded'] = $resultStatistics['imagesAdded'] + 1;

			if ( !$thumbGenerated )
				$resultStatistics['imageErrors'] = $resultStatistics['imageErrors'] + 1;


			if ( !is_null($messageStack) ) {

				$messageStack[] = sprintf ( $pdStrings['add_screen_upload_success'], $fileDescription->PL_FILENAME );

				/*if ( $thumbGenerated )
					messageStack[] = sprintf( $pdStrings['add_screen_upload_info'], $fileDescription->PL_FILENAME, $kernelStrings['app_thumbcreated_message'] );
				else
					if ( PEAR::isError($thumbPath) )
						$messageStack[] = sprintf( $pdStrings['add_screen_upload_info'], $fileDescription->PL_FILENAME, $thumbPath->getMessage() );
				*/
			}

			$addedFiles ++;
		}

		$QuotaManager->Flush( $kernelStrings );

		pd_sendNotifications( $resultFileList, $U_ID, PD_ADDDOC, $kernelStrings );

		return $modifiedFiles;
	}

	function pd_documentAddingPermitted( &$kernelStrings )
	//
	// Checks whether adding document is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		return null;
	}

	function pd_checkFilesExistence( $fileList, $PF_ID, $U_ID, $kernelStrings, $pdStrings, &$filesFound )
	//
	// Check if files already exists in the folder
	//
	//		Parameters:
	//			$fileList - array of pd_fileDescription objects
	//			$PF_ID - folder identifier
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//			$filesFound - sets to true if any files already exists
	//
	//		Returns array with file data or PEAR_Error
	//
	{
		$filesFound = false;

		$result = array();

		foreach ( $fileList as $fileDescription ) {
			$fileInfo = pd_getFileByName( $fileDescription->PL_FILENAME, $PF_ID, $kernelStrings );
			if ( PEAR::isError($fileInfo) )
				return $fileInfo;

			if ( !count($fileInfo) || is_null($fileInfo) )
				continue;

			if ( !is_null($fileInfo) )
				if ( $fileInfo['PL_CHECKSTATUS'] == PD_CHECK_OUT && $fileInfo['PL_CHECKUSERID'] != $U_ID )
					$fileInfo['LOCKED'] = 1;
				else
					$fileInfo['LOCKED'] = 0;

			$result[] = $fileInfo;
			if ( !is_null($fileInfo) )
				$filesFound = true;
		}

		return $result;
	}


	function pd_getFileByName( $fileName, $PF_ID, &$kernelStrings )
	//
	// Returns file data by file name
	//
	//		Parameters:
	//			$fileName - file name
	//			$PF_ID - file folder
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or null PEAR::Error
	//
	{
		global $qr_pd_selectFileByName;

		$params = array();
		$params['PL_FILENAME'] = strtolower(trim($fileName));
		$params['PF_ID'] = $PF_ID;

		$res = db_query_result( $qr_pd_selectFileByName, DB_ARRAY, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !is_array($res) || !count($res) )
			return null;

		if ( !strlen($res['PL_FILENAME']) )
			return null;

		return $res;
	}

	function pd_getDocumentData( $PL_ID, $kernelStrings )
	//
	// Returns document data (object containing record from DOCLIST table)
	//
	//		Parameters:
	//			$PL_ID - document identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns object or PEAR_Error
	//
	{
		global $qr_pd_selectFile;

		$res = db_query_result( $qr_pd_selectFile, DB_ARRAY, array('PL_ID'=>$PL_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return (object)$res;
	}

	function pd_getUserName( $U_ID )
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

	function pd_checkUserCheckOperationRights( $U_ID, $PL_ID, &$kernelStrings )
	//
	// Checks if user has rights to check in or out file
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$PL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $pd_treeClass;

		$docData = pd_getDocumentData( $PL_ID, $kernelStrings );
		if ( PEAR::isError($docData) )
			return $docData;

		$rights = $pd_treeClass->getIdentityFolderRights( $U_ID, $docData->PF_ID, $kernelStrings );
		if ( PEAR::isError($rights) )
			return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITE, TREE_READWRITEFOLDER ) ) )
			return false;
		else
			return true;

		return true;
	}

	function pd_deleteRestoreDocuments( $documentList, $operation, $U_ID, $kernelStrings, $pdStrings, $destPF_ID = null, $admin = false, $fromWidget = false )
	//
	// Moves document to recycle bin or back to folder
	//
	//		Parameters:
	//			$documentList - array of document identifiers
	//			$operation - file operation - PD_DELETEDOC, PD_RESTOREDOC
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//			$destPF_ID - destination folder for restore operation
	//			$admin - do not perform rights checking
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pd_updateFileStatus;
		global $qr_pd_updateFileLocationStatus;
		global $pd_treeClass;

		$versionControlEnabled = pd_versionControlEnabled($kernelStrings);

		// Check user rights for restore operation
		//
		if ( $operation == PD_RESTOREDOC ) {
			if ( is_null($destPF_ID) )
				return PEAR::raiseError( $pdStrings['sv_screen_nodest_message'], ERRCODE_APPLICATION_ERR );

			if ( !$admin ) {
				$rights = $pd_treeClass->getIdentityFolderRights( $U_ID, $destPF_ID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITE, TREE_READWRITEFOLDER ) ) )
					return PEAR::raiseError( $pdStrings['r_screen_norights_message'], ERRCODE_APPLICATION_ERR );
			}
		}

		$resultFileList = array();

		foreach( $documentList as $PL_ID ) {
			$docData = pd_getDocumentData( $PL_ID, $kernelStrings );
			if ( PEAR::isError($docData) )
				return $docData;

			if ( $versionControlEnabled && $operation == PD_RESTOREDOC ) {
				// Delete existing file in the destination folder
				//
				$fileInfo = pd_getFileByName( $docData->PL_FILENAME, $destPF_ID, $kernelStrings );
				if ( PEAR::isError($fileInfo) )
					return $fileInfo;

				if ( !is_null($fileInfo) ) {
					if ( $fileInfo['PL_CHECKSTATUS'] == PD_CHECK_OUT )
						if ( $fileInfo['PL_CHECKUSERID'] != $U_ID ) {
							$userName = pd_getUserName( $fileInfo['PL_CHECKUSERID'] );

							return PEAR::raiseError( sprintf($pdStrings['cm_replace_error'], $docData->PL_FILENAME, $userName), ERRCODE_APPLICATION_ERR );
						}

					pd_deleteRestoreDocuments( array($fileInfo['PL_ID']), PD_DELETEDOC, $U_ID, $kernelStrings, $pdStrings );
				}
			}

			// Check user rights for delete operation
			//
			if ( $operation == PD_DELETEDOC && !$admin ) {
				$rights = $pd_treeClass->getIdentityFolderRights( $U_ID, $docData->PF_ID, $kernelStrings );
				if ( PEAR::isError($rights) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITE, TREE_READWRITEFOLDER ) ) )
					return PEAR::raiseError( $pdStrings['sv_screen_nodelrights_message'], ERRCODE_APPLICATION_ERR );

				$fileIsLocked = ( $docData->PL_CHECKSTATUS == PD_CHECK_OUT && $docData->PL_CHECKUSERID != $U_ID );

				if ( $versionControlEnabled && $fileIsLocked && !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
				{
					$userName = pd_getUserName( $docData->PL_CHECKUSERID );
					return PEAR::raiseError( sprintf($pdStrings['pd_screen_deletelocked_error'], $docData->PL_FILENAME, $userName), ERRCODE_APPLICATION_ERR );
				}
			}

			if ( $operation == PD_DELETEDOC ) {
				$sourcePath = sprintf( "%s/%s", pd_getFolderDir( $docData->PF_ID ), $docData->PL_DISKFILENAME );
				$diskFileName = pd_generateUniqueDiskFilename( $docData->PL_FILENAME, $docData->PF_ID, true );
				$destPath = sprintf( "%s/%s", pd_recycledDir(), $diskFileName );

				$res = pd_makeRecycledDir( $pdStrings );
				if ( PEAR::isError($res) )
					return $res;
			} else {
				$sourcePath = sprintf( "%s/%s", pd_recycledDir(), $docData->PL_DISKFILENAME );
				$diskFileName = pd_generateUniqueDiskFilename( $docData->PL_FILENAME, $destPF_ID );
				$destPath = sprintf( "%s/%s", pd_getFolderDir($destPF_ID), $diskFileName );

				$destDir = pd_getFolderDir($destPF_ID);
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
						return PEAR::raiseError( $pdStrings['app_copyerr_message'] );

					if ( !@unlink($srcThumbFile) )
						return PEAR::raiseError( $pdStrings['app_delerr_message'] );
				}

				// Copy original file
				//
				if ( !@copy( $sourcePath, $destPath ) )
					return PEAR::raiseError( $pdStrings['app_copyerr_message'] );

				if ( !@unlink($sourcePath) )
					return PEAR::raiseError( $pdStrings['app_delerr_message'] );

			}

			$params = array();
			$params['PL_DISKFILENAME'] = $diskFileName;

			if ( $operation == PD_DELETEDOC ) {
				$params['PL_DELETE_U_ID'] = $U_ID;
				$params['PL_DELETE_DATETIME'] = convertToSqlDate( time() );
				$params['PL_DELETE_USERNAME'] = getUserName($U_ID, true);
			} else {
				$params['PL_DELETE_U_ID'] = null;
				$params['PL_DELETE_DATETIME'] = null;
				$params['PL_DELETE_USERNAME'] = null;
			}

			$params['PL_STATUSINT'] =  ($operation == PD_DELETEDOC) ? TREE_DLSTATUS_DELETED : TREE_DLSTATUS_NORMAL;
			$params['PL_ID'] = $PL_ID;

			if ( $operation == PD_DELETEDOC ) {
				$docInfo = $pd_treeClass->getDocumentInfo( $PL_ID, $kernelStrings );
				$resultFileList[$docInfo['PF_ID']][] = $docInfo;

				$params['PF_ID'] = null;
				$res = db_query( $qr_pd_updateFileLocationStatus, $params );
			} else {
				$docInfo = $pd_treeClass->getDocumentInfo( $PL_ID, $kernelStrings );
				$params['PF_ID'] = $destPF_ID;
				$resultFileList[$destPF_ID][] = $docInfo;

				$res = db_query( $qr_pd_updateFileLocationStatus, $params );
			}

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		if ( $operation == PD_DELETEDOC )
			pd_sendNotifications( $resultFileList, $U_ID, PD_DELETEDOC, $kernelStrings );
		else
			pd_sendNotifications( $resultFileList, $U_ID, PD_ADDDOC, $kernelStrings );

		return null;
	}

	function pd_restoreFolders( $folderList, $U_ID, $kernelStrings, $pdStrings, $destPF_ID = null, $admin = false )
	//
	// Restores deleted folders
	//
	//		Parameters:
	//			$folderList - array of folder identifiers
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//			$destPF_ID - destination folder for restore operation
	//			$admin - do not perform rights checking
	//
	//		Returns null or PEAR_Error
	//
	{
		global $pd_treeClass;
		global $qr_pd_updateFolderStatus;
		global $qr_pd_updateFodlerUpdateStatusFields;

		// Check user rights for restore operation
		//
		if ( is_null($destPF_ID) )
			return PEAR::raiseError( $pdStrings['sv_screen_nodest_message'], ERRCODE_APPLICATION_ERR );

		if ( !$admin ) {
			$rights = $pd_treeClass->getIdentityFolderRights( $U_ID, $destPF_ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;


			if ( !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITE, TREE_READWRITEFOLDER ) ) )
				return PEAR::raiseError( $pdStrings['r_screen_norights_message'], ERRCODE_APPLICATION_ERR );
		}

		foreach( $folderList as $PF_ID ) {
			$callbackParams = array( 'ddStrings'=>$pdStrings, "kernelStrings"=>$kernelStrings, "restore" => true, "folder"=>$PF_ID );

			$pd_treeClass->moveFolder( $PF_ID, $destPF_ID, $U_ID, $kernelStrings,
										"pd_onAfterCopyMoveFile", "pd_onCopyMoveFile", "pd_onCreateFolder", "pd_onDeleteFolder",
										$callbackParams, "pd_onFinishMoveFolder", false, true, ACCESSINHERITANCE_INHERIT );
		}
	}

	function pd_getFileVersion( $PL_ID, $kernelStrings )
	//
	// Returns current file version
	//
	//		Parameters:
	//			$PL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns integer or PEAR_Error
	//
	{
		global $qr_pd_selectMaxHistoryID;

		$version = db_query_result( $qr_pd_selectMaxHistoryID, DB_FIRST, array('PL_ID'=>$PL_ID) );
		if ( PEAR::isError($version) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$version = incID($version);

		return $version;
	}

	function pd_getHistoryRecord( $PL_ID, $DLH_VERSION, $kernelStrings )
	//
	// Returns file version record
	//
	//		Parameters:
	//			$PL_ID - file identifier
	//			$DLH_VERSION - version number
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_pd_selectHistoryRecord;

		$res = db_query_result( $qr_pd_selectHistoryRecord, DB_ARRAY, array('PL_ID'=>$PL_ID, 'DLH_VERSION'=>$DLH_VERSION) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function pd_onFinishMoveFolder( $params )
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

		global $qr_pd_updateFolderStatus;
		global $qr_pd_updateFodlerUpdateStatusFields;

		if ( !$suppressNotifications ) {
			$deletedFolderData['folderUsers'] = $folderUsers;
			$objectList = array( $deletedFolderData['PF_ID_PARENT']=>$deletedFolderData );
			pd_sendNotifications( $objectList, $U_ID, PD_DELETEFOLDER, $kernelStrings );

			$objectList = array( $newFolderData['PF_ID_PARENT']=>$newFolderData );
			pd_sendNotifications( $objectList, $U_ID, PD_ADDFOLDER, $kernelStrings );
		}

		if ( isset($restore) ) {
			$params = array();
			$params['PF_ID'] = $folder;
			$params['PF_STATUS'] = TREE_FSTATUS_NORMAL;

			db_query( $qr_pd_updateFolderStatus, $params );
		}

		$userName = getUserName( $U_ID, true );
		$sqlParams = array( 'PF_MODIFYUSERNAME'=>$userName, 'PF_ID'=>$folder );

		db_query( $qr_pd_updateFodlerUpdateStatusFields, $sqlParams );
	}

	function pd_finishCopyFolder( $kernelStrings, $U_ID, $operation, $callbackParams )
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

		global $pd_treeClass;

		$folderInfo = $pd_treeClass->getFolderInfo( $newID, $kernelStrings );
		$objectList = array( $destID=>$folderInfo );

		pd_sendNotifications( $objectList, $U_ID, PD_ADDFOLDER, $kernelStrings );

		global $qr_pd_updateFolderCreateStatusFields;

		$userName = getUserName( $U_ID, true );
		$sqlParams = array( 'PF_CREATEUSERNAME'=>$userName, 'PF_MODIFYUSERNAME'=>$userName, 'PF_ID'=>$folder );

		db_query( $qr_pd_updateFolderCreateStatusFields, $sqlParams );
	}

	function pd_onCreateFolder( $PF_ID, $params )
	//
	// Callback function, on folder create
	//
	//		Parameters:
	//			$PF_ID - new folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		extract($params);

		// Make folder directories
		//
		$res = pd_makeFolderDirs( $PF_ID, $pdStrings );
		if ( PEAR::isError($res) )
			return $res;

		global $pd_treeClass;

		$folderInfo = $pd_treeClass->getFolderInfo( $PF_ID, $kernelStrings );

		$objectList = array( $ID_PARENT=>$folderInfo );

		global $qr_pd_updateFolderCreateStatusFields;
		global $qr_pd_updateFolderCreateMoveStatusFields;

		$userName = getUserName( $U_ID, true );

		if ( !isset($move) ) {
			$sqlParams = array( 'PF_CREATEUSERNAME'=>$userName, 'PF_MODIFYUSERNAME'=>$userName, 'PF_ID'=>$PF_ID );
			db_query( $qr_pd_updateFolderCreateStatusFields, $sqlParams );
		} else {
			$sqlParams = array( 'PF_CREATEUSERNAME'=>$originalData['PF_CREATEUSERNAME'],
								'PF_MODIFYUSERNAME'=>$originalData['PF_MODIFYUSERNAME'],
								'PF_ID'=>$PF_ID, 'PF_CREATEDATETIME'=>$originalData['PF_CREATEDATETIME'] );
			db_query( $qr_pd_updateFolderCreateMoveStatusFields, $sqlParams );
		}


		if ( !$suppressNotifications )
			pd_sendNotifications( $objectList, $U_ID, PD_ADDFOLDER, $kernelStrings );
		
		global $qr_pd_updateFolderWidgets;
		if (isset($deletedFolderData) && is_array($deletedFolderData)) {
			$wgUpdateParams = array ("OLD_FOLDER_ID" =>  $deletedFolderData["PF_ID"], "NEW_FOLDER_ID" => $PF_ID);
			$res = db_query( $qr_pd_updateFolderWidgets, $wgUpdateParams );
		}

		return null;
	}


	function pd_onDeleteFolder( $PF_ID, $params )
	//
	// Callback function, on folder delete
	//
	//		Parameters:
	//			$PF_ID - folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		extract($params);

		if ( !isset($physicallyDelete) )
			$physicallyDelete = true;

		if ( $physicallyDelete ) {
			// Delete folder files
			//
			$destPath = pd_getFolderDir( $PF_ID );

			if ( realpath(PD_FILES_DIR) != realpath($destPath) )
				if (!pd_removeDir($destPath, $U_ID)) {
					return null;
				}
		}

		// Send notification
		//
		if ( !$suppressNotifications ) {
			$objectList = array( $deletedFolderData['PF_ID_PARENT']=>$deletedFolderData );
			pd_sendNotifications( $objectList, $U_ID, PD_DELETEFOLDER, $kernelStrings );
		}

		if ( $physicallyDelete ) {
			// Delete folder's local view settings
			//
			pd_deleteFolderVewSettings( $PF_ID, $kernelStrings );
		} else {
			// Update status fields
			//
			global $qr_pd_updateFodlerUpdateStatusFields;

			$userName = getUserName( $U_ID, true );
			$sqlParams = array( 'PF_MODIFYUSERNAME'=>$userName, 'PF_ID'=>$PF_ID );

			db_query( $qr_pd_updateFodlerUpdateStatusFields, $sqlParams );
		}

		return null;
	}

	function pd_removeDir($destPath, $U_ID) {
		global $PD_APP_ID;
		global $kernelStrings;

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		$InitialTotalSpace = $TotalUsedSpace;
		$InitialUserSpace = $UserUsedSpace;

		$handle = opendir($destPath);
		while (false !== ($file = readdir($handle))){
			if ($file != "." && $file != ".." && !is_dir($file)) {
				$files[] = $file;
			}
		}

		foreach ($files as $fl) {
			$destFile = sprintf("%s/%s", $destPath, $fl);
			$QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, -1 * filesize($destFile) );
			@unlink($destFile);
		}

		$QuotaManager->Flush( $kernelStrings );

		removeDir($destPath);

		return TRUE;
	}

	function pd_removeDocuments( $documentList, $U_ID, $kernelStrings, $pdStrings, $admin = false )
	//
	// Completely deletes document
	//
	//		Parameters:
	//			$documentList - array of document identifiers
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//			$admin - do not perform rights checking
	//
	//		Returns null or PEAR_Error
	//
	{
		global $pd_treeClass;
		global $qr_pd_deleteFile;
		global $qr_pd_selectFileHistory;
		global $qr_pd_deleteFileHistory;
		global $PD_APP_ID;

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		$InitialTotalSpace = $TotalUsedSpace;
		$InitialUserSpace = $UserUsedSpace;

		foreach( $documentList as $PL_ID )
		{
			$docData = pd_getDocumentData( $PL_ID, $kernelStrings );
			if ( PEAR::isError($docData) )
				return $docData;

			if ( !$admin )
				if ( $docData->PL_DELETE_U_ID != $U_ID ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $pdStrings['sv_screen_nodelrights_message'], ERRCODE_APPLICATION_ERR );
				}


			// Remove original file
			//
			$fileDir = pd_getFolderDir( $docData->PF_ID );

			pd_getFileNameAndExtention($docData->PL_DISKFILENAME, $delFileName, $delFileExt);
			pd_getFileNameAndExtention($delFileName, $delFileBaseName, $thumbSize);

			pd_removeAllThumbnails($U_ID, $fileDir, $delFileBaseName, $delFileExt, $QuotaManager);

			// Delete database record
			//
			$res = db_query( $qr_pd_deleteFile, array('PL_ID'=>$PL_ID) );
			if ( PEAR::isError($res) ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}
		}

		$QuotaManager->Flush( $kernelStrings );

		return null;
	}

	function pd_getFileHistory( $PL_ID, $kernelStrings )
	//
	// Returns file history as array
	//
	//		Parameters:
	//			$PL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_ERROR
	//
	{
		global $qr_pd_selectFileHistory;

		$qr = db_query( $qr_pd_selectFileHistory, array('PL_ID'=>$PL_ID) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$result[] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	function pd_getMaxSort($PF_ID) {
		$sql = "SELECT MAX(PL_SORT) as max FROM PIXLIST WHERE PF_ID='".$PF_ID."'";
		$r = db_query( $sql, array() );
		$max = db_fetch_array($r);
		$max = $max["max"] + 1;
		db_free_result( $r );
		return $max;
	}

	function pd_onAfterCopyMoveFile( $kernelStrings, $U_ID, $docData, $operation, $params )
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
		global $qr_pd_insertFile;
		global $qr_pd_updateFileLocation;

		extract( $params );

		if ( $operation == TREE_COPYDOC ) {
			$docData["PL_SORT"] = pd_getMaxSort($docData['PF_ID']);

			$docData['PL_UPLOADUSERNAME'] = getUserName( $U_ID, true );
			$docData['PL_DISKFILENAME'] = $diskFileName;

			$res = pd_documentAddingPermitted( $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			$res = db_query( $qr_pd_insertFile, $docData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			$docData['PL_DISKFILENAME'] = $diskFileName;
			$docData["PL_SORT"] = pd_getMaxSort($docData['PF_ID']);

			$res = db_query( $qr_pd_updateFileLocation, $docData );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function pd_fileExists( $fileName, $PL_ID, $PF_ID, $kernelStrings )
	//
	// Checks if file with the given name exists in the given folder
	//
	//		Parameters:
	//			$fileName - file name to check
	//			$PL_ID - file identifier to skip
	//			$PF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_pd_selectFileNameCount;

		$params = array();
		$params['PL_ID'] = $PL_ID;
		$params['PF_ID'] = $PF_ID;
		$params['PL_FILENAME'] = $fileName;

		$res = db_query_result( $qr_pd_selectFileNameCount, DB_FIRST, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}


	function pd_modifyFile( $PL_ID, $U_ID, $fileData, $kernelStrings, $pdStrings )
	//
	// Modifies file name and description
	//
	//		Parameters:
	//			$PL_ID - file identifier
	//			$U_ID - user identifier
	//			$fileData - array containing file data
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $pd_treeClass;
		global $qr_pd_updateFileNameDescription;
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		// Check required fields and user rights
		//
		$docData = pd_getDocumentData( $PL_ID, $kernelStrings );
		if ( PEAR::isError($docData) )
			return $docData;

		$rights = $pd_treeClass->getIdentityFolderRights( $U_ID, $docData->PF_ID, $kernelStrings );
		if ( PEAR::isError($rights) )
			return $rights;

		if ( !UR_RightsObject::CheckMask( $rights, array( TREE_READWRITE, TREE_READWRITEFOLDER ) ) )
			return PEAR::raiseError( $pdStrings['mf_screen_nomodrights_message'], ERRCODE_APPLICATION_ERR );

		$requiredFields = array( "PL_FILENAME" );
		if ( PEAR::isError( $invalidField = findEmptyField($fileData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		if ( ereg( "\\/|\\\|\\?|:|<|>|\\*", $fileData["PL_FILENAME"] ) || !(strpos($fileData["PL_FILENAME"], "\\") === FALSE) )
			return PEAR::raiseError( $pdStrings['mf_screen_invchars_message'], ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
												$_PEAR_default_error_options, "PL_FILENAME" );

		$fileData['PL_ID'] = $PL_ID;

		// Check if file name is not exists yet
		//
		$versionControlEnabled = pd_versionControlEnabled($kernelStrings);
		if ( PEAR::isError($versionControlEnabled) )
			return $versionControlEnabled;

		if ( $versionControlEnabled ) {
			$res = pd_fileExists( $fileData["PL_FILENAME"], $PL_ID, $docData->PF_ID, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			if ( $res ) {
				return PEAR::raiseError( $pdStrings['mf_fileexists_message'],
											ERRCODE_INVALIDFIELD,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											"PL_FILENAME" );
			}
		}

		// Update database record
		//
		$res = db_query( $qr_pd_updateFileNameDescription, $fileData );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}


	function pd_searchFiles( $searchString, $U_ID, $sortStr, $kernelStrings, $entryProcessor = null )
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
		global $qr_pd_findFiles;
		global $qr_pd_findGroupFiles;
		global $qr_pd_findFilesGlobal;
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
			$fileNameConstraints[] = sprintf( "LOWER(PL_FILENAME) LIKE '!group%s!'", $index );
			$fileDescConstraints[] = sprintf( "LOWER(PL_DESC) LIKE '!group%s!'", $index );
		}

		$fileNameConstraints = implode( " OR ", $fileNameConstraints );
		$fileDescConstraints = implode( " OR ", $fileDescConstraints );

		$params = array( 'U_ID' => $U_ID );

		foreach ( $groupsToFind as $index=>$group ) {
			$params[sprintf('group%s', $index)] = "%".strtolower($group)."%";
		}

		$globalAdmin = $UR_Manager->IsGlobalAdministrator( $U_ID );
		$sql = $globalAdmin ? $qr_pd_findFilesGlobal : $qr_pd_findFiles;

		$sql = sprintf($sql, $fileNameConstraints, $fileDescConstraints, $sortStr);

		$qr = db_query( $sql, $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
			if ( !is_null($entryProcessor) )
				$result[$row->PL_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->PL_ID] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	function pd_getFilesById( $filesIds, $U_ID, $sortStr, $kernelStrings, $entryProcessor = null )
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
		global $qr_pd_getFilesByIds;
		global $UR_Manager;

		$params = array( 'U_ID' => $U_ID);
		if ($sortStr)
			$sortStr = "ORDER BY $sortStr";

		$qr = db_query( sprintf($qr_pd_getFilesByIds, implode(",", $filesIds), $sortStr), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {
			if ( !is_null($entryProcessor) )
				$result[$row->PL_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->PL_ID] = $row;
		}

		db_free_result( $qr );

		return $result;
	}

	/*function pd_getFileById( $FILE_ID, $U_ID, $kernelStrings, $entryProcessor = null )
	//
	// Searches files
	//
	//		Parameters:
	//			$FILE_ID - file id
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array of file objects or PEAR_Error
	//
	{
		global $qr_pd_getImageById;
		global $UR_Manager;

		$params = array( 'U_ID' => $U_ID, 'PL_ID' => $FILE_ID );

		$qr = db_query( $qr_pd_getImageById, $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		echo $qr_pd_getImageById;
		$result = array();

		while ( $row = db_fetch_array($qr, DB_FETCHMODE_OBJECT ) ) {

			if ( !is_null($entryProcessor) )
				$result[$row->PL_ID] = call_user_func( $entryProcessor, $row );
			else
				$result[$row->PL_ID] = $row;
		}

		db_free_result( $qr );

		return $result[$row->PL_ID];
	}*/

	function pd_sendNotifications( $objectList, $U_ID, $operation, $kernelStrings )
	//
	// Sends email notifications about file and folder operations
	//
	//		Parameters:
	//			$objectList - list of objects being updated: array( PF_ID1=>array( object1, object2...),...  )
	//			$U_ID - identificator of user caused operation
	//			$srcFolderList - list of source folders
	//			$operation - operation: PD_ADDDOC, PD_DELETEDOC, PD_ADDFOLDER, PD_DELETEFOLDER
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $pd_treeClass;
		global $PD_APP_ID;
		global $pd_loc_str;

		$fileOperations = array( PD_ADDDOC, PD_DELETEDOC );
		$folderOperations = array( PD_ADDFOLDER, PD_DELETEFOLDER );

		$fileOperation = in_array( $operation, $fileOperations );
		$folderOperation = in_array( $operation, $folderOperations );

		if ( !$fileOperation && !$folderOperation )
			return null;

		if ( !count($objectList) )
			return null;

		$affectedUsers = array();
		$bodyParts = array();
		$folderLinks = array();

		foreach ( $objectList as $PF_ID=>$folderObjects ) {
			// List of users assigned to this folder
			//
			$parentFolder = $PF_ID;

			if ( $folderOperation )
				$PF_ID = $folderObjects['PF_ID'];

			if ( !isset( $folderObjects['folderUsers'] ) ) {
				$folderUsers = $pd_treeClass->listFolderUsers( $PF_ID, $kernelStrings );
				if ( PEAR::isError($folderUsers) )
					return $folderUsers;
			} else {
				$folderUsers = $folderObjects['folderUsers'];
			}

			$affectedUsers = array_merge( $affectedUsers, $folderUsers );

			foreach ( $affectedUsers as $user=>$userData )
				$folderLinks[$user][$PF_ID] = 1;

			$bodyPart = array();

			// Folder name
			//
			$folderInfo = $pd_treeClass->getFolderInfo( $parentFolder, $kernelStrings, true );
			if ( PEAR::isError($folderInfo) )
				return $folderInfo;

			$bodyPart['PF_NAME'] = $folderInfo['PF_NAME'];

			// Path to folder
			//
			if ( $parentFolder != TREE_ROOT_FOLDER ) {
				$path = $pd_treeClass->getPathToFolder( $parentFolder, $kernelStrings );
				if ( PEAR::isError($path) )
					return $path;
			} else
				$path = array( $parentFolder => $folderInfo['PF_NAME'] );

			if ( $fileOperation )
				$path = array_merge( $path, array( $PF_ID=>$folderInfo['PF_NAME'] ) );

			$path = implode( '->', $path );

			$bodyPart['PATH'] = $path;

			// Object list
			//
			$objects = array();
			if ( $fileOperation ) {
				foreach( $folderObjects as $key => $data ) {
					$info = sprintf( "&nbsp;&nbsp;&nbsp;%s", $data['PL_FILENAME'] );
					$objects[] = $info;
				}

				$objects = implode( "<br>", $objects );
			} else
				$objects = $folderObjects;

			$bodyPart['OBJECTS'] = $objects;
			$bodyPart['OBJECT_NUM'] = count($folderObjects);

			$bodyParts[$PF_ID] = $bodyPart;
		}

		$operationTexts = array( PD_ADDDOC=>'app_mail_addoperation', PD_DELETEDOC=>'app_mail_deleteoperation',
									PD_ADDFOLDER=>'app_mail_addfldoperation', PD_DELETEFOLDER=>'app_mail_delfldoperation' );

		foreach( $affectedUsers as $userID => $data ) {
			if ( $userID == $U_ID )
				continue;

			$language = readUserCommonSetting( $userID, LANGUAGE );
			$userDDStrings = $pd_loc_str[$language];

			$messageHeader = sprintf($userDDStrings['app_mail_title'], getUserName( $userID, true ) );
			$messageBody = sprintf( "%s<br><br>", $userDDStrings['app_mail_info'] );

			$messageSubject = $userDDStrings['app_mail_subject'];
			$userFolders = $folderLinks[$userID];

			$index = 0;

			foreach( $userFolders as $PF_ID=>$value ) {
				$folderData = $bodyParts[$PF_ID];

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
					$messageBody .= sprintf( "%s: <b>%s</b><br><br>%s: %s", $folderHeader, $folderData['PATH'], $operationDescription, $bodyPart['OBJECTS']['PF_NAME'] );
				$index++;
			}

			@sendWBSMail( $userID, null, $U_ID, $messageSubject, 1, $messageBody, $kernelStrings, PD_MAIL_NOTIFICATION, $PD_APP_ID, $messageHeader );
		}
	}

	function pd_getViewOptions( $U_ID, &$visibleColumns, &$viewMode, &$recordsPerPage,
								&$showSharedPanel, &$displayIcons, &$folderViewMode, &$restrictDescLen, $PF_ID, $kernelStrings,
								$useCookies = false )
	//
	//	Returns view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (PD_GRID_VIEW, PD_LIST_VIEW, PD_THUMBLIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$displayIcons - display icons
	//			$folderViewMode - folder view mode (PD_FLDVIEW_GLOBAL, PD_FLDVIEW_LOCAL)
	//			$restrictDescLen - maximum description length to display
	//			$PF_ID - folder identifier
	//			$kernelStrings - Kernel localization string
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $pd_columns;
		global $PD_APP_ID;

		$visibleColumns = array();

		$folderViewMode = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_FOLDERVIEWOPT', null, $useCookies );
		if ( !strlen($folderViewMode) )
			$folderViewMode = PD_FLDVIEW_LOCAL;

		$columns = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_VISIBLECOLUMNS', $useCookies );
		if ( $columns != "none" ) {
			if ( strlen($columns) )
				$visibleColumns = explode( ";", $columns );
			else
				$visibleColumns = array( PD_COLUMN_FILETYPE, PD_COLUMN_FILESIZE, PD_COLUMN_UPLOADDATE, PD_COLUMN_UPLOADUSER );
		} else
			$visibleColumns = array();

		// Try to load local view settings
		//
		pd_getLocalViewSettings( $U_ID, $PF_ID, $viewMode );

		// Load global view settings
		//
		if ( is_null($viewMode) )
		{
			$viewMode = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_VIEWMODE', null, $useCookies );

			if ( is_null( $viewMode ) || $viewMode == "" )
				$viewMode =  PD_THUMBTILE_VIEW;
		}

		if ( !strlen($viewMode) )
			$viewMode = PD_LIST_VIEW;

		$recordsPerPage = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_RECORDPERPAGE', null, $useCookies );
		if ( !strlen($recordsPerPage) )
			$recordsPerPage = 30;

		$showSharedPanel = getUserBooleanAppSetting( $U_ID, $PD_APP_ID, $PD_APP_ID.TREE_SHOWWHAREDPANEL, $kernelStrings );
		if ( !strlen($showSharedPanel) )
			$showSharedPanel = false;

		$displayIcons = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_DISPLAYICONS', null, $useCookies );
		if ( !strlen($displayIcons) )
			$displayIcons = 1;

		$restrictDescLen = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_RESTRICTDESCLEN', null, $useCookies );

		return null;
	}

	function pd_setViewOptions( $U_ID, $visibleColumns, $viewMode, $recordsPerPage,
								$showSharedPanel, $displayIcons, $folderViewMode, $restrictDescLen, $PF_ID,
								$kernelStrings, $useCookies = false )
	//
	//	Saves view options for specified user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$visibleColumns - array of visible columns
	//			$viewMode - view mode (PD_GRID_VIEW, PD_LIST_VIEW, PD_THUMBLIST_VIEW)
	//			$recordsPerPage - number of files on one page
	//			$showSharedPanel - show shared panel in Document Depot window
	//			$displayIcons - display icons
	//			$folderViewMode - folder view mode (PD_FLDVIEW_GLOBAL, PD_FLDVIEW_LOCAL)
	//			$restrictDescLen - maximum description length to display
	//			$PF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//			$useCookies - use cookies instead of database
	//
	//		Returns null
	//
	{
		global $PD_APP_ID;

		if ( is_null($folderViewMode) ) {
			$folderViewMode = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_FOLDERVIEWOPT', null, $useCookies );
			if ( !strlen($folderViewMode) )
				$folderViewMode = PD_FLDVIEW_LOCAL;
		} else
			setAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_FOLDERVIEWOPT', $folderViewMode, $kernelStrings, $useCookies );

		if ( $folderViewMode == PD_FLDVIEW_GLOBAL )
			$PF_ID = null;

		// Save global view settings
		//
		if ( !is_null($visibleColumns) ) {
			$visibleColumns = implode( ";", $visibleColumns );

			if ( !strlen($visibleColumns) )
				$visibleColumns = "none";

			setAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_VISIBLECOLUMNS', $visibleColumns, $kernelStrings, $useCookies );
		}

		if ( $folderViewMode == PD_FLDVIEW_GLOBAL )
			if ( !is_null($viewMode) )
				setAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_VIEWMODE', $viewMode, $kernelStrings, $useCookies );

		if ( !is_null($recordsPerPage) )
			setAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_RECORDPERPAGE', $recordsPerPage, $kernelStrings, $useCookies );

		if ( !is_null($showSharedPanel) ) {
			if ( !$showSharedPanel )
				$showSharedPanel = 0;
			setAppUserCommonValue( $PD_APP_ID, $U_ID, $PD_APP_ID.TREE_SHOWWHAREDPANEL, $showSharedPanel, $kernelStrings, $useCookies );
		}

		if ( !is_null($displayIcons) ) {
			if ( !$displayIcons )
				$displayIcons = 0;
			setAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_DISPLAYICONS', $displayIcons, $kernelStrings, $useCookies );
		}

		if ( !is_null($restrictDescLen) ) {
			if ( !$restrictDescLen )
				$restrictDescLen = 0;
			setAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_RESTRICTDESCLEN', $restrictDescLen, $kernelStrings, $useCookies );
		}

		// Set local view settings
		//
		pd_applyLocalViewSettings( $U_ID, $PF_ID, $folderViewMode, $viewMode, $kernelStrings );
	}

	function pd_applyLocalViewSettings( $U_ID, $PF_ID, $folderViewMode, $viewMode, $kernelStrings )
	//
	// Applies local view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$PF_ID - folder identifier
	//			$folderViewMode - folder view mode (PD_FLDVIEW_GLOBAL, PD_FLDVIEW_LOCAL)
	//			$viewMode - view mode (PD_GRID_VIEW, PD_LIST_VIEW, PD_THUMBLIST_VIEW)
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;
		global $pd_treeClass;

		// Apply settings to the PF_ID folder
		//
		if ( $folderViewMode == PD_FLDVIEW_LOCAL ) {
			if ( !is_null($PF_ID) ) {
				setAppUserCommonValue($PD_APP_ID, $U_ID, "FOLDERVIEW_".$PF_ID, $viewMode, $kernelStrings);
			}
		}

		// Apply settings to all other folders
		//
		if ( $folderViewMode == PD_FLDVIEW_GLOBAL ) {
			setAppUserCommonValue($PD_APP_ID, $U_ID, "FOLDERVIEW", $viewMode, $kernelStrings);
		}

	}

	function pd_getLocalViewSettings( $U_ID, $PF_ID, &$viewMode )
	//
	// Returns folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$PF_ID - folder identifier
	//			$viewMode - view mode (PD_GRID_VIEW, PD_LIST_VIEW, PD_THUMBLIST_VIEW)
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;

		if ( is_null($PF_ID) )
			return null;

		$folderViewMode = getAppUserCommonValue( $PD_APP_ID, $U_ID, 'PD_FOLDERVIEWOPT', PD_FLDVIEW_LOCAL, $useCookies );
		
		if ($folderViewMode == PD_FLDVIEW_LOCAL) {
			$viewMode = getAppUserCommonValue($PD_APP_ID, $U_ID, "FOLDERVIEW_".$PF_ID, PD_GRID_VIEW);
		} else {
			$viewMode = getAppUserCommonValue($PD_APP_ID, $U_ID, "FOLDERVIEW", PD_GRID_VIEW);
		}
		
	}

	function pd_deleteFolderVewSettings( $PF_ID, $kernelStrings )
	//
	// Deletes folder local view settings
	//
	//		Parameters:
	//			$PF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $pd_treeClass;

		// Get folder user list
		//
		$users = $pd_treeClass->listFolderUsers( $PF_ID, $kernelStrings );
		if ( PEAR::isError($users) )
			return $users;

		// Delete view settings for each user
		//
		foreach ( $users as $key=>$value )
			pd_deleteUserFolderViewSettings( $key, $PF_ID, $kernelStrings );

		return null;
	}

	function pd_deleteUserFolderViewSettings( $U_ID, $PF_ID, $kernelStrings )
	//
	// Deletes user folder view settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$PF_ID - folder identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return $kernelStrings[ERR_XML];

		$result = array();

		$appNode = getElementByTagname( $settingsElement, $PD_APP_ID );
		if ( !$appNode )
			return null;

		$xpath = xpath_new_context($dom);

		$foldersViewNode = getElementByTagname( $appNode, 'FOLDERSVIEW' );
		if ( !$foldersViewNode )
			return null;

		$folderElement = &xpath_eval( $xpath, "FOLDER[@ID='$PF_ID']", $foldersViewNode );
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

	function pd_onDeleteUser( $params )
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


	function pd_onRemoveUser( $params )
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

		eval( prepareFileContent($appDirPath."/pd_queries_cmn.php") );

		global $pd_loc_str;
		global $loc_str;

		$pdStrings = $pd_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$vars = get_defined_vars();
				saveVariables( $vars, "qr_dd" );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				return null;
			}
		}
	}

	function pd_onDeleteCurrency( $params )
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

	function pd_disableThumbnails( $kernelStrings, $pdStrings )
	//
	// Disables thumbnail generation in Document Depot
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - document depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;
		global $pd_treeClass;

		// Write database value
		//
		$res = writeApplicationSettingValue( $PD_APP_ID, PD_THUMBNAILSTATE, PD_THUMBDISABLED, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;


		return null;
	}

	function pd_enableThumbnails( $kernelStrings, $pdStrings )
	//
	// Enables thumbnail generation in Document Depot
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - document depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;

		// Write database value
		//
		$res = writeApplicationSettingValue( $PD_APP_ID, PD_THUMBNAILSTATE, PD_THUMBENABLED, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function pd_updateThumbnails( $kernelStrings, $pdStrings, &$messageStack )
	//
	// Updates thumbnails
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - document depot localization strings
	//			$messageStack - stack to put errors and messages
	//
	//		Returns number of files processed or PEAR_Error
	//
	{
		global $pd_treeClass;

		@set_time_limit(0);

		$messageStack = array();
		$totalFilesProcessed = 0;

		// Generate thumbnails
		//
		$access = null;
		$hierarchy = null;
		$deletable = null;
		$folders = $pd_treeClass->listFolders( null, TREE_ROOT_FOLDER, $kernelStrings, 0, true,
												$access, $hierarchy, $deletable,
												null, null, false, null, false );
		if ( PEAR::isError($folders) ) {
			$fatalError = true;
			$errorStr = $folders->getMessage();

			break;
		}

		foreach ( $folders as $key=>$value ) {

			$files = $pd_treeClass->listFolderDocuments( $key, null, "PL_ID asc", $kernelStrings, null, true );
			if ( PEAR::isError($files) )
				return $files;

			$fileList = array();

			$thumbsCreatedInFolder = 0;

			foreach( $files as $data ) {

				if( $data->PL_STATUSINT == TREE_DLSTATUS_NORMAL )
					$attachmentPath = pd_getFolderDir( $data->PF_ID )."/".$data->PL_DISKFILENAME;
				elseif ( $data->PL_STATUSINT == TREE_DLSTATUS_DELETED )
					$attachmentPath = pd_recycledDir()."/".$data->PL_DISKFILENAME;

				$ext = null;
				$thumbPath = findThumbnailFile( $attachmentPath, $ext );

				if ( is_null($thumbPath) ) {
					$originalFileInfo = pathinfo( $data->PL_FILENAME );

					$ext = $data->PL_FILETYPE;
					if ( strlen($ext) ) {
						$thumbPath = makeThumbnail( $attachmentPath, $attachmentPath, $ext, 96, $kernelStrings );
						if ( PEAR::isError($thumbPath) ) {
							$totalFilesProcessed++;
							$thumbsCreatedInFolder++;
							$fileList[$data->PL_FILENAME] = $thumbPath->getMessage();
						}	else
								if ( $thumbPath ) {
									$totalFilesProcessed++;
									$thumbsCreatedInFolder++;
									$fileList[$data->PL_FILENAME] = $kernelStrings['app_thumbcreated_message'];
								}
					}
				}
			}

			if ( $thumbsCreatedInFolder ) {
				$messageStack[$key] = array( 'name'=>$value->PF_NAME, 'offset'=>$value->OFFSET_STR );
				$messageStack[$key]['files'] = $fileList;
			}
		}

		return $totalFilesProcessed;
	}

	function pd_getTotalImagesNum( $kernelStrings )
	//
	// Returns total number of images in the database
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization string
	//
	//		Returns integer or PEAR_Error
	//
	{
		global $qr_pd_imageNum;

		$res = db_query_result( $qr_pd_imageNum, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function pd_enableVersionControl( $kernelStrings, $pdStrings )
	//
	// Enables Document Depot version control
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$pdStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;

		$duplicateFiles = pd_listDuplicateFlies( $kernelStrings );
		if ( PEAR::isError($duplicateFiles) )
			return $duplicateFiles;

		if ( count($duplicateFiles) )
			return PEAR::raiseError( $pdStrings['sv_duplicatefilesfound_message'], ERRCODE_APPLICATION_ERR );

		writeApplicationSettingValue( $PD_APP_ID, PD_VERSIONCONTROLSTATE, PD_VCENABLED, $kernelStrings );
	}

	function pd_disableVersionControl( $kernelStrings )
	//
	// Disables Document Depot version control
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;

		writeApplicationSettingValue( $PD_APP_ID, PD_VERSIONCONTROLSTATE, PD_VCDISABLED, $kernelStrings );
	}

	function pd_listDuplicateFlies( $kernelStrings )
	//
	// Returns information about duplicate files required by the Service screen
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_pd_selectDuplicateFilenames;
		global $qr_pd_selectFileByName;
		global $pd_treeClass;

		$result = array();

		$qr = db_query( $qr_pd_selectDuplicateFilenames, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$files_qr = db_query( $qr_pd_selectFileByName, $row );
			if ( PEAR::isError($files_qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$folderPath = $pd_treeClass->getPathToFolder( $row['PF_ID'], $kernelStrings );
			if ( PEAR::isError($folderPath) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$folderPath = implode( "/", $folderPath );

			if ( !isset($result[$row['PF_ID']]) )
				$result[$row['PF_ID']] = array( 'PATH'=>$folderPath, 'files'=>array() );

			if ( !isset($result[$row['PF_ID']]['files'][$row['PL_FILENAME']]) )
				$result[$row['PF_ID']]['files'][$row['PL_FILENAME']] = array();

			while ( $fileRow = db_fetch_array($files_qr) ) {
				$result[$row['PF_ID']]['files'][$row['PL_FILENAME']][] = $fileRow;
			}

			db_free_result($files_qr);
		}

		db_free_result($qr);

		return $result;
	}


	function pd_saveSortOrder( $U_ID, $PF_ID, $oldSort, $newSort, $pdStrings, $kernelStrings )
	{
		global $qr_pd_selectFilesForSort;
		global $qr_pd_updateSort;

		$sortArray = array();

		foreach( $oldSort as $key=>$value )
			if ( $newSort[$key] != $value )
				$sortArray[intval($newSort[$key])] = intval($value);

		if ( ( $cnt = count( $sortArray ) ) == 0 )
			return null;

		ksort( $sortArray );

		$keys = array_keys( $sortArray );
		$min = $keys[0];
		$max = $keys[$cnt-1];

		$params =array();
		$params['PF_ID'] = $PF_ID;
		$params['MIN'] = $min;
		$params['MAX'] = $max;

		$res = db_query( $qr_pd_selectFilesForSort, $params );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$rowsForSort = array();
		while ( $row = db_fetch_array($res) )
			$rowsForSort[$row['PL_ID']] = $row['PL_SORT'];

		if ( count( $sortArray ) > count( $rowsForSort ) )
			return PEAR::raiseError( "Database was changed by anybody else.".count( $sortArray )."-".count( $rowsForSort ) );

		db_free_result( $res );

		foreach( $sortArray as $key=>$value )
		{
			if ( !isset( $rowsForSort[$key] ) )
				return PEAR::raiseError( "Database was changed by anybody else. $key" );

			$params =array();

			$params['PL_ID'] = intval( $key );
			$params['PL_SORT'] = intval( $rowsForSort[$value] );

			$res = db_query( $qr_pd_updateSort, $params );

			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING].$qr_pd_updateSort.$key." ".$rowsForSort[$value] );

		}

		return null;
	}

	function pd_getDbFileName( $DL_ID, &$kernelStrings )
	//
	// Returns a file name
	//
	//		Parameters:
	//			$PL_ID - file identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pd_selectFileName;

		$result = db_query_result( $qr_pd_selectFileName, DB_FIRST, array('PL_ID' => $PL_ID) );
		if ( PEAR::isError($result) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $result;
	}

	function pd_getSharedTemplates( $U_ID, &$kernelStrings )
	//
	// Returns array with user share albums and default albums
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_pd_selectSharedTemplates;

		$qr = db_query( $qr_pd_selectSharedTemplates, array("U_ID" => $U_ID) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			$result[$row['T_ID']] = $row['T_TITLE'];
		}

		db_free_result($qr);

		return $result;
	}

	function pd_getSharedTemplate( $U_ID, $T_ID, &$kernelStrings )
	//
	// Returns array with user share albums and default albums
	//
	//		Parameters:
	//			$U_ID - user identifier
	//		  $T_ID - template identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_pd_selectSharedTemplate;

		$qr = db_query( $qr_pd_selectSharedTemplate, array("U_ID" => $U_ID, "T_ID" => $T_ID) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$row = db_fetch_array($qr);

		db_free_result($qr);

		return $row;
	}

	function pd_updateFileTitleAndDescription($U_ID, $PL_ID, $data, &$kernelStrings) {
		global $qr_pd_updateFileTitleAndDescr;
		global $PD_APP_ID;

		$fileData = pd_getFilesById(array($PL_ID), $U_ID, "", $kernelStrings, null);
		$fileData = $fileData[$PL_ID];

		$newDiskFileName = pd_prepareDiskFileName($data["title"], $fileData->PL_FILETYPE);

		if( $fileData->PL_STATUSINT == TREE_DLSTATUS_NORMAL ){
			$path = pd_getFolderDir( $fileData->PF_ID )."/";
			$oldFilePath = $path.$fileData->PL_DISKFILENAME;
			$newDiskFilePath = $path;
		}
		elseif ( $fileData->PL_STATUSINT == TREE_DLSTATUS_DELETED ) {
			$path = pd_recycledDir()."/";
			$oldFilePath = $path.$fileData->PL_DISKFILENAME;
			$newDiskFilePath = $path;
		}

		$basename = pd_getFileBaseName(basename($oldFilePath));
		pd_getFileNameAndExtention(basename($oldFilePath), $fileName, $fileExt);

		$destPath = $newDiskFilePath.$newDiskFileName;
		
		if ($oldFilePath != $destPath) {
			if (!file_exists($oldFilePath))
				$oldFilePath = iconv("UTF-8", "WINDOWS-1251", $oldFilePath);
			copy($oldFilePath, $destPath);
		}

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		$InitialTotalSpace = $TotalUsedSpace;
		$InitialUserSpace = $UserUsedSpace;

		// Generate new thumbnails
		$thumbGenerated = pd_createThumbnailsForImage($U_ID, $destPath, $ext, $deleteOriginal, $newOriginalPath, $QuotaManager);
		if ($thumbGenerated) {
			if ($newOriginalPath) {
				$newDiskFileName = basename($newOriginalPath);
			}

			if ($deleteOriginal) {
				@unlink($destPath);
			}
		}

		if ($oldFilePath != $newOriginalPath) {
			pd_removeAllThumbnails($U_ID, dirname($oldFilePath), $basename, $fileExt, $QuotaManager);
		}

		$params = array(
			"PL_FILENAME" => $data["title"],
			"PL_DESC" => $data["description"],
			"PL_MODIFYUSERNAME" => $U_ID,
			"PL_ID" => $PL_ID,
			"PL_DISKFILENAME" => $newDiskFileName,
		);

		$qr = db_query( $qr_pd_updateFileTitleAndDescr, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		db_free_result($qr);

		return TRUE;
	}
	
	
	
	function pd_updateFileDescription($U_ID, $PL_ID, $PL_DESC, &$kernelStrings) {

		global $qr_pd_updateFileDescr;
		$params = array(
			"PL_DESC" => $PL_DESC,
			"PL_MODIFYUSERNAME" => $U_ID,
			"PL_ID" => $PL_ID
		);

		$qr = db_query( $qr_pd_updateFileDescr, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		db_free_result($qr);

		return TRUE;
	}

	
	
	
	

	function pd_prepareDiskFileName($name, $type)
	{
		$pat = array(" ");
		$rep = array("_");

		$ret = str_replace($pat, $rep, $name);
		if (!strpos($name, ".".$type)) {
			$ret .= ".".$type;
		}

		return translit( $ret );
	}

	function pd_checkIsExistTitle($PF_ID, $PL_ID, $title, &$kernelStrings) {
		global $qr_pd_checkIsExistTitle;

		$params = array(
			"PF_ID" => $PF_ID,
			"PL_ID" => $PL_ID,
			"PL_FILENAME" => $title,
		);

		$qr = db_query( $qr_pd_checkIsExistTitle, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$row = db_fetch_array($qr);

		db_free_result($qr);

		if ($row["PL_ID"]) return TRUE;
		return FALSE;
	}

	function pd_getUserContactFolders($currentUser) {
		global $kernelStrings;
		global $pdStrings;
		global $cm_groupClass; // declared in AA/user_rights.php

		$access = null;
		$hierarchy = null;
		$deletable = null;
		$returnData = array();

		$userId = isAdministratorID($currentUser) ? null : $currentUser;

		$folders = $cm_groupClass->listFolders( $userId, TREE_ROOT_FOLDER, $kernelStrings, 0,
												false, $access, $hierarchy,
												$deletable, null, null, false, null, true, null,
												false );


		foreach ($folders as $folderID => $folderData) {
			if ($folderData->CF_ID == TREE_AVAILABLE_FOLDERS) {
				$returnData[TREE_ROOT_FOLDER] = $pdStrings["sm_all_contacts_label"];
			}
			else {
				$returnData[$folderData->CF_ID] = $folderData->CF_NAME;
			}
		}

		return $returnData;
	}

	function pd_getAvailableContactsList($currentUser, $CONTACT_FOLDER) {
		global $language;
		global $kernelStrings;
		global $cm_groupClass;

		$access = null;
		$hierarchy = null;
		$deletable = null;
		$result = array();

		$userId = isAdministratorID($currentUser) ? null : $currentUser;

		$subFolders = $cm_groupClass->listFolders( $userId, $CONTACT_FOLDER, $kernelStrings, 0,
												false, $access, $hierarchy,
												$deletable, null, null, false, null, true, null,
												false );
		foreach ($subFolders as $folderID => $folderData) {
			$cont = pd_getAvailableContactsList($currentUser, $folderData->CF_ID);
			$result = array_merge($result, $cont);
		}

		$typeDescription = true;

		$contactCollection = new contactCollection($typeDescription, $fieldsPlainDesc, $language);

		$totalObjects = $contactCollection->getFolderContactNum( $CONTACT_FOLDER, $kernelStrings );
		$sortStr = "C.C_FIRSTNAME, C_LASTNAME ASC";
		$recordsPerPage = $totalObjects;
		$currentPage = 1;
		$callbackParams = array();

		getQueryLimitValues( $totalObjects, $recordsPerPage, $showPageSelector, $currentPage, $pages, $pageCount, $startIndex, $count );
		$contactCollection->loadFromContactFolder( $CONTACT_FOLDER, $sortStr, $startIndex, $count, $callbackParams, null, $kernelStrings );

		foreach ($contactCollection->items as $key => $contact) {
			if ($contact->C_EMAILADDRESS) {
				$contactName = $contact->C_FIRSTNAME." ".$contact->C_LASTNAME;
				$result[$contact->C_ID] = sprintf( "%s <%s>", $contactName, $contact->C_EMAILADDRESS );

				/*
				if (strlen($result[$contact->C_ID]) > 45) {
					$result[$contact->C_ID] = sprintf( "<%s>", substr_replace($contact->C_EMAILADDRESS, "...", 40) );
				}
				*/
			}
		}

		return $result;
	}

	function pd_getUserEmail( $currentUser, $kernelStrings ) {
		global $qr_selectUser;
		$userdata["U_ID"] = $currentUser;
		$res = exec_sql( $qr_selectUser, $userdata, $userdata, true );
		if ( PEAR::isError( $res ) ) {
			return;
		}

		return $userdata;
	}

	function pd_arrayKeyExists($key, $arr) {
		if (!is_array($arr)) return FALSE;

		if (array_key_exists($key, $arr)) {
			return TRUE;
		}
		else {
			foreach ($arr as $k => $v) {
				if (is_array($v)) {
					if (pd_arrayKeyExists($key, $v)) {
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}

	function pd_arrayValueExists($value, $arr) {
		if (!is_array($arr)) return FALSE;

		if (in_array($value, $arr)) {
			return TRUE;
		}
		else {
			foreach ($arr as $k => $v) {
				if (is_array($v)) {
					if (pd_arrayValueExists($value, $v)) {
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}

	function pd_parsePDError($res) {
		global $pdStrings;
		global $currentUser;
		global $kernelStrings;

		$userApplications = listUserScreens($currentUser);
		$aiAccess = pd_arrayValueExists("AI", $userApplications);
		$messageStack = array();
		$errorUserInfo = $res->getUserInfo();
		$errorCode = $res->getCode();
		$upgradeUrl = getUpgradeLink( $kernelStrings, true );
		$helpUrl = "#hosted-accounts.htm";

		if ($errorCode == PD_FILE_UPLOAD_EXC_LIMIT) {
			$messageStack[] = sprintf($pdStrings['pd_error_account_exc_limit'], $errorUserInfo[1]).
				($aiAccess ? ' '.sprintf($pdStrings['pd_error_account_exc_limit_help'], $upgradeUrl, $helpUrl) : '');

			if ($errorUserInfo[2]) {
				if ($errorUserInfo[0]) {
					$messageStack[] = sprintf($pdStrings['pd_error_account_exc_limit_archive_info'], $errorUserInfo[2]);
				}
				$messageStack[] = $pdStrings['ua_filesextracted_label'].": ".$errorUserInfo[0];
			}
			elseif($errorUserInfo[0]) {
				$messageStack[] = sprintf($pdStrings['add_screen_upload_image_count'], $errorUserInfo[0]);
			}
		}
		elseif ($errorCode == PD_FILE_UPLOAD_EXC_DB_SIZE_LIMIT) {
			$messageStack[] = sprintf($pdStrings['pd_error_account_exc_db_size_limit'], $errorUserInfo[1]).
				($aiAccess ? ' '.sprintf($pdStrings['pd_error_account_exc_limit_help'], $upgradeUrl, $helpUrl) : '');

			if ($errorUserInfo[2]) {
				if ($errorUserInfo[0]) {
					$messageStack[] = sprintf($pdStrings['pd_error_account_exc_limit_archive_info'], $errorUserInfo[2]);
				}
				$messageStack[] = $pdStrings['ua_filesextracted_label'].": ".$errorUserInfo[0];
			}
			elseif($errorUserInfo[0]) {
				$messageStack[] = sprintf($pdStrings['add_screen_upload_image_count'], $errorUserInfo[0]);
			}
		}
		elseif ($errorCode == PD_CANT_COPY_INTO_SAME_ALBUM) {
			$messageStack[] = $res->getMessage();
		}
		else {
			$messageStack[] = $res->getMessage();
		}
		return $messageStack;
	}
?>