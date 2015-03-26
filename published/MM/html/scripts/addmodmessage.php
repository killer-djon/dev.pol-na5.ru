<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );
	require_once( "../../../common/html/includes/controls/varlistcontrol.php" );

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

	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$invalidField = null;
	$metric = metric::getInstance();
	$curMMF_ID = base64_decode( $MMF_ID );
	$curFolderID = $curMMF_ID;

	if ( !isset( $action ) )
		$action = ACTION_NEW;


	$attachSizeLimit = mm_getLimitationOption( MM_OPT_ATTACHMENT_SIZE_LIMIT, $kernelStrings );

	$readOnlyFlag = true;

	switch (true)
	{
		case true :
					$rights = $mm_treeClass->getIdentityFolderRights( $currentUser, $curMMF_ID, $kernelStrings );
					if ( PEAR::isError($rights) ) {
						$fatalError = true;
						$errorStr = $rights->getMessage();

						break;
					}

					if ( UR_RightsObject::CheckMask( $rights, TREE_WRITEREAD ) )
						$readOnlyFlag = false;

	}

	$btnIndex = getButtonIndex( array( BTN_ATTACH, BTN_SAVE, BTN_CANCEL, BTN_DELETEFILES, "deleteNoteBtn", 'saveaddbtn', 'sendbtn', 'savetplbtn', 'savecopybtn', 'addimgsbtn', 'deleteimgsbtn', 'showText', 'showHTML', 'gentextbtn', 'saveclosebtn', 'showSummary', 'showStat', 'showAttach', 'cancelSending', 'sendtestbtn' ), $_POST );

	switch ($btnIndex)
	{
		case 14: // save and close
		case 10: // delete images btn
		case 9 : // add imgs btn
		case 8 : // savecopybtn
		case 7 : // saveastemplate
		case 19 : // sendtestbtn
		case 6 : // sendbtn
		case 5 : // save and add another
		case 1 : // save
		case 0 : // attach

					if ( $readOnlyFlag )
					{
						$fatalError = true;
						$errorStr = $mmStrings['amn_screen_norights_message'];
						break;
					}

					if ( !in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) )
					{
						if ( $btnIndex == 7 )
							redirectBrowser( PAGE_MM_SAVEAS, array( "MMF_ID"=>$MMF_ID, "MMM_ID"=>$messageData['MMM_ID'], "STATUS"=>MM_STATUS_TEMPLATE ) );
						else if ( $btnIndex == 8 )
							redirectBrowser( PAGE_MM_SAVEAS, array( "MMF_ID"=>$MMF_ID, "MMM_ID"=>$messageData['MMM_ID'], "STATUS"=>MM_STATUS_DRAFT ) );
						else if ( $btnIndex == 14 ) // close
							redirectBrowser( PAGE_MM_MAILMASTER, array() );
					}

					// Move new attached file
					//
					if ( isset($_FILES['notefile']) )
					{
						$res = mm_add_moveAttachedFile(	$attachSizeLimit,
												$_FILES['notefile'],
												base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($PAGE_ATTACHED_FILES),
												$kernelStrings, $mmStrings
											);

						if ( PEAR::isError( $res ) )
						{
							$errorStr = $res->getMessage();

							break;
						}

						$PAGE_ATTACHED_FILES = base64_encode($res);
					}

					if ( $btnIndex == 0 ) // attach
						break;

					$IMAGE_ATTACHED_FILES = null;
					$IMAGE_DELETED_FILES = null;


					// Delete Images button

					if ( $btnIndex == 10 )
					{
						if ( !isset($cbdeletenewimage) ) $cbdeletenewimage = array();
						if ( !isset($cbdeleterecordimage) ) $cbdeleterecordimage = array();

						$delImages = null;
						$pageImages = null;

						$res = deleteAttachedFiles( base64_decode($IMAGE_FILES), $delImages, $pageImages,
													$cbdeletenewimage, $cbdeleterecordimage, $kernelStrings );

						if ( PEAR::isError( $res ) ) {
							$errorStr =  $res->getMessage();

							break;
						}

						$IMAGE_ATTACHED_FILES = base64_encode( $pageImages );
						$IMAGE_DELETED_FILES = base64_encode( $delImages );

					}

					// Make note attachments list
					//
					$res = makeRecordAttachedFilesList( base64_decode($RECORD_FILES),
														base64_decode($PAGE_DELETED_FILES),
														base64_decode($PAGE_ATTACHED_FILES),
														$kernelStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					$messageData["MMM_ATTACHMENT"] = base64_encode($res);

					// Make note images list
					//
					$res = makeRecordAttachedFilesList( base64_decode($IMAGE_FILES),
														base64_decode($IMAGE_DELETED_FILES),
														base64_decode($IMAGE_ATTACHED_FILES),
														$kernelStrings );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break;
					}

					$messageData["MMM_IMAGES"] = base64_encode($res);

					$messageData["MMF_ID"] = $curFolderID;

					$message = new mm_message( $mm_message_data_schema );

					if ( $action == ACTION_EDIT )
					{
						if ( PEAR::isError($ret = $message->loadEntry($messageData['MMM_ID'], $kernelStrings, $mmStrings ) ) )
						{
							$fatalError = true;
							$errorStr = $ret->getMessage();

							break;
						}
					}


					if ( $curScreen != 0 )
						$messageData['MMM_CONTENT'] = $_SESSION['HTML_SAVED'];

					$ret = $message->loadFromArray($messageData, $kernelStrings, true, array( s_datasource=>s_form ) );

					if ( PEAR::isError( $ret ) )
					{
						$errorStr = $ret->getMessage();
						$errCode = $ret->getCode();

						if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
							$invalidField = $ret->getUserInfo();

						break;
					}
					$MM_ND = $message->saveEntry( $currentUser, $action, $kernelStrings, $mmStrings );
					if ( PEAR::isError( $MM_ND ) )
					{
						$errorStr = $MM_ND->getMessage();
						break;
					}
					
					if ( $action == ACTION_NEW && !$MMF_ID ) {
						$metric->addAction($DB_KEY, $currentUser, 'MM', 'COMPOSE', 'ACCOUNT');
					}
					
					$messageData['MMM_ID']= $MM_ND;

					// Apply attachments
					//
					$attachmentsPath = mm_getNoteAttachmentsDir( $MM_ND );

					$res = applyPageAttachments( base64_decode($PAGE_ATTACHED_FILES),
													base64_decode($PAGE_DELETED_FILES),
													$attachmentsPath, $kernelStrings, $MM_APP_ID );
					if ( PEAR::isError($res) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					// Apply images
					//
					$res = applyPageAttachments( base64_decode($IMAGE_ATTACHED_FILES),
													base64_decode($IMAGE_DELETED_FILES),
													$attachmentsPath, $kernelStrings, $MM_APP_ID );
					if ( PEAR::isError($res) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					$IMAGE_FILES = $messageData["MMM_IMAGES"];
					$RECORD_FILES = $messageData["MMM_ATTACHMENT"];

					$PAGE_ATTACHED_FILES = null;
					$PAGE_DELETED_FILES = null;
					$IMAGE_ATTACHED_FILES = null;

					$action = ACTION_EDIT;

					$_SESSION['MM_SUMARRAY'] = (array) mm_processFileListEntry( $message );

					if ( $btnIndex == 14 ) // save
						redirectBrowser( PAGE_MM_MAILMASTER, array() );
					else if ( $btnIndex == 7 )
						redirectBrowser( PAGE_MM_SAVEAS, array( "MMF_ID"=>$MMF_ID, "MMM_ID"=>$MM_ND, "STATUS"=>MM_STATUS_TEMPLATE ) );
					else if ( $btnIndex == 8 )
						redirectBrowser( PAGE_MM_SAVEAS, array( "MMF_ID"=>$MMF_ID, "MMM_ID"=>$MM_ND, "STATUS"=>MM_STATUS_DRAFT ) );
					else if ( $btnIndex == 6 )
						redirectBrowser( PAGE_MM_SEND, array( "MMF_ID"=>$MMF_ID, "doclist"=>base64_encode( serialize( array( $MM_ND ) ) ), "opener"=>PAGE_MM_ADDMODMESSAGE ) );
					else if ( $btnIndex == 19 )
						redirectBrowser( PAGE_MM_TESTSEND, array( "MMF_ID"=>$MMF_ID, "doclist"=>base64_encode( serialize( array( $MM_ND ) ) ), "opener"=>PAGE_MM_ADDMODMESSAGE ) );
					else if ( $btnIndex == 9 )
						redirectBrowser( PAGE_MM_ADDIMAGES,  array( "MMM_ID"=>$MM_ND, "MMF_ID"=>$MMF_ID )  );

					break;


		case 2 :
					redirectBrowser( PAGE_MM_MAILMASTER, array() );
		case 3 :
					$pageFiles = base64_decode($PAGE_ATTACHED_FILES);
					$delFiles = base64_decode($PAGE_DELETED_FILES);

					if ( !isset($cbdeletenewfile) ) $cbdeletenewfile = array();
					if ( !isset($cbdeleterecordfile) ) $cbdeleterecordfile = array();

					$res = deleteAttachedFiles( base64_decode($RECORD_FILES), $delFiles, $pageFiles,
												$cbdeletenewfile, $cbdeleterecordfile, $kernelStrings );

					if ( PEAR::isError( $res ) ) {
						$errorStr =  $res->getMessage();

						break;
					}

					$PAGE_ATTACHED_FILES = base64_encode( $pageFiles );
					$PAGE_DELETED_FILES = base64_encode( $delFiles );

					break;


		case 4 : // delete message
					$res = mm_deleteMessage( $currentUser, $messageData["MMM_ID"], $curFolderID, $kernelStrings, $mmStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						$fatalError = true;

						break;
					}

					redirectBrowser( PAGE_MM_MAILMASTER, array() );

		case 11: // showText

					if ( in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) )
					{
						if ( $curScreen == 0 )
							$_SESSION['HTML_SAVED'] = $messageData['MMM_CONTENT'];
					}

					$curScreen = 1;


					break;

		case 12: // showHTML

					$messageData['MMM_CONTENT'] = $_SESSION['HTML_SAVED'];
					$curScreen = 0;

					break;

		case 13: // Create from HTML

					//$messageData['CONTENT_TEXT'] = html2text( $_SESSION['HTML_SAVED'] );
					break;


		case 15: // showSummary

					if ( in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) )
					{
						if ( $curScreen == 0 )
							$_SESSION['HTML_SAVED'] = $messageData['MMM_CONTENT'];
					}

					$curScreen = 2;

					break;

		case 17: // showAttach

					if ( in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) )
					{
						if ( $curScreen == 0 )
							$_SESSION['HTML_SAVED'] = $messageData['MMM_CONTENT'];
					}

					$curScreen = 4;

					break;

		case 16: // showStat
					if ( in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) )
					{
						if ( $curScreen == 0 )
							$_SESSION['HTML_SAVED'] = $messageData['MMM_CONTENT'];
					}

					$curScreen = 3;

					$sentBounced = 0;
					$sentOpened = 0;

					if ( PEAR::isError( $sentRecipients = mm_getSentRecipients( $messageData['MMM_ID'], $sentBounced, $sentOpened, $kernelStrings ) ) )
					{
						$errorStr = $sentRecipients->getMessage();
						break;
					}

					$sentTotal = count( $sentRecipients );

					$sentBouncedPrct = ceil(($sentBounced/$sentTotal)*100);
					$sentBouncedPrct = $sentBouncedPrct > 100 ? 100 : $sentBouncedPrct;

					$sentOpenedPrct = ceil(($sentOpened/$sentTotal)*100);
					$sentOpenedPrct = $sentOpenedPrct > 100 ? 100 : $sentOpenedPrct;

					$sentReport = array(
											'RECIPIENTS'=>$sentRecipients,
											'TOTAL'=> $sentTotal,
											'SENT'=> $sentTotal - $sentBounced,
											'SENTPRCT' => 100 - $sentBouncedPrct,
											'BOUNCED' => $sentBounced,
											'BOUNCEDPRCT' => $sentBouncedPrct,
											'OPENED' => $sentOpened,
											'OPENEDPRCT' => $sentOpenedPrct
					);

					break;


		case 18 : 	// Cancel Sending

					$sumArray = $_SESSION['MM_SUMARRAY'];

					$message = new mm_message( $mm_message_data_schema );
					if ( PEAR::isError($ret = $message->loadEntry($sumArray['MMM_ID'], $kernelStrings, $mmStrings ) ) )
					{
						$fatalError = true;
						$errorStr = $ret->getMessage();

						break;
					}

					if ( $message->MMM_STATUS != MM_STATUS_PENDING )
						redirectBrowser( PAGE_MM_MAILMASTER, array( ) );

					$message->MMM_DATETIME = null;
					$message->MMM_TO= null;
					$message->MMM_FROM= null;

					$message->MMM_STATUS=MM_STATUS_DRAFT;

					$MM_ND = $message->saveEntry( $currentUser, ACTION_EDIT, $kernelStrings, $mmStrings );
					if ( PEAR::isError( $MM_ND ) )
					{
						$errorStr = $MM_ND->getMessage();
						break;
					}

					redirectBrowser( PAGE_MM_ADDMODMESSAGE, array( 'MMM_ID'=>$message->MMM_ID, 'MMF_ID'=>base64_encode( $message->MMF_ID ), 'action'=>ACTION_EDIT ) );

					break;

	}

	switch (true)
	{
		case true :

					if ( !isset($edited) || !$edited )
					{
						$PAGE_ATTACHED_FILES = null;
						$PAGE_DELETED_FILES = null;

						$_SESSION['HTML_SAVED'] = "";
						$_SESSION['TEXT_SAVED'] = "";

						$message = new mm_message( $mm_message_data_schema );

						if ( $action == ACTION_NEW && !isset( $CREATEFROM_ID ) )
						{
							$messageData = $message->getValuesArray();

							if ( isset( $MSGSTATUS) )
								$messageData['MMM_STATUS'] = $MSGSTATUS;

							$RECORD_FILES = null;
							$IMAGE_FILES = null;
						}
						else
						{
							if ( isset( $CREATEFROM_ID ) )
							{
								$action = ACTION_NEW;
								$MMM_ID = $CREATEFROM_ID;
							}

							if ( PEAR::isError($ret = $message->loadEntry($MMM_ID, $kernelStrings, $mmStrings ) ) )
							{
								$fatalError = true;
								$errorStr = $ret->getMessage();

								break;
							}

							if ( !$ret )
							{
								$messageData = $message->getValuesArray();
								$action = ACTION_NEW;
								$RECORD_FILES = null;
							}

							$messageData = $message->getValuesArray();

							if ( $action == ACTION_EDIT && !isset( $MMF_ID ) )
								$MMF_ID = base64_encode( $messageData['MMF_ID'] );


							$_SESSION['HTML_SAVED'] = $messageData['MMM_CONTENT'];

							$RECORD_FILES = $messageData["MMM_ATTACHMENT"];

							$IMAGE_FILES = $messageData["MMM_IMAGES"];

							if ( isset( $CREATEFROM_ID ) )
							{
								$messageData['MMM_STATUS'] = MM_STATUS_DRAFT;
							}

						}

						$_SESSION['MM_SUMARRAY'] = (array) mm_processFileListEntry( $message );
					}

					if ( !isset( $MMF_ID ) )
						redirectBrowser( PAGE_MM_MAILMASTER, array() );

					if ( !isset($edited) && !isset($curScreen) || !in_array( $curScreen, array( 0, 1, 2, 3, 4 ) ) )
						$curScreen = 0;

					if ( in_array( $curScreen, array( 0, 1 ) ) )
					{
						$service = new ContactsTextService( $kernelStrings, $language );
						$cmVars =  $service->ListAvailableVariables( $kernelStrings, array(VS_CONTACT, VS_CURRENT_USER, VS_COMPANY) ) ;

						foreach( $contactSectionsNames as $key=>$value )
						{
							$value['NAME'] = $mmStrings[$value['NAME']];
							$value['COMMENT'] = $mmStrings[$value['COMMENT']];

							$contactSectionsNames[$key] = $value;
						}
					}

					$mode =  ( !$readOnlyFlag ) ? in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) : false;

					$fileMenu = array();

					if (  $messageData['MMM_STATUS'] == MM_STATUS_PENDING )
					{
						$fileMenu[$mmStrings['amn_cancelsending_menu']] = sprintf( $processButtonTemplate, 'cancelSending' )."||onSubmitEditorLocal()";
						$fileMenu[] = '-';
					}

					if (  $messageData['MMM_STATUS'] != MM_STATUS_TEMPLATE )
					{
						$fileMenu[$mmStrings['amn_screen_saveascopy_btn']] = sprintf( $processButtonTemplate, 'savecopybtn' )."||onSubmitEditorLocal()";
						$fileMenu[$mmStrings['amn_screen_saveastpl_btn']] = sprintf( $processButtonTemplate, 'savetplbtn' )."||onSubmitEditorLocal()";
					}
					else
					{
						if (  $action != ACTION_NEW )
							$fileMenu[$mmStrings['amn_screen_saveasmsg_btn']] = sprintf( $processButtonTemplate, 'savecopybtn' )."||onSubmitEditorLocal()";
						else
							$fileMenu[$mmStrings['amn_screen_saveasmsg_btn']] = null;
					}

					if ( isset( $_SESSION['MM_SUMARRAY']['SENDTO'] ) && is_array($_SESSION['MM_SUMARRAY']['SENDTO']) )
					{
						$sendTo = $_SESSION['MM_SUMARRAY']['SENDTO'];

						if ( isset($sendTo['RECMAX']) && !is_null($sendTo['RECMAX']) &&  $sendTo['RECTOTAL'] >  $sendTo['RECMAX'] )
							$recExceededText = sprintf( $mmStrings['sm_recipient_exceeded_message'], $sendTo['RECMAX'] );

						if ( isset($sendTo['RECDAILYMAX']) && !is_null($sendTo['RECDAILYMAX']) && $sendTo['RECTOTAL']+$sendTo['RECSENT']>$sendTo['RECDAILYMAX'] )
							$recDailyExceededText = sprintf( $mmStrings['sm_daily_quotaexceeded_message'], $sendTo['RECDAILYMAX'] );
					}

					$fileMenu[] = '-';

					if (  $action != ACTION_NEW && $mode)
						$fileMenu[$mmStrings['amn_screen_delnote_btn']] = sprintf( $processButtonTemplate, 'deleteNoteBtn' )."||confirmDelete()";
					else
						$fileMenu[$mmStrings['amn_screen_delnote_btn']] = null;

					$sendMenu = array();

					if (  $messageData['MMM_STATUS'] == MM_STATUS_DRAFT )
					{
						if ( $mode )
						{
							$sendMenu[$mmStrings['app_sendtest_menu']] = sprintf( $processButtonTemplate, 'sendtestbtn' )."||onSubmitEditorLocal()";
							$sendMenu[$mmStrings['app_send_menu']] = sprintf( $processButtonTemplate, 'sendbtn' )."||onSubmitEditorLocal()";
						}
						else
						{
							$sendMenu[$mmStrings['app_sendtest_menu']] = null;
							$sendMenu[$mmStrings['app_send_menu']] = null;
						}
					}

					// Prepare tabs
					//
					$tabs = array();

					$checked = ($curScreen == 0) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$mmStrings['amn_tab_html_label'], 'link'=>sprintf( $processButtonTemplate, 'showHTML' )."||$checked" );

					$checked = ($curScreen == 1) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$mmStrings['amn_tab_text_label'], 'link'=>"javascript:processButton('showText' )||$checked" );

					$checked = ($curScreen == 4) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$mmStrings['amn_tab_attach_label'], 'link'=>"javascript:processButton('showAttach' )||$checked" );

					$checked = ($curScreen == 2) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$mmStrings['amn_tab_summary_label'], 'link'=>"javascript:processButton('showSummary' )||$checked" );

					if ( $messageData['MMM_STATUS'] != MM_STATUS_DRAFT && $messageData['MMM_STATUS'] != MM_STATUS_TEMPLATE )
					{
						$checked = ($curScreen == 3) ? "checked" : "unchecked";
						$tabs[] = array( 'caption'=>$mmStrings['amn_tab_stat_label'], 'link'=>"javascript:processButton('showStat' )||$checked" );
					}

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
	// Generating attached files lists
	//
	if ( !$fatalError )
	{
		$attachedFiles = makeAttachedFileList( base64_decode($RECORD_FILES),
												base64_decode($PAGE_DELETED_FILES),
												base64_decode($PAGE_ATTACHED_FILES),
												"cbdeletenewfile",
												"cbdeleterecordfile" );

		$attachedImages = makeAttachedFileList( base64_decode($IMAGE_FILES),
												null,
												null,
												"cbdeletenewimage",
												"cbdeleterecordimage" );
	}

	$thumbPerms[] = array();

	$attachmentsPath = mm_getNoteAttachmentsDir( $messageData["MMM_ID"], MM_IMAGES );

	if ( isset( $attachedImages ) )
		foreach ( $attachedImages as $key=>$value )
		{
			$diskfilename = base64_decode( $value["diskfilename"] );

			$imgPath = $attachmentsPath."/".$diskfilename;

			$imgUrl = prepareURLStr( PAGE_MM_GETMSGIMAGE, array( "fileName"=>$value["diskfilename"], "MMM_ID"=>$messageData['MMM_ID'], "MMF_ID"=>$MMF_ID ) );

			$value["URL"] = "<img src=".str_replace( " ", "%20", $imgUrl ).">";
			$value["DIMS"] = getimagesize( $imgPath );
			$value["old"]= 1;
			$thumbParams = array();

			$pInfo = pathinfo( $diskfilename );

			$destPath = sprintf( "%s/%s", $attachmentsPath, $diskfilename );

			$thumbPerms[] = $destPath;

			$srcExt = null;
			$thumbParams['nocache'] = getThumbnailModifyDate( $destPath, 'win', $srcExt );
			$thumbParams['basefile'] = base64_encode( $destPath );
			$thumbParams['ext'] = base64_encode( $pInfo['extension'] );

			$value['THUMB_URL'] = prepareURLStr( PAGE_GETFILETHUMB, $thumbParams );

			$attachedImages[$key] = $value;
		}

	$attachmentsPath = mm_getNoteAttachmentsDir( $messageData["MMM_ID"], MM_ATTACHMENTS );

	if ( isset( $attachedFiles ) )
		foreach ( $attachedFiles as $key=>$value )
		{
				$params = array( "MMF_ID"=>$MMF_ID, "MMM_ID"=>$messageData['MMM_ID'], "fileName"=>$value["diskfilename"] );
				$fileURL = prepareURLStr( PAGE_MM_GETMSGFILE, $params );

				if ( $value['delcbName'] == 'cbdeleterecordfile' )
					$value['FILELINK'] = sprintf( "<a href=\"%s\" target=\"_blank\">%s (%s)</a>", $fileURL, $key, $value["size"] );
				else
					$value['FILELINK'] = sprintf( "%s (%s)", $key, $value["size"] );

				$attachedFiles[$key] = $value;
		}

	$_SESSION['THUMBPERMS'] = $thumbPerms;

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	if ( !$readOnlyFlag)
	{
		if ( $messageData['MMM_STATUS'] == MM_STATUS_DRAFT || $messageData['MMM_STATUS'] == MM_STATUS_ERROR )
			$title =  $mmStrings['amn_screen_add_title'];
		else
		if ( $messageData['MMM_STATUS'] == MM_STATUS_TEMPLATE )
			$title =  ( $action == ACTION_NEW ) ? $mmStrings['amn_screen_cr_tpl_title'] : $mmStrings['amn_screen_add_tpl_title'];
		else
			$title =  $mmStrings['amn_screen_view_title'];
	}
	else
		$title =  $mmStrings['amn_screen_view_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_MM_ADDMODMESSAGE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );

	$preproc->assign( "mmStrings", $mmStrings );
	$preproc->assign( "MMF_ID", $MMF_ID );

	$preproc->assign( "HTML_AREA_FIELD", "messageData[MMM_CONTENT]" );
	$preproc->assign( "HTML_AREA_CONFIG", "full" );

	$preproc->assign( "limitStr", is_null( $attachSizeLimit ) ? nl2br(getUploadLimitInfoStr( $kernelStrings )) : sprintf( $mmStrings["app_attachment_size_error"], $attachSizeLimit ) );

	$preproc->assign( "tabs", $tabs );
	$preproc->assign( "curScreen", $curScreen );

	if ( isset( $vVisible ) )
		$preproc->assign( "vVisible", $vVisible );

	$preproc->assign( "mode", ( !$readOnlyFlag ) ? in_array( $messageData['MMM_STATUS'], $mm_EditorEnabledStatuses ) : false ) ;

	$preproc->assign( 'deleteConfirmMessage', ( $messageData['MMM_IMAGES'] != '' && $messageData['MMM_STATUS'] == MM_STATUS_SENT ) ?  $mmStrings['amn_screen_messageimgdel_message']."\\n".$mmStrings['app_delete_anyway_text']  :  $mmStrings['amn_screen_messagedel_message'] ) ;

	if ( !$fatalError ) {

		$preproc->assign( "attachedFiles", $attachedFiles );
		$preproc->assign( "filesCount", count( $attachedFiles ) );

		$preproc->assign( "attachedImages", $attachedImages );

		$preproc->assign( "fileMenu", $fileMenu );
		$preproc->assign( "sendMenu", $sendMenu );

		$preproc->assign( PAGE_ATTACHED_FILES, $PAGE_ATTACHED_FILES );
		$preproc->assign( RECORD_FILES, $RECORD_FILES );
		$preproc->assign( PAGE_DELETED_FILES, $PAGE_DELETED_FILES );

		$preproc->assign( "IMAGE_FILES", isset( $IMAGE_FILES ) ? $IMAGE_FILES : '' );

		$preproc->assign( "attachedFiles", $attachedFiles );
		$preproc->assign( "messageData", $messageData );

		$preproc->assign( "summaryArray", $_SESSION['MM_SUMARRAY'] );

		if ( isset($searchString) )
			$preproc->assign( "searchString", $searchString );

		if ( isset($edited) )
			$preproc->assign( "edited", $edited );

		if ( isset($sentReport) )
			$preproc->assign( 'sentReport', $sentReport );

		$preproc->assign( 'msgTypeNames', mm_prepareLocArray( $mm_msgTypes, $mmStrings ) );

		if ( isset( $cmVars ))
			$preproc->assign( 'cmVars', $cmVars );

		$preproc->assign( 'contactSectionsNames', $contactSectionsNames );
	}

	$preproc->display( "addmodmessage.htm" );
?>
