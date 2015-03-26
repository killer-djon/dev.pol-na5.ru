<?php

	include_once '_screen.init.php';
	Wbs::authorizeUser('MM');
	include_once '../../common/html/scripts/tmp_functions.php';

	$app = MMApplication::getInstance();

	$errorStr = '';

	$mid = WebQuery::getParam('mid');
	$action = WebQuery::getParam('action');

	$message = $app->getMessage($mid);
	$message['all_emails'] = str_replace("'", "\'", stripslashes($message['all_emails']));

	$attachments = array();

	if($message['MMM_ATTACHMENT'])
	{
		$url = WebQuery::getPublishedUrl('/MM/2.0/getattach.php', null, true);

		$sxml = new SimpleXMLElement(base64_decode($message['MMM_ATTACHMENT']));

		$i = 0;
		foreach($sxml->FILE as $file)
		{
			$fn = (string)$file->attributes()->FILENAME;
			$fs = (string)$file->attributes()->FILESIZE;
			$ft = (string)$file->attributes()->MIME_TYPE;

			$attachments[$i]['name'] = base64_decode($fn);
			$attachments[$i]['size'] = formatFileSize($fs);
			$attachments[$i]['type'] = $ft;
			$attachments[$i]['url'] = "$url?mid=$mid&file=".urlencode($fn);

			$i++;
		}
	}

	$lists = '';
	if($message['MMM_LISTS'])
	{
		$lists = array_flip(explode(',', $message['MMM_LISTS']));

		$allLists = $app->getLists();
		foreach($allLists as $item)
			if(isset($lists[$item['CL_ID']]))
				$lists[$item['CL_ID']] = $item['CL_NAME'];
		$lists = join(', ', $lists);
	}

	$pendingError = false;
	$ts = strtotime($message['MMM_DATETIME']);
	if($message['MMM_STATUS'] == MM_STATUS_PENDING)
		if(($ts + 3600) < time())
			$pendingError = true;
	$message['MMM_DATETIME'] = WbsDateTime::getTime($ts);


	$sentCount = $errorCount = $successReport = $errorReport = 0;
	if($message['MMM_STATUS'] == MM_STATUS_SENT || $message['MMM_STATUS'] == MM_STATUS_ERROR)
	{
		try
		{
			if($action == 'successReport')
			{
				$sql = new CSelectSqlQuery('MMMSENTTO');
				$sql->setSelectFields('MMMST_EMAIL, MMMST_STATUS');
				$sql->addConditions("MMM_ID='".$message['MMM_ID']."'");
				$sql->addConditions("MMMST_STATUS='0'");
				$sql->setOrderBy('MMMST_EMAIL', 'ASC');
				$successReport = Wdb::getData($sql);
			}
			elseif($action == 'errorReport')
			{
				$sql = new CSelectSqlQuery('MMMSENTTO');
				$sql->setSelectFields('MMMST_EMAIL, MMMST_STATUS');
				$sql->addConditions("MMM_ID='".$message['MMM_ID']."'");
				$sql->addConditions("MMMST_STATUS<>'0'");
				$sql->setOrderBy('MMMST_EMAIL', 'ASC');
				$errorReport = Wdb::getData($sql);
			}
			else
			{
				$sql = new CSelectSqlQuery('MMMSENTTO');
				$sql->setSelectFields('COUNT(*)');
				$sql->addConditions("MMM_ID='".$message['MMM_ID']."'");
				$sql->addConditions("MMMST_STATUS<>'0'");

				$errorCount = Wdb::getFirstField($sql);
			}
		}
		catch (Exception $e) { exit(sprintf(ERROR_STRING, $e->getMessage())); }
	}

	//
	// Page implementation
	//
	$language = User::getLang();
	$preproc = new WbsSmarty(realpath(dirname(__FILE__))."/templates", 'MM', substr($language, 0, 2));

	$preproc->assign('errorStr', $errorStr);
	$preproc->assign('mid', $mid);
	$preproc->assign('message', $message);
	$preproc->assign('attachments', $attachments);
	$preproc->assign('lists', $lists);
	$preproc->assign('pendingError', $pendingError);
	$preproc->assign('errorCount', $errorCount);
	$preproc->assign('successReport', $successReport);
	$preproc->assign('errorReport', $errorReport);
	$preproc->assign('STATUS_PENDING', MM_STATUS_PENDING);
	$preproc->assign('STATUS_SENT', MM_STATUS_SENT);
	$preproc->assign('STATUS_ERROR', MM_STATUS_ERROR);
	$preproc->assign('MM_STATUS_RECEIVED', MM_STATUS_RECEIVED);
	$preproc->assign('styleURL', '../../MM/2.0/css/mm.css');
	
	$preproc->assign('replyTo', str_replace("'", "\'", stripslashes($message['MMM_REPLY_TO'] ? $message['MMM_REPLY_TO'] : $message['MMM_FROM'])));

	$preproc->display('viewmessage.html');

?>