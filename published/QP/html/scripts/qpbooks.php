<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/QP/qp.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "QP";

	pageUserAuthorization( $SCR_ID, $QP_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qpStrings = $qp_loc_str[$language];

	$btnIndex = getButtonIndex( array( 'addbookbtn' ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_QP_QPADDMODBOOK, $commonRedirParams );
	}

	switch (true) {
		case true :

				$access = null;
				$hierarchy = null;
				$deletable = null;

				$themes = qp_getThemesList( $currentUser, $kernelStrings );

				if ( PEAR::isError( $themes ) )
				{
					$fatalError = true;
					$errorStr = $themes->getMessage();
					break;
				}

				$folders = $qp_treeClass->listFolders( "", TREE_ROOT_FOLDER, $kernelStrings, 0, false,
														$access, $hierarchy, $deletable );

				if ( PEAR::isError($folders) )
				{
					$fatalError = true;
					$errorStr = $folders->getMessage();
					break;
				}

				foreach( $folders as $key=>$value )
				{

					if ( $value->QPB_PUBLISHED == 1 )
					{
						if ( !is_null( $value->QPB_THEME ) && isset($themes[$value->QPB_THEME]) )
						{
							$value->THEMENAME = $themes[$value->QPB_THEME]["QPT_NAME"];
							$value->THEMENAME .= " (".$qpStrings[ ( $themes[$value->QPB_THEME]["QPT_TYPE"]==1 ) ? "qp_publish_tree_title" : "qp_publish_plain_title" ].")";
						}
						else
							$value->THEMENAME = $qpStrings["app_systemtheme_text"];
					}
					else
						$value->THEMENAME = "-- ".$qpStrings["qp_screen_notpublished_title"]." --";

					$value->TREE_ACCESS_RIGHTS = $qp_treeClass->getIdentityFolderRights( $currentUser, $key, $kernelStrings );

					$value->RIGHTS_STRING = "";

					if ( UR_RightsManager::CheckMask( $value->TREE_ACCESS_RIGHTS, UR_TREE_READ ) )
						$value->RIGHTS_STRING .= "R";

					if ( UR_RightsManager::CheckMask( $value->TREE_ACCESS_RIGHTS, UR_TREE_WRITE ) )
						$value->RIGHTS_STRING .= "W";

					if ( UR_RightsManager::CheckMask( $value->TREE_ACCESS_RIGHTS, UR_TREE_FOLDER ) )
						$value->RIGHTS_STRING .= "F";

					$value->ROW_URL = prepareURLStr( PAGE_QP_QPADDMODBOOK, array( "QPB_ID"=> base64_encode( $value->QPB_ID ), ACTION=>ACTION_EDIT, OPENER => base64_encode( PAGE_QP_BOOKS ) ) );

					$folders[$key] = $value;
				}

				$isRootIdentity = $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings );

				if ( !isset( $currentPage ) )
					$currentPage = 1;
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['qpb_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QP_BOOKS );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	$preproc->assign( "isRoot", $isRootIdentity );

	if ( !$fatalError )
	{
		$preproc->assign( "books", $folders );
	}

	$preproc->display( "booklist.htm" );
?>