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

	$btnIndex = getButtonIndex( array( BTN_CANCEL, BTN_SAVE ), $_POST );

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
		case 0 : redirectBrowser( PAGE_DD_FILEPROPERTIES, array('DL_ID'=>$DL_ID, PAGES_CURRENT=>$currentPage) );
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

					$versionControlEnabled = dd_versionControlEnabled($kernelStrings);
					if ( $versionControlEnabled && !$readOnly ) {
						$readOnly = $docData->DL_CHECKSTATUS == DD_CHECK_OUT && $docData->DL_CHECKUSERID != $currentUser;
					}

					if ( $readOnly ) {
						$fatalError = true;
						$errorStr = $ddStrings['fpp_norights_message'];

						break;
					}

					if ( !isset($edited) || $readOnly ) {
						$fileData = dd_processFileListEntry($docData);
						$fileName = $fileData->DL_FILENAME;

						$DL_FILETYPE = $fileData->DL_FILETYPE;
						$DL_FILESIZE = $fileData->DL_FILESIZE;
						$DL_UPLOADDATETIME = $fileData->DL_UPLOADDATETIME;
						$DL_UPLOADUSERNAME = $fileData->DL_UPLOADUSERNAME;

						$fileData = (array)$fileData;
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['mf_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_DD_MODIFYFILE );
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

		$preproc->assign( "DL_FILETYPE", $DL_FILETYPE );
		$preproc->assign( "DL_FILESIZE", $DL_FILESIZE );
		$preproc->assign( "DL_UPLOADDATETIME", $DL_UPLOADDATETIME );
		$preproc->assign( "DL_UPLOADUSERNAME", $DL_UPLOADUSERNAME );
	}

	$preproc->display( "modifyfile.htm" );
?>