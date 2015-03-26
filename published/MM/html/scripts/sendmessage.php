<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );
	require_once( WBS_DIR."/published/CM/cm.php" );
	require_once( WBS_DIR . "kernel/classes/class.eventdispatcher.php" );

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

					if ( !UR_RightsObject::CheckMask( $rights, TREE_READWRITE ) )
					{
						$fatalError = true;
						$errorStr = $mmStrings['app_access_violation_error'];

						break;
					}
	}

	$btnIndex = getButtonIndex( array('sendbtn', 'savebtn', 'cancelbtn'), $_POST, false );

	$statArray = array();

	set_time_limit( 3600 );

	$maxRec = mm_getLimitationOption( MM_OPT_RECIPIENTS_LIMIT, $kernelStrings );
	$dailyMaxRec = mm_getLimitationOption( MM_OPT_DAILY_RECIPIENTS_LIMIT, $kernelStrings );
	$sendNowMaxRec = mm_getLimitationOption( MM_OPT_SENDNOW_RECIPIENTS_LIMIT, $kernelStrings );

	switch ($btnIndex)
	{
		case 'savebtn':

				if ( $fatalError )
					break;

				$contactsList = $_SESSION['CONTACTSLIST'];
				$messageData = $_SESSION['MESSAGEDATA'];

				$typeDescription = $fieldsPlainDesc = null;
				$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc );

				$ContactCollection->loadAsArrays  = true;

				// Get mail recipients' contact list

				$list = $ContactCollection->loadMixedEntityContactWithEmails( $contactsList[CM_OT_FOLDERS], $contactsList[CM_OT_USERGROUPS], $contactsList[CM_OT_LISTS], $contactsList['CONTACTS'], 'C_ID', $kernelStrings, true );
				if ( PEAR::isError( $list ) )
				{
					$errorStr = $list->getMessage();
					break;
				}

				// Check if recipients number does not exceed limitations

				$numEmails = count( $ContactCollection->items ) + count( mm_mailSender::_parseRecipientList( $messageData['TOMORE'] ) );

				$sentNum = mm_getSentCount( $kernelStrings );
				if ( PEAR::isError( $sentNum ) )
				{
					$errorStr = $sentNum->getMessage( );
					break;
				}

				$docsList = unserialize( base64_decode(  $doclist ));

				$statWillSendNum = $sentNum+count( $docsList ) * $numEmails;

				if ( $messageData['WHEN'] == 'now' && ( $numEmails == 0 || ( !is_null($maxRec) && $numEmails >  $maxRec ) || (!is_null( $dailyMaxRec) && $statWillSendNum>$dailyMaxRec ) ) )
				{
					if ( !is_null($maxRec) && $numEmails >  $maxRec )
					{
						$errorStr = mm_getLimitationMessage( MM_OPT_RECIPIENTS_LIMIT, $maxRec, $kernelStrings, $mmStrings );
						break;
					}

					if (!is_null( $dailyMaxRec) && $numEmails+$sentNum>$dailyMaxRec )
					{
						$errorStr = mm_getLimitationMessage( MM_OPT_DAILY_RECIPIENTS_LIMIT, $dailyMaxRec, $kernelStrings, $mmStrings );
						break;
					}
				}

				if ( !is_null( $sendNowMaxRec ) && $numEmails >  $sendNowMaxRec )
				{
					$messageData['WHEN'] = 'later';
					$messageData['WHENDATETIME'] = convertToSqlDateTime(time());
				}

				// Prepare Sender object

				$sender = new mm_mailSender();

				$sendNow = ( $messageData['WHEN'] == 'later' ) ? false : true;
				if ($sendNow)
					$sender->recipients = $ContactCollection->items;
				else
					$sender->recipients = array_slice($ContactCollection->items,0,1);

				$sender->docList = $docsList;

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
					echo $invalidField = $res->getUserInfo();

					break;
				}

				$messageData['SENDTO'] = base64_encode( serialize( array(  'SENDER'=> $sender->from, 'CGLF'=> $_SESSION['CONTACTSLIST'] , 'TOMORE'=>$messageData['TOMORE'], 'URI'=>dirname( getCurrentAddress() ) ) ) );

				$sendNow = ( $messageData['WHEN'] == 'later' ) ? false : true;

				$disableUnsibscribeFooter = mm_getLimitationOption( MM_OPT_DISABLE_UNSUBSCRIBE_FOOTER, $kernelStrings );

				foreach( $sender->docList as $msgID )
				{
					// Loading message data

					$message = new mm_message( $mm_message_data_schema );

					if ( PEAR::isError($ret = $message->loadEntry( $msgID, $kernelStrings, $mmStrings ) ) )
					{
						$errorStr = $res->getMessage();
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

					// Change message status from DRAFT to SENDING

					if ( $messageData['WHEN'] == 'now' )
					{
						$message->MMM_STATUS = MM_STATUS_SENDING;
						if ( PEAR::isError( $res = $message->saveEntry( $currentUser, ACTION_EDIT, $kernelStrings, $mmStrings ) ) )
						{
							$errorStr = $res->getMessage();
							break 2;
						}
					}

					// Prepare unsubscribe footer if needed

					$MMM_CONTENT = $message->MMM_CONTENT;

					$footerArray = sliceLocalizaionArray( $mm_loc_str, "app_mail_footer" );
					$footerString = nl2br( ( isset( $footerArray[ $sender->from['MMS_LANGUAGE'] ] ) ) ? $footerArray[ $sender->from['MMS_LANGUAGE'] ] : $footerArray[ $sender->from[LANG_ENG] ] );

					if ( /*!is_null($disableUnsibscribeFooter) && */ $disableUnsibscribeFooter != 1 )
					{
						$message->MMM_CONTENT .= '<small>';
						$message->MMM_CONTENT .= $footerString;
						$message->MMM_CONTENT .= '<a href="'. MM_VAR_UNSIBSCRIBE_URL.'">'.$mmStrings['app_mail_footer_link'].'</a>';
						$message->MMM_CONTENT .= '</small>';
						// $mmStrings['app_mail_footer_link'].' [{UNSUBSCRIBE_URL}]';
					}

					$sender->filesDir = mm_getNoteAttachmentsDir( $message->MMM_ID, MM_ATTACHMENTS );
					$sender->imagesDir = mm_getNoteAttachmentsDir( $message->MMM_ID, MM_IMAGES );
					$sender->imageUri = dirname( getCurrentAddress() ) . "/" . prepareURLStr( PAGE_MM_GETSENTIMG, array( 'DB_KEY'=>base64_encode( $DB_KEY ), 'messageId'=>$message->MMM_ID ) );
  					$sender->includeImages = ( isset( $messageData['IMAGES'] ) && $messageData['IMAGES'] == 1 ) ? true : false;

 					// Sending or Pending a message

					if ( $sendNow )
					{
						$sendRes = $sender->send( $currentUser, $message, $includeImages, $kernelStrings, $mmStrings );

						$statArray[] = array(	"MESSAGE"=>$message->getValuesArray(),
											"SENT"=>$sendRes["SENT"],
											"SENTCOUNT"=>count($sendRes["SENT"]),
											"BOUNCED"=>$sendRes["BOUNCED"],
											"BOUNCEDCOUNT"=>count($sendRes["BOUNCED"]),
										);

						$message->MMM_STATUS = MM_STATUS_SENT;
						$message->MMM_DATETIME = convertToSqlDateTime( time() );

					}
					else
					{
						$message->MMM_STATUS = MM_STATUS_PENDING;
						$message->MMM_DATETIME =  $messageData['WHENDATETIME'];
					}

					$message->MMM_CONTENT = $MMM_CONTENT;
					$message->MMM_TO = $messageData['TO'];

					if ( trim( $sender->from['MMS_FROM'] ) == "" )
						$message->MMM_FROM = $sender->from['MMS_EMAIL'];
					else
						$message->MMM_FROM = "\"".$sender->from['MMS_FROM']."\" <".$sender->from['MMS_EMAIL'].">";

					if ( PEAR::isError( $res = $message->saveEntry( $currentUser, ACTION_EDIT, $kernelStrings, $mmStrings ) ) )
					{
						$errorStr = $res->getMessage();
						break 2;
					}

				}
				

				if ( onWebasystServer() && $numEmails > MM_ADJOURN && $messageData['WHEN'] == 'now') { //default: 50
					list($date, $time) = split(' ', displayDateTime( convertTimestamp2Local( time()+120 ) ));
					$messageData['WHEN'] = 'later'; 
					$messageData['WHENDATE'] = $date;//date('m/d/Y', time());
					$messageData['WHENTIME'] = $time;//date('H:i', time()+60);
					$_adjourn = 'true';
				}
				
				//$messageData['WHENDATETIME'] = convertToSqlDateTime( $whentimestamp, true );
				if (onWebasystServer() && strtolower($messageData['WHEN']) == 'later') {
					if (PEAR::isError($res = CronEventDispatcher::getInstance())) {
						$fatalError = true;
						$errorStr = $mmStrings['sm_event_insert_error'];
						break;
					}
					$_ret = CronEventDispatcher::getInstance()->addEvent($DB_KEY,'MM','Subscribe',$messageData['WHENDATETIME']);
					if (PEAR::isError($_ret)) {
						$fatalError = true;
						$errorStr = $mmStrings['sm_event_insert_error'];
						break;
					}
				}

				$messagesSent = true;


				if ($numEmails) {
					$metric = metric::getInstance();
					if ( $messageData['WHEN'] == 'now' )
						$metric->addAction($DB_KEY, $currentUser, 'MM', 'SEND-NOW', 'ACCOUNT', $numEmails);
					else 
						$metric->addAction($DB_KEY, $currentUser, 'MM', 'SEND-LATER', 'ACCOUNT', $numEmails);
				}




				redirectBrowser( PAGE_MM_MAILMASTER, array() );

				break;

		case 'cancelbtn':

				$docsList = unserialize( base64_decode(  $doclist ));

				if ( isset( $opener ) && $opener == PAGE_MM_ADDMODMESSAGE && isset($docsList[0]) )
					redirectBrowser(  PAGE_MM_ADDMODMESSAGE, array( ACTION=>ACTION_EDIT, "MMM_ID"=>$docsList[0], "MMF_ID"=>$MMF_ID ) );
				else
					redirectBrowser( PAGE_MM_MAILMASTER, array() );

				break;

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

		case 'sendbtn':

				if ( $fatalError ) break;
				unset($_adjourn);

				$_SESSION['CONTACTSLIST']  = null;
				$messageData['TOMORE']="";
				$_SESSION['INCLUDEDCONTACTS'] = base64_encode( serialize( $includedContacts ) );

				$contacts = $groups = $lists = $folders = array();
				$_savedContacts = $_SESSION['SAVEDCONTACTS'];
				foreach( $includedContacts as $value ) {
					if ( strstr( $value, 'CONTACT' ) ) $contacts[] = base64_decode( substr( $value, 7 ) );
					else if ( strstr( $value, CM_OT_USERGROUPS ) ) $groups[] = base64_decode( substr( $value, strlen(CM_OT_USERGROUPS) ) );
					else if ( strstr( $value, CM_OT_LISTS ) )$lists[] = base64_decode( substr( $value, strlen(CM_OT_LISTS) ) );
					$contactsSend[$value] = $_savedContacts[$value];
				}

				$typeDescription = $fieldsPlainDesc = null;
				$ContactCollection = new contactCollection( $typeDescription, $fieldsPlainDesc );
				$ContactCollection->loadAsArrays  = true;
				$list = $ContactCollection->loadMixedEntityContactWithEmails( $folders, $groups, $lists, $contacts, 'C_ID', $kernelStrings );
				if ( PEAR::isError( $list ) )
				{
					$errorStr = $list->getMessage();
					break;
				}




				$numEmails = count( $ContactCollection->items ) + count( mm_mailSender::_parseRecipientList( $messageData['TOMORE'] ) );
				
				$sentNum = mm_getSentCount( $kernelStrings );
				if ( PEAR::isError( $sentNum ) ) {
					$errorStr = $sentNum->getMessage( );
					break;
				}
				$docsList = unserialize( base64_decode(  $doclist ));
				$statWillSendNum = $sentNum+count( $docsList ) * $numEmails;
				if ( onWebasystServer() && $numEmails > MM_ADJOURN && $messageData['WHEN']!='later') { //default: 50
					list($date, $time) = split(' ', displayDateTime( convertTimestamp2Local( time()+120 ) ));
					$messageData['WHEN'] = 'later'; 
					$messageData['WHENDATE'] = $date;//date('m/d/Y', time());
					$messageData['WHENTIME'] = $time;//date('H:i', time()+60);
					$_adjourn = 'true';
				}
				
				// Check whether  send date is in the future
				if ( $messageData['WHEN'] == 'later' ) {
					$whentimestamp = 0;
					if ( !( validateInputDate( $messageData['WHENDATE'], $whentimestamp, false ) ) || !isTimeStr( $messageData['WHENTIME'] ) ) {
						$errorStr = $mmStrings['sm_date_select_error']; break;
					}
					$parts = explode( ":", $messageData['WHENTIME']  );
					$whentimestamp = $whentimestamp + $parts[0]*3600 + $parts[1]*60;
					if ( $whentimestamp <= convertTimestamp2Local( time() ) ) {
						$errorStr = $mmStrings['sm_date_select_error']; break;
					}

					$messageData['WHENDATETIME'] = convertToSqlDateTime( $whentimestamp, true );
					
				}

				$_SESSION['INCLUDEDCONTACTS'] = base64_encode( serialize( $includedContacts ) );
				$contacts = $groups = $lists = $folders = array();
				$_savedContacts = $_SESSION['SAVEDCONTACTS'];

				foreach( $includedContacts as $value )
				{
					if ( strstr( $value, 'CONTACT' ) )
						$contacts[] = base64_decode( substr( $value, 7 ) );
					else
					if ( strstr( $value, CM_OT_USERGROUPS ) )
						$groups[] = base64_decode( substr( $value, strlen(CM_OT_USERGROUPS) ) );
					else
					if ( strstr( $value, CM_OT_LISTS ) )
						$lists[] = base64_decode( substr( $value, strlen(CM_OT_LISTS) ) );

					$contactsSend[$value] = $_savedContacts[$value];
				}

				$typeDescription = $fieldsPlainDesc = null;

				$sentNum = mm_getSentCount( $kernelStrings );
				if ( PEAR::isError( $sentNum ) )
				{
					$errorStr = $sentNum->getMessage( );
					break;
				}

				$docsList = unserialize( base64_decode(  $doclist ));

				$statWillSendNum = $sentNum+count( $docsList ) * $numEmails;

				$showSendBtn = true;

				$numText = sprintf( $mmStrings['sm_email_counts_text'], $numEmails );
				
				if ( $messageData['WHEN'] == 'now' && ( $numEmails == 0 || ( !is_null($maxRec) && $numEmails >  $maxRec ) || (!is_null( $dailyMaxRec) && $statWillSendNum>$dailyMaxRec ) ) )
				{
					$showSendBtn = false;
					$_SESSION['CONTACTSLIST'] = null;

					if ( !is_null($maxRec) && $numEmails >  $maxRec )
						$recExceededText = mm_getLimitationMessage( MM_OPT_RECIPIENTS_LIMIT, $maxRec, $kernelStrings, $mmStrings );

					if (!is_null( $dailyMaxRec) && $numEmails+$sentNum>$dailyMaxRec )
						$recDailyExceededText = mm_getLimitationMessage( MM_OPT_DAILY_RECIPIENTS_LIMIT, $dailyMaxRec, $kernelStrings, $mmStrings );
				}
				else
					$_SESSION['CONTACTSLIST'] = array( 'CONTACTS'=> $contacts, CM_OT_USERGROUPS => $groups, CM_OT_LISTS => $lists, CM_OT_FOLDERS => $folders );

				if ( $showSendBtn && ( !is_null( $sendNowMaxRec ) && $numEmails >  $sendNowMaxRec )  )
					$recSendNowExceededText = mm_getLimitationMessage( MM_OPT_SENDNOW_RECIPIENTS_LIMIT, $sendNowMaxRec, $kernelStrings, $mmStrings );

				/* 'CONTACTS'=>base64_encode( serialize( $contactsSend) ), */

				$_SESSION['MESSAGEDATA'] = $messageData;

				$informationGathered = true;

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
						$messageData['WHEN'] = 'now';
					}

					$FGL = mm_prepareFGLList( $currentUser, $kernelStrings, $mmStrings );

					if ( PEAR::isError( $FGL ) )
					{
						$fatalError = true;
						$errorStr = $FGL->getMessage();

						break;
					}

					if ( !isset( $currentObject )  || !isset($FGL[$currentObject] ) )
					{
						$curObj = reset( $FGL );
						$currentObject = $curObj['ID'];
					}

					// Prepare contacts list
					//
					$contactsForInclude = mm_listObjects( $currentObject, $currentUser, $kernelStrings, $mmStrings );
					if ( PEAR::isError($contactsForInclude) )
					{
						$fatalError = true;
						$errorStr = $contactsForInclude->getMessage();
						break;
					}

					if ( !isset($edited) )
					{
						$selectedPriority = MM_PRIORITY_NORMAL;
						$includedContacts = array();

						foreach( $contactsForInclude as $key=>$contact )
							$_savedContacts[$key] = $contact;
					}
					else
					{
						$_savedContacts = $_SESSION['SAVEDCONTACTS'];

						foreach( $contactsForInclude as $key=>$contact )
							$_savedContacts[$key] = $contact;

						if ( !isset($includedContacts) )
							$includedContacts = array();
					}

					$_SESSION['SAVEDCONTACTS']= $_savedContacts;
					foreach( $includedContacts as $value )
						$includedContactsItems[$value] = $_savedContacts[$value];

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

					for ( $i=0; $i<24; $i++ )
						$timeArray[] = sprintf( "%02d:00", $i );

					if ( isset( $recExceededText ) )
					{
						if ( trim($errorStr != "" ) )
							$errorStr .= "<BR>";

						$errorStr .= $recExceededText;
					}

					if ( isset( $recDailyExceededText ) )
					{
						if ( trim($errorStr != "" ) )
							$errorStr .= "<BR>";

						$errorStr .= $recDailyExceededText;
					}
	}
	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $MM_APP_ID );

	$title = $mmStrings['sm_screen_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_MM_SEND );
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

		$preproc->assign( "includedContacts", $includedContacts );
		$preproc->assign( "contactsForInclude", $contactsForInclude );

		if ( isset($includedContactsItems) )
			$preproc->assign( "includedContactsItems", $includedContactsItems );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		$preproc->assign( "senderNames", $senderNames );
		$preproc->assign( "senderIds", $senderIds );

		$preproc->assign( "noSenderEmail", $noSenderEmail );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );


		if ( isset( $informationGathered ) && $informationGathered )
		{
			$preproc->assign( 'informationGathered', $informationGathered );
			$preproc->assign( 'numText', $numText );
			$preproc->assign( 'showSendBtn', $showSendBtn );
		}

		$preproc->assign( 'FGL', $FGL );
		$preproc->assign( 'adjourn', $_adjourn );

		$preproc->assign( 'currentObject', $currentObject );
		$preproc->assign( 'currentObjectProp', $FGL[$currentObject] );

		$preproc->assign( 'MaxRecNum', $maxRec );
		$preproc->assign( 'DailyMaxRecNum', $dailyMaxRec );
		$preproc->assign( "AlreadySent", $sentNum );
		if ($dailyMaxRec) {
			$preproc->assign( "NumEmails", $numEmails );
			$preproc->assign( "SendAnother", $dailyMaxRec-$sentNum );
		}

		if ( isset( $recSendNowExceededText ) )
			$preproc->assign( 'recSendNowExceededText', $recSendNowExceededText );

		if ( isset($statSendText) )
		{
			$preproc->assign( 'statSendText', $statSendText );
			$preproc->assign( 'statWillSendText', $statWillSendText );
		}

		if ( $currentObject != CM_OT_LISTS && $currentObject != CM_OT_USERGROUPS )
			$preproc->assign( 'showContactsNote', true );
		else
			$preproc->assign( 'showContactsNote', false );

		$preproc->assign('serverTimeNote', sprintf( $mmStrings['sm_send_servertime_note'],  "<b>".convertToDisplayDateTime( convertToSqlDateTime(time() ), false, true, true )."</b>" ) );
	}

	$preproc->display( "sendmessage.htm" );
?>