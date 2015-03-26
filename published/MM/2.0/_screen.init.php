<?php

	include_once '../../../system/init.php';
	include_once '../../../system/packages/folders_tree/_package.php';
	Autoload::addRule('substr($class, 0, 2) == "MM"', "published/MM/2.0");

	define('CONTACTS_PER_PAGE', 100);
	define('RESULTS_PER_PAGE', 30);
	define('ERROR_STRING', '<font color="indianred"><b>%s</b></font>');

	Wbs::authorizeUser('MM');
	$lang = User::getLang();
	$lang = mb_substr($lang, 0, 2);
	$domain = 'webasystMM'.Wbs::getDbkeyObj()->getVersion('MM');
	if (!file_exists(dirname(__FILE__).'/locale/'.$lang.'/LC_MESSAGES/'.$domain.'.mo')) {
		$domain = 'webasystMM';
	}		
	GetText::load($lang, realpath(dirname(__FILE__))."/locale", $domain);

	$updater = new WbsUpdater('MM');
	$updater->check();
	
	include_once '../../common/scripts/mailconsts.php';
	include_once '../../common/scripts/mailparse.php';
	include_once '../mm_parsers.php';
	
	
	$mm_statusNames = array(
		MM_STATUS_DRAFT => _('Draft'),
		MM_STATUS_PENDING => _('Pending'),
		MM_STATUS_SENDING => _('Sending'),
		MM_STATUS_SENT => _('Sent'),
		MM_STATUS_RECEIVED => _('Received'),
		MM_STATUS_TEMPLATE => _('Template'),
		MM_STATUS_ERROR => _('Sent')
		);
	$mm_virtualFoldersNames = array(
		MM_STATUS_DRAFT => _('Folder Drafts'),
		MM_STATUS_PENDING => _('Folder Pending'),
		MM_STATUS_SENDING => _('Folder Sent'),
		MM_STATUS_SENT => _('Folder Sent'),
		MM_STATUS_TEMPLATE => _('Folder Templates'),
		MM_STATUS_ERROR => _('Folder Sent')
		);
	$mm_statusStyle = array(
		MM_STATUS_DRAFT => 'color: #006400',
		MM_STATUS_PENDING => 'color: #006400; font-style: italic',
		MM_STATUS_SENDING => 'color: #006400; font-weight: bold; font-style: italic',
		MM_STATUS_SENT => 'color: #a52a2a',
		MM_STATUS_RECEIVED => 'color: #000000',
		MM_STATUS_TEMPLATE => 'color: #2C3EE6',
		MM_STATUS_ERROR => 'color: #FF0000; font-weight: bold'
		);
	$mm_statusNodes = array(
		MM_STATUS_DRAFT => 'draftBox',
		MM_STATUS_PENDING => 'pendingBox',
		MM_STATUS_SENDING => 'sentBox',
		MM_STATUS_SENT => 'sentBox',
		MM_STATUS_RECEIVED => '',
		MM_STATUS_TEMPLATE => 'templateBox',
		MM_STATUS_ERROR => 'sentBox'
		);	

?>