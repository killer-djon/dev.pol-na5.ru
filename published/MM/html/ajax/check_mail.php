<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . '/published/MM/mm.php';

	pageUserAuthorization( 'MM', $MM_APP_ID, false ); // returns $currentUser

	session_write_close();

	//
	// Page variables setup
	//
	$mmStrings = $mm_loc_str[$language];

	if(empty($mailbox))
	{
		$accounts = mm_getAccounts( $currentUser );
		if(PEAR::isError($accounts))
			exit($accounts->getMessage());
	}
	else
	{
		$account = mm_getAccountByEmail($mailbox);
		if(PEAR::isError($account))
			exit($account->getMessage());

		$accounts = array($account);
	}

	$new_msg = mm_checkMail($accounts);
	if(PEAR::isError($new_msg))
		exit($new_msg->getMessage());

	$out = array();

	if($new_msg && is_array($new_msg))
		foreach($new_msg as $key=>$val)
			$out[] = "'$key':'$val'";

	if(empty($mailbox) && ($sending = mm_getSendingMessages()) && is_array($sending))
		foreach($sending as $id=>$stat)
			$out[] = "'$id':'$stat'";

	if($out)
		echo '{'.join(',', $out).'}';

?>