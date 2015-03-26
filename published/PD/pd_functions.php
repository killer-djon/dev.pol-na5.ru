<?php
		
	//
	// Photo Depot non-DMBS application functions
	//

	function pd_processFileListEntry( $entry )
	//
	// Callback function to prepare some PD file attributes to show in list
	//
	//		Parameters:
	//			$entry - source entry
	//
	//		Returns processed entry
	//
	{
		$entry->PL_FILESIZE = formatFileSizeStr($entry->PL_FILESIZE);
		$entry->PL_UPLOADDATETIME = convertToDisplayDate($entry->PL_UPLOADDATETIME, true);
		$entry->PL_MODIFYDATETIME = convertToDisplayDate($entry->PL_MODIFYDATETIME, true);
		$entry->PL_DELETE_DATETIME = convertToDisplayDate($entry->PL_DELETE_DATETIME, true);
		$entry->PL_DISPLAYNAME = pd_getFullFileName( $entry );

		$noCache = base64_encode( uniqid( "file" ) );
		$entry->ROW_URL = prepareURLStr( PAGE_PD_GETFILE, array( 'PL_ID'=>base64_encode($entry->PL_ID), 'nocache'=>$noCache ) );

		return $entry;
	}

	function pd_getFullFileName( $entry )
	//
	// Returns file name with extension
	//
	//		Parameters:
	//			$entry - file entry as object
	//
	//		Returns string
	//
	{
		$result = $entry->PL_FILENAME;
		if ($entry->PL_FILETYPE)
			$result .= ".".$entry->PL_FILETYPE;

		return $result;
	}

	function pd_getFolderDir( $PF_ID )
	//
	// Returns path to a folder directory
	//
	//		Parameters:
	//			$PF_ID - folder identifier
	//
	//		Returns strings
	//
	{
        //TODO: картинки работают на ID без точки!
		//$PF_ID = substr( $PF_ID, 0, strlen($PF_ID)-1 );

		return PD_FILES_DIR. "/" .$PF_ID;
	}

	function pd_recycledDir()
	//
	// Returns path to a folder recycled directory
	//
	//		Returns string
	//
	{
		return PD_FILES_DIR."/recycled";
	}

	function pd_makeFolderDirs( $PF_ID, $pdStrings )
	//
	// Creates folder directories
	//
	//		Parameters:
	//			$PF_ID - folder identifier
	//			$pdStrings - Photo Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( !@forceDirPath( pd_getFolderDir($PF_ID), $errStr ) )
			return PEAR::raiseError( $pdStrings['addfld_screen_errorfld_message'] );

		return null;
	}

	function pd_makeRecycledDir( $pdStrings )
	//
	// Creates directory for storing recycled files
	//
	//		Parameters:
	//			$pdStrings - Photo Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( !@forceDirPath( pd_recycledDir(), $errStr ) )
			return PEAR::raiseError( $pdStrings['addfld_screen_errorfld_message'] );

		return null;
	}

	function pd_generateUniqueDiskFilename( $fileName, $PF_ID, $recycled = false, $allowNameMatch = false )
	//
	// Generates unique disk file name. If file with given
	//		name already exists, function attaches suffix
	//		with instance number
	//
	//		Parameters:
	//			$fileName - original file name
	//			$PF_ID - folder identifier
	//			$recycled - use "deleted" folder as target
	//			$allowNameMatch - do not try to find unique file name, allowing overwriting existing files
	//
	//		Returns string
	//
	{
		$diskFileName = translit( $fileName );
		pd_getFileNameAndExtention($diskFileName, $newFileName, $newFileExt);
		$diskFileName = $newFileName."_".pd_genUniqueStr(5).".".$newFileExt;

        /*
		$targetPath = ( !$recycled ) ? pd_getFolderDir( $PF_ID ) : pd_recycledDir();

		$destPath = sprintf( "%s/%s", $targetPath, $diskFileName );

		$destPath1 = sprintf( "%s/%s", $targetPath, $diskFileName.".".PD_DEFAULT_THUMB_SIZE.".jpg" );
		$destPath2 = sprintf( "%s/%s", $targetPath, $diskFileName.".".PD_ULTRA_SMALL_THUMB_SIZE.".jpg" );
		$destPath3 = sprintf( "%s/%s", $targetPath, $diskFileName.".".PD_SMALL_THUMB_SIZE.".jpg" );
		$destPath4 = sprintf( "%s/%s", $targetPath, $diskFileName.".".PD_MEDIUM_THUMB_SIZE.".jpg" );
		$destPath5 = sprintf( "%s/%s", $targetPath, $diskFileName.".".PD_LARGE_THUMB_SIZE.".jpg" );

		if ( !(file_exists($destPath) ||
		    file_exists($destPath1) ||
		    file_exists($destPath2) ||
		    file_exists($destPath3) ||
		    file_exists($destPath4) ||
		    file_exists($destPath5)) ||
		    $allowNameMatch
		) {
	        return $diskFileName;
		}

		$instance = 1;
		$fileName = $diskFileName;
		$fileExt = explode(".", $diskFileName);
		$fileExt = $fileExt[count($fileExt) - 1];
		do {
			$diskFileName = $fileName."_copy".$instance.".".$fileExt;
			$destPath = sprintf( "%s/%s", $targetPath, $diskFileName );

			$instance++;
		} while ( file_exists($destPath) );
        */
		return $diskFileName;
	}

	function pd_getFileType( $fileName )
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

	function pd_getFileName( $fileName )
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

	function pd_sendNewFolderMessage( $PF_ID, $params )
	//
	// Callback function sends user notifications on folder create
	//
	//		Parameters:
	//			$PF_ID - new folder identifier
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		extract($params);

		global $pd_treeClass;

		$folderInfo = $pd_treeClass->getFolderInfo( $PF_ID, $kernelStrings );

		$objectList = array( $folderInfo['PF_ID_PARENT']=>$folderInfo );

		pd_sendNotifications( $objectList, $U_ID, PD_ADDFOLDER, $kernelStrings );
	}

	function pd_onCopyMoveFile( $PL_ID, $kernelStrings, $srcPF_ID, $destPF_ID, $operation, $docData, $params )
	//
	//	Copies or moves file on disk
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$PL_ID - file identifier
	//			$srcPF_ID - source folder identifier
	//			$destPF_ID - destination folder identifier
	//			$operation - operation: TREE_COPYDOC, TREE_MOVEDOC
	//			$docData - file data, record from DOCLIST table as array
	//			$params - other parameters array
	//
	//		Returns array containing disk file name or PEAR_Error
	//
	{
	    global $PD_APP_ID;
	    global $currentUser;
	    global $pdStrings;

	    $U_ID = $currentUser;

	    if ($srcPF_ID == $destPF_ID) {
	        return PEAR::raiseError(
    		        $pdStrings['pd_error_cant_copy_into_same_album'],
    		        PD_CANT_COPY_INTO_SAME_ALBUM
    		    );
	    }

	    if ($operation == TREE_COPYDOC) {
    	    $limitPhotosCount = getApplicationResourceLimits($PD_APP_ID);

    	    //$limitPhotosCount = 1;

    	    $sql = "SELECT COUNT(*) FROM PIXLIST";
    	    $curCountPhotos = db_query_result( $sql, DB_FIRST, array() );

            if ($limitPhotosCount) {
    			if ($curCountPhotos >= $limitPhotosCount) {
    			    return PEAR::raiseError(
    			        $kernelStrings['app_dbsizelimit_message'],
    			        PD_FILE_UPLOAD_EXC_LIMIT,
    			        null,
    			        null,
    			        array(0, $limitPhotosCount, false)
    			    );
    			}
    	    }
	    }

		extract($params);

		$res = pd_makeFolderDirs( $destPF_ID, $pdStrings );
		if ( PEAR::isError($res) )
			return $res;

		/*
		$versionControlEnabled = pd_versionControlEnabled($kernelStrings);

		if ( $versionControlEnabled ) {
			// Delete existing file in the destination folder
			//
			$fileInfo = pd_getFileByName( $docData['PL_FILENAME'], $destPF_ID, $kernelStrings );
			if ( PEAR::isError($fileInfo) )
				return $fileInfo;

			if ( !is_null($fileInfo) ) {
				if ( $fileInfo['PL_CHECKSTATUS'] == PD_CHECK_OUT )
					if ( $fileInfo['PL_CHECKUSERID'] != $U_ID ) {
						$userName = pd_getUserName( $fileInfo['PL_CHECKUSERID'] );

						return PEAR::raiseError( sprintf($pdStrings['cm_replace_error'], $docData['PL_FILENAME'], $userName), ERRCODE_APPLICATION_ERR );
					}

				pd_deleteRestoreDocuments( array($fileInfo['PL_ID']), PD_DELETEDOC, $U_ID, $kernelStrings, $pdStrings );
			}
		}
		*/

		$QuotaManager = new DiskQuotaManager();

		$UserUsedSpace = $QuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
		if ( PEAR::isError($UserUsedSpace) )
			return $UserUsedSpace;

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		$InitialTotalSpace = $TotalUsedSpace;
		$InitialUserSpace = $UserUsedSpace;

		$sourceFolder = pd_getFolderDir( $srcPF_ID );
		$sourcePath = sprintf( "%s/%s", $sourceFolder, $docData['PL_DISKFILENAME'] );

		$basename = pd_getFileBaseName($sourcePath);

		pd_getFileNameAndExtention($docData['PL_DISKFILENAME'], $fileName, $fileExt);

		$destFolder = pd_getFolderDir($destPF_ID);
		$diskFileName = pd_generateUniqueDiskFilename( $basename.".".$fileExt, $destPF_ID );
		$destPath = sprintf( "%s/%s", $destFolder, $diskFileName );

		if ( file_exists($sourcePath) ) {

			if ( $operation == TREE_COPYDOC ) {
			    $UserUsedSpace = $InitialUserSpace + $QuotaManager->GetSpaceUsageAdded();
			    $TotalUsedSpace = $InitialTotalSpace + $QuotaManager->GetSpaceUsageAdded();

			    $fileSize = filesize( $sourcePath );

			    if ( $QuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileSize) ) {
				    //$QuotaManager->Flush( $kernelStrings );
				    return $QuotaManager->ThrowNoSpaceError( $kernelStrings );
			    }

    			if ( $QuotaManager->UserApplicationQuotaExceeded( $UserUsedSpace + $fileSize, $U_ID, $PD_APP_ID, $kernelStrings ) ) {
    				//$QuotaManager->Flush( $kernelStrings );
    				$limitSpace = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );
    				return PEAR::raiseError(
    			        $kernelStrings['app_dbsizelimit_message'],
    			        PD_FILE_UPLOAD_EXC_DB_SIZE_LIMIT,
    			        null,
    			        null,
    			        array(0, $limitSpace, false, 0)
    			    );
    			}

			    /*
				if ( DATABASE_SIZE_LIMIT != 0 ) {
					$spaceUsed = getSystemSpaceUsed() + getDatabaseSize();

					$fileSize = filesize( $sourcePath );

					$spaceUsed += $fileSize;

					if ( spaceLimitExceeded( $spaceUsed/MEGABYTE_SIZE ) )
						return PEAR::raiseError( $kernelStrings['app_dbsizelimit_message'], ERRCODE_APPLICATION_ERR );
				}
				*/

				$res = pd_documentAddingPermitted( $kernelStrings );
				if ( PEAR::isError($res) )
					return $res;
			}

			if ( !@copy( $sourcePath, $destPath ) )
				return PEAR::raiseError( $pdStrings['app_copyerr_message'] );

            // Generate new thumbnails
            $thumbGenerated = pd_createThumbnailsForImage($U_ID, $destPath, $ext, $deleteOriginal, $newOriginalPath, $QuotaManager);

		    if ($thumbGenerated) {
		        if ($newOriginalPath) {
		            $diskFileName = basename($newOriginalPath);
		        }

		        if ($deleteOriginal) {
		            @unlink($destPath);
		        }
		    }

		    if ($operation == TREE_MOVEDOC) {
    			// Remove original file
    			//
			    pd_removeAllThumbnails($U_ID, $sourceFolder, $basename, $fileExt, $QuotaManager);
		    }

		    $QuotaManager->Flush( $kernelStrings );

            /*
			// Copy thumbnail file as well
			//
			$ext = null;
			$srcThumbFile = findThumbnailFile( $sourcePath, $ext );
			if ( $srcThumbFile ) {
				$destThumbFile = $destPath.".$ext";

				if ( !@copy( $srcThumbFile, $destThumbFile ) )
					return PEAR::raiseError( $pdStrings['app_copyerr_message'] );
			}

			if ( $operation == TREE_MOVEDOC ) {
				if ( !@unlink($sourcePath) )
					return PEAR::raiseError( $pdStrings['app_delerr_message'] );


				if ( file_exists($srcThumbFile) )
					if ( !@unlink($srcThumbFile) )
						return PEAR::raiseError( $pdStrings['app_delerr_message'] );
			}*/
		}

		return array( 'diskFileName' => $diskFileName );
	}

	function pd_onFinishCopyMoveFiles( $kernelStrings, $U_ID, $destFileList, $srcFileList, $operation, $callbackParams )
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
			pd_sendNotifications( $destFileList, $U_ID, PD_ADDDOC, $kernelStrings );

			if ( $operation == TREE_MOVEDOC )
				pd_sendNotifications( $srcFileList, $U_ID, PD_DELETEDOC, $kernelStrings );
		}
	}

	function pd_directorySize( $path )
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

	function pd_archiveSupported()
	//
	// Checks if PHP has access to the ZLIB functions
	//
	//		Returns boolean
	//
	{
		return function_exists('gzopen');
	}

	function pd_thumbnailsSupported()
	//
	// Checks if PHP has access to the GD functions
	//
	//		Returns boolean
	//
	{
		return function_exists('gd_info');
	}

	function pd_postExtractCallBack($p_event, &$p_header)
	//
	// Post extract callback function
	//
	{
		global $pd_extractTmpName__;

		$p_header['filename'] = $pd_extractTmpName__;

		return 1;
	}

	function pd_preAddCallBack($p_event, &$p_header)
	//
	// Pre extract callback function
	//
	{
		global $pd_addTmpName__;

		$p_header['stored_filename'] = $pd_addTmpName__;

		return 1;
	}

	function pd_checkFileName( $fileName, &$pdStrings )
	//
	// Checks if file name is valid
	//
	//		Parameters:
	//			$fileName - fila nem
	//			$pdStrings - Photo Depot localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		if ( ereg( "\\/|\\\|\\?|:|<|>|\\*", $fileName ) || !(strpos($fileName, "\\") === FALSE) )
			return PEAR::raiseError( $pdStrings['mf_screen_invchars_message'],
										ERRCODE_INVALIDFIELD );
	}

	function pd_versionControlEnabled( &$kernelStrings )
	//
	// Returns true if version control is enabled
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $PD_APP_ID;

		$versionControlStatus = readApplicationSettingValue( $PD_APP_ID, PD_VERSIONCONTROLSTATE, PD_VCDISABLED, $kernelStrings );
		if ( PEAR::isError($versionControlStatus) )
			return $versionControlStatus;

		return $versionControlStatus == PD_VCENABLED;
	}

	function pd_getEmailSettingsParams( &$mode, &$name, &$address, &$kernelStrings )
	//
	// Returns the Photo Depot email settings
	//
	//		Parameters:
	//			$mode - settings mode (PD_EMAILPARAMS_GLOBAL, PD_EMAILPARAMS_USER)
	//			$name - sender name for the PD_EMAILPARAMS_GLOBAL mode
	//			$address - sender email for the PD_EMAILPARAMS_GLOBAL mode
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_selectCompanyInfo;
		global $PD_APP_ID;
		global $databaseInfo;

		$params = readApplicationSettingValue( $PD_APP_ID, PD_EMAILPARAMS, null, $kernelStrings );
		if ( PEAR::isError($params) )
			return $params;

		if ( !strlen($params) ) {
			$mode = PD_EMAILPARAMS_GLOBAL;

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

	function pd_setEmailSettingsParams( $settings, &$kernelStrings )
	//
	// Sets the Photo Depot email settings
	//
	//		Parameters:
	//			$settings - email settings as array
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $PD_APP_ID;

		if ( $settings['mode'] == PD_EMAILPARAMS_GLOBAL ) {
			$validator = new pd_emailSettingsValidator();

			$res = $validator->loadFromArray( $settings, $kernelStrings, true, array( s_datasource=>s_form ) );
			if ( PEAR::isError($res) )
				return $res;
		}

		$params = base64_encode( serialize($settings) );

		$res = writeApplicationSettingValue( $PD_APP_ID, PD_EMAILPARAMS, $params, $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function pd_checkImageFile($filepath) {
	    // Checks image files
	    global $pd_knownImageFormats;
	    $info = @getimagesize($filepath);
	    if (!$info) return FALSE;
	    switch ($info[2]) {
	        case 1: $ext = "gif"; break;
	        case 2: $ext = "jpg"; break;
	        case 3: $ext = "png"; break;
	        case 5: $ext = "psd"; break;
	        case 6: $ext = "bmp"; break;
	        case 7: $ext = "tiff"; break;
	        case 8: $ext = "tiff"; break;
	        case 9: $ext = "jpc"; break;
	        case 10: $ext = "jp2"; break;
	        default: $ext = "";
	    }

	    return in_array($ext, $pd_knownImageFormats);
 	}

 	function pd_makeThumbnail( $filePath, $resultPath, $ext = null, $size = 96, $kernelStrings, $dumpToTheScreen = false )
	//
	// Creates a thumbnail frou source image file
	//
	//		Parameters:
	//			$filePath - source file path
	//			$resultPath - path to save thumbnail
	//			$ext - file extension
	//			$size - thumbnail size
	//			$kernelStrings - Kernel localization strings
	//			$dumpToTheScreen - output destination image to the output stream
	//
	//		Returns path to thumbnail or NULL, or PEAR_Error
	//
	{
		//
		// Check if file has supported format
		//
		if ( is_null($ext) ) {
			$fileInfo = pathinfo( $filePath );

			if ( isset($fileInfo["extension"]) )
				$ext = $fileInfo["extension"];
		}
		
		$ext = strtolower( $ext );

		// Override JPEG extension
		//
		if ( $ext == 'jpeg' )
			$ext = 'jpg';

		// Return null if file has unknown format
		//
		//if ( !in_array($ext, array('jpg', 'gif', 'png')) )
		//	return null;

		// Return null if GD is unavailable
		//
		if ( !function_exists('gd_info') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		if ( !function_exists('imagecreatetruecolor') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		if ( !function_exists('imagecopyresized') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		if ( !function_exists('getimagesize') )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		$gdInfo = @gd_info();
		if ( !$gdInfo )
			return PEAR::raiseError( $kernelStrings['app_gdunavailable_message'], ERRCODE_APPLICATION_ERR );

		// Make sure what image resolution does not exceeds fixed limit
		//
		$sizes = @getimagesize( $filePath );

		//$thumbPossible = estimateThumnGeneratingPossibility( $ext, $sizes[0], $sizes[1] );

		//if ( !$thumbPossible )
			//return PEAR::raiseError( $kernelStrings['app_gdresexceeds_message'], ERRCODE_APPLICATION_ERR );
		// Check if GIF or JPEG creation is available
		//
		if ( !$gdInfo['GIF Create Support'] && !$gdInfo['JPG Support'] )
			return PEAR::raiseError( $kernelStrings['app_gdoutimgsupport_message'], ERRCODE_APPLICATION_ERR );

		// Create source image resource
		//
		$srcIm = false;

	    $srcIm = pd_readImage($filePath, $info);

	    if ( !$srcIm )
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

		//
		// Calculate thumbnail size
		//
		$srcWidth = $width = @imagesx($srcIm);
		$srcHeight = $height = @imagesy($srcIm);

		if ( !$width ) {
			@imagedestroy( $srcIm );
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		if ( !$height ) {
			@imagedestroy( $srcIm );
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		// Crop Image
		if ($size == PD_DEFAULT_THUMB_SIZE) {
	        $smallerDimension = ($width > $height) ? $height : $width;

	        $destImgSquare = @imagecreatetruecolor( $smallerDimension, $smallerDimension );

	        $srcX = round(($width - $smallerDimension) / 2);
	        if ($srcX < 0) $srcX = 0;

	        $srcY = round(($height - $smallerDimension) / 2);
	        if ($srcY < 0) $srcY = 0;

	        $res = @imagecopy ( $destImgSquare, $srcIm, 0, 0, $srcX, $srcY, $smallerDimension, $smallerDimension );

	        if ($destImgSquare) {
	            $srcIm = $destImgSquare;
	            $width = $height = $srcWidth = $srcHeight = $smallerDimension;
	        }
	        else {
	            @imagedestroy( $srcIm );
			    return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
	        }
	    }

		// Shrink image
		//

	    if ( $width > $height ) {
			if ( $width > $size ) {
				$ratio = $width/$height;

				$height = $size/$ratio;
				$width = $size;
			}
		} else {
			if ( $height > $size ) {
				$ratio = $width/$height;

				$width = $size*$ratio;
				$height = $size;
			}
		}

		// Create image copy
		//
		$destImg = @imagecreatetruecolor( $width, $height );

		if ( !$destImg ) {
			@imagedestroy( $srcIm );
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		if ( function_exists('imagecopyresampled') )
			$res = @imagecopyresampled ( $destImg, $srcIm, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );
		else
			$res = @imagecopyresized ( $destImg, $srcIm, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );

		if ( !$res ) {
			@imagedestroy( $srcIm );
			@imagedestroy( $destImg );

			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );
		}

		// Output image
		//
		/*if ( $gdInfo['GIF Create Support'] ) {
			if ( !$dumpToTheScreen ) {
				$resultPath = $resultPath.".$size.gif";
				$res = @imagegif( $destImg, $resultPath );
			} else {
				$res = @imagegif( $destImg );
			}
		} else {
			if ( !$dumpToTheScreen ) {
				$resultPath = $resultPath.".$size.jpg";
				$res = @imagejpeg( $destImg, $resultPath );
			} else {
				$res = @imagejpeg( $destImg );
			}
		}*/

		if ( !$dumpToTheScreen ) {
		    $path = dirname($resultPath);
		    $fileName = basename($resultPath);
		    pd_getFileNameAndExtention($fileName, $newFileName, $newFileExt);
			$resultPath = $path."/".$newFileName.".$size.".PD_DEFAULT_OUTPUT_IMAGES_FORMAT;
			$res = @imagejpeg( $destImg, $resultPath );
		} else {
			$res = @imagejpeg( $destImg );
		}

		@imagedestroy( $destImg );
		@imagedestroy( $srcIm );

		if ( $res )
			return $resultPath;
		else
			return PEAR::raiseError( $kernelStrings['app_gdcommonerr_message'], ERRCODE_APPLICATION_ERR );

		return null;
	}

	function pd_readImage($fileName, &$info) {
	    $srcIm = FALSE;
	    $info = @getimagesize($fileName);
	    if (!$info) return FALSE;
	    switch ($info[2]) {
	        case 1:
	            // Create recource from gif image
				$srcIm = @imagecreatefromgif( $fileName );
	            break;
	        case 2:
	            // Create recource from jpg image
				$srcIm = @imagecreatefromjpeg( $fileName );
	            break;
	        case 3:
	            // Create resource from png image
				$srcIm = @imagecreatefrompng( $fileName );
	            break;
	        case 5:
	            // Create resource from psd image
	            break;
	        case 6:
	            // Create recource from bmp image imagecreatefromwbmp
				$srcIm = @imagecreatefromwbmp( $fileName );
	            break;
	        case 7:
	            // Create resource from tiff image
	            break;
	        case 8:
	            // Create resource from tiff image
	            break;
	        case 9:
	            // Create resource from jpc image
	            break;
	        case 10:
	            // Create resource from jp2 image
	            break;
	        default:
	            break;
	    }

	    if (!$srcIm) return FALSE;
	    else return $srcIm;
	}

	function pd_writeImage($destImg, $info, $resultPath="") {
	    // If $resultPath = "" then image will be output into browser

	    switch ($info[2]) {
	        case 1:
	            // Create recource from gif image
	            if (!$resultPath) {
			    	@imagegif( $destImg );
			    } else {
			        @imagegif($destImg, $resultPath);
			    }
	            break;
	        case 2:
	            // Create recource from jpg image
	            if (!$resultPath) {
			    	@imagejpeg( $destImg );
			    } else {
			        @imagejpeg($destImg, $resultPath);
			    }
	            break;
	        case 3:
	            // Create resource from png image
	            if (!$resultPath) {
			    	@imagepng( $destImg );
			    } else {
			        @imagepng($destImg, $resultPath);
			    }
	            break;
	        case 5:
	            // Create resource from psd image
	            break;
	        case 6:
	            // Create recource from bmp image imagecreatefromwbmp
	            if (!$resultPath) {
			    	@imagewbmp( $destImg );
			    } else {
			        @imagewbmp($destImg, $resultPath);
			    }
	            break;
	        case 7:
	            // Create resource from tiff image
	            break;
	        case 8:
	            // Create resource from tiff image
	            break;
	        case 9:
	            // Create resource from jpc image
	            break;
	        case 10:
	            // Create resource from jp2 image
	            break;
	        default:
	            break;
	    }
	}

	function pd_readAndResizeImage($fileName, $newSize, $resultPath="") {
	    $srcIm = pd_readImage($fileName, $info);

	    if (!$srcIm) return FALSE;
		//
		// Calculate file size
		//
		$srcWidth = $width = @imagesx($srcIm);
		$srcHeight = $height = @imagesy($srcIm);

		if ( !$width || !$height) {
			@imagedestroy( $srcIm );
			return FALSE;
		}
		// Shrink image
		//
		if ($newSize) {
    		if ( $width > $height ) {
    			if ( $width > $newSize ) {
    				$ratio = $width/$height;

    				$height = $newSize/$ratio;
    				$width = $newSize;
    			}
    		} else {
    			if ( $height > $newSize ) {
    				$ratio = $width/$height;

    				$width = $newSize*$ratio;
    				$height = $newSize;
    			}
    		}
	    }

		// Create image copy
		//
		$destImg = @imagecreatetruecolor( $width, $height );
		if ( !$destImg ) {
			@imagedestroy( $srcIm );

			return FALSE;
		}

		if ( function_exists('imagecopyresampled') )
			$res = @imagecopyresampled ( $destImg, $srcIm, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );
		else
			$res = @imagecopyresized ( $destImg, $srcIm, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight );
		if ( !$res ) {
			@imagedestroy( $srcIm );
			@imagedestroy( $destImg );

			return FALSE;
		}

		// Output image
		//
		pd_writeImage($destImg, $info, $resultPath);
	    return TRUE;
	}

	function pd_rotateImage($fileName, $angle, $resultPath="") {
	    $srcIm = pd_readImage($fileName, $info);

	    if (!$srcIm) {
	        return FALSE;
	    }

	    $destIm = @imagerotate($srcIm, $angle, 0);
	    if (!$destIm) {
	        return FALSE;
	    }

	    pd_writeImage($destIm, $info, $resultPath);
	    return TRUE;
	}

	function pd_getThumbFile($filePath, $os, $srcExt = null) {
	    $thumbPath = findThumbnailFile( $filePath, $srcExt );

		if ( !is_null($thumbPath) ) {
		    return $thumbPath;
		} else {
		    return null;
		}
	}

	function pd_markSearchKeywords($keywords, $string, $markColor) {
	    $pat = array();
	    $rep = array();

	    $keywordsArray = explode(" ", $keywords);

	    foreach($keywordsArray as $key) {
	        if ($key) {
	            $pat[] = "/(".$key.")/ui";
	            $rep[] = '<span style="background-color: '.$markColor.';">\\1</span>';
	        }
	    }

	    $return = preg_replace($pat, $rep, $string);

	    return $return;
	}

	function pd_registerNewShareLink($data) {
	    $code = pd_createShareCode($data);

	    $key = pd_genUniqueStr(13);

	    $qr = db_query( "INSERT INTO `PDSHARELINKS` (`KEY`, `CODE`, `CREATE_TIME`) VALUES ('$key', '$code', '".time()."')" , array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

	    return $key;
	}

	function pd_createShareCode($data) {
	    global $language;
	    $linkData = array(
            'PF_ID' => $data["PF_ID"],
            //'template' => base64_encode($_POST["template"]),
            'template' => base64_encode($data["template"]),
            'DB_KEY' => base64_encode($data["wbs_dbkey"]),
            'currentUser' => $data["user"],
            'rand' => rand(0, 999999),
            'lang' => $language,
        );

        $queryStr = base64_encode(serialize($linkData));

        return $queryStr;
	}

	function pd_createShareLink($data) {
	    $queryStr = pd_createShareCode($data);

        $linkFullUrl = PD_HTTP_PATH.PAGE_PD_VIEW_SHARED_ALBUM."?uid=".$queryStr;

        return $linkFullUrl;
	}

	function pd_createThumbnailsForImage($U_ID, $destPath, $ext, &$deleteOriginal, &$newOriginalPath, &$QuotaManager, $destPathNew = null) {
	    global $kernelStrings;
	    global $PD_APP_ID;

	    $deleteOriginal = FALSE;
	    $newOriginalPath = '';

	    $srcIm = pd_readImage($destPath, $info);

	    if (!$info) {
	        return FALSE;
	    }

	    // $info contains information about file
	    $imageWidth = $info[0];
	    $imageHeight = $info[1];
	    $bigerDimension = ($imageWidth > $imageHeight) ? $imageWidth : $imageHeight;
	    
	    $destPathNew = ($destPathNew) ? $destPathNew : $destPath;
	    
		//zzzz//
		file_put_contents( "out.txt", "alert111-1" );
	    
		//$thumb2 = pd_makeThumbnail( $destPath, $destPath, $ext, PD_ULTRA_SMALL_THUMB_SIZE, $kernelStrings );
		$thumb5 = pd_makeThumbnail( $destPath, $destPathNew, $ext, PD_LARGE_THUMB_SIZE, $kernelStrings );
		$thumb4 = pd_makeThumbnail( $thumb5, $destPathNew, $ext, PD_MEDIUM_THUMB_SIZE, $kernelStrings );
		$thumb3 = pd_makeThumbnail( $thumb4, $destPathNew, $ext, PD_SMALL_THUMB_SIZE, $kernelStrings );

	    $thumb1 = pd_makeThumbnail( $thumb3, $destPathNew, $ext, PD_DEFAULT_THUMB_SIZE, $kernelStrings );
	    
		if (
		    !PEAR::isError($thumb1) &&
		    //!PEAR::isError($thumb2) &&
		    !PEAR::isError($thumb3) &&
		    !PEAR::isError($thumb4) &&
		    !PEAR::isError($thumb5)
		) {
		    if ($U_ID) {

		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, filesize($thumb1) );
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, filesize($thumb3) );
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, filesize($thumb4) );
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, filesize($thumb5) );
		    }

		    if ($bigerDimension > PD_MEDIUM_THUMB_SIZE) {
		        $newOriginalPath = $thumb5;
		    }
		    elseif ($bigerDimension > PD_SMALL_THUMB_SIZE) {
		        $newOriginalPath = $thumb4;
		    }
		    elseif ($bigerDimension > PD_DEFAULT_THUMB_SIZE) {
		        $newOriginalPath = $thumb3;
		    }
		    else {
		        $newOriginalPath = $thumb1;
		    }

		    $deleteOriginal = TRUE;
		    return TRUE;
		}

	    return FALSE;
	}

	function pd_genUniqueStr($n){
	    $str = '';
    	$sym = array_merge(
    			range('A', 'F'),
    			range('a', 'f'),
    			range('0', '9')
    		);
    	$sum_count = count($sym)-1;
    	for ($i = 0; $i < $n; $i ++) $str .= $sym[rand(0, $sum_count)];
    	return $str;
    }

    function pd_dumpFileThumbnail( $filePath, $os, $srcExt = null )
	//
	// Prints thumbnail file content
	//
	//		Parameters:
	//			$filePath - path to the original file
	//			$os - thumbnail operation system style
	//			$srcExt - file extension
	//
	//		Returns file content
	//
	{
	    $sz = (array_key_exists("SIZE", $_REQUEST) ? $_REQUEST["SIZE"] : 0);

	    if ($sz) {
	        $size = $sz;
	    }
	    else {
	        $size = PD_DEFAULT_THUMB_SIZE;
	    }
if($size == 1024) 
  $size = 970;
		//$thumbPath = pd_findThumbnailFile( $filePath, $ext, $size );
		
		$thumbPath = preg_replace('~\.1024\.~', '.'.$size.'.', $filePath);
        $thumbPath = preg_replace('~\.1024~', '', $thumbPath);
		
//		var_dump($thumbPath);
		
		if ( !is_null($thumbPath) ) {
			// calc an offset of 24 hours
		  $offset = 3600 * 24;	
			// calc the string in GMT not localtime and add the offset
			  $expire = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
			//output the HTTP header

			  
			$basename = pd_getFileBaseName($filePath);
			
			header('Content-Disposition: inline; filename="' . $basename . '"');
          header($expire);
			header('Pragma: public');
			header("Cache-Control: private");
			header("Cache-Control: max-age=3600");
            ob_end_clean();
			if ( $ext == 'gif' )
				header( 'Content-type: image/gif' );
			else			    
				header( 'Content-type: image/jpeg' );
			readfile( $thumbPath );
		} else {
            ob_end_clean();
		    $fileNameData = pathinfo( $filePath );

			$baseDir = "../../../common/html/thumbnails";
			$filePath = "$baseDir/$srcExt.$os.32.gif";

			if ( !file_exists($filePath) )
				$filePath = "$baseDir/common.$os.32.gif";

			header( 'Content-type: image/gif' );
			readfile( $filePath );
		}
	}

	function pd_findThumbnailFile( $filePath, &$ext, $size )
	//
	// Returns path to the thumbnail file, if it exists, or null
	//
	//		Parameters:
	//			$filePath - path to the original document
	//			$ext - thumbnail extension
	//
	//		Returns null or string
	{
    $path = dirname($filePath);
    $ext = PD_DEFAULT_OUTPUT_IMAGES_FORMAT;
    $basename = pd_getFileBaseName($filePath);
    $jpgFilePath = $path."/".$basename.".".$size.".$ext";
    

    if (@file_exists($jpgFilePath)) {
			return $jpgFilePath;
		}
		if (@file_exists(iconv("UTF-8", "WINDOWS-1251", $jpgFilePath))) {
			return iconv("UTF-8", "WINDOWS-1251", $jpgFilePath);
		}
		
		$jpgFilePath = $path."/".$basename.".".PD_DEFAULT_THUMB_SIZE.".$ext";
		
		if (@file_exists($jpgFilePath)) {
			return $jpgFilePath;
		}
		
		if (@file_exists(iconv("UTF-8", "WINDOWS-1251", $jpgFilePath))) {
			return iconv("UTF-8", "WINDOWS-1251", $jpgFilePath);
		}

		return null;
	}

	function pd_getFileNameAndExtention($sourceFileName, &$fileName, &$fileExt) {
	    $dt = explode(".", $sourceFileName);
	    if ($dt[0] && $dt[sizeof($dt)-1]) {
    		$fileExt = array_pop($dt);
    		$fileName = implode(".", $dt);
	    }
	}

	function pd_getFileBaseName($filePath) {
	    $path = dirname($filePath);
	    $fname = basename($filePath);
	    //if (defined("TEST")) {
	    	$fname = preg_replace("/(.*)\/([^\/]*)/", "$2", $filePath);
	    //}
	    $ext = PD_DEFAULT_OUTPUT_IMAGES_FORMAT;

	    $basename='';

	    $pat = "/^(.*?)\.[0-9]*\.".$ext."$/ui";
	    if (preg_match($pat, $fname, $match)) {
            $basename = $match[1];

            if (!$basename) {
                $pat = "/^(.*?)\.".$ext."$/ui";
                if (preg_match($pat, $fname, $match)) {
                    $basename = $match[1];
                }
            }
        }

	    return $basename;
	}

	function pd_removeAllThumbnails($U_ID, $fileDir, $baseName, $baseExt, &$QuotaManager) {
	    global $pdStrings;
	    global $PD_APP_ID;
	    // Remove if exist base file
		$filePath = sprintf( "%s/%s", $fileDir, $baseName.".".$baseExt );

		if ( file_exists($filePath) ) {
		    if ($U_ID) {
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, -1*filesize($filePath) );
		    }
			if ( !@unlink($filePath) ) return PEAR::raiseError( $pdStrings['app_delerr_message'] );
		}

		// Remove all thumbnails
		$filePath = sprintf( "%s/%s", $fileDir, $baseName.".".PD_DEFAULT_THUMB_SIZE.".".$baseExt );
		if ( file_exists($filePath) ) {
		    if ($U_ID) {
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, -1*filesize($filePath) );
		    }
			if ( !@unlink($filePath) ) return PEAR::raiseError( $pdStrings['app_delerr_message'] );
		}

		/*
		$filePath = sprintf( "%s/%s", $fileDir, $baseName.".".PD_ULTRA_SMALL_THUMB_SIZE.".".$baseExt );
		if ( file_exists($filePath) ) {
			if ( !@unlink($filePath) ) return PEAR::raiseError( $pdStrings['app_delerr_message'] );
		}
		*/

		$filePath = sprintf( "%s/%s", $fileDir, $baseName.".".PD_SMALL_THUMB_SIZE.".".$baseExt );
		if ( file_exists($filePath) ) {
		    if ($U_ID) {
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, -1*filesize($filePath) );
		    }
			if ( !@unlink($filePath) ) return PEAR::raiseError( $pdStrings['app_delerr_message'] );
		}

		$filePath = sprintf( "%s/%s", $fileDir, $baseName.".".PD_MEDIUM_THUMB_SIZE.".".$baseExt );
		if ( file_exists($filePath) ) {
		    if ($U_ID) {
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, -1*filesize($filePath) );
		    }
			if ( !@unlink($filePath) ) return PEAR::raiseError( $pdStrings['app_delerr_message'] );
		}

		$filePath = sprintf( "%s/%s", $fileDir, $baseName.".".PD_LARGE_THUMB_SIZE.".".$baseExt );
		if ( file_exists($filePath) ) {
		    if ($U_ID) {
		        $QuotaManager->AddDiskUsageRecord( $U_ID, $PD_APP_ID, -1*filesize($filePath) );
		    }
			if ( !@unlink($filePath) ) return PEAR::raiseError( $pdStrings['app_delerr_message'] );
		}
	}
?>