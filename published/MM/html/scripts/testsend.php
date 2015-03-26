<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );
	require_once( WBS_DIR."/published/CM/cm.php" );

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

	$messagesSent = false;

	$btnIndex = getButtonIndex( array('sendbtn', 'savebtn', 'cancelbtn'), $_POST, false );

	$statArray = array();

	set_time_limit( 3600 );

	$maxRec = getApplicationVariable( $DB_KEY, $MM_APP_ID, MM_OPT_RECIPIENTS_LIMIT, $kernelStrings);
	$dailyMaxRec = getApplicationVariable( $DB_KEY, $MM_APP_ID, MM_OPT_DAILY_RECIPIENTS_LIMIT, $kernelStrings);
	$sendNowMaxRec = getApplicationVariable( $DB_KEY, $MM_APP_ID, MM_OPT_SENDNOW_RECIPIENTS_LIMIT, $kernelStrings);

	switch( true )
	{
		case true:
					if ( !isset( $MMF_ID ) || $MMF_ID=="" )
					{
						$fatalError = true;
						$errorStr = $kernelStrings['app_treefoldernotfound_message'];

						break;
					}

					$curMMF_ID = base64_decode( $MMF_ID );

					$folderInfo = $mm_treeClass->getFolderInfo( $curMMF_ID , $kernelStrings );
					if ( PEAR::isError($folderInfo) ) {
						$fatalError = true;
						$errorStr = $folderInfo->getMessage();

						break;
					}

					$rights = $mm_treeClass->getIdentityFolderRights( $currentUser, $curMMF_ID, $kernelStrings );
					if ( PEAR::isError($rights) )
					{
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					if ( !UR_RightsObject::CheckMask( $rights, TREE_READ ) )
					{
						$fatalError = true;
						$errorStr = $mmStrings['app_access_violation_error'];

						break;
					}
	}

	switch ($btnIndex)
	{
		case 'savebtn':

				if ( $fatalError )
					break;

				$typeDescription = $fieldsPlainDesc = null;
				$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc );

				$ContactCollection->loadAsArrays  = true;

				// Get mail recipients' contact list
				$contactId = isset($messageData['SENDTOME']) ? array( db_query_result( $qr_selectUserContact, DB_FIRST, array('U_ID'=>$currentUser) ) ) : null;

				$list = $ContactCollection->loadMixedEntityContactWithEmails( null, null, null, $contactId, 'C_ID', $kernelStrings, true );
				if ( PEAR::isError( $list ) )
				{
					$errorStr = $list->getMessage();
					break;
				}

				// Check if recipients number does not exceed limitations
				$numEmails = count( $ContactCollection->items ) + count( mm_mailSender::_parseRecipientList( $messageData['TOMORE'] ) );

				if ( $numEmails == 0 )
				{
					$errorStr = $mmStrings['sm_test_noemail_error'];
					break;
				}

				if ( $numEmails > 5 )
				{
					$errorStr = $mmStrings['sm_test_emailcount_error'];
					break;
				}

				// Prepare Sender object

				$sender = new mm_mailSender();

				$sender->recipients = $ContactCollection->items;
				$sender->docList = unserialize( base64_decode( $doclist ));

				$textService = new ContactsTextService($kernelStrings, $language);
				$textService->ListAvailableVariables( $kernelStrings, array(VS_CONTACT, VS_CURRENT_USER, VS_COMPANY) );
				$sender->textService = $textService;

				$params = array();
				$params['mmStrings'] = $mmStrings;
				$params['kernelStrings'] = $kernelStrings;
				$params['U_ID'] = $currentUser;

				$res = $sender->loadFromArray( $messageData, $kernelStrings, true, $params );
				if ( PEAR::isError($res) )
				{
					$errorStr = $res->getMessage();
					$invalidField = $res->getUserInfo();

					break;
				}

				$sendNow =  true;

				$disableUnsibscribeFooter = mm_getLimitationOption( MM_OPT_DISABLE_UNSUBSCRIBE_FOOTER, $kernelStrings );

				foreach( $sender->docList as $msgID )
				{
					// Loading message data

					$message = new mm_message( $mm_message_data_schema );
					if ( PEAR::isError($ret = $message->loadEntry( $msgID, $kernelStrings, $mmStrings ) ) )
					{
						$errorStr = $ret->getMessage();
						break 2;
					}

					if ( $message->MMM_STATUS != MM_STATUS_DRAFT )
					{
						$statArray[] = array( "MESSAGE"=>$message->getValuesArray(), "NOTSENT"=>"Not Sent - wrong status." );
						continue;
					}

					if ( $message->MMF_ID != $curMMF_ID )
					{
						$statArray[] = array( "MESSAGE"=>$message->getValuesArray(), "NOTSENT"=>$mmStrings['app_access_violation_error'] );
						continue;
					}

					// Prepare unsubscribe footer if needed

					$MMM_CONTENT = $message->MMM_CONTENT;

					if ( /*!is_null($disableUnsibscribeFooter) && */ $disableUnsibscribeFooter != 1)
					{
						$message->MMM_CONTENT .= '<small>';
						$message->MMM_CONTENT .= nl2br( $mmStrings['app_mail_footer'] );
						$message->MMM_CONTENT .= '<a href="'. MM_VAR_UNSIBSCRIBE_URL.'">'.$mmStrings['app_mail_footer_link'].'</a>';
						$message->MMM_CONTENT .= '</small>';
					}

					$sender->filesDir = mm_getNoteAttachmentsDir( $message->MMM_ID, MM_ATTACHMENTS );
					$sender->imagesDir = mm_getNoteAttachmentsDir( $message->MMM_ID, MM_IMAGES );
					$sender->imageUri = dirname( getCurrentAddress() ) . "/" . prepareURLStr( PAGE_MM_GETSENTIMG, array( 'DB_KEY'=>base64_encode( $DB_KEY ), 'messageId'=>$message->MMM_ID ) );
  					$sender->includeImages = ( isset( $messageData['IMAGES'] ) && $messageData['IMAGES'] == 1 ) ? true : false;

					$sender->save_stat = false;

  					$sendRes = $sender->send( $currentUser, $message, $includeImages, $kernelStrings, $mmStrings );

				}

				$messagesSent = true;

		case 'cancelbtn':

				$docsList = unserialize( base64_decode(  $doclist ));

				if ( isset( $opener ) && $opener == PAGE_MM_ADDMODMESSAGE && isset($docsList[0]) )
					redirectBrowser(  PAGE_MM_ADDMODMESSAGE, array( ACTION=>ACTION_EDIT, "MMM_ID"=>$docsList[0], "MMF_ID"=>$MMF_ID ) );
				else
					redirectBrowser( PAGE_MM_MAILMASTER, array() );

		case 'sendcancelbtn':
				if ( $fatalError )
					break;

				$_SESSION['CONTACTSLIST']  = null;

				$messageData = $_SESSION['MESSAGEDATA'];

				$includedContacts = unserialize( base64_decode( $_SESSION['INCLUDEDCONTACTS'] ) );

				$_SESSION['INCLUDEDCONTACTS'] = $_SESSION['MESSAGEDATA'] = null;
				$informationGathered = false;

				$edited = 1;

				break;
	}

	switch (true)
	{
		case true :
					if ( $fatalError )
						break;

					if ( !isset($edited) )
					{
						$messageData = array();
						$messageData['PRIORITY'] = MM_PRIORITY_NORMAL;
						$messageData['SENDTOME'] = 1;
					}

					// Prepare priority values
					//
					$priorityValues = array( MM_PRIORITY_HIGH, MM_PRIORITY_NORMAL, MM_PRIORITY_LOW );
					$priorityNames = array( $mmStrings['app_high_priority'], $mmStrings['app_normal_priority'], $mmStrings['app_low_priority'] );

					$senders = mm_getSenders( $currentUser, $kernelStrings );
					if ( PEAR::isError($senders) )
					{
						$fatalError = true;
						$errorStr = $senders->getMessage();

						break;
					}

					$senderNames = array();
					$senderIds = array();

					foreach( $senders as $sender )
					{
						$senderNames[] = $sender['MMS_FROM']." <".$sender['MMS_EMAIL'].">";
						$senderIds[] = $sender['MMS_ID'];
					}

					$noSenderEmail = !count($senders);

					if ( $noSenderEmail )
						$errorStr = $mmStrings['sm_nosenderemail_message'];

					$messageList = array();
					$msgs = unserialize( base64_decode($doclist) );
					foreach ( $msgs as $msgID )
					{
						$message = new mm_message( $mm_message_data_schema );

						if ( PEAR::isError($ret = $message->loadEntry( $msgID, $kernelStrings, $mmStrings ) ) )
						{
							$fatalError = true;
							$errorStr = $ret->getMessage();

							break 2;
						}

						$tempData = $message->getValuesArray();
						$tempData['MMM_SUBJECT'] = ( $tempData['MMM_SUBJECT'] == '' ) ? $mmStrings['app_nosubject_text'] :  $tempData['MMM_SUBJECT'];

						if ( $tempData['MMM_STATUS'] != MM_STATUS_DRAFT )
							continue;

						if ( $tempData['MMF_ID'] != $curMMF_ID )
						{
							$fatalError = true;
							$errorStr = $mmStrings['app_access_violation_error'];
							break 2;
						}

						if ( $tempData['MMM_STATUS'] != MM_STATUS_DRAFT )
							continue;


						$messageList[] = $tempData;
					}

					if ( count( $messageList ) == 0  && count( $msgs ) > 0 )
					{
						$fatalError = true;
						$errorStr = $mmStrings['sm_onlydraft_error_text'];
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $mmStrings['sm_test_screen_name'] );
	$preproc->assign( FORM_LINK, PAGE_MM_TESTSEND );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( "mmStrings", $mmStrings );

	$preproc->assign( "limitStr", nl2br(getUploadLimitInfoStr( $locStrings )) );

	$preproc->assign( "messagesSent", $messagesSent );

	$preproc->assign( "timeArray", $timeArray );
	$preproc->assign( "MMF_ID", $MMF_ID );

	if ( isset($opener) )
		$preproc->assign( "opener", $opener );

	if ( !$fatalError )
	{
		$preproc->assign( 'statArray', $statArray );

		$preproc->assign( "messageList", $messageList );
		$preproc->assign( "messageCount", count( $messageList ) );

		$preproc->assign( "messageData", $messageData );

		$preproc->assign( "priorityValues", $priorityValues );
		$preproc->assign( "priorityNames", $priorityNames );

		$preproc->assign( "selectedPriority", isset( $selectedPriority ) ? $selectedPriority : $messageData['PRIORITY'] );

		$preproc->assign( "doclist", $doclist );
		$preproc->assign( "doclistCount", count( $msgs ) );

		$preproc->assign( "senderNames", $senderNames );
		$preproc->assign( "senderIds", $senderIds );

		$preproc->assign( "noSenderEmail", $noSenderEmail );
		$preproc->assign( "myEmail", !isset( $senders[-1] ) ? $mmStrings['sm_test_email_missing_text'] : $senders[-1]["MMS_EMAIL"] );


		if ( isset($edited) )
			$preproc->assign( "edited", $edited );
	}

	$preproc->display( "testsend.htm" );
?>
