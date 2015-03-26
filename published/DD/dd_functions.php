<?php

	//
	// Document Depot non-DMBS application functions
	//

	function dd_processFileListEntry( $entry )
	//
	// Callback function to prepare some DD file attributes to show in list
	//
	//		Parameters:
	//			$entry - source entry
	//
	//		Returns processed entry
	//
	{
		$entry->DL_FILESIZE_BYTES = $entry->DL_FILESIZE;
		$entry->DL_FILESIZE = formatFileSizeStr($entry->DL_FILESIZE);
		$entry->DL_UPLOADDATETIME = convertToDisplayDateTime($entry->DL_UPLOADDATETIME, false,true,true);
		$entry->DL_MODIFYDATETIME = convertToDisplayDate($entry->DL_MODIFYDATETIME, true);
		$entry->DL_DELETE_DATETIME = convertToDisplayDate($entry->DL_DELETE_DATETIME, true);
		$entry->DL_DISPLAYNAME = dd_getFullFileName( $entry );

		$noCache = base64_encode( uniqid( "file" ) );
		$entry->ROW_URL = prepareURLStr( PAGE_DD_GETFILE, array( 'DL_ID'=>base64_encode($entry->DL_ID), 'nocache'=>$noCache ) );

		return $entry;
	}

	function dd_getFullFileName( $entry )
	//
	// Returns file name with extension
	//
	//		Parameters:
	//			$entry - file entry as object
	//
	//		Returns string
	//
	{
		$result = $entry->DL_FILENAME;
		if ($entry->DL_FILETYPE)
			$result .= ".".$entry->DL_FILETYPE;

		return $result;
	}

	function dd_getFolderDir( $DF_ID )
	//
	// Returns path to a folder directory
	//
	//		Parameters:
	//			$DF_ID - folder identifier
	//
	//		Returns strings
	//
	{
		$DF_ID = substr( $DF_ID, 0, strlen($DF_ID)-1 );

		return DD_FILES_DIR."/".$DF_ID;
	}

	function dd_recycledDir()
	//
	// Returns path to a folder recycled directory
	//
	//		Returns string
	//
	{
		return DD_FILES_DIR."/recycled";
	}

	function dd_makeFolderDirs( $DF_ID, $ddStrings )
	//
	// Creates folder directories
	//
	//		Parameters:
	//			$DF_ID - folder identifier
	//			$ddStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( !@forceDirPath( dd_getFolderDir($DF_ID), $errStr ) )
			return PEAR::raiseError( $ddStrings['addfld_screen_errorfld_message'] );

		return null;
	}

	function dd_makeRecycledDir( $ddStrings )
	//
	// Creates directory for storing recycled files
	//
	//		Parameters:
	//			$ddStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( !@forceDirPath( dd_recycledDir(), $errStr ) )
			return PEAR::raiseError( $ddStrings['addfld_screen_errorfld_message'] );

		return null;
	}

	function dd_generateUniqueDiskFilename( $fileName, $DF_ID, $recycled = false, $allowNameMatch = false )
	//
	// Generates unique disk file name. If file with given
	//		name already exists, function attaches suffix
	//		with instance number
	//
	//		Parameters:
	//			$fileName - original file name
	//			$DF_ID - folder identifier
	//			$recycled - use "deleted" folder as target
	//			$allowNameMatch - do not try to find unique file name, allowing overwriting existing files
	//
	//		Returns string
	//
	{
		$diskFileName = translit( $fileName );

		$targetPath = ( !$recycled ) ? dd_getFolderDir( $DF_ID ) : dd_recycledDir();

		$destPath = sprintf( "%s/%s", $targetPath, $diskFileName );
		if ( !file_exists($destPath) || $allowNameMatch )
			return $diskFileName;

		$instance = 1;
		$fileName = $diskFileName;
		do {
			$diskFileName = $fileName."_copy".$instance;
			$destPath = sprintf( "%s/%s", $targetPath, $diskFileName );

			$instance++;
		} while ( file_exists($destPath) );

		return $diskFileName;
	}

	function dd_generateUniqueDbFileName( $fileName, $DF_ID, &$kernelStrings )
	//
	// Generates unique database file name
	//
	//		Parameters:
	//			$fileName - original file name
	//			$DF_ID - folder identifier
	//			$allowNameMatch - do not try to find unique file name, allowing overwriting existing files
	//
	//		Returns string
	//
	{
		$fileBaseName = $fileName;
		$fileExt = dd_getFileType( $fileName );
		if ( strlen($fileExt) )
			$fileBaseName = substr($fileName, 0, strlen($fileName)-strlen($fileExt)-1 );

		$curFileName = $fileName;
		$instance = 1;
		while ( !is_null( dd_getFileByName($curFileName, $DF_ID, $kernelStrings) ) ) {
			$curFileName = $fileBaseName."_copy".$instance;
			if ( strlen($fileExt) )
				$curFileName .= ".".$fileExt;

			$instance++;
		}

		return $curFileName;
	}

	function dd_getFileType( $fileName )
	//
	// Returns file extension
	//
	//		Parameters:
	//			$fileName - file name
	//
	//		Returns strings
	//
	{
		$path_parts = pathinfo( $fileName );

		if ( array_key_exists("extension", $path_parts) )
			return $path_parts["extension"];
		else
			return null;
	}

	function dd_getFileName( $fileName )
	//
	// Returns file name without extension
	//
	//		Parameters:
	//			$fileName - file name
	//
	//		Returns strings
	//
	{
		$path_parts = pathinfo( $fileName );

		if ( array_key_exists("extension", $path_parts) )
			return basename( $fileName, ".".$path_parts["extension"] );
		else
			return $fileName;
	}

	function dd_sendNewFolderMessage( $DF_ID, $params )
	//
	// Callback function sends user notifications on folder create
	//
	//		Parameters:
	//			$DF_ID - new folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		extract($params);

		global $dd_treeClass;

		$folderInfo = $dd_treeClass->getFolderInfo( $DF_ID, $kernelStrings );

		$objectList = array( $folderInfo['DF_ID_PARENT']=>$folderInfo );

		dd_sendNotifications( $objectList, $U_ID, DD_ADDFOLDER, $kernelStrings );
	}

	function dd_onCopyMoveFile( $DL_ID, $kernelStrings, $srcDF_ID, $destDF_ID, $operation, &$docData, $params )
	//
	//	Copies or moves file on disk
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$DL_ID - file identifier
	//			$srcDF_ID - source folder identifier
	//			$destDF_ID - destination folder identifier
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$docData - file data, record from DOCLIST table as array
	//			$params - other parameters array
	//
	//		Returns array containing disk file name or PEAR_Error
	//
	{
		global $DD_APP_ID;
		global $_ddQuotaManager;
		global $qr_dd_updateFileName;
		extract($params);

		$res = dd_makeFolderDirs( $destDF_ID, $ddStrings );
		if ( PEAR::isError($res) )
			return $res;

		$sourcePath = sprintf( "%s/%s", dd_getFolderDir( $srcDF_ID ), $docData['DL_DISKFILENAME'] );
		$diskFileName = dd_generateUniqueDiskFilename( $docData['DL_FILENAME'], $destDF_ID );
		$destPath = sprintf( "%s/%s", dd_getFolderDir($destDF_ID), $diskFileName );

		$fileExists = file_exists($sourcePath);

		if ( $fileExists ) {
			$fileSize = filesize( $sourcePath );

			if ( $operation == TREE_COPYDOC ) {
				$UserUsedSpace += $_ddQuotaManager->GetSpaceUsageAdded();
				$TotalUsedSpace += $_ddQuotaManager->GetSpaceUsageAdded();

				// Check if the user disk space quota is not exceeded
				//
				if ( $_ddQuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) )
					return $_ddQuotaManager->ThrowNoSpaceError( $kernelStrings );

				// Check if the system disk space quota is not exceeded
				//
				if ( $_ddQuotaManager->UserApplicationQuotaExceeded( $UserUsedSpace + $fileSize, $U_ID, $DD_APP_ID, $kernelStrings ) )
					return PEAR::raiseError( $kernelStrings['app_usersizelimit_message'], ERRCODE_APPLICATION_ERR );

				$res = dd_documentAddingPermitted( $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) )
					return $res;
			}
		}

		$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
		if ( $versionControlEnabled ) {
			// Create file copy in the destination folder, if file with this name already exists
			//
			$newFileName = dd_generateUniqueDbFileName( $docData['DL_FILENAME'], $destDF_ID, $kernelStrings );
			if ( strlen($newFileName) != strlen($docData['DL_FILENAME']) ) {
				$docData['DL_FILENAME'] = $newFileName;

				if ( $operation != TREE_COPYDOC ) {
					$params = array();
					$params['DL_ID'] = $DL_ID;
					$params['DL_FILENAME'] = $newFileName;
					db_query( $qr_dd_updateFileName, $params );
				}
			}

/*			// Delete existing file in the destination folder
			//
			$fileInfo = dd_getFileByName( $docData['DL_FILENAME'], $destDF_ID, $kernelStrings );
			if ( PEAR::isError($fileInfo) )
				return $fileInfo;

			if ( !is_null($fileInfo) ) {
				if ( $fileInfo['DL_CHECKSTATUS'] == DD_CHECK_OUT )
					if ( $fileInfo['DL_CHECKUSERID'] != $U_ID ) {
						$userName = dd_getUserName( $fileInfo['DL_CHECKUSERID'] );
						return PEAR::raiseError( sprintf($ddStrings['cm_replace_error'], $docData['DL_FILENAME'], $userName), ERRCODE_APPLICATION_ERR );
					}

				dd_deleteRestoreDocuments( array($fileInfo['DL_ID']), DD_DELETEDOC, $U_ID, $kernelStrings, $ddStrings );
			} */
		}

		if ( $fileExists ) {
			if ( !@copy( $sourcePath, $destPath ) )
				return PEAR::raiseError( $ddStrings['app_copyerr_message'] );

			if ( $operation != TREE_MOVEDOC ) {
				if ( is_object($_ddQuotaManager) )
					$_ddQuotaManager->AddDiskUsageRecord( $U_ID, $DD_APP_ID, $fileSize );
			}

			// Copy thumbnail file as well
			//
			$ext = null;
			$srcThumbFile = findThumbnailFile( $sourcePath, $ext );
			if ( $srcThumbFile ) {
				$destThumbFile = $destPath.".$ext";

				if ( !@copy( $srcThumbFile, $destThumbFile ) )
					return PEAR::raiseError( $ddStrings['app_copyerr_message'] );

				if ( $operation != TREE_MOVEDOC )
					if ( is_object($_ddQuotaManager) )
						$_ddQuotaManager->AddDiskUsageRecord( $U_ID, $DD_APP_ID, filesize($srcThumbFile) );
			}

			if ( $operation == TREE_MOVEDOC ) {
				if ( !@unlink($sourcePath) )
					return PEAR::raiseError( $ddStrings['app_delerr_message'] );

				if ( file_exists($srcThumbFile) )
					if ( !@unlink($srcThumbFile) ) {
						return PEAR::raiseError( $ddStrings['app_delerr_message'] );
					}
			}
		}

		return array( 'diskFileName'=>$diskFileName );
	}

	function dd_onFinishCopyMoveFiles( $kernelStrings, $U_ID, $destFileList, $srcFileList, $operation, $callbackParams )
	//
	//  Callback function, executes after files copied or moved
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - user identifier
	//			$destFileList - list of files being updated
	//			$srcFileList - list of files moved (deleted)
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$callbackParams - other parameters array
	//
	//		Returns null
	//
	{
		extract( $callbackParams );

		if ( !$suppressNotifications ) {
			dd_sendNotifications( $destFileList, $U_ID, DD_ADDDOC, $kernelStrings );

			if ( $operation == TREE_MOVEDOC )
				dd_sendNotifications( $srcFileList, $U_ID, DD_DELETEDOC, $kernelStrings );
		}
	}

	function dd_directorySize( $path )
	//
	// Returns directory size in bytes
	//
	//		Parameters:
	//			$path - path to the directory
	//
	//		Returns integer
	//
	{
		$result = 0;

		if ( !file_exists($path) )
			return $result;

		$handle = @opendir($path);

		if ( !$handle )
			return $result;

		while ( $file = @readdir ($handle) ) {
			$filePath = $path."/".$file;
			if ( is_dir($filePath) )
				continue;

			$result = filesize($filePath);
		}

		@closedir($handle);

		return $result;
	}

	function dd_archiveSupported()
	//
	// Checks if PHP has access to the ZLIB functions
	//
	//		Returns boolean
	//
	{
		return function_exists('gzopen');
	}

	function dd_thumbnailsSupported()
	//
	// Checks if PHP has access to the GD functions
	//
	//		Returns boolean
	//
	{
		return function_exists('gd_info');
	}

	function dd_postExtractCallBack($p_event, &$p_header)
	//
	// Post extract callback function
	//
	{
		global $dd_extractTmpName__;

		$p_header['filename'] = $dd_extractTmpName__;

		return 1;
	}

	function dd_preAddCallBack($p_event, &$p_header)
	//
	// Pre extract callback function
	//
	{
		global $dd_addTmpName__;

		$p_header['stored_filename'] = $dd_addTmpName__;

		return 1;
	}

	function dd_checkFileName( $fileName, &$ddStrings )
	//
	// Checks if file name is valid
	//
	//		Parameters:
	//			$fileName - fila nem
	//			$ddStrings - Document Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( ereg( "\\/|\\\|\\?|:|<|>|\\*", $fileName ) || !(strpos($fileName, "\\") === FALSE) )
			return PEAR::raiseError( $ddStrings['mf_screen_invchars_message'],
										ERRCODE_INVALIDFIELD );
	}

	function dd_versionControlEnabled( &$kernelStrings )
	//
	// Returns true if version control is enabled
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		return true;
		global $DD_APP_ID;

		$versionControlStatus = readApplicationSettingValue( $DD_APP_ID, DD_VERSIONCONTROLSTATE, DD_VCDISABLED, $kernelStrings );
		if ( PEAR::isError($versionControlStatus) )
			return $versionControlStatus;

		return $versionControlStatus == DD_VCENABLED;
	}
	
	function dd_zohoeditEnabled( &$kernelStrings )
	//
	// Returns true if zoho edit is enabled
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $DD_APP_ID;

		$state = readApplicationSettingValue( $DD_APP_ID, DD_ZOHOEDITSTATE, DD_ZOHOENABLED, $kernelStrings );
		if ( PEAR::isError($state) )
			return $state;

		return $state == DD_ZOHOENABLED;
	}
	
	function dd_getzohoKey( &$kernelStrings )
	//
	// Returns zoho secret key
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $DD_APP_ID;

		$result = readApplicationSettingValue( $DD_APP_ID, DD_ZOHOSECRETKEY, '', $kernelStrings );
		if ( PEAR::isError($result) )
			return $result;

		return $result;
	}

	function dd_getVersionOverrideParams( &$enabled, &$versionNum, &$kernelStrings )
	//
	// Returns the Document Depot version override feature parameters
	//
	//		Parameters:
	//			$enabled - indicates that version override is enabled
	//			$versionNum - the number of versions to keep
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		$enabled = readApplicationSettingValue( $DD_APP_ID, DD_VERSIONOVERRIDEENABLED, 0, $kernelStrings );
		if ( PEAR::isError($enabled) )
			return $enabled;

		$enabled = $enabled == 1;

		if ( !$enabled )
			return;

		$versionNum = readApplicationSettingValue( $DD_APP_ID, DD_MAXVERSIONNUM, 0, $kernelStrings );
		if ( PEAR::isError($enabled) )
			return $enabled;
	}

	function dd_setVersionOverrideParams( $enabled, $versionNum, &$kernelStrings )
	//
	// Sets the Document Depot version override feature parameters
	//
	//		Parameters:
	//			$enabled - indicates that version override is enabled
	//			$versionNum - the number of versions to keep
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		$validator = new dd_vcSettingsValidator();

		$settings = array('maxVersionNum'=>$versionNum);
		$res = $validator->loadFromArray( $settings, $kernelStrings, true, array( s_datasource=>s_form ) );
		if ( PEAR::isError($res) )
			return $res;

		$enabled = $enabled ? 1 : 0;

		$res = writeApplicationSettingValue( $DD_APP_ID, DD_VERSIONOVERRIDEENABLED, $enabled, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		$res = writeApplicationSettingValue( $DD_APP_ID, DD_MAXVERSIONNUM, $versionNum, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;
	}

	function dd_getEmailSettingsParams( &$mode, &$name, &$address, &$kernelStrings )
	//
	// Returns the Document Depot email settings
	//
	//		Parameters:
	//			$mode - settings mode (DD_EMAILPARAMS_GLOBAL, DD_EMAILPARAMS_USER)
	//			$name - sender name for the DD_EMAILPARAMS_GLOBAL mode
	//			$address - sender email for the DD_EMAILPARAMS_GLOBAL mode
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_selectCompanyInfo;
		global $DD_APP_ID;
		global $databaseInfo;

		$params = readApplicationSettingValue( $DD_APP_ID, DD_EMAILPARAMS, null, $kernelStrings );
		if ( PEAR::isError($params) )
			return $params;

		if ( !strlen($params) ) {
			$mode = DD_EMAILPARAMS_GLOBAL;

			$companyData = db_query_result( $qr_selectCompanyInfo, DB_ARRAY );
			if ( PEAR::isError($companyData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$name = $companyData['COM_NAME'];
			$address = $databaseInfo[HOST_FIRSTLOGIN][HOST_EMAIL];
		} else {
			$params = unserialize( base64_decode($params) );

			$mode = $params['mode'];
			$name = $params['name'];
			$address = $params['email'];
		}
	}

	function dd_setEmailSettingsParams( $settings, &$kernelStrings )
	//
	// Sets the Document Depot email settings
	//
	//		Parameters:
	//			$settings - email settings as array
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $DD_APP_ID;

		if ( $settings['mode'] == DD_EMAILPARAMS_GLOBAL ) {
			$validator = new dd_emailSettingsValidator();

			$res = $validator->loadFromArray( $settings, $kernelStrings, true, array( s_datasource=>s_form ) );
			if ( PEAR::isError($res) )
				return $res;
		}

		$params = base64_encode( serialize($settings) );

		$res = writeApplicationSettingValue( $DD_APP_ID, DD_EMAILPARAMS, $params, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}
	

?>