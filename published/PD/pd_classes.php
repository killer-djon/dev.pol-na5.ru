<?php

	//
	// WebAsyst Document Depot common classes
	//

	require_once realpath(WBS_DIR."kernel/includes/modules/pclzip.lib.php");

	class pd_fileDescription
	{
		var $PL_FILENAME;
		var $PL_FILESIZE;
		var $PL_DESC;
		var $PL_MIMETYPE;
		var $PL_VERSIONCOMMENT;

		var $sourcePath;
	}

	class pd_documentFolderTree extends genericDocumentFolderTree
	{
		function pd_documentFolderTree( &$descriptor )
		{
			$this->folderDescriptor = $descriptor->folderDescriptor;
			$this->documentDescriptor = $descriptor->documentDescriptor;

			$this->globalPrefix = "PD";
		}

		function addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback = null, $callbackParams = null, $propagateFolderRights = true, $suppressNotifications = false, $folderStatus = null )
		{
			$ID = parent::addmodFolder( $action, $U_ID, $ID_PARENT, $folderdata, $kernelStrings, $admin, $createCallback, $callbackParams, $propagateFolderRights, $suppressNotifications, $folderStatus );
			if ( PEAR::isError($ID) )
				return $ID;

			if ( $action == ACTION_EDIT ) {
				// Update folder status fields
				//
				global $qr_pd_updateFodlerUpdateStatusFields;

				$userName = getUserName( $U_ID, true );
				$sqlParams = array( 'PF_MODIFYUSERNAME'=>$userName, 'PF_ID'=>$folderdata['PF_ID'] );

				db_query( $qr_pd_updateFodlerUpdateStatusFields, $sqlParams );
			}

			return $ID;
		}

		function PropagateRightsRecursive( $U_ID, $PF_ID, &$kernelStrings, &$pdStrings )
		{
			$minimalRights = array(TREE_ONLYREAD, TREE_WRITEREAD, TREE_READWRITEFOLDER);

			$thisUserRights = $this->getIdentityFolderRights( $U_ID, $PF_ID, $kernelStrings );
			if ( PEAR::isError($thisUserRights) )
				return $thisUserRights;

			if ( !UR_RightsObject::CheckMask( $thisUserRights, TREE_READWRITEFOLDER ) )
				return PEAR::raiseError( $pdStrings['paa_norights_message'], ERRCODE_APPLICATION_ERR );

			$access = null;
			$hierarchy = null;
			$deletable = null;
			$folders = $this->listFolders( $U_ID, $PF_ID, $kernelStrings, 0, false,
											$access, $hierarchy, $deletable, $minimalRights );
			if ( PEAR::isError($folders) )
				return $folders;

			foreach ( $folders as $fPF_ID=>$folderData ) {
				if ( UR_RightsObject::CheckMask( $folderData->TREE_ACCESS_RIGHTS, TREE_READWRITEFOLDER ) ) {
					$res = $this->propagateFolderRights( $PF_ID, $fPF_ID, $kernelStrings );
					if ( PEAR::isError($res) )
						return $res;
				}
			}

			return null;
		}

		function recycleFolder( $ID, $U_ID, $kernelStrings, $pdStrings, $admin, $deleteCallback = null, $callbackParams = null, $suppressNotifications = false )
		{
			// Move folder to the root
			//
			$callbackParams = array( 'pdStrings'=>$pdStrings, "kernelStrings"=>$kernelStrings, 'folder'=>$ID, 'U_ID'=>$U_ID );

			$res = $this->deleteFolder( $ID, $U_ID, $kernelStrings, $admin, $deleteCallback, $callbackParams, $suppressNotifications, true );
			if ( PEAR::isError($res) )
				return $res;

			$callbackParams['suppressNotifications'] = true;

			$ID = $this->moveFolder( $ID, TREE_ROOT_FOLDER, $U_ID, $kernelStrings,
								"pd_onAfterCopyMoveFile", "pd_onCopyMoveFile", "pd_onCreateFolder", "pd_onDeleteFolder",
								$callbackParams, "pd_onFinishMoveFolder", false, true, ACCESSINHERITANCE_COPY, null, TREE_FSTATUS_DELETED, true );

			if ( PEAR::isError($ID) )
				return $ID;
		}

		function copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation = null, $callbackParams = null, $perFileCheck = true, $checkUserRights = true, $onFinishOperation = null, $suppressNotifications = false )
		{
			global $_pdQuotaManager;
			global $PD_APP_ID;

			$_pdQuotaManager = new DiskQuotaManager();

			$UserUsedSpace = $_pdQuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
			if ( PEAR::isError($UserUsedSpace) )
				return $UserUsedSpace;

			$TotalUsedSpace = $_pdQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['UserUsedSpace'] = $UserUsedSpace;
			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyMoveDocuments( $documentList, $destID, $operation, $U_ID, $kernelStrings, $onAfterOperation, $onBeforeOperation, $callbackParams, $perFileCheck, $checkUserRights, $onFinishOperation, $suppressNotifications );

			$_pdQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function moveFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null,
							$onFolderDelete = null, $callbackParams = null, $onFinishMove = null, $checkUserRights = true,
							$topLevel = true, $accessInheritance = ACCESSINHERITANCE_COPY, $mostTopRightsSource = null,
							$folderStatus = TREE_FSTATUS_NORMAL, $plainMove = false, $checkFolderName = true )
		{
			global $_pdQuotaManager;
			global $PD_APP_ID;

			$_pdQuotaManager = new DiskQuotaManager();

			$UserUsedSpace = $_pdQuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
			if ( PEAR::isError($UserUsedSpace) )
				return $UserUsedSpace;

			$TotalUsedSpace = $_pdQuotaManager->GetUsedSpaceTotal( $kernelStrings );
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

			$_pdQuotaManager->Flush( $kernelStrings );

			return $res;
		}

		function copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation = null, $onFolderCreate = null, $callbackParams = null, $onFininshCopy = null, $accessInheritance = ACCESSINHERITANCE_COPY, $onBeforeFolderCreate = null, $checkFolderName = true, $copyChilds = true )
		{
			global $_pdQuotaManager;
			global $PD_APP_ID;

			$_pdQuotaManager = new DiskQuotaManager();

			$UserUsedSpace = $_pdQuotaManager->GetUserApplicationUsedSpace( $U_ID, $PD_APP_ID, $kernelStrings );
			if ( PEAR::isError($UserUsedSpace) )
				return $UserUsedSpace;

			$TotalUsedSpace = $_pdQuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( is_null($callbackParams) )
				$callbackParams = array();

			$callbackParams['UserUsedSpace'] = $UserUsedSpace;
			$callbackParams['TotalUsedSpace'] = $TotalUsedSpace;

			$res = parent::copyFolder( $srcID, $destID, $U_ID, $kernelStrings, $onAfterDocumentOperation, $onBeforeDocumentOperation, $onFolderCreate, $callbackParams, $onFininshCopy, $accessInheritance, $onBeforeFolderCreate, $checkFolderName, $copyChilds );

			$_pdQuotaManager->Flush( $kernelStrings );

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

		function _createArchiveFolder( $U_ID, $parentID, &$folders, &$kernelStrings, &$pdStrings, $folderUsersArr, $folderGroupsArr, &$counter, $topLevel = true )
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
				$callbackParams = array( 'pdStrings'=>$pdStrings, 'kernelStrings'=>$kernelStrings );

				$newID = null;

				$folderData = array('PF_NAME'=>$folderName);
				$newID = $folderID = $this->addmodFolder( ACTION_NEW, $U_ID, $parentID, $folderData,
													$kernelStrings, false, 'pd_onCreateFolder', $callbackParams, true, true );
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
				$res = $this->_createArchiveFolder( $U_ID, $newID, $content, $kernelStrings, $pdStrings, $folderUsersArr, $folderGroupsArr, $counter, false );
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

		function uploadArchive( $U_ID, $ID, $originalName, $filePath, $extractSubdirs, $createThumbs, $kernelStrings, $pdStrings, &$resultStatistics, $existingFilesOperation = PD_REPLACE_FILES, $filenameAsDesc  )
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
		//			$pdStrings - Document Depot localization strings
		//			$resultStatistics - result statistics array
		//			$existingFilesOperation - operation to perform on existing files
		//			$filenameAsDesc - use filename as description
		//
		//		Returns null or PEAR_Error
		//
		{
			@set_time_limit( 3600 );

			global $pd_extractTmpName__;

			$resultStatistics['filesAdded'] = 0;
			$resultStatistics['imagesAdded'] = 0;
			$resultStatistics['imageErrors'] = 0;
			$resultStatistics['foldersCreated'] = 0;

			$archivePath = $filePath;

			$messageStack = array();

			// Check if archives are supported
			//
			if ( !pd_archiveSupported() )
				return PEAR::raiseError( $pdStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Get user folder rights
			//
			$rights = $this->getIdentityFolderRights( $U_ID, $ID, $kernelStrings );
			if ( PEAR::isError($rights) )
				return $rights;

			if ( !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
				return PEAR::raiseError( $pdStrings['add_screen_norights_message'], ERRCODE_APPLICATION_ERR );

			// Load archive content
			//
			$zip = new PclZip($filePath);

			if ( ($list = $zip->listContent()) == 0 )
				return PEAR::raiseError( $pdStrings['ua_errorarchive_message'], ERRCODE_APPLICATION_ERR );

			// Create directory structure
			//
			if ( $extractSubdirs ) {
				// Check folder rights
				//

				if ( !UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER ) )
					return PEAR::raiseError( $pdStrings['ua_nofolderrights_message'], ERRCODE_APPLICATION_ERR );

				// Create folder structure
				//
				$folders = array();
				foreach ( $list as $element ) {
					if ( $element['folder'] )
						$this->_makePath( $folders, $element['filename'] );
				}

				// Create folders in PD
				//
				$folderContent = &$folders['content'];
				$folderCounter = 0;
				$res = $this->_createArchiveFolder( $U_ID, $ID, $folderContent, $kernelStrings, $pdStrings, null, null, $folderCounter );
				$resultStatistics['foldersCreated'] = $folderCounter;

				if ( PEAR::isError($res) )
					return $res;
			}

			// Group files by folders
			//
			$files = array();
			foreach ( $list as $index => $element ) {
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

			foreach ( $files as $parentID => $indexes ) {
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
					$fileObj = new pd_fileDescription();
					$fileObj->PL_FILENAME = $this->_convertFileName( $fileName );
					$fileObj->PL_FILESIZE = $list[$index]['size'];
					if ($filenameAsDesc) {
						pd_getFileNameAndExtention($fileObj->PL_FILENAME, $cutFileName, $cutFileExt);
						$fileObj->PL_DESC = $cutFileName;
					} else
						$fileObj->PL_DESC = null;
					$fileObj->PL_MIMETYPE = getMimeType($fileName);

					// Extract file to the temporary dir
					//
					$tmpFileName = uniqid( TMP_FILES_PREFIX );
					$destPath = WBS_TEMP_DIR."/".$tmpFileName;
					$pd_extractTmpName__ = $destPath;

					$zip->extract(PCLZIP_OPT_PATH, WBS_TEMP_DIR, PCLZIP_OPT_BY_INDEX, array ($index), PCLZIP_CB_PRE_EXTRACT, "pd_postExtractCallBack");
					$fileObj->sourcePath = $destPath;

					if (pd_checkImageFile($destPath))
					{
						$fileList[] = $fileObj;
					}
				}

				// Add files to PD
				//
				$addStats = array();
				$lastFile = null;
				$res = pd_addFiles( $fileList, $parentID, $U_ID, $kernelStrings, $pdStrings, $messageStack, $lastFile, $addStats, $createThumbs, $existingFilesOperation,  $originalName);

				$resultStatistics['filesAdded'] = $resultStatistics['filesAdded'] + $addStats['filesAdded'];
				$resultStatistics['imagesAdded'] = $resultStatistics['imagesAdded'] + $addStats['imagesAdded'];
				$resultStatistics['imageErrors'] = $resultStatistics['imageErrors'] + $addStats['imageErrors'];

				if ( PEAR::isError($res) )
					return $res;
			}

			@unlink($archivePath);

			return $res;
		}

		function analyzeArchive( $filePath, &$kernelStrings, &$pdStrings )
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
			global $pd_knownImageFormats;

			$files = 0;
			$folders = 0;
			$images = 0;
			$totalSize = 0;

			// Check if archives are supported
			//
			if ( !pd_archiveSupported() )
				return PEAR::raiseError( $pdStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Load archive content
			//
			$zip = new PclZip($filePath);

			if ( ($list = $zip->listContent()) == 0 )
				return PEAR::raiseError( $pdStrings['ua_errorarchive_message'], ERRCODE_APPLICATION_ERR );

			// Count files and images
			//

			foreach ( $list as $index => $element ) {
				if ( $element['folder'] )
					$folders++;
				else {
					$files++;

					$pathData = pathinfo($element['filename']);
					if ( isset($pathData['extension']) ) {
						$ext = strtolower(trim($pathData['extension']));
						if ( in_array($ext, $pd_knownImageFormats) ) {
							$images++;
							$totalSize += $element['size'];
						}
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

		function _getFileDiskInfo( $PL_ID, &$existingNames, &$kernelStrings, $fileInfo = null )
		//
		// Internal function
		//
		{
			$result = array();

			if ( is_null($fileInfo) ) {
				$fileInfo = pd_getDocumentData( $PL_ID, $kernelStrings );
				if ( PEAR::isError($fileInfo) )
					return $fileInfo;
			}

			$diskFileName = $fileInfo->PL_DISKFILENAME;
			$PF_ID = $fileInfo->PF_ID;
			$fileName = $fileInfo->PL_FILENAME;

			$pdFilesPath = pd_getFolderDir( $PF_ID );
			$attachmentPath = $pdFilesPath."/".$diskFileName;

			$result['filePath'] = $attachmentPath;
			$result['fileDir'] = $pdFilesPath;
			$result['fileName'] = $this->_resolveObjName( $existingNames, $fileName, true );

			$existingNames[] = $result['fileName'];

			return $result;
		}

		function _mapResolvedFolderFiles( $U_ID, &$folders, &$hierarchy, &$kernelStrings, $path = null )
		{
			$result = array();

			// Scan subfolders of this folder
			//
			$existingNames = array();

			if (!is_array($hierarchy)) return $result;

			foreach ( $hierarchy as $PF_ID=>$subfolders ) {
				$folderInfo = array();

				$folderInfo['name'] = $this->_resolveObjName( $existingNames, $folders[$PF_ID]->PF_NAME, false );
				$existingNames[] = $folderInfo['name'];

				if ( is_null($path) )
					$localPath = $folderInfo['name'];
				else
					$localPath = $path."/".$folderInfo['name'];

				$folderInfo['path'] = $localPath;

				// Check user folder rights
				//
				$rights = $this->getIdentityFolderRights( $U_ID, $PF_ID, $kernelStrings );
				if( PEAR::isError( $rights ) )
					return $rights;

				if ( !UR_RightsObject::CheckMask( $rights, TREE_ONLYREAD ) || !strlen($rights) )
					$folderInfo['files'] = array();
				else {
					// Add folder files
					//
					$nameList = array();
					$fileList = array();
					$documents = $this->listFolderDocuments( $PF_ID, null, 'PL_ID asc', $kernelStrings, null, true );

					if (is_array($documents)) {
						foreach ( $documents as $docData ) {
							$PL_ID = $docData->PL_ID;
							$fileInfo = $this->_getFileDiskInfo( $PL_ID, $nameList, $kernelStrings, $docData );
							if ( PEAR::isError($fileInfo) )
								return $fileInfo;

							$fileList[] = $fileInfo;
						}
					}

					$folderInfo['files'] = $fileList;
				}

				// Add subfolders
				//
				$folderInfo['folders'] = $this->_mapResolvedFolderFiles( $U_ID, $folders, $subfolders, $kernelStrings, $localPath );
				if ( PEAR::isError($folderInfo['folders']) )
					return $folderInfo['folders'];

				$result[$PF_ID] = $folderInfo;
			}

			return $result;
		}

		function _createFolderFilesMap( $parentFolder, $U_ID, &$kernelStrings, &$pdStrings )
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

			return $this->_mapResolvedFolderFiles( $U_ID, $folders, $hierarchy, $kernelStrings );
		}

		function _addArchiveFolders( &$archive, $folders, &$kernelStrings, &$pdStrings, &$fileNumber )
		//
		// Internal function
		//
		{
			global $pd_addTmpName__;

			if (!is_array($folders)) return null;

			foreach ( $folders as $PF_ID=>$folderData ) {
				$fileList = $folderData['files'];
				if (is_array($fileList)) {
					foreach ( $fileList as $fileData ) {
						$pd_addTmpName__ = $folderData['path']."/".$fileData['fileName'];
						if ( file_exists($fileData['filePath']) && @filesize($fileData['filePath']) ) {
							$fileNumber++;
							$archive->add( $fileData['filePath'], PCLZIP_CB_PRE_ADD, "pd_preAddCallBack" );
						}
					}
				}

				$this->_addArchiveFolders( $archive, $folderData['folders'], $kernelStrings, $pdStrings, $fileNumber );
			}

		}

		function _createFoldersRecursiveArchive( $archivePath, $parentFolder, $U_ID, &$kernelStrings, &$pdStrings, &$fileNumber )
		//
		// Internal function
		//
		{
			// Generate folder and files map
			//
			$map = $this->_createFolderFilesMap( $parentFolder, $U_ID, $kernelStrings, $pdStrings );
			if ( PEAR::isError($map) )
				return $map;

			// Create archive
			//
			$archive = new PclZip($archivePath);
			$res = $archive->create( array() );

			$res = $this->_addArchiveFolders( $archive, $map, $kernelStrings, $pdStrings, $fileNumber );
			if ( PEAR::isError($res) )
				return $res;

			return null;
		}

		function createArchive( $U_ID, $createMode, $objects, &$kernelStrings, &$pdStrings, $addsubfolders, &$fileNumber )
		//
		// Creates archive in the temporary folder
		//
		//		Parameters:
		//			$U_ID - user identifier
		//			$createMode - archive creating mode, one of the PD_CREATEARCHIVE constants
		//			$objects - list of files or folder identifier for files and folder modes
		//			$kernelStrings - Kernel localization strings
		//			$pdStrings - Document Depot localization strings
		//			$apdsubfolders - add subfolders in case of mode == PD_CREATEARCHIVE_FOLDER
		//			$fileNumber - number of files added to the archive
		//
		//		Returns path to archive as string or PEAR_Error
		//
		{
			global $pd_addTmpName__;

			@set_time_limit( 3600 );

			// Check if archives are supported
			//
			if ( !pd_archiveSupported() )
				return PEAR::raiseError( $pdStrings['app_nozlib_message'], ERRCODE_APPLICATION_ERR );

			// Create archive in the temporary directory
			//
			$tmpFileName = uniqid( TMP_FILES_PREFIX );
			$archivePath = WBS_TEMP_DIR."/".$tmpFileName;

			$fileNumber = 0;

			// Add files or folders to the archive
			//
			switch ($createMode)
			{
				case PD_CREATEARCHIVE_FILES :
					// Create archive
					//
					$archive = new PclZip($archivePath);
					$res = $archive->create(array());

					// Add files to the archive
					//
					$nameList = array();

					foreach ( $objects as $PL_ID ) {
						// Obtain file information
						//
						$fileInfo = $this->_getFileDiskInfo( $PL_ID, $nameList, $kernelStrings );

						// Add file to the archive
						//
						$pd_addTmpName__ = $fileInfo['fileName'];

						if ( file_exists($fileInfo['filePath']) && @filesize($fileInfo['filePath']) )
						{
							$fileNumber++;
							$archive->add( $fileInfo['filePath'], PCLZIP_OPT_REMOVE_PATH, $fileInfo['fileDir'], PCLZIP_CB_PRE_ADD, "pd_preAddCallBack" );
						}
					}

					break;
				case PD_CREATEARCHIVE_FOLDER :
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
							return PEAR::raiseError( $pdStrings['ca_nodownloadrights_message'], ERRCODE_APPLICATION_ERR );

						// Create archive
						//
						$archive = new PclZip($archivePath);
						$res = $archive->create(array());

						// Add files to the archive
						//
						$nameList = array();
						$documents = $this->listFolderDocuments( $objects, $U_ID, 'PL_ID asc', $kernelStrings, null, true );

						foreach ( $documents as $docData ) {
							$PL_ID = $docData->PL_ID;
							$fileInfo = $this->_getFileDiskInfo( $PL_ID, $nameList, $kernelStrings, $docData );
							if ( PEAR::isError($fileInfo) )
								return $fileInfo;

							// Add file to the archive
							//
							if ( file_exists($fileInfo['filePath']) && @filesize($fileInfo['filePath']) ) {
								$fileNumber++;
								$pd_addTmpName__ = $folderInfo['PF_NAME']."/".$fileInfo['fileName'];
								$archive->add( $fileInfo['filePath'], PCLZIP_CB_PRE_ADD, "pd_preAddCallBack" );
							}
						}
					} else {
						// Use recursive folders map
						//
						$res = $this->_createFoldersRecursiveArchive( $archivePath, $objects, $U_ID, $kernelStrings, $pdStrings, $fileNumber );
						if ( PEAR::isError($res) )
							return $res;
					}

					break;
				case PD_CREATEARCHIVE_ENTIRE :
						// Use recursive folders map
						//
						$res = $this->_createFoldersRecursiveArchive( $archivePath, TREE_ROOT_FOLDER, $U_ID, $kernelStrings, $pdStrings, $fileNumber );
						if ( PEAR::isError($res) )
							return $res;
			}

			return $tmpFileName;
		}
	}
	// Global PD tree class
	//

	global $pd_treeClass;
	global $pd_TreeFoldersDescriptor;
	$pd_treeClass = new pd_documentFolderTree( $pd_TreeFoldersDescriptor );

	class pd_reportDataRangeValidator extends arrayAdaptedClass
	//
	// Validates date range input data in the PD report pages
	//
	{
		var $days;
		var $from;
		var $to;
		var $type;

		function pd_reportDataRangeValidator()
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
					return PEAR::raiseError ( $kernelStrings[ERR_REQUIREPFIELDS],
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

	class pd_vcSettingsValidator extends arrayAdaptedClass
	//
	// Validates date range input data in the PD report pages
	//
	{
		var $maxVersionNum;

		function pd_vcSettingsValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'maxVersionNum', t_integer, false );
		}
	}

	class pd_emailSettingsValidator extends arrayAdaptedClass
	//
	// Validates the email settings
	//
	{
		var $name;
		var $email;

		function pd_emailSettingsValidator()
		{
			$this->dataDescrition = new dataDescription();

			$this->dataDescrition->addFieldDescription( 'name', t_string, true );
			$this->dataDescrition->addFieldDescription( 'email', t_string, true );
		}
	}

	function pd_ftpFolder_sort( $a, $b )
	{
		return strcmp( $a["NAME"], $b["NAME"] );
	}

	class pd_ftpFolder
	{
		var $DB_KEY="";
		var $dir_path = "";

		function pd_ftpFolder( $DB_KEY )
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

			usort( $files, "pd_ftpFolder_sort" );

			return $files;
		}
	}

?>