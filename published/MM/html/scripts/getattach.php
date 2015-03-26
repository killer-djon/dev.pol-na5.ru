<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . 'published/MM/mm.php';

	pageUserAuthorization( 'MM', $MM_APP_ID, false );

	getMsgAttach( $acc, $uid, $part_num, $type );

?>