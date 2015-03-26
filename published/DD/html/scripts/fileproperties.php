<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/DD/dd.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CT";

	pageUserAuthorization( $SCR_ID, $DD_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$ddStrings = $dd_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	$btnIndex = getButtonIndex( array( BTN_CANCEL, BTN_SAVE, 'deletebtn' ), $_POST );

	switch ($btnIndex) {
		case 1 :
				$curDL_ID = base64_decode( $DL_ID );

				$res = dd_modifyFile( $curDL_ID, $currentUser, prepareArrayToStore($fileData), $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					if ( $res->getCode() == ERRCODE_INVALIDFIELD )
						$invalidField = $res->getUserInfo();

					break;
				}
		case 0 : redirectBrowser( PAGE_DD_CATALOG, array( PAGES_CURRENT=>$currentPage ) );
		case 2 :
				if ( !isset($deleteVersion) )
					break;

				$curDL_ID = base64_decode( $DL_ID );

				dd_deleteFileVersions( $curDL_ID, $deleteVersion, $kernelStrings );
	}

	switch (true) {
		case true :
					$curDL_ID = base64_decode( $DL_ID );
					$docData = dd_getDocumentData( $curDL_ID, $kernelStrings );
					if( PEAR::isError( $docData ) ) {
						$errorStr = $docData->getMessage();
						$fatalError = true;
						break;
					}

					$rights = $dd_treeClass->getIdentityFolderRights( $currentUser, $docData->DF_ID, $kernelStrings );
					if ( PEAR::isError($rights) )
						return $rights;

					$readOnly = !UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD );
					$fullRights = UR_RightsObject::CheckMask( $rights, TREE_READWRITEFOLDER );

					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
					if ( $versionControlEnabled && !$readOnly ) {
						$readOnly = $docData->DL_CHECKSTATUS == DD_CHECK_OUT && $docData->DL_CHECKUSERID != $currentUser;
					}

					$editFileURL = prepareURLStr( PAGE_DD_MODIFYFILE, array('DL_ID'=>$DL_ID, PAGES_CURRENT=>$currentPage) );

					if ( $versionControlEnabled ) {
						$fileHistory = dd_getFileHistory( $curDL_ID, $kernelStrings );
						if ( PEAR::isError($fileHistory) ) {
							$errorStr = $fileHistory->getMessage();
							$fatalError = true;
							break;
						}

						foreach ( $fileHistory as $key=>$value ) {
							$value['DLH_DATETIME'] = convertToDisplayDateTime($value['DLH_DATETIME'], false, true, true );
							$value['DLH_SIZE'] = formatFileSizeStr( $value['DLH_SIZE'] );
							$noCache = base64_encode( uniqid( "file" ) );
							$value['FILE_URL'] = prepareURLStr( PAGE_DD_GETHISTORYFILE, array('DL_ID'=>$DL_ID, 'DLH_VERSION'=>base64_encode($value['DLH_VERSION']), 'nocache'=>$noCache ) );
							$fileHistory[$key] = $value;
						}
					}

					if ( strlen($docData->DL_CHECKUSERID) )
						$checkedUserName = getUserName($docData->DL_CHECKUSERID);
					else
						$checkedUserName = $docData->DL_MODIFYUSERNAME;

					$fileVersion = dd_getFileVersion( $curDL_ID, $kernelStrings );

					$fileData = dd_processFileListEntry($docData);
					if ($fileData->DL_CHECKDATETIME)
						$fileData->DL_CHECKDATETIME = convertToDisplayDateTime($fileData->DL_CHECKDATETIME, false, true, true );
					$fileName = $fileData->DL_FILENAME;
					$fileData->CHECKOUTMESSAGE = sprintf( "%s %s %s", $ddStrings['add_screen_checkout_label'], $checkedUserName, $fileData->DL_CHECKDATETIME );

					$DL_FILETYPE = $fileData->DL_FILETYPE;
					$DL_FILESIZE = $fileData->DL_FILESIZE;
					$DL_UPLOADDATETIME = $fileData->DL_UPLOADDATETIME;
					$DL_UPLOADUSERNAME = $fileData->DL_UPLOADUSERNAME;
					
					
					$keys = array_keys($fileHistory);
					for ($i = 0; $i < sizeof($keys); $i++) {
						$cKey = $keys[$i];
						$cRow = $fileHistory[$cKey];
						if ($i < sizeof($keys)-1) {
							$nextRow = $fileHistory[$keys[$i+1]];
							$cRow["DLH_DATETIME"] = $nextRow["DLH_DATETIME"];
							$cRow["DLH_USERNAME"] = $nextRow["DLH_USERNAME"];
							//$cRow["DL"] = $nextRow["DLH_DATETIME"];
						} else {
							$cRow["DLH_DATETIME"] = $DL_UPLOADDATETIME;
							$cRow["DLH_USERNAME"] = $DL_UPLOADUSERNAME;
						}
						$fileHistory[$cKey] = $cRow;
					}

					$fileData = (array)$fileData;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['fpp_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_FILEPROPERTIES );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( PAGES_CURRENT, $currentPage );
	$preproc->assign( "ddStrings", $ddStrings );
	$preproc->assign( "DL_ID", $DL_ID );

	if ( !$fatalError ) {
		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		$preproc->assign( "fileData", $fileData );
		$preproc->assign( "fileName", $fileName );

		$preproc->assign( "readOnly", $readOnly );
		$preproc->assign( "fileVersion", $fileVersion );
		$preproc->assign( "checkedUserName", $checkedUserName );
		$preproc->assign( "fullRights", $fullRights );

		$preproc->assign( "versionControlEnabled", $versionControlEnabled );
		if ( $versionControlEnabled ) {
			$preproc->assign( "showFileHistory", count($fileHistory) );
			$preproc->assign( "fileHistory", $fileHistory );
		}

		$preproc->assign( "DL_FILETYPE", $DL_FILETYPE );
		$preproc->assign( "DL_FILESIZE", $DL_FILESIZE );
		$preproc->assign( "DL_UPLOADDATETIME", $DL_UPLOADDATETIME );
		$preproc->assign( "DL_UPLOADUSERNAME", $DL_UPLOADUSERNAME );
		$preproc->assign( "editFileURL", $editFileURL );
	}

	$preproc->display( "fileproperties.htm" );
?>