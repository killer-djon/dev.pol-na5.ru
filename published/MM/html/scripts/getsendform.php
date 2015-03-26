<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . '/published/MM/mm.php';

	//
	// Authorization
	//
	$errorStr = null;
	$SCR_ID = 'MM';
	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	//
	// Page variables setup
	//
	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];

	$senders = mm_getSenders( $currentUser, $kernelStrings );
	if( PEAR::isError($senders) ) exit('Error: ' . $senders->getMessage());
	$senderNames = array();
	$senderIds = array();
	foreach( $senders as $sender )
	{
		$senderNames[] = $sender['MMS_FROM']." <".$sender['MMS_EMAIL'].">";
		$senderIds[] = $sender['MMS_ID'];
	}

	$FGL = mm_prepareFGLList( $currentUser, $kernelStrings, $mmStrings );
	$contactList = $lists = array();

	if( empty( $currentObject ) )
		$currentObject = false;

	$contactsForInclude = array();
	$i = 0;
	foreach( $FGL as $obj )
	{
		if( $obj['TYPE'] == CM_OT_FOLDERS )
		{
			$foldersList[] = $obj;
			$cont = mm_listObjects( $obj['ID'], $currentUser, $kernelStrings, $mmStrings );

			if( $currentObject == $obj['ID'] || !$currentObject || $currentObject == 'all folders' )
			{
				foreach( $cont as $item )
				{
					$item['NAME'] = htmlspecialchars( $item['NAME'] );

					if( $searchString )
					{
						if( strpos( $item['NAME'], $searchString ) !== false )
						{
							$item['NAME'] = preg_replace(
								"/$searchString/i",
								'<span class="searchResults">'.$searchString.'</span>',
								$item['NAME'] );
						  $contactsForInclude[] = $item['NAME'];
						}
					}
					else
					  $contactsForInclude[] = $item['NAME'];
				}
			}
			$i = 1;
		}
		elseif( $obj['TYPE'] == CM_OT_LISTS )
			$lists[$obj['NAME']] = mm_listObjects( $obj['ID'], $currentUser, $kernelStrings, $mmStrings );
	}

	foreach( $lists as $item )
		foreach( $item as $key=>$val )
			$sendLists[$key] = $val;

	natcasesort( $contactsForInclude );
	$contactsForInclude = array_flip( $contactsForInclude );

	if( empty( $currentPage ) )
		$currentPage = 0;

	if( ( $currentPage + 1 ) * CONTACTS_PER_PAGE >= count( $contactsForInclude ) )
		$showMore = false;
	else
		$showMore = true;

	$offset = $currentPage * CONTACTS_PER_PAGE;
	$contactsForInclude = array_slice( $contactsForInclude, $offset, CONTACTS_PER_PAGE, true );

	$access = null;
	$hierarchy = null;
	$deletable = null;
	$folders = $mm_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
							$access, $hierarchy, $deletable, $minimalRights, $supressID,
							$supressChildren, $suppressParent, $showRootFolder );
	if( PEAR::isError($folders) )
		exit( 'Error: ' . $folders->getMessage() );
	foreach ( $folders as $id=>$folderData )
	{
		$encodedID = base64_encode($id);
		$folderData->curID = $encodedID;
		$folderData->OFFSET_STR = str_replace(' ', '&nbsp;&nbsp;', $folderData->OFFSET_STR);
		$folders[$id] = $folderData;
	}
	$currentFolder = base64_decode( getAppUserCommonValue( $MM_APP_ID, $currentUser, 'MMS_CURRENTFOLDER', null, $readOnly ) );

	$langTrans = array( 'rus'=>'ru', 'gem'=>'de' );
	if( empty( $langTrans[$language] ) ) $lang = 'en';
	else $lang = $langTrans[$language];

	clearUploadedFiles();

	for( $i=0; $i<24; $i++ )
		$timeArray[] = sprintf( "%02d:00", $i );

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( 'kernelStrings', $kernelStrings );
	$preproc->assign( 'mmStrings', $mmStrings );

	$preproc->assign( 'senderNames', $senderNames );
	$preproc->assign( 'senderIds', $senderIds );

	$preproc->assign( 'sendLists', $sendLists);

	$preproc->assign( 'folders', $folders );
	$preproc->assign( 'currentFolder', $currentFolder );

	$preproc->assign( 'EDITOR_LANGUAGE', $lang );

	$preproc->assign('serverTimeNote', sprintf( $mmStrings['sm_send_servertime_note'],
		'<b>'.convertToDisplayDateTime( convertToSqlDateTime(time()), false, true, true ).'</b>' ) );
	$preproc->assign( 'timeArray', $timeArray );

	$preproc->assign( 'foldersList', $foldersList );
	$preproc->assign( 'currentObject', $currentObject );
	$preproc->assign( 'contactsForInclude', $contactsForInclude );
	$preproc->assign( 'currentPage', $currentPage );
	$preproc->assign( 'showMore', $showMore );

	$preproc->display( 'getsendform.htm' );

?>