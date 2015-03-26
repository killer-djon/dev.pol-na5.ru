<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/MM/mm.php" );

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

	$commonRedirParams = array();

	if ( !isset( $MMS_ID ) || !isset( $action ) )
		$action = ACTION_NEW;

	switch ($btnIndex)
	{
		case 'savebtn' :

						$sender = new mm_sender( $mm_sender_data_schema );

						if ( $action == ACTION_EDIT )
							$senderData['MMS_ID']=$MMS_ID;

						$ret = $sender->loadFromArray( $senderData, $kernelStrings, true, array( s_datasource=>s_form ) );

						if ( PEAR::isError( $ret ) )
						{
							$errorStr = $ret->getMessage();
							$errCode = $ret->getCode();

							if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE) ) )
								$invalidField = $ret->getUserInfo();

							break;
						}

						$MM_ND = $sender->saveEntry( $currentUser, $action, $kernelStrings, $mmStrings );
						if ( PEAR::isError( $MM_ND ) )
						{
							$errorStr = $MM_ND->getMessage();
							break;
						}


		case 'cancelbtn' :

						redirectBrowser( PAGE_MM_SERVICE, array( ) );

				break;


		case 'deletebtn' :

						$sender = new mm_sender( $mm_sender_data_schema );
						$ret = $sender->delete( $MMS_ID, $kernelStrings);

						if ( PEAR::isError( $ret ) )
						{
							$errorStr = $ret->getMessage();
							break;
						}

						redirectBrowser( PAGE_MM_SERVICE, array( ) );
				break;

	}

	switch (true)
	{
		case true :

					if ( !isset($edited) || !$edited )
					{
						$sender = new mm_sender( $mm_sender_data_schema );

						if ( $action == ACTION_NEW )
							$senderData = $sender->getValuesArray();
						else
						{

							if ( PEAR::isError($ret = $sender->loadEntry($MMS_ID, $kernelStrings, $mmStrings ) ) )
							{
								$fatalError = true;
								$errorStr = $ret->getMessage();

								break;
							}

							$senderData = $sender->getValuesArray();

							if ( !$ret )
							{
								$action = ACTION_NEW;
								break;
							}

						}

					}

					$language_names = array();
					$language_ids = array();

					foreach( $wbs_languages as $key=>$value )
					{
						$language_names[] = $value["NAME"];
						$language_ids[] = $value["ID"];
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $MM_APP_ID );

	$preproc->assign( PAGE_TITLE, $action == ACTION_EDIT ? $mmStrings['ams_screen_edit_name'] : $mmStrings['ams_screen_add_name'] );
	$preproc->assign( FORM_LINK, PAGE_MM_ADDMODSENDER );

	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "mmStrings", $mmStrings );

	$preproc->assign( "language_names", $language_names );
	$preproc->assign( "language_ids", $language_ids );

	if ( !$fatalError )
	{
		$preproc->assign( "senderData", $senderData );

		$preproc->assign( "MMS_ID", $MMS_ID );
		$preproc->assign( "action", $action );

		if ( isset( $edited) )
			$preproc->assign( "edited", $edited );

	}

	$preproc->display( "addmodsender.htm" );
?>