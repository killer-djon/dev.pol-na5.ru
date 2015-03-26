<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . 'published/MM/mm.php';

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

	$btnIndex = getButtonIndex( array( "savebtn", "cancelbtn", "deletebtn" ), $_POST, false );

	if( $action == 'delete' )
		$btnIndex = 'deletebtn';

	$commonRedirParams = array();

	if(!empty($onServer)) // selected by user
		$htmlForm = 'addintaccount.htm';
	else
		$htmlForm = 'addextaccount.htm';

	if( !isset( $MMA_ID ) || !isset( $action ) )
		$action = ACTION_NEW;

	$str = "qwertyuiopasdfghjkzxcvbnmQWERTYUPASDFGHJKLZXCVBNM23456789"; 
	$count = strlen($str) - 1; 
	$newPassword = '';
	for($i=0; $i<7; $i++) 
		$newPassword .= $str[rand(0, $count)]; 

	switch($btnIndex)
	{
		case 'savebtn' :

			if($accountData['MMA_INTERNAL'])
			{
				$htmlForm = 'addintaccount.htm';
				//
				// Fill required form fields
				//
				$accountData[MMA_SERVER] = '*';
				$accountData[MMA_LOGIN] = '*';
			}
			else
			{
				$htmlForm = 'addextaccount.htm';
				$accountData[MMA_DOMAIN] = '*';
			}

			$accountData['MMA_EMAIL'] = strtolower($accountData['MMA_EMAIL']);
			$accountData['MMA_DOMAIN'] = strtolower($accountData['MMA_DOMAIN']);

			$account = new mm_account( $mm_account_data_schema );

			if( $action == 'edit' )
				$accountData['MMA_ID'] = $MMA_ID;

			$ret = $account->loadFromArray( $accountData, $kernelStrings, true, array( s_datasource=>s_form ) );
			if( PEAR::isError( $ret ) )
			{
				$errorStr = $ret->getMessage();
				$errCode = $ret->getCode();

				if( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
					$invalidField = $ret->getUserInfo();

				break;
			}

			if( !$accountData['MMA_INTERNAL'] )
				$email_str = $accountData['MMA_EMAIL'];
			else
				$email_str = $accountData['MMA_EMAIL'].'@'.$accountData['MMA_DOMAIN'];
			$email_arr = explode('@', $email_str);

			if( !valid_email( $email_str ) )
			{
				$errorStr = $mmStrings['acc_inputemail_error'];
				break;
			}

			if(!$accountData['MMA_INTERNAL'] && function_exists('getmxrr') && !getmxrr($email_arr[1], $mxhosts))
			{
				$errorStr = sprintf($mmStrings['app_unavailable_email'], $email_str);
				break;
			}

			if(!$accountData['MMA_ACCESS'] && $accountData['MMA_INTERNAL'])
				$accountData['MMA_PASSWORD'] = $newPassword;
			$account->MMA_PASSWORD = $accountData['MMA_PASSWORD'];

			//
			// Save account to common DB
			//
			if( $accountData['MMA_INTERNAL'] )
			{
				$accountData['MMA_OWNER'] = $DB_KEY;

				$dailyMaxRec = mm_getLimitationOption( MM_OPT_DAILY_RECIPIENTS_LIMIT, $kernelStrings );
				$accounsLimit = $mt_hosting_plan_extensions[$MM_APP_ID][$dailyMaxRec]['MM_DISK_QUOTA'];
				$accountData['MMA_QUOTA'] = $accounsLimit;

				if( $action == ACTION_NEW )
					$ret = MailCommonDB::addAccount($accountData);
				else
					$ret = MailCommonDB::updateAccount($accountData);
				if( PEAR::isError( $ret ) )
				{
					switch ($ret->userinfo)
					{
					  case 1:
							$errorStr = $mmStrings['acc_dbexist_error'];
							break;

						default:
							$errorStr = $ret->getMessage();
							$err = true;
					}
					if(isset($err))
						break;
				}
			}

			//
			// Save account to local DB
			//
			$ret = $account->saveEntry( $currentUser, $action, $kernelStrings, $mmStrings );
			if( PEAR::isError( $ret ) )
			{
				switch ($ret->getCode())
				{
					case 1:
						$errorStr = $mmStrings['acc_dbexist_error'];
				}
				break;
			}
			if( empty( $MMA_ID ) &&
				PEAR::isError( $MMA_ID = $account->getAccountId( $accountData, $kernelStrings ) ) )
					$errorStr = $ret->getMessage();

			if( !$accountData['MMA_INTERNAL'] ) // || $doExit )
				redirectBrowser( PAGE_MM_MAILMASTER, array( ) );
			else
				$action = 'edit';

			break;

		case 'cancelbtn' :

			redirectBrowser( PAGE_MM_MAILMASTER, array( ) );

			break;

		case 'deletebtn' :

			if($accountData['MMA_INTERNAL'])
				$htmlForm = 'addintaccount.htm';
			else
				$htmlForm = 'addextaccount.htm';

			$accounts = mm_getAccounts( $currentUser );
			if (PEAR::isError($accounts))
			{
				$errorStr = $accounts->getMessage();
				$fatalError= true;
				break;
			}
			$accounts['DB_KEY'] = $DB_KEY;

			//
			// Delete account from local DB
			//
			$account = new mm_account( $mm_account_data_schema );
			if( PEAR::isError( $ret = $account->delete($MMA_ID, $kernelStrings) ) )
			{
				$errorStr = $ret->getMessage();
				break;
			}

			//
			// Delete account from common DB
			//
			if ( $accounts[$MMA_ID]['MMA_INTERNAL'] )
			{
				$accounts[$MMA_ID]['MMA_OWNER'] = $DB_KEY;

				if ( PEAR::isError( $ret = MailCommonDB::deleteAccount( $accounts[$MMA_ID] ) ) )
				{
					$errorStr = $ret->getMessage();
					break;
				}
			}

			redirectBrowser( PAGE_MM_MAILMASTER, array( ) );
			break;
	}

	switch (true)
	{
		case true :

			if ( !isset($edited) || !$edited )
			{
				$account = new mm_account( $mm_account_data_schema );

				if( $action == ACTION_NEW )
				{
					$accountData = $account->getValuesArray();

					if(!empty($onServer))
						$accountData['MMA_PASSWORD'] = $newPassword;
				}
				else
				{
					if ( PEAR::isError($ret = $account->loadEntry($MMA_ID, $kernelStrings, $mmStrings ) ) )
					{
						$fatalError = true;
						$errorStr = $ret->getMessage();

						break;
					}
					$accountData = $account->getValuesArray();

					if($accountData['MMA_INTERNAL'])
						$htmlForm = 'addintaccount.htm';
					else
						$htmlForm = 'addextaccount.htm';

					if ( !$ret )
					{
						$action = ACTION_NEW;
						break;
					}
					if(!$accountData['MMA_INTERNAL'])
						$accountData['MMA_PASSWORD'] = '';
				}
			}

			// Prepare proto arrays for html "options"

			$protocol_names = array();
			$protocol_ids = array();

			foreach ($mm_account_data_schema["MMA_PROTOCOL"]["OPTIONS"] as $key => $value )
			{
				$protocol_names[] = $value["NAME"];
				$protocol_ids[] = $value["VALUE"];
			}
			
			$domains = getUserDomainsList();
			$domain_names = array();
			foreach($domains as $row) {
				if ($row['REG_DATE'] != '')
				    $domain_names[] = $row['DOMAIN_NAME'];
			}
			sort($domain_names);
			$domain_names[] = $_SERVER['HTTP_HOST'];
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $action == 'edit' ? $mmStrings['ama_screen_edit_name'] : $mmStrings['acc_add_label'] );
	$preproc->assign( FORM_LINK, PAGE_MM_ADDMODACCOUNT );

	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );

	$preproc->assign( "protocol_names", $protocol_names );
	$preproc->assign( "protocol_ids", $protocol_ids );
	$preproc->assign( "domain_names", $domain_names );

	if ( !$fatalError )
	{
		$preproc->assign( "accountData", $accountData );

		$preproc->assign( "MMA_ID", $MMA_ID );
		$preproc->assign( "action", $action );

		if ( isset( $edited) )
			$preproc->assign( "edited", $edited );
	}

	$preproc->display( $htmlForm );
?>