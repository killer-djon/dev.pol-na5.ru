<?php

	//
	// Mail Master main script
	//

	require_once 'mm_classes.php';
	require_once 'mm_dbfunctions_cmn.php';
	require_once 'mm_queries_cmn.php';
	require_once 'mm_consts.php';
	require_once 'mm_functions.php';
	require_once 'html2text.php';
	require_once 'sockets.php';
	require_once 'mimeDecode.php';
	require_once 'mm_parsers.php';
	require_once WBS_DIR . 'published/common/scripts/mailparse.php';
	require_once WBS_DIR . 'published/common/html/scripts/tmp_functions.php';
		
	$updater = new WbsUpdater('MM');
	$updater->check();

?>