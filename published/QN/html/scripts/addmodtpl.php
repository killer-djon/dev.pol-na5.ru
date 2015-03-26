<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QN/qn.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QN";

	pageUserAuthorization( $SCR_ID, $QN_APP_ID, false );

	if ( !isset( $action ) )
		$action = ACTION_NEW;

	//
	// Page variables setup
	//

	$locStrings = $loc_str[$language];
	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;

	$btnIndex = getButtonIndex( array( BTN_SAVE, BTN_CANCEL, "deleteTplBtn", 'saveaddbtn' ), $_POST );

	switch ($btnIndex) {
		case 3 :
		case 0 :

					$safehtml =& new safehtml();

					if ( $use_editor == 0 )
					{
						$res = file_get_contents( $_FILES['tplfile']['tmp_name'] ) ;

						if ( $res == NULL  ) {
							$errorStr = "Error uploading file " . $_FILES['tmp_name'];
							break;
						}

						$result = $safehtml->parse($res);

						$tplData["QNT_HTML"] = addslashes( trim( $result ) );

					}
					else
					{
						$excl = null;

						//$tplData["QNT_HTML"] = $safehtml->parse(stripslashes($tplData["QNT_HTML"]));
					}
					//print $tplData["QNT_HTML"];
					//exit;

					// Make note attachments list
					//
					$res = qn_addModTpl( $action, $currentUser, prepareArrayToStore($tplData, $excl ), $locStrings, $qnStrings );

					if ( PEAR::isError( $res ) )
					{
						$errorStr = $res->getMessage();
						break;
					}

					if ( $btnIndex == 0 )
						redirectBrowser( PAGE_QN_TPLLIST, array() );
					else {
						$params = array();
						$params[ACTION] = ACTION_NEW;
						redirectBrowser( PAGE_QN_ADDMODTPL, $params );
					}
		case 1 :
					redirectBrowser( PAGE_QN_TPLLIST, array() );
		case 2 :
					$res = qn_deleteTpl( $currentUser, $tplData["QNT_ID"], $locStrings, $qnStrings );
					if ( PEAR::isError($res) )
					{
						$errorStr = $res->getMessage();
						$fatalError = true;

						break;
					}

					redirectBrowser( PAGE_QN_TPLLIST, array() );
	}

	switch (true) {
		case true :

				if ( !isset($edited) || !$edited )
				{
					if ( $action != ACTION_NEW )
					{
						$tplData = qn_getTemplate( $QNT_ID, $currentUser, $locStrings );

						if ( PEAR::isError($tplData) )
						{
							$fatalError = true;
							$errorStr = $tplData->getMessage();

							break;
						}
					}
				}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $locStrings, $language, $QN_APP_ID );

	$title = ( $action == ACTION_NEW ) ? $qnStrings['qnt_screen_add_title'] : $qnStrings['qnt_screen_modify_title'];

	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_QN_ADDMODTPL );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "qnStrings", $qnStrings );
	$preproc->assign( "locStrings", $locStrings );

	$preproc->assign( "HTML_AREA_FIELD", "tplData[QNT_HTML]" );

	if ( !$fatalError )
	{
		if ( isset($tplData) ) {
			if ( isset($edited) )
				$tplData["QNT_HTML"] = stripSlashes( $tplData["QNT_HTML"] );

			$preproc->assign( "tplData", prepareArrayToDisplay( $tplData, array( "QNT_HTML" ), isset($edited) && $edited ) );
		}
	}

	$preproc->display( "addmodtpl.htm" );
?>
