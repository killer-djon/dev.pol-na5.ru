<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . 'published/MM/mm.php';
	require_once WBS_DIR . 'published/CM/cm.php';
	require_once WBS_DIR . 'kernel/classes/class.eventdispatcher.php';

	//
	// Authorization
	//
	pageUserAuthorization( 'MM', $MM_APP_ID, false );
	//
	// Page variables setup
	//
	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$errorStr = '';
	$sent = '';

	switch( $action )
	{
		case 'send':
			$htmlPage = 'messagesent.htm';

			if( $toDraft && (! $messageData['MMM_SAVE'] || $messageData['MMM_FOLDER'] == -3 ) )
			{
				$errorStr = 'No folder selected';
				break;
			}

			$inputLists = array();
			if( $lists = trim( $messageData['MMM_LISTS'] ) )
				$lists = explode( ',', $lists );
			foreach( $lists as $lst )
				if( $lst = trim( $lst ) )
					$inputLists[$lst] = 1;

			$FGL = mm_prepareFGLList( $currentUser, $kernelStrings, $mmStrings );
			$contactList = $lists = $includedContacts = array();
			foreach( $FGL as $obj )
			{
				if( $obj['TYPE'] == CM_OT_FOLDERS && $obj['NAME'] != '_Unsorted' )
					$contactList[$obj['NAME']] = mm_listObjects( $obj['ID'], $currentUser, $kernelStrings, $mmStrings );
				if( $obj['TYPE'] == CM_OT_LISTS )
					$lists[$obj['NAME']] = mm_listObjects( $obj['ID'], $currentUser, $kernelStrings, $mmStrings );
			}

			$listsIsSet = false;
			foreach( $lists as $item )
				foreach( $item as $key=>$val )
					if( isset( $inputLists[$val['NAME']] ) )
					{
						$includedContacts[] = $key; // $key = $val['ID']
						$listsIsSet = true;
					}

			$inputAddresses = stripslashes($messageData['MMM_TO'] . $messageData['MMM_CC'] . $messageData['MMM_BCC']);
			$acceptedInput = array();

			$numEmails = 0;

			foreach( $contactList as $item )
				foreach( $item as $key=>$val )
					if( strpos( $inputAddresses, $val['NAME'] ) !== false )
					{
						$includedContacts[] = $key;
						$numEmails--;
					}

			$contacts = $groups = $lists = $folders = array();
			foreach( $includedContacts as $value )
			{
				if( strstr( $value, 'CONTACT' ) ) $contacts[] = base64_decode( substr( $value, 7 ) );
				elseif( strstr( $value, CM_OT_USERGROUPS ) ) $groups[] = base64_decode( substr( $value, strlen(CM_OT_USERGROUPS) ) );
				elseif( strstr( $value, CM_OT_LISTS ) ) $lists[] = base64_decode( substr( $value, strlen(CM_OT_LISTS) ) );
			}

			$typeDescription = $fieldsPlainDesc = null;
			$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc );
			$ContactCollection->loadAsArrays  = true;
			$list = $ContactCollection->loadMixedEntityContactWithEmails( $folders, $groups, $lists, $contacts, 'C_ID', $kernelStrings );
			if( PEAR::isError( $list ) )
			{
				$errorStr = $list->getMessage();
				break;
			}
			$numEmails += count( $ContactCollection->items );

			$sent = $to_more = $cc_more = $bcc_more = array();

			$accepted = array();
			if( !empty( $messageData['MMM_TO'] ) )
			{
				$addr = parseAddressString( stripslashes( $messageData['MMM_TO'] ) );
				for( $i=0; $i<count($addr['accepted']); $i++ )
					$to_more[] = $accepted[] = $addr['accepted'][$i][0].' <'.$addr['accepted'][$i][1].'@'.$addr['accepted'][$i][2].'>';
				if($addr['bounced'])
				{
					$sent['BOUNCED'] = $addr['bounced'];
					break;
				}
			}
			if( !empty( $messageData['MMM_CC'] ) )
			{
				$addr = parseAddressString( stripslashes( $messageData['MMM_CC'] ) );
				for( $i=0; $i<count($addr['accepted']); $i++ )
					$cc_more[] = $accepted[] = $addr['accepted'][$i][0].' <'.$addr['accepted'][$i][1].'@'.$addr['accepted'][$i][2].'>';
				if($addr['bounced'])
				{
					$sent['BOUNCED'] = $addr['bounced'];
					break;
				}
			}
			if( !empty( $messageData['MMM_BCC'] ) )
			{
				$addr = parseAddressString( stripslashes( $messageData['MMM_BCC'] ) );
				for( $i=0; $i<count($addr['accepted']); $i++ )
					$bcc_more[] = $accepted[] = $addr['accepted'][$i][0].' <'.$addr['accepted'][$i][1].'@'.$addr['accepted'][$i][2].'>';
				if($addr['bounced'])
				{
					$sent['BOUNCED'] = $addr['bounced'];
					break;
				}
			}
			$tomore = array('TO' => $to_more, 'CC' => $cc_more, 'BCC' => $bcc_more);

			$numEmails += count( $accepted );

			//
			// Check whether send date is in the future
			//
			if( $messageData['WHEN'] == 'later' )
			{
				$whentimestamp = 0;
				if( !( validateInputDate($messageData['WHENDATE'], $whentimestamp, false ) ) ||
						!isTimeStr( $messageData['WHENTIME'] ) )
				{
					$errorStr = $mmStrings['sm_date_select_error'];
					break;
				}
				$parts = explode( ':', $messageData['WHENTIME'] );
				$whentimestamp = $whentimestamp + $parts[0]*3600 + $parts[1]*60;
				if( $whentimestamp <= convertTimestamp2Local( time() ) )
				{
					$errorStr = $mmStrings['sm_date_select_error'];
					break;
				}
			}
			else
				$whentimestamp = time();

			$sentNum = mm_getSentCount( $kernelStrings, $whentimestamp );
			if(PEAR::isError($sentNum))
			{
				$errorStr = $sentNum->getMessage();
				break;
			}

			if( !$numEmails && !$toDraft )
			{
				$errorStr = "Can't find contact(s)";
				break;
			}

			$maxRec = mm_getLimitationOption( MM_OPT_RECIPIENTS_LIMIT, $kernelStrings );
			$dailyMaxRec = mm_getLimitationOption( MM_OPT_DAILY_RECIPIENTS_LIMIT, $kernelStrings );

			if( !is_null($maxRec) && $numEmails > $maxRec )
			{
				$errorStr = mm_getLimitationMessage( MM_OPT_RECIPIENTS_LIMIT, $maxRec, $kernelStrings, $mmStrings );
				break;
			}
			if( !is_null( $dailyMaxRec ) && $numEmails + $sentNum > $dailyMaxRec )
			{
				$errorStr = mm_getLimitationMessage( MM_OPT_DAILY_RECIPIENTS_LIMIT, $dailyMaxRec, $kernelStrings, $mmStrings );
				break;
			}

			$messageData['WHENDATETIME'] = convertToSqlDateTime( $whentimestamp, true );

			// ***********************************************************************
			// Send e-mail
			// ***********************************************************************

			$sendNow = ( $messageData['WHEN'] == 'later' ) ? false : true;

			if( $sendNow && $numEmails > SEND_NOW_MAX_EMAILS )
			{
				if(!$userConfirmSend)
				{
					$userConfirmSend = sprintf($mmStrings['snd_confirm_send_later'], SEND_NOW_MAX_EMAILS);
					break;
				}
				else
				{
					$sendNow = false;
					$messageData['WHEN'] == 'later';
					$messageData['WHENDATETIME'] = convertToSqlDateTime( time(), true );
					$userConfirmSend = '';
				}
			}

			if( $listsIsSet )
			{
				if( !$userConfirmSend )
				{
					$userConfirmSend = sprintf($mmStrings['snd_confirm_send_number'], $numEmails);
					break;
				}
				else
					$userConfirmSend = '';
			}

			$MMM_CONTENT = stripslashes( $messageData['MMM_CONTENT'] );
			$MMM_CONTENT = utf8_urldecode( $MMM_CONTENT );
			$msgBody = str_replace( PAGE_PREVIEW . '?file=', '', $MMM_CONTENT );

			$message = new mm_message( $mm_message_data_schema );

			if( !$MMF_ID = base64_decode( $messageData['MMM_FOLDER'] ) )
				$MMF_ID = 'Outbox';
			$message->MMF_ID = $MMF_ID;
			$message->MMM_SUBJECT = trim( stripslashes( $messageData['MMM_SUBJECT'] ) );

			$senders = mm_getSenders( $currentUser, $kernelStrings );
			if( PEAR::isError( $senders ) )
			{
				$errorStr = $senders->getMessage();
				break;
			}
			$message->MMM_FROM = $senders[$messageData['MMM_FROM']]['MMS_FROM'].
				' <'.$senders[$messageData['MMM_FROM']]['MMS_EMAIL'].'>';

			$message->MMM_CONTENT = $msgBody;

			// enable footer only if subscribe LIST is set
			if( $listsIsSet && $sendNow )
			{
				$disableUnsibscribeFooter = mm_getLimitationOption( MM_OPT_DISABLE_UNSUBSCRIBE_FOOTER, $kernelStrings );
				if( trim( $msgBody ) && ( disableUnsibscribeFooter != 1 ) )
				{
					$message->MMM_CONTENT .= '<br>---<small>'.nl2br($mmStrings['app_mail_footer']);
					$message->MMM_CONTENT .= '<a href="'. MM_VAR_UNSIBSCRIBE_URL.'">'.$mmStrings['app_mail_footer_link'].'</a>';
					$message->MMM_CONTENT .= '</small>';
				}
			}
			if( $toDraft )
				$message->MMM_STATUS = MM_STATUS_DRAFT;
			elseif( $sendNow )
				$message->MMM_STATUS = MM_STATUS_SENT;
			else
				$message->MMM_STATUS = MM_STATUS_PENDING;

	    $message->MMM_PRIORITY = MM_PRIORITY_NORMAL;

			$attachments = getUploads();
			$MMM_ATTACHMENT = '';
			foreach( $attachments as $file )
			{
				$fileinfo = array( 'name'=>$file['name'], 'type'=>$file['type'], 'size'=>$file['size'], 'diskfilename'=>$file['name'] );
				$MMM_ATTACHMENT = addAttachedFile( $MMM_ATTACHMENT, $fileinfo );
			}
			$images = getUploads( 'images' );
			$MMM_IMAGES = '';
			foreach( $images as $file )
				if( strpos( $msgBody, base64_encode( $file['name'] ) ) )
				{
					$fileinfo = array( 'name'=>$file['name'], 'type'=>$file['type'], 'size'=>$file['size'], 'diskfilename'=>$file['name'] );
					$MMM_IMAGES = addAttachedFile( $MMM_IMAGES, $fileinfo );
				}

			$message->MMM_ATTACHMENT = base64_encode( $MMM_ATTACHMENT );
			$message->MMM_IMAGES = base64_encode( $MMM_IMAGES );

			$message->MMM_TO = $tomore['TO'];

			$message->MMM_DATETIME = $messageData['WHENDATETIME'];

			$sender = new mm_mailSender();

			$sender->TOMORE = $tomore;
			$sender->recipients = $ContactCollection->items;

			$sender->SENDER = $messageData['MMM_FROM'];

			$params = array();
			$params['mmStrings'] = $mmStrings;
			$params['kernelStrings'] = $kernelStrings;
			$params['U_ID'] = $currentUser;

			$res = $sender->loadFromArray( $messageData, $kernelStrings, true, $params );
			if( PEAR::isError( $res ) )
			{
				$errorStr = $res->getMessage();
				break;
			}

			$textService = new ContactsTextService( $kernelStrings, $language );
			$textService->ListAvailableVariables( $kernelStrings, array( VS_CONTACT, VS_CURRENT_USER, VS_COMPANY ) );
			$sender->textService = $textService;

			if( PEAR::isError( $MMM_ID = $message->saveEntry( $currentUser, ACTION_NEW, $kernelStrings, $mmStrings ) ) )
			{
				$errorStr = $MMM_ID->getMessage();
				break;
			}
			if( PEAR::isError( $res = mm_saveAttachment( $attachments, $MMM_ID, 0 ) ) )
			{
				$errorStr = $res->getMessage();
				break;
			}
			if( PEAR::isError( $res = mm_saveAttachment( $images, $MMM_ID, 1 ) ) )
			{
				$errorStr = $res->getMessage();
				break;
			}
			if( $toDraft )
				break;

			$sender->filesDir = mm_getNoteAttachmentsDir( $message->MMM_ID, MM_ATTACHMENTS );
			$sender->imagesDir = mm_getNoteAttachmentsDir( $message->MMM_ID, MM_IMAGES );
			if( $MMF_ID == 'Outbox' && $images )
				$sender->includeImages = true;
			$sender->imageUri = dirname( getCurrentAddress() ) . '/' .
				prepareURLStr( PAGE_MM_GETSENTIMG, array( 'DB_KEY'=>base64_encode( $DB_KEY ), 'messageId'=>$message->MMM_ID ) );

			$sender->leaveHeaders = !$listsIsSet;

			if( $sendNow )
			{
				$sent = $sender->send( $currentUser, $message, $includeImages, $kernelStrings, $mmStrings );

				if( $MMF_ID == 'Outbox' )
				{
					$res = mm_deleteMessage( $currentUser, $MMM_ID, $MMF_ID, $kernelStrings, $mmStrings );
					if( PEAR::isError( $res ) )
					{
						$errorStr = $mmStrings['sm_event_insert_error'];
						break;
					}
				}
			}
			else
			{
				if( PEAR::isError( CronEventDispatcher::getInstance() ) )
					$errorStr = PEAR::raiseError( $mmStrings['sm_event_insert_error'] );

				$_ret = CronEventDispatcher::getInstance()->addEvent( $DB_KEY,'MM','Subscribe',$messageData['WHENDATETIME'] );
				if( PEAR::isError( $_ret ) )
				{
					$errorStr = $mmStrings['sm_event_insert_error'];
					break;
				}
			}

			break;

// *****************************************************************************

		case 'file':
			$htmlPage = 'files_list.htm';

			$size = getUploadedFilesSize();

			if(isset($_FILES['file']))
			{
				$file = $_FILES['file'];
				if($file['size'] + $size > $_size_limit)
					$errorStr = sprintf($mmStrings['app_attach_size_error'], formatFileSizeStr($_size_limit));
				else
				{
					$file['body'] = file_get_contents($file['tmp_name']);
					addUploadedFile($file);
				}
				$list = getUploadedFilesList();
			}
			break;

		case 'delete':
			$htmlPage = 'files_list.htm';
			$list = deleteUploadedFile($deleteFile);
			break;

		case 'clear':
			$htmlPage = 'files_list.htm';
			clearUploadedFiles();
			$list = false;
			break;
	}

	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( 'mmStrings', $mmStrings );
	$preproc->assign( 'errorStr', $errorStr );
	$preproc->assign( 'sent', $sent );
	$preproc->assign( 'list', $list );
	$preproc->assign( 'userConfirmSend', $userConfirmSend );

	$preproc->display( $htmlPage );

?>