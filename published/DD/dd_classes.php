<?php

 
	//
	// WebAsyst Document Depot common classes
	//
	$for_pclzip = WBS_DIR."kernel/includes/modules/pclzip.lib.php";
	require_once realpath($for_pclzip);

	class dd_fileDescription
	{
		var $DL_FILENAME;
		var $DL_FILESIZE;
		var $DL_DESC;
		var $DL_MIMETYPE;
		var $DL_VERSIONCOMMENT;

		var $sourcePath;
	}

	class dd_documentFolderTree extends genericDocumentFolderTree
	{
		/** true if this server is host */
		var $is_host;
		
		function dd_documentFolderTree( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;
			$this->is_host =  onWebAsystServer(); //FIXME
			//$this->is_host =  false; 
			$this->globalPrefix = "DD";
		}

		function addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback = null, $callbackParams = null, $propagateFolderRights = true, $suppressNotifications = false, $folderStatus = null )
		{
			$ID = parent::addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback, $callbackParams, $propagateFolderRights, $suppressNotifications, $folderStatus );
			if ( PEAR::isError($ID) )
				return $ID;
			
			if ( $action == ACTION_EDIT ) {
				// Update folder status fields
				//
				global $qr_dd_updateFodlerUpdateStatusFields;

				$userName = getUserName( $U_ID, true );
				$sqlParams = array( 'DF_MODIFYUSERNAME'=>$userName, 'DF_ID'=>$folderdata['DF_ID'] );

				db_query( $qr_dd_updateFodlerUpdateStatusFields, $sqlParams );
			} else {
				
				// Set folder special status
				//
				global $qr_dd_updateFolderSpecialstatusFields;
				
				$specialStatus = 0;
				if (isset($folderdata["DF_SPECIALSTATUS"]) && !$folderdata["DF_ID"]) {
					$specialStatus = $folderdata["DF_SPECIALSTATUS"];
				}
				else {
					if ($ID_PARENT != TREE_ROOT_FOLDER) {
						$parentData = $this->getFolderInfo ($ID_PARENT, $kernelStrings);
						if ( PEAR::isError($parentData) )
							return $parentData;
					}
					if ($parentData["DF_SPECIALSTATUS"] == FOLDER_SPECIALSTATUS_PM_PROJECT || $parentData["DF_SPECIALSTATUS"] == FOLDER_SPECIALSTATUS_DD_SUBFOLDER)
						$specialStatus = FOLDER_SPECIALSTATUS_DD_SUBFOLDER;
				}
				
				$sqlParams = array( 'DF_SPECIALSTATUS'=>$specialStatus, 'DF_ID'=>$ID );
				db_query( $qr_dd_updateFolderSpecialstatusFields, $sqlParams );
				
				if ($specialStatus) {
					global $UR_Manager;
					
					$res = $UR_Manager->CopyRightsLink ("/ROOT/DD/FOLDERS", $ID_PARENT, "/ROOT/DD/FOLDERS", $ID);
					if (PEAR::isError($res))
						return $res;
				}
			}

			return $ID;
		}

		function PropagateRightsRecursive( $U_ID, $DF_ID, &$kernelStrings, &$ddStrings )
		{
			$minimalRights = array(TREE_ONLYREAD, TREE_WRITEREAD, TREE_READWRITEFOLDER);

			$thisUserRights = $this->getIdentityFolderRights( $U_ID, $DF_ID, $kernelStrings );
			if ( PEAR::isError($thisUserRights) )
				return $thisUserRights;

			if ( !UR_RightsObject::CheckMask( $thisUserRights, TREE_READWRITEFOLDER ) )
				return PEAR::raiseError( $ddStrings['paa_norights_message'], ERRCODE_APPLICATION_ERR );

			$access = null;
			$hierarchy = null;
			$deletable = null;
			$folders = $this->listFolders( $U_ID, $DF_ID, $kernelStrings, 0, false,
											$access, $hierarchy, $deletable, $minimalRights );
			if ( PEAR::isError($folders) )
				return $folders;

			foreach ( $folders as $fDF_ID=>$folderData ) {
				if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) ) {
					$res = $this->propagateFolderRights( $DF_ID, $fDF_ID, $kernelStrings );
					if ( PEAR::isError($res) )
						return $res;
				}
			}

			return null;
		}

		function recycleFolder( $ID, $U_ID, $kernelStrings, $ddStrings, $admin, $deleteCallback = null, $callbackParams = null, $suppressNotifications = false )
		{
			// Move folder to the root
			//
			$callbackParams = array( 'ddStrings'=>$ddStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$ID, 'U_ID'=>$U_ID );

			$res = $this->deleteFolder( $ID, $U_ID, $kernelStrings, $admin, $deleteCallback, $callbackParams, $suppressNotifications, true );
			if ( PEAR::isError($res) )
				return $res;

			$callbackParams['suppressNotifications'] = true;

			$ID = $this->moveFolder( $ID, TREE_ROOT_FOLDER, $U_ID, $kernelStrings,
								"dd_onAfterCopyMoveFile", "dd_onCopyMoveFile", "dd_onCreateFolder", "dd_onDeleteFolder",
								$callbackParams, "dd_onFinishMoveFolder", false, true, ACCESSINHERITANCE_COPY, null, TREE_FSTATUS_DELETED, true );

			if ( PEAR::isError($ID) )
				return $ID;
		}
		
		/*function deleteFolder ($ID, $U_ID, $kernelStrings, $admin, $deleteCallback = null, $callbackParams = null, $suppressNotifications = false, $changeStatusOnly = false) {
			return parent::deleteFolder( $ID, $U_ID, $kernelStrings, $admin, $deleteCallback , $callbackParams ,
								$suppressNotifications , $changeStatusOnly);
		}*/

		function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false )
		{
			global $_ddQuotaManager;
			global $DD_APP_ID;

			$_ddQuotaManager = new DiskQuotaManager();

			$UserUsedSpace = $_ddQuotaManager->GetUserApplicationUsedSpace( $U_ID, $DD_APP_ID, $kernelStrings );
			if ( PEAR::isError($UserUsedSpace) )
				return $UserUsedSpace;

			$TotalUsedSpace = $_ddQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['UserUsedSpace'] = $UserUsedSpace;
			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation, $callbackParams, $perFileCheck, $checkUserRights, $onFinishOperation, $suppressNotifications );

			$_ddQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,
							$onFolderDelete = null, $callbackParams = null, $onFinishMove = null, $checkUserRights = true,
							$topLevel = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null,
							$folderStatus = TREE_FSTATUS_NORMAL, $plainMove = false, $checkFolderName = true )
		{
			global $_ddQuotaManager;
			global $DD_APP_ID;

			$_ddQuotaManager = new DiskQuotaManager();

			$UserUsedSpace = $_ddQuotaManager->GetUserApplicationUsedSpace( $U_ID, $DD_APP_ID, $kernelStrings );
			if ( PEAR::isError($UserUsedSpace) )
				return $UserUsedSpace;

			$TotalUsedSpace = $_ddQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['UserUsedSpace'] = $UserUsedSpace;
			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate,
							$onFolderDelete, $callbackParams, $onFinishMove, $checkUserRights,
							$topLevel, $accessInheritance, $mostTopRightsSource,
							$folderStatus, $plainMove, $checkFolderName );

			$_ddQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true )
		{
			global $_ddQuotaManager;
			global $DD_APP_ID;

			$_ddQuotaManager = new DiskQuotaManager();

			$UserUsedSpace = $_ddQuotaManager->GetUserApplicationUsedSpace( $U_ID, $DD_APP_ID, $kernelStrings );
			if ( PEAR::isError($UserUsedSpace) )
				return $UserUsedSpace;

			$TotalUsedSpace = $_ddQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['UserUsedSpace'] = $UserUsedSpace;
			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $onFininshCopy, $accessInheritance, $onBeforeFolderCreate, $checkFolderName, $copyChilds );

			$_ddQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function _pathExists( &$array, $path, $autoAdd = false )
		//
		// Internal function
		//
		{
			$pathParts = explode( "/", $path );

			foreach ( $pathParts as $part ) {
				if ( !strlen($part) )
					continue;

				if ( isset($array['content'][$part]) )
					$array = &$array['content'][$part];
				else
					if ( $autoAdd ) {
						if ( !isset($array['content']) )
							$array['content'] = array();

						$array['content'][$part] = array( "ID"=>null, "path"=>$path );
					}
			}

			return true;
		}

		function _getFolderID( &$array, $path )
		//
		// Internal function
		//
		{
			if ( isset($array['content']) && is_array($array['content']) )
				foreach ( $array['content'] as $folderData ) {
					if ( $folderData['path'] == $path )
						return $folderData['ID'];

					$res = $this->_getFolderID( $folderData, $path );
					if ( !is_null($res) )
						return $res;
				}

			return null;
		}

		function _makePath( &$array, $path )
		//
		// Internal function
		//
		{
			$pathParts = explode( "/", $path );

			$arrayPath = &$array;

			$curPath = null;
			foreach ( $pathParts as $part ) {
				if ( !strlen($part) )
					continue;

				if ( is_null($curPath) )
					$curPath .= $part;
				else
					$curPath .= "/".$part;

				$this->_pathExists( $array, $curPath, true );
			}

			return $array;
		}

		function _createArchiveFolder( $U_ID, $parentID, &$folders, &$kernelStrings, &$ddStrings, $folderUsersArr, $folderGroupsArr, &$counter, $topLevel = true )
		//
		// Internal function
		//
		{
			if ( $topLevel ) {
				$folderUsers = $this->listFolderUsersRights( $parentID, $kernelStrings );
				$folderGroups = $this->listFolderGroupsRights( $parentID, $kernelStrings );

				$folderUsersArr = array();

				if (is_array($folderUsers))
					foreach ( $folderUsers as $key=>$data )
						if ( $data['RIGHTS'] != TREE_NOACCESS )
							$folderUsersArr[$key] = $data['RIGHTS'];

				$folderGroupsArr = array();
				if (is_array($folderGroups))
					foreach ( $folderGroups as $key=>$data )
						if ( $data['RIGHTS'] != TREE_NOACCESS )
							$folderGroupsArr[$key] = $data['RIGHTS'];
			}

			if (!is_array($folders)) return null;

			foreach ( $folders as $folderName=>$folderContent ) {

				// Create folder with name $folderName
				//
				$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings );

				$newID = null;

				$folderData = array('DF_NAME'=>$folderName);
				$newID = $folderID = $this->addmodFolder( ACTION_NEW, $U_ID, $parentID, $folderData,
													$kernelStrings, false, 'dd_onCreateFolder', $callbackParams, true, true );
				if ( PEAR::isError($newID) )
					return $newID;

				$counter++;

				$folders[$folderName]['ID'] = $newID;

				// Set folder rights
				//
				$res = $this->setFolderRights( $newID, $folderUsersArr, $folderGroupsArr, $kernelStrings, true );
				if ( PEAR::isError( $res ) )
					return $res;

				// Create folder contents
				//
				$content = &$folders[$folderName]["content"];
				$res = $this->_createArchiveFolder( $U_ID, $newID, $content, $kernelStrings, $ddStrings, $folderUsersArr, $folderGroupsArr, $counter, false );
				if ( PEAR::isError($res) )
					return $res;
			}

			return null;
		}

		function _convertFileName( $fileName )
		{
			global $html_encoding;

			if ( strtoupper($html_encoding) == 'WINDOWS-1251' )
				$fileName = convert_cyr_string( $fileName, 'd', 'w' );

			return $fileName;
		}

		function uploadArchive( $U_ID, $ID, $originalName, $filePath, $extractSubdirs, $createThumbs, $kernelStrings, $ddStrings, &$resultStatistics, $existingFilesOperation = DD_REPLACE_FILES  )
		//
		// Extraxts arhive into Document Depot folder
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$ID - folder identifier
		//			$originalName - original file name
		//			$filePath - path to the archive
		//			$extractSubdirs - extract subdirectories flag
		//			$createThumbs - create thumbnail images
		//			$kernelStrings - Kernel localization strings
		//			$ddStrings - Document Depot localization strings
		//			$resultStatistics - result statistics array
		//			$existingFilesOperation - operation to perform on existing files
		//
		//		Returns null or PEAR_Error
		//
		{
			@set_time_limit( 3600 );

			global $dd_extractTmpName__;

			$resultStatistics['filesAdded'] = 0;
			$resultStatistics['imagesAdded'] = 0;
			$resultStatistics['imageErrors'] = 0;
			$resultStatistics['foldersCreated'] = 0;

			$archivePath = $filePath;

			$messageStack = array();

			// Check if archives are supported
			//
			if ( !dd_archiveSupported() )
				return PEAR::raiseError( $ddStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Get user folder rights
			//
			$rights = $this->getIdentityFolderRights( $U_ID, $ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
				return PEAR::raiseError( $ddStrings['add_screen_norights_message'], ERRCODE_APPLICATION_ERR );

			// Load archive content
			//
			$zip = new PclZip($filePath);

			if ( ($list = $zip->listContent()) == 0 )
				return PEAR::raiseError( $ddStrings['ua_errorarchive_message'], ERRCODE_APPLICATION_ERR );

			// Create directory structure
			//
			if ( $extractSubdirs ) {
				// Check folder rights
				//

				if ( !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
					return PEAR::raiseError( $ddStrings['ua_nofolderrights_message'], ERRCODE_APPLICATION_ERR );

				// Create folder structure
				//
				$folders = array();
				foreach ( $list as $element ) {
					if ( $element['folder'] )
						$this->_makePath( $folders, $element['filename'] );
				}

				// Create folders in DD
				//
				$folderContent = &$folders['content'];
				$folderCounter = 0;
				$res = $this->_createArchiveFolder( $U_ID, $ID, $folderContent, $kernelStrings, $ddStrings, null, null, $folderCounter );
				$resultStatistics['foldersCreated'] = $folderCounter;

				if ( PEAR::isError($res) )
					return $res;
			}

			// Group files by folders
			//
			$files = array();
			foreach ( $list as $index=>$element ) {
				if ( $element['folder'] )
					continue;

				// Find file folder ID
				//
				$parentFolder = $ID;

				$path = $element['filename'];
				$pos = strrpos( $path, "/" );
				if ( $pos === false )
					$filePath = null;
				else
					$filePath = substr( $path, 0, $pos );

				if ( $extractSubdirs && $filePath != null ) {
					$parentFolder = $this->_getFolderID( $folders, $filePath );
				}

				// Add file to group
				//
				if ( !isset($files[$parentFolder]) )
					$files[$parentFolder] = array();

				$files[$parentFolder][] = $index;
			}

			foreach ( $files as $parentID=>$indexes ) {
				$fileList = array();

				foreach ( $indexes as $index ) {
					$path = $list[$index]['filename'];

					$pos = strrpos( $path, "/" );
					if ( $pos === false )
						$fileName = $path;
					else
						$fileName = substr( $path, $pos+1 );

					// Prepare file description
					//
					$fileObj = new dd_fileDescription();
					$fileObj->DL_FILENAME = $this->_convertFileName( $fileName );
					$fileObj->DL_FILESIZE = $list[$index]['size'];
					$fileObj->DL_DESC = null;
					$fileObj->DL_MIMETYPE = getMimeType($fileName);

					// Extract file to the temporary dir
					//
					$tmpFileName = uniqid( TMP_FILES_PREFIX );
					$destPath = ($this->is_host ? "/tmp" : WBS_TEMP_DIR )."/".$tmpFileName;
					$dd_extractTmpName__ = $destPath;

					$zip->extract(PCLZIP_OPT_PATH, WBS_TEMP_DIR, PCLZIP_OPT_BY_INDEX, array ($index), PCLZIP_CB_PRE_EXTRACT, "dd_postExtractCallBack");
					$fileObj->sourcePath = $destPath;

					$fileList[] = $fileObj;
				}

				// Add files to DD
				//
				$addStats = array();
				$lastFile = null;
				$res = dd_addFiles( $fileList, $parentID, $U_ID, $kernelStrings, $ddStrings, $messageStack, $lastFile, $addStats, $createThumbs, $existingFilesOperation );

				$resultStatistics['filesAdded'] = $resultStatistics['filesAdded'] + $addStats['filesAdded'];
				$resultStatistics['imagesAdded'] = $resultStatistics['imagesAdded'] + $addStats['imagesAdded'];
				$resultStatistics['imageErrors'] = $resultStatistics['imageErrors'] + $addStats['imageErrors'];

				if ( PEAR::isError($res) )
					return $res;
			}

			//@unlink($archivePath);

			return null;
		}

		function analyzeArchive( $filePath, &$kernelStrings, &$ddStrings )
		//
		// Analyzes archive. Returns number of files, images, folders, and total unpacked size
		//
		//		Parameters:
		//			$filePath - path to the archive
		//			$kernelStrings - Kernel localization strings
		//			$ddStrings - Document Depot localization strings
		//
		//		Returns array or PEAR_Error
		//
		{
			global $dd_knownImageFormats;

			$files = 0;
			$folders = 0;
			$images = 0;
			$totalSize = 0;

			// Check if archives are supported
			//
			if ( !dd_archiveSupported() )
				return PEAR::raiseError( $ddStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Load archive content
			//
			$zip = new PclZip($filePath);

			if ( ($list = $zip->listContent()) == 0 )
				return PEAR::raiseError( $ddStrings['ua_errorarchive_message'], ERRCODE_APPLICATION_ERR );

			// Count files and images
			//
			foreach ( $list as $index=>$element ) {
				if ( $element['folder'] )
					$folders++;
				else {
					$totalSize += $element['size'];
					$files++;

					$pathData = pathinfo($element['filename']);
					if ( isset($pathData['extension']) ) {
						$ext = strtolower(trim($pathData['extension']));
						if ( in_array($ext, $dd_knownImageFormats) )
							$images++;
					}
				}
			}

			$result = array();
			$result['files'] = $files;
			$result['folders'] = $folders;
			$result['images'] = $images;
			$result['totalSize'] = $totalSize;

			return $result;
		}

		function _objNameExists( &$existingNames, $name )
		//
		// Internal function
		//
		{
			$name = strtolower($name);

			foreach ( $existingNames as $existingName ) {
				$existingName = trim( strtolower($existingName) );
				if ( $existingName == $name )
					return true;
			}

			return false;
		}

		function _resolveObjName( &$existingNames, $newName, $keepExtensions = false )
		//
		// Internal function
		//
		{
			$newNameLower = strtolower($newName);
			$found = false;

			if ( !$this->_objNameExists( $existingNames, $newNameLower ) )
				return $newName;

			if ( $keepExtensions ) {
				$nameParts = pathinfo($newName);
				if ( isset($nameParts['extension']) )
					$extension = $nameParts['extension'];
				else
					$extension = null;

				if ( !strlen($extension) )
					$extension = null;

				if ( !is_null($extension) )
					$baseName = substr( $newName, 0, strlen($newName)-strlen($extension)-1 );
			}

			$copyIndex = 0;

			do {
				$copyIndex++;
				if ( !$keepExtensions )
					$name = $newName."_copy".$copyIndex;
				else {
					if ( is_null($extension) )
						$name = $newName."_copy".$copyIndex;
					else
						$name = $baseName."_copy".$copyIndex.".".$extension;
				}
			} while ( $this->_objNameExists($existingNames, $name) );

			return $name;
		}

		function _getFileDiskInfo( $DL_ID, &$existingNames, &$kernelStrings, $fileInfo = null )
		//
		// Internal function
		//
		{
			$result = array();

			if ( is_null($fileInfo) ) {
				$fileInfo = dd_getDocumentData( $DL_ID, $kernelStrings );
				if ( PEAR::isError($fileInfo) )
					return $fileInfo;
			}

			$diskFileName = $fileInfo->DL_DISKFILENAME;
			$DF_ID = $fileInfo->DF_ID;
			$fileName = $fileInfo->DL_FILENAME;

			$ddFilesPath = dd_getFolderDir( $DF_ID );
			$attachmentPath = $ddFilesPath."/".$diskFileName;

			$result['filePath'] = $attachmentPath;
			$result['fileDir'] = $ddFilesPath;
			$result['fileName'] = $this->_resolveObjName( $existingNames, $fileName, true );

			$existingNames[] = $result['fileName'];

			return $result;
		}

		function _mapResolvedFolderFiles( $U_ID, &$folders, &$hierarchy, &$kernelStrings, $path = null , &$count_of_size = 0, &$fileNumber = 0 , &$bad_files)
		{
			$result = array();
			
			// Scan subfolders of this folder
			//
			$existingNames = array();

			if (!is_array($hierarchy)) return $result;

			foreach ( $hierarchy as $DF_ID=>$subfolders ) {
				$folderInfo = array();

				$folderInfo['name'] = $this->_resolveObjName( $existingNames, $folders[$DF_ID]->DF_NAME, false );
				$existingNames[] = $folderInfo['name'];

				if ( is_null($path) )
					$localPath = $folderInfo['name'];
				else
					$localPath = $path."/".$folderInfo['name'];

				$folderInfo['path'] = $localPath;

				// Check user folder rights
				//
				$rights = $this->getIdentityFolderRights( $U_ID, $DF_ID, $kernelStrings );
				if( PEAR::isError( $rights ) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) || !strlen($rights) )
					$folderInfo['files'] = array();
				else {
					// Add folder files
					//
					$nameList = array();
					$fileList = array();
					$documents = $this->listFolderDocuments( $DF_ID, null, 'DL_ID asc', $kernelStrings, null, true );

					if (is_array($documents)) {
						foreach ( $documents as $docData ) {
							$DL_ID = $docData->DL_ID;
							$fileInfo = $this->_getFileDiskInfo( $DL_ID, $nameList, $kernelStrings, $docData );
							if ( PEAR::isError($fileInfo) )
								return $fileInfo;

							if ($count_of_size > 0 and file_exists($fileInfo['filePath'])) 
								{
								$size = filesize($fileInfo['filePath']);
			
								// check file size for a limit
								//
								if ($size > DD_FILE_LIMIT) 
									{
									// Collect bad_file
									//
									$bad_files[] = array('path' => $folderInfo['path'].'/', 'file'=>$fileInfo['fileName'] , 'size'=>formatFileSizeStr($size));
									}
								else 
									{
									$fileNumber++;
									// Collect size information
									//
									$count_of_size += $size;
									}
								} 
							else 
								{
								$size = filesize($fileInfo['filePath']);
								// check file size for a limit
								//
								if ($size < DD_FILE_LIMIT) 
									{
									$fileList[] = $fileInfo;
									}
								}
						}
					}

					$folderInfo['files'] = $fileList;
				}

				// Add subfolders
				//
				$folderInfo['folders'] = $this->_mapResolvedFolderFiles( $U_ID, $folders, $subfolders, $kernelStrings, $localPath , $count_of_size, $fileNumber,$bad_files);
				if ( PEAR::isError($folderInfo['folders']) )
					return $folderInfo['folders'];

				$result[$DF_ID] = $folderInfo;
			}

			return $result;
		}

		function _createFolderFilesMap( $parentFolder, $U_ID, &$kernelStrings, &$ddStrings, $get_fize = false )
		//
		// Internal function
		//
		{
			$access = null;
			$hierarchy = null;
			$deletable = null;

			$folders = $this->listFolders( $U_ID, $parentFolder, $kernelStrings, 0, false,
												$access, $hierarchy, $deletable, null, null,
												false, null );
			if ( PEAR::isError($folders) )
				return $folders;

			// Add parent folder to the hierarchy root
			//
			if ( $parentFolder != TREE_ROOT_FOLDER ) {
				$hierarchy = array ( $parentFolder=>$hierarchy );

				$folderInfo = $this->getFolderInfo( $parentFolder, $kernelStrings );
				if ( PEAR::isError($folderInfo) )
					return $folderInfo;

				$folders = array_merge( array($parentFolder=>((object)$folderInfo)), $folders );
			}

			return $this->_mapResolvedFolderFiles( $U_ID, $folders, $hierarchy, $kernelStrings , NULL, $get_fize);
		}

		function _addArchiveFolders( &$archive, $folders, &$kernelStrings, &$ddStrings, &$fileNumber )
		//
		// Internal function
		//
		{
			global $dd_addTmpName__;

			if (!is_array($folders)) return null;

			foreach ( $folders as $DF_ID=>$folderData ) {
				$fileList = $folderData['files'];
				if (is_array($fileList)) {
					foreach ( $fileList as $fileData ) {
						$dd_addTmpName__ = $folderData['path']."/".$fileData['fileName'];
						if ( file_exists($fileData['filePath']) && @filesize($fileData['filePath']) ) {
							
							$size = filesize($fileInfo['filePath']);
							
							if ($size < DD_FILE_LIMIT) 
								{
								$fileNumber++;
								$archive->add( $fileData['filePath'], PCLZIP_CB_PRE_ADD, "dd_preAddCallBack" );
								
								$this->store_progress($fileNumber);
								
								$res = $this->check_user_activity($ddStrings);
								
								if( PEAR::isError( $res ) )
									return $res;	
								}
						}
					}
				}

				$res = $this->_addArchiveFolders( $archive, $folderData['folders'], $kernelStrings, $ddStrings, $fileNumber );
				if( PEAR::isError( $res ) )
					return $res;
			}

		}

		function _createFoldersRecursiveArchive( $archivePath, $parentFolder, $U_ID, &$kernelStrings, &$ddStrings, &$fileNumber )
		//
		// Internal function
		//
		{
			// Generate folder and files map
			//
			$map = $this->_createFolderFilesMap( $parentFolder, $U_ID, $kernelStrings, $ddStrings );
			if ( PEAR::isError($map) )
				return $map;
			
			// Create archive
			//
			($this->is_host && false ) ? ($archive = new dd_shell_archive($archivePath)) : ($archive = new PclZip($archivePath));
			$res = $archive->create( array() );

			$res = $this->_addArchiveFolders( $archive, $map, $kernelStrings, $ddStrings, $fileNumber );
			if ( PEAR::isError($res) )
				return $res;
				
			if ($this->is_host && false ) 
				{
				$res = $archive->exec_code($ddStrings);
				if( PEAR::isError( $res ) )
					return $res;
				}
			else 
				{
				$this->erase_temp();
				}
				
			return null;
		}

		function createArchive( $U_ID, $createMode, $objects, &$kernelStrings, &$ddStrings, $addsubfolders, &$fileNumber )
		//
		// Creates archive in the temporary folder
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$createMode - archive creating mode, one of the DD_CREATEARCHIVE constants
		//			$objects - list of files or folder identifier for files and folder modes
		//			$kernelStrings - Kernel localization strings
		//			$ddStrings - Document Depot localization strings
		//			$addsubfolders - add subfolders in case of mode == DD_CREATEARCHIVE_FOLDER
		//			$fileNumber - number of files added to the archive
		//
		//		Returns path to archive as string or PEAR_Error
		//
		{
			global $dd_addTmpName__;

			@set_time_limit( 3600 );

			// Check if archives are supported
			//
			if ( !dd_archiveSupported() )
				return PEAR::raiseError( $ddStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Create archive in the temporary directory
			//
			$tmpFileName = uniqid( TMP_FILES_PREFIX );
			$archivePath = ($this->is_host ? "/tmp" : WBS_TEMP_DIR ) ."/".$tmpFileName;

			$fileNumber = 0;
			$res =  $this->prepare_archire();
			
			if( PEAR::isError( $res ) )
				return $res;
								
			// Add files or folders to the archive
			//
			switch ($createMode) {
				case DD_CREATEARCHIVE_FILES :
					// Create archive
					//
					
					($this->is_host && false ) ? ($archive = new dd_shell_archive($archivePath)) : ($archive = new PclZip($archivePath));
					
					$res = $archive->create(array());

					// Add files to the archive
					//
					$nameList = array();

					foreach ( $objects as $DL_ID ) {
						// Obtain file information
						//
						$fileInfo = $this->_getFileDiskInfo( $DL_ID, $nameList, $kernelStrings );
						
						$fileInfo['fileName'] = iconv("UTF-8", "UTF-8", $fileInfo['fileName']);
						
						// Add file to the archive
						//
						$dd_addTmpName__ = $fileInfo['fileName'];
						 
						if ( file_exists($fileInfo['filePath']) && @filesize($fileInfo['filePath']) ) {
							
							
							$size = filesize($fileInfo['filePath']);
							
							if ($size < DD_FILE_LIMIT) 
								{
								$fileNumber++;
								
								$archive->add( $fileInfo['filePath'], PCLZIP_OPT_REMOVE_PATH, $fileInfo['fileDir'], PCLZIP_CB_PRE_ADD, "dd_preAddCallBack" );

								$this->store_progress($fileNumber);
							
								$res = $this->check_user_activity($ddStrings);
								if( PEAR::isError( $res ) )
									return $res;
								}
						}


					}
					if ($this->is_host && false ) 
						{
						$res = $archive->exec_code($ddStrings);
						if( PEAR::isError( $res ) )
							return $res;
						}
					else 
						{
						$this->erase_temp();
						}
					break;
				case DD_CREATEARCHIVE_FOLDER :
					if ( !$addsubfolders ) {

						// Obtain folder information
						//
						$folderInfo = $this->getFolderInfo( $objects, $kernelStrings );
						if ( PEAR::isError($folderInfo) )
							return $folderInfo;

						// Check user rights
						//
						$rights = $this->getIdentityFolderRights( $U_ID, $objects, $kernelStrings );
						if( PEAR::isError( $rights ) )
							return $rights;

						if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) || !strlen($rights) )
							return PEAR::raiseError( $ddStrings['ca_nodownloadrights_message'], ERRCODE_APPLICATION_ERR );

						// Create archive
						//
						($this->is_host && false ) ? ($archive = new dd_shell_archive($archivePath)) : ($archive = new PclZip($archivePath));
						$res = $archive->create(array());

						// Add files to the archive
						//
						$nameList = array();
						$documents = $this->listFolderDocuments( $objects, $U_ID, 'DL_ID asc', $kernelStrings, null, true );

						foreach ( $documents as $docData ) {
							$DL_ID = $docData->DL_ID;
							$fileInfo = $this->_getFileDiskInfo( $DL_ID, $nameList, $kernelStrings, $docData );
							if ( PEAR::isError($fileInfo) )
								return $fileInfo;

							// Add file to the archive
							//
							if ( file_exists($fileInfo['filePath']) && @filesize($fileInfo['filePath']) ) {
								
								$size = filesize($fileInfo['filePath']);
								
								if ($size < DD_FILE_LIMIT) 
									{
									$fileNumber++;
									$dd_addTmpName__ = $folderInfo['DF_NAME']."/".$fileInfo['fileName'];
									$archive->add( $fileInfo['filePath'], PCLZIP_CB_PRE_ADD, "dd_preAddCallBack" );
									$this->store_progress($fileNumber);
									
									$res = $this->check_user_activity($ddStrings);
									if( PEAR::isError( $res ) )
										return $res;
									}
							}
						}
						
						if ($this->is_host && false ) 
							{
							$res = $archive->exec_code($ddStrings);
							if( PEAR::isError( $res ) )
								return $res;
							}
						else 
							{
							$this->erase_temp();
							}
						
							
					} else {
						// Use recursive folders map
						//
						$res = $this->_createFoldersRecursiveArchive( $archivePath, $objects, $U_ID, $kernelStrings, $ddStrings, $fileNumber );
						if ( PEAR::isError($res) )
							return $res;
					}

					break;
				case DD_CREATEARCHIVE_ENTIRE :
						// Use recursive folders map
						//
						$res = $this->_createFoldersRecursiveArchive( $archivePath, TREE_ROOT_FOLDER, $U_ID, $kernelStrings, $ddStrings, $fileNumber );
						if ( PEAR::isError($res) )
							return $res;
			}
			
			if ( PEAR::isError($folderInfo) )
				return $folderInfo;
			
			return $tmpFileName;
		}
		
		/**
		 * (bool) check_user_activity: Check user for activity on the site, true is an active 
		 * @param array $ddStrings localization
		 * @access private
		 * @return bool
		**/
		private function check_user_activity(&$ddStrings)
			{
			
			
			$activity = ($this->is_host ? "/tmp" : WBS_TEMP_DIR).'/'.session_id().'.tmp'; 
			
			
			
			$last_modify = filemtime($activity);
			
			if ((time() - $last_modify) > DD_LIMIT_EXPIRE_TIME) 
				{
				// erase temp files
				//
				$this->erase_temp();
				if (file_exists($this->archivePath))
					unlink($this->archivePath);
				
				//return 1;
				return PEAR::raiseError($ddStrings['ca_archivecencel_text']);
				}
			}
		
		/**
		 * (void) prepare_archire: create temp file
		 * @access private
		 * @return nothing or PEAR error
		**/
		private function prepare_archire()
			{
			try	{
				$filename = ($this->is_host ? "/tmp" : WBS_TEMP_DIR).'/'.session_id().'.tmp';

					
				$handle = fopen($filename, "w");
				
				if(!$handle)
					throw new Exception($ddStrings['ca_archivecencel_text']);
				
				$count = 0;
				$res = fwrite($handle, $count);
				
				if(!$res)
					throw new Exception($ddStrings['ca_archivecencel_text']);
					
				fclose($handle);
				}
			catch (Exception $e)
				{
				return PEAR::raiseError($e->getMessage());
				}
			}
		
		/**
		 * (void) erase_temp: erase temp files after an archivation
		 * @access private
		 * @return void
		**/
		private function erase_temp()
			{
			$activity = ($this->is_host ? "/tmp" : WBS_TEMP_DIR).'/'.session_id().'.tmp'; 	
			unlink($activity);
			unlink($activity.'_up');
			}
		
		/**
		 * (bool) store_progress: storage progress of archive in to the file
		 * @param int $progress count of archived files
		 * @access private
		 * @return bool
		**/
		private function store_progress($progress)
			{
			$filename = ($this->is_host ? "/tmp" : WBS_TEMP_DIR ) .'/'.session_id().'.tmp_up';
			
			if (!$handle = fopen($filename, "w")) 
				{
				return false;
				}
		
			$count = intval($progress);
			if (fwrite($handle, $count) === FALSE) 
				{
				return false;
				}
			else 
				{
				return true;
				}
			fclose($handle);
			}
		
		function prepareArchive( $U_ID, $createMode, $objects, &$kernelStrings, &$ddStrings, $addsubfolders, &$fileNumber ,&$count_of_size, &$bad_files)
		//
		// Creates archive in the temporary folder
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$createMode - archive creating mode, one of the DD_CREATEARCHIVE constants
		//			$objects - list of files or folder identifier for files and folder modes
		//			$kernelStrings - Kernel localization strings
		//			$ddStrings - Document Depot localization strings
		//			$addsubfolders - add subfolders in case of mode == DD_CREATEARCHIVE_FOLDER
		//			$fileNumber - number of files added to the archive
		//
		//		Returns path to archive as string or PEAR_Error
		//
		{
			global $dd_addTmpName__;

			@set_time_limit( 3600 );

			// Check if archives are supported
			//
			if ( !dd_archiveSupported() )
				return PEAR::raiseError( $ddStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			$fileNumber = 0;
			
			// Add files or folders to the archive
			//
			switch ($createMode) {
				case DD_CREATEARCHIVE_FILES :

					// check limit space in baits
					//
					
					foreach ( $objects as $DL_ID ) {
						// Obtain file information
						//
						
						$nameList = array ();
						$fileInfo = $this->_getFileDiskInfo( $DL_ID, $nameList, $kernelStrings );
						
						$size = filesize($fileInfo['filePath']);
						
						// check file size for a limit
						//
						if ($size > DD_FILE_LIMIT) 
							{
							// Collect bad_file
							//
							$bad_files[] = array('file'=>$fileInfo['fileName'] , 'size'=>formatFileSizeStr($size)); 
							}
						else 
							{
							$fileNumber++;
							// Collect size information
							//
							$count_of_size += $size;
							}
						
					}
					

					break;
				case DD_CREATEARCHIVE_FOLDER :
					if ( !$addsubfolders ) {

						// Obtain folder information
						//
						$folderInfo = $this->getFolderInfo( $objects, $kernelStrings );
						if ( PEAR::isError($folderInfo) )
							return $folderInfo;
						
						// Check user rights
						//
						$rights = $this->getIdentityFolderRights( $U_ID, $objects, $kernelStrings );
						if( PEAR::isError( $rights ) )
							return $rights;

						if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) || !strlen($rights) )
							return PEAR::raiseError( $ddStrings['ca_nodownloadrights_message'], ERRCODE_APPLICATION_ERR );

 
						// Add files to the archive
						//
						$nameList = array();
						$documents = $this->listFolderDocuments( $objects, $U_ID, 'DL_ID asc', $kernelStrings, null, true );
						

						foreach ( $documents as $docData ) {
							$DL_ID = $docData->DL_ID;
							$fileInfo = $this->_getFileDiskInfo( $DL_ID, $nameList, $kernelStrings, $docData );
							if ( PEAR::isError($fileInfo) )
								return $fileInfo;
							
							// Add file to the archive
							//
							if ( file_exists($fileInfo['filePath']) && @filesize($fileInfo['filePath']) ) {
								
								$size = filesize($fileInfo['filePath']);
						
								// check file size for a limit
								//
								if ($size > DD_FILE_LIMIT) 
									{
									// Collect bad_file
									//
									$bad_files[] = array('file'=>$fileInfo['fileName'] , 'size'=>formatFileSizeStr($size)); 
									}
								else 
									{
									$fileNumber++;
									// Collect size information
									//
									$count_of_size += $size;
									}
							}
						}
					} else {
						// Use recursive folders map
						//
						$res = $this->_prepareFolderFilesMap(  $objects, $U_ID, $kernelStrings, $ddStrings , $count_of_size, $fileNumber, $bad_files);
						if ( PEAR::isError($res) )
							return $res;
							
					}

					break;
				case DD_CREATEARCHIVE_ENTIRE :
						// Use recursive folders map
						//

						$res = $this->_prepareFolderFilesMap( TREE_ROOT_FOLDER, $U_ID, $kernelStrings, $ddStrings , $count_of_size, $fileNumber, $bad_files);
						if ( PEAR::isError($res) )
							return $res;

			}
	
		}
		

		
	function _prepareFolderFilesMap( $parentFolder, $U_ID, &$kernelStrings, &$ddStrings , &$count_of_size, &$fileNumber, &$bad_files)
		{
		$access = null;
		$hierarchy = null;
		$deletable = null;
		
		$folders = $this->listFolders( $U_ID, $parentFolder, $kernelStrings, 0, false,
											$access, $hierarchy, $deletable, null, null,
											false, null );
		if ( PEAR::isError($folders) )
			return $folders;
		
		// Add parent folder to the hierarchy root
		//
		
		if ( $parentFolder != TREE_ROOT_FOLDER ) {
			$hierarchy = array ( $parentFolder=>$hierarchy );
			
			$folderInfo = $this->getFolderInfo( $parentFolder, $kernelStrings );
			if ( PEAR::isError($folderInfo) )
				return $folderInfo;
		
			$folders = array_merge( array($parentFolder=>((object)$folderInfo)), $folders );
		}
		$count_of_size += 1;
		
		$this->_mapResolvedFolderFiles( $U_ID, $folders, $hierarchy, $kernelStrings ,null, $count_of_size, $fileNumber, $bad_files);
		$count_of_size -= 1;
		
		
		}	 
	
	}

	class dd_fileSender extends arrayAdaptedClass
	{
		var $PRIORITY;
		var $TOMORE;
		var $SUBJECT;
		var $MESSAGE;
		var $COMPRESS;
		var $ARCHIVENAME;

		var $recipients = array();
		var $extraRecipients;
		var $fileList = null;
		var $fullRecipientList = array();

		function dd_fileSender()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'PRIORITY', t_integer, true );
			$this->dataDescrition->addFieldDescription( 'TOMORE', t_string, false );
			$this->dataDescrition->addFieldDescription( 'SUBJECT', t_string, false );
			$this->dataDescrition->addFieldDescription( 'MESSAGE', t_string, false );
			$this->dataDescrition->addFieldDescription( 'COMPRESS', t_integer, false );
			$this->dataDescrition->addFieldDescription( 'ARCHIVENAME', t_string, false );
		}

		function _parseRecipientList( $listStr )
		{
			$fieldValue = str_replace( ",", ";", $listStr );
			$fieldValue = str_replace( "\r\n", ";", $listStr );

			$list = explode( ";", $listStr );
			$resultList = array();
			foreach( $list as $key=>$value ) {
				$value = trim($value);
				if ( strlen($value) )
					$resultList[] = $value;
			}

			return $resultList;
		}

		function onValidateField( $array, $fieldName, $fieldValue, &$params )
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			extract($params);

			// Check if archive name is supplied
			//
			if ( $fieldName == 'ARCHIVENAME' && $array['COMPRESS'] && !strlen($fieldValue) )
				return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS],
											ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											$fieldName );

			// Validate archive name
			//
			if ( $fieldName == 'ARCHIVENAME' && $array['COMPRESS'] && strlen($fieldValue) ) {
				if ( ereg( "\\/|\\\|\\?|:|<|>|\\*", $fieldValue ) || !(strpos($fieldValue, "\\") === FALSE) )
					return PEAR::raiseError( $ddStrings['mf_screen_invchars_message'],
												ERRCODE_INVALIDFIELD,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$fieldName );
			}

			// Validate additional recipient list
			//
			if ( $fieldName == 'TOMORE' ) {
				$extraRecipients = $this->_parseRecipientList( $fieldValue );
				foreach ( $extraRecipients as $key=>$value )
					if ( !eregi('^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$', $value ) )
						return PEAR::raiseError( sprintf($ddStrings['sm_invalidemail_message'], $value),
												ERRCODE_APPLICATION_ERR,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$fieldName );

				if ( !count($this->recipients) && !count($extraRecipients) ) {
					return PEAR::raiseError( $kernelStrings[ERR_REQUIREDFIELDS],
											ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											'recipients' );
				}
			}
		}

		function onAfterSet( $array, $params = null )
		//
		// onAfterSet event handler
		//
		//		Parameters:
		//			$array - data source array
		//			$params - packed parameters. Must contain following keys:
		//				'source' - source of data - s_form, s_database
		//
		{
			extract( $params );
			
			$this->extraRecipients = $this->_parseRecipientList( $this->TOMORE );
			$this->ARCHIVENAME = $this->ARCHIVENAME.".zip";
		}

		function send( $U_ID, &$kernelStrings, &$ddStrings )
		//
		// Sends files by e-mail
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$kernelStrings - Kernel localization strings
		//			$ddStrings - Document Depot localization strings
		//
		//		Returns null or PEAR_Error
		//
		{
			global $dd_treeClass;
			global $html_encoding;

			@set_time_limit( 3600 );

			$finalFileList = array();
			
			if ( $this->COMPRESS ) {
				// Compress files
				//
				$archiveName = $dd_treeClass->createArchive( $U_ID, DD_CREATEARCHIVE_FILES, $this->fileList, $kernelStrings, $ddStrings, false, $fileNumber = 0 );
				if ( PEAR::isError($archiveName) )
					return $res;

				// Create new temporary directory
				//
				$tmpDirName = uniqid( TMP_FILES_PREFIX );
				$tmpDirPath = WBS_TEMP_DIR."/".$tmpDirName;

				if ( !@mkdir($tmpDirPath) )
					return PEAR::raiseError( $kernelStrings[ERR_CREATEDIRECTORY] );

				// Move archive to it
				//
				$srcPath = WBS_TEMP_DIR."/".$archiveName;
				$destPath = $tmpDirPath."/".$this->ARCHIVENAME;

				if ( !@copy( $srcPath, $destPath ) )
					return PEAR::raiseError( sprintf($kernelStrings[ERR_COPYFILE], $this->ARCHIVENAME) );

				$finalFileList = array( array($this->ARCHIVENAME, $destPath) );
			} else {
				$nameList = array();
				foreach ( $this->fileList as $DL_ID ) {
					$fileInfo = $dd_treeClass->_getFileDiskInfo( $DL_ID, $nameList, $kernelStrings );
					$fileName = dd_getDbFileName( $DL_ID, $kernelStrings );
					if ( file_exists($fileInfo['filePath']) && @filesize($fileInfo['filePath']) )
						$finalFileList[] = array( $fileName, $fileInfo['filePath'] );
				}
			}

			// Load email settings
			//
			$emailMode = null;
			$emailName=  null;
			$emailAddress = null;
			$res = dd_getEmailSettingsParams( $emailMode, $emailName, $emailAddress, $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

//			if ( $emailMode != DD_EMAILPARAMS_GLOBAL ) {
			$userData = dd_getUserEmail( $U_ID, $kernelStrings );
			$emailName=  $userData['name'];
			$emailAddress = $userData['email'];
//			}

			$priorityMap = array( 2=>1, 1=>3, 0=>5 );
			$this->PRIORITY = $priorityMap[$this->PRIORITY];

			// Prepare message text
			//
			$this->MESSAGE = trim( str_replace( "\r\n", "\n", $this->MESSAGE ) );

			// Send message
			//
			foreach ( $this->fullRecipientList as $email ) {
				
				$mail = new WBSMailer();

//				$mail->IsSendmail();

				$mail->SMTPAuth = false;
				$mail->CharSet = $html_encoding;

				$mail->From = $emailAddress;
				$mail->FromName = $emailName;
				$mail->AddReplyTo( $emailAddress, $emailName );
				$mail->Sender = $emailAddress;

				$mail->AddAddress($email);

				$mail->IsHTML(false);

				$mail->Subject = $this->SUBJECT;
				$mail->Body = $this->MESSAGE;
				$mail->AltBody = $this->MESSAGE;
				$mail->Priority = $this->PRIORITY;

				foreach ( $finalFileList as $fileData ) {
					$mail->AddAttachment($fileData[1], $fileData[0]);
				}

				if ( !$mail->Send() )
					return PEAR::raiseError( $ddStrings['sm_errorsending_message'], ERRCODE_APPLICATION_ERR );
			}

			// Delete temporary file and directory
			//
			if ( $this->COMPRESS ) {
				@unlink($srcPath);
				@unlink($destPath);
				@rmdir($tmpDirPath);
			}

			return null;
		}
	}

	// Global DD tree class
	//

	global $dd_treeClass;
	global $dd_TreeFoldersDescriptor;
	$dd_treeClass = new dd_documentFolderTree( $dd_TreeFoldersDescriptor );

	class dd_reportDataRangeValidator extends arrayAdaptedClass
	//
	// Validates date range input data in the DD report pages
	//
	{
		var $days;
		var $from;
		var $to;
		var $type;

		function dd_reportDataRangeValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'days', t_integer, false );
			$this->dataDescrition->addFieldDescription( 'from', t_date, false );
			$this->dataDescrition->addFieldDescription( 'to', t_date, false );
		}


		function onValidateField( $array, $fieldName, $fieldValue, &$params )
		{
			global $_PEAR_default_error_mode;
			global $_PEAR_default_error_options;

			extract($params);

			if ( (($fieldName == 'from' || $fieldName == 'to') && $array['type'] == 'range') ||
					($fieldName == 'days' && $array['type'] == 'days')) {
				if ( !strlen($fieldValue) )
					return PEAR::raiseError ( $kernelStrings[ERR_REQUIREDFIELDS],
												SOAPROBOT_ERR_EMPTYFIELD,
												$_PEAR_default_error_mode,
												$_PEAR_default_error_options,
												$fieldName );
			}
		}

		function onAfterSet( $array, $params = null )
		//
		// onAfterSet event handler
		//
		//		Parameters:
		//			$array - data source array
		//			$params - packed parameters. Must contain following keys:
		//				'source' - source of data - s_form, s_database
		//
		{
			extract( $params );

			$timestamp = null;

			if ( strlen($this->from) ) {
				validateInputDate( $this->from, $timestamp );
				$this->from = convertToSQLDate($timestamp);
			}

			if ( strlen($this->to) ) {
				validateInputDate( $this->to, $timestamp );
				$this->to = convertToSQLDate($timestamp);
			}
		}
	}

	class dd_vcSettingsValidator extends arrayAdaptedClass
	//
	// Validates date range input data in the DD report pages
	//
	{
		var $maxVersionNum;

		function dd_vcSettingsValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'maxVersionNum', t_integer, false );
		}
	}

	class dd_emailSettingsValidator extends arrayAdaptedClass
	//
	// Validates the email settings
	//
	{
		var $name;
		var $email;

		function dd_emailSettingsValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'name', t_string, true );
			$this->dataDescrition->addFieldDescription( 'email', t_string, true );
		}
	}

	function dd_ftpFolder_sort( $a, $b )
	{
		return strcmp( $a["NAME"], $b["NAME"] );
	}

	class dd_ftpFolder
	{
		var $DB_KEY="";
		var $dir_path = "";

		function dd_ftpFolder( $DB_KEY )
		{
			$this->DB_KEY = $DB_KEY;

			$this->dir_path = sprintf( "%s/%s/ftp", WBS_DATA_DIR, $DB_KEY );
		}

		function GetPath( )
		{
			return $this->dir_path;
		}

		function isExists( )
		{
			if ( file_exists( $this->dir_path ) && is_dir( $this->dir_path ) )
				return true;
		}

		function getMimeType( $filename )
		{
			$fileInfo = pathinfo( $filename );

			if ( isset($fileInfo["extension"]) )
				$ext = $fileInfo["extension"];

			$ext = strtolower( $ext );
			
			switch( $ext )
			{
				case "jpg":
				case "jpe":
				case "jpeg":
					return "image/jpeg";
				case "gif":
					return "image/gif";
				case "bmp":
					return "image/bmp";
				case "png":
					return "image/png";
				case "htm":
				case "html":
					return "text/html";
				case "txt":
				case "asc":
					return "text/plain";
				case "css":
					return "text/css";
				case "rtf":
					return "text/rtf";
				case "pdf":
					return "application/pdf";
				case "doc":
					return "application/msword";
				case "xls":
					return "application/vnd.ms-excel";
				case "mp3":
				case "mp2":
				case "mpga":
					return "audio/mpeg";
				case "avi":
					return "video/x-msvideo";
				case "docm":
				case "docx":
				case "dotm":
				case "dotx":
				case "ppsm":
				case "ppsx":
				case "pptm":
				case "pptx":
				case "xlsb":
				case "xlsm":
				case "xlsx":
				case "xps":
					return "application/force-download";
				default:
					return "application/octet-stream";
			}
		}

		function GetFiles( )
		{
			$files = array();

			if ( $this->isExists() )
			{
				if ( $dh = opendir( $this->dir_path ) )
				{
					while ( ( $file = readdir( $dh ) ) !== false)
					{
						$fullName = $this->dir_path  . "/" . $file;

						if ( filetype( $fullName ) != "dir" )
							$files[base64_encode($file)] = array( "NAME"=>$file, "FULLNAME"=>$fullName, "SIZE"=>filesize( $fullName ), "SIZESTR"=>formatFileSizeStr( filesize( $fullName ) ) , "UNIXTIME"=>filectime( $fullName ), "DATETIME"=>convertToDisplayDateTime( convertToSqlDateTime( filemtime( $fullName ) ), false, true, true ), "MIMETYPE"=>$this->getMimeType( $file ) );
					}

					closedir($dh);
				}
			}

			usort( $files, "dd_ftpFolder_sort" );

			return $files;
		}
		
	}

/**
 * dd_shell_archive : a shell archiver for compress user`s files
 * @author Ivan Chura
 * date of create: 26.11.2007
**/
class dd_shell_archive 
	{
	/** Path of archive */
	var $archivePath;
	/** Files for archive */
	var $archiveFiles;
	/** include for pclzip */
	var $for_pclzip;
	/** file for exchange user`s activity  */
	var $activity;
	/** limit time for break archive  */
	var $limit_time;
	/** path to the temporary files  */
	var $tmp_path;
	
	/**
	 * __construct: constructer of dd_shell_ZIP class
	 * @param string $archivePath the archive in the temporary directory
	**/
	function __construct($archivePath) 
		{
		$this->for_pclzip = $GLOBALS['for_pclzip'];
		$this->archivePath = $archivePath;
		$this->archiveFiles = array();
		$this->limit_time = DD_LIMIT_EXPIRE_TIME;
		}
		
	/**
	 * (void) create: Create a new archive
	 * @access public
	 * @param array temp_var 
	 * @return void
	**/
	public function create($temp_var)
		{
		;
		}
		
	/**
	 * (viod) add: add the file to a prerare archive
	 * @param array $oops many variables
	 * @access public
	 * @return viod
	**/
	public function add($filePath, $PCLZIP_OPT_REMOVE_PATH, $fileDir  = NULL, $PCLZIP_CB_PRE_ADD = NULL, $dd_preAddCallBack  = NULL)
		{
		global $dd_addTmpName__;
		$this->archiveFiles[] = array('stored_filename' => $dd_addTmpName__, 'filePath'=>$filePath, 'PCLZIP_OPT_REMOVE_PATH'=>$PCLZIP_OPT_REMOVE_PATH, 'fileDir'=>$fileDir, 'PCLZIP_CB_PRE_ADD'=>PCLZIP_CB_PRE_ADD, "dd_preAddCallBack"=>"dd_preAddCallBack");
		
		} 
	
	/**
	 * (bool) exec_code: implementation archive code
	 * @access public
	 * @return bool
	**/
	public function exec_code(&$ddStrings)
		{
		@set_time_limit(0);
		// prepare variables
		//
		$this->activity = WBS_TEMP_DIR.'/'.session_id().'.tmp'; 
		$this->tmp_path = WBS_TEMP_DIR; 
		$this->tmp_zip = PCLZIP_TEMPORARY_DIR; 
		
		// encoding archive information
		//
		$for_tranc = $this;
		
		$json_style = json_encode($for_tranc); 
		
		// write info to file
		//
		if (!$handle = fopen($this->activity, 'w+')) {
			return PEAR::raiseError($ddStrings['ca_archiverror_text']);
		}

		$content = $json_style;
		
		if (fwrite($handle, $content) === FALSE) 
			return PEAR::raiseError($ddStrings['ca_archiverror_text']);
		
		fclose($handle);
		
		// alter 
		//
		$response = exec('nice -n 19 /usr/bin/php -c /etc/php.d/cli/php.ini -f system_archive.php '.$this->activity);
		$response = trim($response);
		
		// erase temp files
		//
		unlink($this->activity);
		unlink($this->activity.'_up');
		
		// if have error
		//
		if (substr($response,0,6) == '#ERROR') 
			{
			unlink($this->archivePath);
			return PEAR::raiseError($ddStrings['ca_archivecencel_text']);
			}
		 
		if(empty($response))
			return PEAR::raiseError($ddStrings['ca_archiverror_text']);
		
			 
		}
	}

?>