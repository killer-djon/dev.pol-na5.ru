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

	$btnIndex = getButtonIndex( array( 'cancelbtn' ), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_QP_QUICKPAGES, array() );
	}

	switch (true) {
		case true :


				$access = null;
				$hierarchy = null;
				$deletable = null;

				$folders = $qp_treeClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
														$access, $hierarchy, $deletable );

				if ( PEAR::isError($folders) )
				{
					$fatalError = true;
					$errorStr = $folders->getMessage();
					break;
				}

				foreach( $folders as $key=>$value )
				{
					$value->ROW_URL = prepareURLStr( PAGE_QP_QUICKPAGES, array( "currentBookID"=> base64_encode( $value->QPB_ID ) ) );

					$value->RIGHTS_STRING = "";

					if ( UR_RightsManager::CheckMask( $value->TREE_ACCESS_RIGHTS, UR_TREE_READ ) )
						$value->RIGHTS_STRING .= "R";

					if ( UR_RightsManager::CheckMask( $value->TREE_ACCESS_RIGHTS, UR_TREE_WRITE ) )
						$value->RIGHTS_STRING .= "W";

					if ( UR_RightsManager::CheckMask( $value->TREE_ACCESS_RIGHTS, UR_TREE_FOLDER ) )
						$value->RIGHTS_STRING .= "F";

					$folders[$key] = $value;
				}

				$isRootIdentity = $qp_treeClass->isRootIdentity( $currentUser, $kernelStrings );

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QP_APP_ID );

	$preproc->assign( PAGE_TITLE, $qpStrings['jmp_screen_title'] );
	$preproc->assign( FORM_LINK, PAGE_QP_JUMPBOOK );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "qpStrings", $qpStrings );
	$preproc->assign( "kernelStrings", $kernelStrings );

	$preproc->assign( "isRoot", $isRootIdentity );

	if ( !$fatalError )
	{
		$preproc->assign( "books", $folders );
		$preproc->assign( "bookCount", count( $folders ) );
	}

	$preproc->display( "jump2book.htm" );
?>