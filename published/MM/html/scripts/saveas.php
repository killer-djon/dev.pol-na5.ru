<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	//
	// Page variables setup
	//

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$invalidField = null;

	$curMMF_ID = base64_decode( $MMF_ID );
	$curFolderID = $curMMF_ID;

	$action = ACTION_NEW;

	$btnIndex = getButtonIndex( array( 'savebtn', 'cancelbtn' ), $_POST, false );

	if ( !isset($STATUS) || ( $STATUS != MM_STATUS_DRAFT && $STATUS != MM_STATUS_TEMPLATE ) )
		$STATUS = MM_STATUS_DRAFT;

	switch ($btnIndex)
	{

		case 'savebtn':

					$documents[] = $MMM_ID;

					$destMMF_ID = base64_decode( $MMF_ID );

					$callbackParams = array( 'mmStrings'=>$mmStrings, 'setMsgStatus'=>$STATUS );

					$res = $mm_treeClass->copyMoveDocuments( $documents, $destMMF_ID, TREE_COPYDOC, $currentUser, $kernelStrings,
																"mm_onAfterCopyMoveMessage", "mm_onCopyMoveNote", $callbackParams,
																true, true );
					if ( PEAR::isError($res) )
						$errorStr = $res->getMessage();
					else
					{
						$mm_treeClass->setUserDefaultFolder( $currentUser, $destMMF_ID, $kernelStrings );
						redirectBrowser( PAGE_MM_ADDMODMESSAGE, array( 'MMF_ID'=>$MMF_ID, 'MMM_ID'=>$mm_lastID, 'action'=>ACTION_EDIT ) );
					}

					break;

		case 'cancelbtn':

					if ( !isset($opener) )
						redirectBrowser( PAGE_MM_MAILMASTER, array() );
					else
						redirectBrowser( PAGE_MM_ADDMODMESSAGE, array( 'MMM_ID'=>$MMM_ID, 'action'=>ACTION_EDIT ) );

					break;
	}

	switch (true)
	{
		case true :
					$folderInfo = $mm_treeClass->getFolderInfo( $curMMF_ID, $kernelStrings );
					if ( PEAR::isError($folderInfo) ) {
						$fatalError = true;
						$errorStr = $folderInfo->getMessage();

						break;
					}

					$rights = $mm_treeClass->getIdentityFolderRights( $currentUser, $curMMF_ID, $kernelStrings );
					if ( PEAR::isError($rights) ) {
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					if ( $rights < TREE_WRITEREAD ) {
						$fatalError = true;
						$errorStr = $mmStrings['amn_screen_norights_message'];

						break;
					}

					$minimalRights = TREE_WRITEREAD;
					$supressID = null;
					$showRootFolder = false;
					$supressChildren = true;
					$suppressParent = false;
					$access = null;
					$hierarchy = null;
					$deletable = null;

					$folders = $mm_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
															$access, $hierarchy, $deletable, $minimalRights, $supressID,
															$supressChildren, $suppressParent, $showRootFolder );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					foreach ( $folders as $fMMF_ID=>$folderData )
					{
						$encodedID = base64_encode($fMMF_ID);
						$folderData->curID = $encodedID;
						$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

						$params = array();

						$folders[$fMMF_ID] = $folderData;
					}



					if ( !isset($edited) || !$edited )
					{
						$PAGE_ATTACHED_FILES = null;
						$PAGE_DELETED_FILES = null;

						$message = new mm_message( $mm_message_data_schema );

						if ( PEAR::isError($ret = $message->loadEntry($MMM_ID, $kernelStrings, $mmStrings ) ) )
						{
							$fatalError = true;
							$errorStr = $ret->getMessage();

							break;
						}

						$messageData = $message->getValuesArray();
						$RECORD_FILES = $messageData["MMM_ATTACHMENT"];
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $MM_APP_ID );

	$title = ( $STATUS == MM_STATUS_DRAFT ) ? $mmStrings['sa_screen_copy_title'] : $mmStrings['sa_screen_tpl_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_MM_SAVEAS );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );

	$preproc->assign( "mmStrings", $mmStrings );

	$preproc->assign( "curMMF_ID", base64_decode( $MMF_ID ));

	$preproc->assign( "folders", $folders );
	$preproc->assign( "folderCount", count($folders) );
	$preproc->assign( "hierarchy", $hierarchy );

	if ( !$fatalError )
	{
		$preproc->assign( "MMM_ID", $messageData['MMM_ID'] );
		$preproc->assign( "subject", $messageData['MMM_SUBJECT'] == '' ? $mmStrings['app_nosubject_text']:  $messageData['MMM_SUBJECT'] );
	}

	if ( isset($opener))
		$preproc->assign( "opener", $opener );

	$preproc->assign( "STATUS", $STATUS );

	$preproc->display( "saveas.htm" );
?>