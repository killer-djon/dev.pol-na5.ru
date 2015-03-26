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
	$invalidField = null;
	$contactCount = 0;
	$thumbnailsGenerated = false;
	$duplicateFilesFound = false;

	$btnIndex = getButtonIndex( array( "showSender", "addsenderbtn" ), $_POST, false );

	$commonRedirParams = array();

	switch ($btnIndex) 
	{
		case 'showSender' :
				$curScreen = 0;
				break;

		case 'addsenderbtn' :
				redirectBrowser( PAGE_MM_ADDMODSENDER, array( ) );
				break;
	}

	switch (true) {
		case true : 

					if ( !isset($edited) && !isset($curScreen) )
						$curScreen = 0;

						
					$senders = mm_getSenders( $currentUser, $kernelStrings );
					unset( $senders[-1] );
	
						
					// Prepare tabs
					//
					$tabs = array();

					$checked = ($curScreen == 0) ? "checked" : "unchecked";
					$tabs[] = array( 'caption'=>$mmStrings['sv_tab_sender_label'], 'link'=>sprintf( $processButtonTemplate, 'showSender' )."||$checked" );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $mmStrings['sv_screen_short_name'] );
	$preproc->assign( FORM_LINK, PAGE_MM_SERVICE );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );

	if ( !$fatalError ) {
		$preproc->assign( SORTING_COL, $sorting );

		$preproc->assign( "tabs", $tabs );
		$preproc->assign( "curScreen", $curScreen );

		
		$preproc->assign( "senders", $senders );
	}

	$preproc->display( "service.htm" );
?>