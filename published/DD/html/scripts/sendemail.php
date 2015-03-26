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
	$noSenderEmail = false;

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	switch ($btnIndex) {
		case 0: 
				if ( !isset($includedContacts) )
					$includedContacts = array();
		
				if (get_magic_quotes_gpc()) {
					$messageData["SUBJECT"] = stripslashes($messageData["SUBJECT"]);
					$messageData["MESSAGE"] = stripslashes($messageData["MESSAGE"]);
				}

				if ( !isset($messageData['COMPRESS']) )
					$messageData['COMPRESS'] = '0';

				$sender = new dd_fileSender();
				$sender->recipients = $includedContacts;
				$sender->fileList = unserialize( base64_decode($doclist) );

				$params = array();
				$params['ddStrings'] = $ddStrings;
				$params['kernelStrings'] = $kernelStrings;
				$params['U_ID'] = $currentUser;

				$res = $sender->loadFromArray( $messageData, $kernelStrings, true, $params );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

				$res = $sender->send( $currentUser, $kernelStrings, $ddStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}

		case 1:
				redirectBrowser( PAGE_DD_CATALOG, array() );
	}

	switch (true) {
		case true :
					if ( !isset($edited) ) {
						$messageData = array();
						$messageData['PRIORITY'] = 1;
					}

					// Prepare contacts list
					//
					$contacts = dd_listContacts( $currentUser, $kernelStrings );
					if ( PEAR::isError($contacts) ) {
						$fatalError = true;
						$errorMessage = $contacts->getMessage();
						break;
					}

					if ( !isset($edited) ) {
						$notincludedContacts = array_keys( $contacts ); 
						$includedContacts = array();
					} else {
						if ( !isset($notincludedContacts) )
							$notincludedContacts = array();

						if ( !isset($includedContacts) )
							$includedContacts = array();

						$notincludedContacts = array_diff( array_keys($contacts), $includedContacts );
					}

					foreach( $includedContacts as $key=>$value )
						$includedContactsNames[] = $contacts[$value];

					foreach( $notincludedContacts as $key=>$value )
						$notincludedContactsNames[] = $contacts[$value];

					// Prepare priority values
					//
					$priorityValues = array( 2, 1, 0 );
					$priorityNames = array( $ddStrings['sm_hipriority_item'], $ddStrings['sm_normalpriority_item'], $ddStrings['sm_lowpriority_item'] );

					// Prepare file list
					//
					$files = unserialize( base64_decode($doclist) );

					$fileList = array();
					$totalSize = 0;
					foreach ( $files as $DL_ID ) {
						$docData = dd_getDocumentData( $DL_ID, $kernelStrings );
						$totalSize += $docData->DL_FILESIZE;
						$docData->DL_FILESIZE = formatFileSizeStr($docData->DL_FILESIZE);

						$fileList[] = $docData;
					}

					$totalFilesInfo = sprintf( $ddStrings['sm_totalfileinfo_label'], count($fileList), formatFileSizeStr($totalSize) );

					$archivesSupported = dd_archiveSupported();

					// Load email settings
					//
					$emailMode = null;
					$emailName=  null;
					$emailAddress = null;
					dd_getEmailSettingsParams( $emailMode, $emailName, $emailAddress, $kernelStrings );

					if ( $emailMode != DD_EMAILPARAMS_GLOBAL ) {
						$userData = dd_getUserEmail( $currentUser, $kernelStrings );
						$emailName=  $userData['name'];
						$emailAddress = $userData['email'];

						$noSenderEmail = !strlen($emailAddress);
					}

					if ( $noSenderEmail )
						$errorStr = $ddStrings['sm_nosenderemail_message'];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $DD_APP_ID );

	$preproc->assign( PAGE_TITLE, $ddStrings['sm_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_DD_SENDEMAIL );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "ddStrings", $ddStrings );

	if ( !$fatalError ) {
		$preproc->assign( "priorityValues", $priorityValues );
		$preproc->assign( "priorityNames", $priorityNames );
		$preproc->assign( "messageData", $messageData );
		$preproc->assign( "doclist", $doclist );
		$preproc->assign( "archivesSupported", $archivesSupported );

		$preproc->assign( "includedContacts", $includedContacts );
		$preproc->assign( "notincludedContacts", $notincludedContacts );

		if ( isset($includedContactsNames) )
			$preproc->assign( "includedContactsNames", $includedContactsNames );

		if ( isset($notincludedContactsNames) )
			$preproc->assign( "notincludedContactsNames", $notincludedContactsNames );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		$preproc->assign( "fileList", $fileList );
		$preproc->assign( "totalFilesInfo", $totalFilesInfo );
		$preproc->assign( "emailName", $emailName );
		$preproc->assign( "emailAddress", $emailAddress );
		$preproc->assign( "noSenderEmail", $noSenderEmail );
	}

	$preproc->display( "sendemail.htm" );
?>