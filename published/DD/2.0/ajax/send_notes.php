<?php

	include_once '_ajax.init.php';
	Wbs::authorizeUser('DD');
	//Kernel::incAppFile('MM', 'mm_app');
	Kernel::incPackage('contacts');
	Kernel::incPackage('date');
	Kernel::incPackage('mail');

	$folder = Env::Post('fid', Env::TYPE_STRING);
	$files = Env::Post('files', Env::TYPE_STRING);

	$user_id = CurrentUser::getInstance()->getId();
	
	$affectedUsers = array();

	$qr_folderUsers = "SELECT U.U_ID, U.C_ID FROM "
		."U_ACCESSRIGHTS AS UA, WBS_USER AS U WHERE "
		."UA.AR_PATH = '/ROOT/DD/FOLDERS' AND UA.AR_OBJECT_ID = '".mysql_real_escape_string($folder)
		."' AND UA.AR_VALUE > 0 AND "
		."U.U_ID=UA.AR_ID AND U.U_STATUS <> 1 AND UA.AR_ID <> '$user_id'";
	$qr_notifyUsers = "SELECT AR_ID FROM U_ACCESSRIGHTS WHERE "
		."AR_PATH = '/ROOT/DD/MESSAGES' AND AR_OBJECT_ID = 'ONFOLDERUPDATE' AND AR_VALUE > 0";

	$qr_folderInfo = "SELECT * FROM DOCFOLDER WHERE "
		."DF_ID = '".mysql_real_escape_string($folder)."'";
/*
	$qr_tree_selectFolderGroupUsers = "SELECT U.U_ID, BIT_OR( UGA.AR_VALUE ) AS GROUPRIGHTS, UGA.AR_ID FROM "
		."WBS_USER U, UGROUP_USER UGU, UG_ACCESSRIGHTS UGA WHERE "
		."UGU.U_ID = U.U_ID AND UGA.AR_ID = UGU.UG_ID AND "
		."UGA.AR_OBJECT_ID = '$folder' AND UGA.AR_PATH = '/ROOT/DD/FOLDERS' AND U.U_STATUS <> 1 "
		."GROUP BY UGU.U_ID";
*/

	$db_model = new DbModel();
	$folderUsers = $db_model->query($qr_folderUsers);
	$notifyUsers = $db_model->query($qr_notifyUsers);
	$folderName = $db_model->query($qr_folderInfo)->fetchField('DF_NAME');

	$notify = array();
	foreach($notifyUsers as $row) {
		$notify[$row['AR_ID']] = 1;
	}

	$senderName = Users::getUsername($user_id);

	$companyName = Company::getName();
	$robotEmail = Wbs::getSystemObj()->getEmail();

	$from = "$companyName <$robotEmail>";

	$filesNumber = count($files);
	$filesList = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.join('<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $files);

	$result = 'Notes sended';

	foreach($folderUsers as $row) {
		if(isset($notify[$row['U_ID']])) {

			$user = Users::getUserByCID($row['C_ID']);
			$userData = $user->getData();
			$userName = Users::getUserDisplayName($userData);

			if($userData['C_EMAILADDRESS']) {

				$language = $user->getLanguage();
				if(is_file("../mail/note_$language.html")) {
					$msg = file_get_contents("../mail/note_$language.html");
				} else {
					$msg = file_get_contents('../mail/note_eng.html');
				}
				$msg = explode("\n", $msg, 2);
				$subject = trim($msg[0]);
				$content = trim($msg[1]);
				$content = str_replace('{RECIPIENT_NAME}', $userName, $content);
				$content = str_replace('{UPDATED_FOLDER}', $folderName, $content);
				$content = str_replace('{FILES_NUMBER}', $filesNumber, $content);
				$content = str_replace('{FILES_LIST}', $filesList, $content);
				$content = str_replace('{SENDER_NAME}', $senderName, $content);
				$content = str_replace('{COMPANY_NAME}', $companyName, $content);

				if($timezoneId = $user->getSetting('TIME_ZONE_ID')) {
					$timezoneDst = $user->getSetting('TIME_ZONE_DST');
					$timeZone = new CTimeZone($timezoneId, $timezoneDst);
				} else {
					$timeZone = false;
				}
				$content = str_replace('{DATE_TIME}', WbsDateTime::getTime(time(), $timeZone), $content);

				$message = Mailer::composeMessage();
				$message->addFrom($from);
				$message->addTo(trim($userName.' <'.$userData['C_EMAILADDRESS'].'>'));
				$message->addSubject($subject);
				$message->addContent($content);
				$message->addAppId('--');
				try {
					Mailer::send($message);
				} catch (Exception $e) {
					$result = $e->getMessage();
				}
			}
		}
	}

	print json_encode($result);
	
?>