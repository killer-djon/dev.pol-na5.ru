<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	// 
	// Page variables setup
	//

	$locStrings = $loc_str[$language];
	$qn_locStrings = $qn_loc_str[$language];
	$invalidField = null;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL ), $_POST );

	switch ($btnIndex) {
		case 0 : 
				if ( !isset($userdata) ) $userdata = array();

				$folderRights = array();
				if ( isset($rights) && is_array($rights) )
					foreach( $rights as $QNF_ID=>$curFolderRights ) {
						if ( isset($curFolderRights[QN_WRITEREAD]) && $curFolderRights[QN_WRITEREAD] )
							$folderRights[$QNF_ID] = QN_WRITEREAD;
						elseif ( isset($curFolderRights[QN_READONLY]) && $curFolderRights[QN_READONLY] )
							$folderRights[$QNF_ID] = QN_READONLY;
					}

				$res = qn_modifyUserRights( $locStrings, $U_ID, $folderRights );
				if ( PEAR::isError( $res ) ) {
					$fatalError = true;
					$errorStr = $res->getMessage();

					break;
				}
				
				redirectBrowser( PAGE_QN_QUICKNOTESMANAGER, array('firstIndex'=>$firstIndex) );
		case 1 :redirectBrowser( PAGE_QN_QUICKNOTESMANAGER, array('firstIndex'=>$firstIndex) );
	}

	switch (true) {
		case true:
					$userName = getUserName( $U_ID, true );

					if ( !isset($edited) || !$edited ) {
						$rights = qn_listUsersRights( $locStrings, $U_ID );
						if ( PEAR::isError( $rights ) ) {
							$fatalError = true;
							$errorStr = $rights->getMessage();

							break;
						}

						foreach( $rights as $key => $folderRights ) {
							foreach( $folderRights as $QNF_ID => $folderRightsValue ) {
								if ( $folderRightsValue == QN_READONLY )
									$folderRightsValue = array( QN_READONLY=>1 );
								elseif ( $folderRightsValue == QN_WRITEREAD || $folderRightsValue == QN_READWRITEFOLDER )
									$folderRightsValue = array( QN_READONLY=>1, QN_WRITEREAD=>1 );

								$folderRights[$QNF_ID] = $folderRightsValue;
							}

							$rights[$key] = $folderRights;
						}
					}

					// Load folders
					//
					$folders = qn_listFolders( $locStrings );
					if ( PEAR::isError( $folders ) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					foreach( $folders as $key=>$folderData ) {
						$folderData['QNF_NAME'] = prepareStrToDisplay( $folderData['QNF_NAME'], true );

						$folders[$key] = $folderData;
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qn_locStrings[12] );
	$preproc->assign( FORM_LINK, PAGE_QN_MODIFYUSERRIGHTS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qnStrings", $qn_locStrings );
	$preproc->assign( "firstIndex", $firstIndex );
	$preproc->assign( HELP_TOPIC, "userrights.htm");

	if ( !$fatalError ) {
		$preproc->assign( "U_ID", $U_ID );
		$preproc->assign( "userName", $userName );
		$preproc->assign( "folders", $folders );
		$preproc->assign( "rights", $rights );

		if ( isset($userdata) )
			$preproc->assign( "userdata", $userdata );
	}

	$preproc->display( "modifyuserrights.htm" );
?>