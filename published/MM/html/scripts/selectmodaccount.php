<?php

	require_once '../../../common/html/includes/httpinit.php';
	require_once WBS_DIR . 'published/MM/mm.php';

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = 'MM';

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$invalidField = null;
	$upgradeLink = false;

	$btnIndex = getButtonIndex( array( "nextbtn", "cancelbtn" ), $_POST, false );

	$mmInternalComment = strtolower( $currentUser ) . '@' . getenv( 'HTTP_HOST' );

	switch( $btnIndex )
	{
		case 'nextbtn' :

			if( $onServer )
			{
				$accounts = mm_getAccounts( $currentUser, $kernelStrings );
				if( PEAR::isError( $accounts ) )
				{
				  $errorStr = $accounts->getMessage();
				  $fatalError = true;
				  break;
				}
				$count = 0;
				foreach( $accounts as $acc)
					if( $acc['MMA_INTERNAL'] )
						$count++;

				$dailyMaxRec = mm_getLimitationOption( MM_OPT_DAILY_RECIPIENTS_LIMIT, $kernelStrings );
//				$accounsLimit = $mt_hosting_plan_extensions[$MM_APP_ID][$dailyMaxRec]['MM_ACCOUNTS_NUMBER'];
//				if( $accounsLimit != -1 && $count >= $accounsLimit )
				if( MAX_USER_COUNT && ( MAX_USER_COUNT != -1 ) && ( $count >= MAX_USER_COUNT ) )
				{
				  $errorStr = sprintf( $mmStrings['app_create_account_limit'], MAX_USER_COUNT ) .
						' <a href="/AA/html/scripts/change_plan.php?Select_your_tariff&enlarge=MM">' .
						$kernelStrings['app_upgradeacc_label'] . '</a>';
				  break;
				}
			}

			redirectBrowser( PAGE_MM_ADDMODACCOUNT, array('onServer'=>$onServer) );

			break;

		case 'cancelbtn' :

			redirectBrowser( PAGE_MM_MAILMASTER, array( ) );

			break;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $mmStrings['acc_add_label'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );
	$preproc->assign( 'mmInternalComment', $mmInternalComment );
	$preproc->assign( 'upgradeLink', $upgradeLink );

	$preproc->assign( mainPageURL, prepareURLStr(PAGE_MM_MAILMASTER, array()) );

	if ( !$fatalError )
		$preproc->assign( "internalServer", $internalServer );

	$preproc->display( "selectmodaccount.htm" );
?>