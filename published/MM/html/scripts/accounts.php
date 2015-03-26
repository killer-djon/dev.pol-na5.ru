<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "MM";

	set_time_limit( 3600 );

	pageUserAuthorization( $SCR_ID, $MM_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$mmStrings = $mm_loc_str[$language];
	$contactCount = 0;

	$btnIndex = getButtonIndex( array( "showAccount", "addaccbtn" ), $_POST, false );
	
	switch ($btnIndex) 
	{
		case 'showAccount' :
				$curScreen = 0;
				break;

		case 'addaccbtn' :

				if(onWebAsystServer())
					$redirect_page = PAGE_MM_SELECTMODACCOUNT;
				else
					$redirect_page = PAGE_MM_ADDMODACCOUNT;

				redirectBrowser( $redirect_page, array( ) );
				break;
	}

	switch (true) {
		case true : 

					if ( !isset($edited) && !isset($curScreen) )
						$curScreen = 0;

					$accounts = mm_getAccounts( $currentUser, $kernelStrings );
					if (PEAR::isError($accounts)) {
					  $errorStr = $accounts->getMessage();
					  $fatalError = true;
					  break;
					}

					foreach($accounts as $acc)
					{
						if($acc['MMA_INTERNAL'])
							$acc['MMA_EMAIL'] .= '@'.$acc['MMA_DOMAIN'];
						$acc_out[] = $acc;
					}

					$checked = ($curScreen == 0) ? "checked" : "unchecked";
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $mmStrings['acc_screen_short_name'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );
	$preproc->assign( "hrefPage", PAGE_MM_ADDMODACCOUNT );

	if ( !$fatalError )
	{
		$preproc->assign( "curScreen", $curScreen );
		$preproc->assign( "accounts", $acc_out );
	}

	$preproc->display( "accounts.htm" );
?>