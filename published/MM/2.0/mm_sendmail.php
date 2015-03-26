<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');
	include_once '../../common/html/scripts/tmp_functions.php';

	$app = MMApplication::getInstance();

	$messageData = WebQuery::getParam('messageData');
	$action = WebQuery::getParam('action');
	$status = WebQuery::getParam('status');
	$toDraft = WebQuery::getParam('toDraft');
	$toTemplate = WebQuery::getParam('toTemplate');
	$currentMID = WebQuery::getParam('currentMID');
	$currentFID = WebQuery::getParam('currentFID');
	$deleteFile = WebQuery::getParam('deleteFile');
	$userConfirmSend = WebQuery::getParam('userConfirmSend');
	$listsBox = WebQuery::getParam('listsBox');

	$errorStr = $upgradeUrl = $imageName = $when = '';
	$sent = array();

	switch($action)
	{
		case 'send':
			$htmlPage = 'messagesent.html';

			$message = Mailer::composeMessage();

			$to_more = stripslashes($messageData['MMM_TO']);
			$cc_more = stripslashes($messageData['MMM_CC']);
			$bcc_more = stripslashes($messageData['MMM_BCC']);

			$to_input = $to_more.', '.$cc_more.', '.$bcc_more;
			$addr = parseAddressString($to_input);

			$to_more = preg_replace('/(.*?)\s*[,;]*\s*$/', "$1", $to_more);

			$message->addTo($to_more);
			$message->addCc($cc_more);
			$message->addBcc($bcc_more);

			$numEmails = count($addr['accepted']) + count($addr['bounced']);

			$lists = $inputLists = array();
			$listsIsSet = false;
			if(trim($messageData['MMM_LISTS']) && is_array($listsBox))
			{
				foreach($listsBox as $key=>$val) {
					if($val) {
						$lists[] = $key;
					}
				}
				$listsIsSet = true;

				$numEmails += $app->getListsCount($lists);
			}

			$message->addLists($lists);

			//
			// Check whether send date is in the future
			//
			//$now = CDateTime::now()->value;
			if($messageData['WHEN'] == 'later' && !$toDraft)
			{
				$when = $messageData['WHENDATE'] . ' ' . $messageData['WHENTIME'];
				$whentimestamp = WbsDateTime::unixtime($when);
				if ($whentimestamp <= WbsDateTime::getTimeStamp(time()))
				{
					$errorStr = _('Please specify future date/time');
					break;
				}
				$message->addDateTime($whentimestamp);
			}
			else
			{
				$whentimestamp = time();
				$when = '';
			}

			$sendNow = ($messageData['WHEN'] == 'later') ? false : true;

			if(!$toDraft)
			{
				$sentNum = $app->getSentCount($whentimestamp);
				$dailySendLimit = MM_DAILY_SEND_LIMIT;
				if(!empty($dailySendLimit) && $numEmails + $sentNum > $dailySendLimit)
				{
					$errorStr = sprintf(_('ACCOUNT LIMIT: Daily quota for outgoing messages has been exceeded: %s'), $dailySendLimit);
					$upgradeUrl = WebQuery::getPublishedUrl('AA/html/scripts/change_plan.php', null, true);
					break;
				}
			}

			// ***********************************************************************
			// Send e-mail
			// ***********************************************************************

			if($listsIsSet && !$toDraft)
			{
				if(!$userConfirmSend)
				{
					$userConfirmSend = ' '.$numEmails.' ';
					break;
				}
				else
					$userConfirmSend = 0;
			}

			$message->addSubject(trim(stripslashes($messageData['MMM_SUBJECT'])));

			// Copy template images (...getimage.php...) into message (...preview.php...)
			$content = htmlspecialchars_decode(stripslashes($messageData['MMM_CONTENT']));
			
			$reloadInfo = array(); // will be merged with UploadsInfo
			if(stripos($content, '/common/html/scripts/getimage.php') &&
				preg_match_all('/(\/common\/html\/scripts\/getimage\.php\?user=([^&]+)&msg=([^&]+)&file=)([^"\'& ]+)/i',
					$content, $match))
			{
				for($i=0; $i<count($match[0]); $i++)
				{
					$content = str_ireplace($match[1][$i], PAGE_PREVIEW.'?file=', $content);

					$user = base64_decode($match[2][$i]);
					$msg =  $match[3][$i];
					$file = base64_decode($match[4][$i]);
					$path = Wbs::getSystemObj()->files()->getDataPath()."/$user/attachments/mm/images/$msg/$file";

					$reloadInfo[] = array(
						'name' => $file,
						'type' => get_mime($file),
						'size' => @filesize($path),
						'path' => $path
					);
				}
			}	

			$message->addContent(utf8_urldecode($content));

			$message->addFrom($messageData['MMM_FROM']);

			try
			{
				CurrentUser::getInstance()->setSetting('MM', 'CURRENT_SENDER', $messageData['MMM_FROM']);
			}
			catch (Exception $e)
			{
				$errorStr = $e->getMessage();
				break;
			}

			if($currentMID && ($status == MM_STATUS_DRAFT || $status == MM_STATUS_TEMPLATE))
				$update = true;
			else
				$update = false;

			if(($status != MM_STATUS_DRAFT && $status != MM_STATUS_TEMPLATE) ||
				($status == MM_STATUS_TEMPLATE && !$toDraft) ||
				($status == 'doForward') ||
				($status == MM_STATUS_TEMPLATE && !$toTemplate))
			{
				$currentMID = false;
				$update = false;
			}

			if($toDraft)
			{
				if(is_numeric($status))
					$MMM_STATUS = $status;
				else
					$MMM_STATUS = MM_STATUS_DRAFT;
				$doSend = false;
			}
			elseif(!$sendNow)
			{
				$MMM_STATUS = MM_STATUS_PENDING;
				$doSend = 'later';
			}
			else
			{
				$MMM_STATUS = MM_STATUS_SENDING;
				$doSend = 'now';
			}
			if($MMM_STATUS == MM_STATUS_TEMPLATE && !$toTemplate) {
				$MMM_STATUS = MM_STATUS_DRAFT;
			}

			$message->addStatus($MMM_STATUS);
			$message->addPriority($messageData['MMM_PRIORITY']);

			$MMF_ID = 0;
			if($currentFID && $toDraft && $update)
				$MMF_ID = $currentFID;
			/*
			if(($id = base64_decode(CurrentUser::getInstance()->getAppSetting('MM', 'CURRENT_FOLDER'))) &&
				($app->getFoldersTree()->getNode($id)->Rights & 6)) // 6 = Full(4) or Write(2)
					$MMF_ID = $id;
			*/
			$message->addFolderId($MMF_ID);

			if($currentMID)
				$message->addId($currentMID);

			$message->addAttachments(getUploadsInfo());
			$message->addImages(array_merge(getUploadsInfo('images'), $reloadInfo));

			try
			{
				Mailer::send($message, $doSend, $update);
			}
			catch (Exception $e)
			{
				$errorStr = $e->getMessage();
				break;
			}

			$DB_KEY = Wbs::getDbkeyObj()->getDbkey();
			$currentUser = CurrentUser::getInstance()->getId();
			$metric = metric::getInstance();
			if($doSend == 'now')
				$metric->addAction($DB_KEY, $currentUser, 'MM', 'SEND-NOW', 'ACCOUNT', $numEmails);
			elseif($doSend == 'later')
				$metric->addAction($DB_KEY, $currentUser, 'MM', 'SEND-LATER', 'ACCOUNT', $numEmails);
			else
				$metric->addAction($DB_KEY, $currentUser, 'MM', 'COMPOSE', 'ACCOUNT');				

			break;

// *****************************************************************************

		case 'file':
			$htmlPage = 'files_list.html';

			$size = getUploadedFilesSize();

			if(isset($_FILES['file']))
			{
				$file = $_FILES['file'];
				if($file['size'] + $size > $_size_limit)
					$errorStr = sprintf(_("Total size of attachments can not be exceed %s MB"), $_size_limit/1000000);
				else
				{
//					$file['body'] = file_get_contents($file['tmp_name']);
					addUploadedFile(&$file);
				}
				$attachments = getUploadedFilesList();
			}
			break;

		case 'delete':
			$htmlPage = 'files_list.html';
			$attachments = deleteUploadedFile($deleteFile);
			break;

		case 'clear':
			$htmlPage = 'files_list.html';
			clearUploadedFiles();
			$attachments = false;
			break;

		case 'image':
			$htmlPage = 'add_image.html';

			$size = getUploadedFilesSize();

			if(isset($_FILES['image']))
			{
				$file = $_FILES['image'];
				if($file['size'] + $size > $_size_limit)
					$errorStr = sprintf(_("Total upload size can not exceed %s MB"), $_size_limit/1000000);
				else
				{
//					$file['body'] = file_get_contents($file['tmp_name']);
					addUploadedFile(&$file, 'images');
				}
				$imageName = base64_encode($file['name']);
			}
			break;
	}

	//
	// Page implementation
	//
	$language = User::getLang();
	$preproc = new WbsSmarty(realpath(dirname(__FILE__))."/templates", 'MM', substr($language, 0, 2));

	$preproc->assign('errorStr', $errorStr);
	$preproc->assign('sent', $sent);
	$preproc->assign('attachments', $attachments);
	$preproc->assign('imageName', $imageName);
	$preproc->assign('userConfirmSend', $userConfirmSend);
	$preproc->assign('when', $when);
	$preproc->assign('mainPageURL', WebQuery::getPublishedUrl('MM/html/scripts/mailmaster.php', null, true));
	$preproc->assign('upgradeUrl', $upgradeUrl);

	$preproc->display($htmlPage);

	function get_mime($fileName)
	{
		preg_match('/\.(.*?)$/', $fileName, $m);
		switch(strtolower($m[1]))
		{
			case 'jpg': case 'jpeg': case 'jpe': return 'image/jpg';
			case 'png': case 'gif': case 'bmp': case 'tiff' : return 'image/'.strtolower($m[1]);
			default: return 'image/gif';
		}
	}

?>