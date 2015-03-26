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

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$qnStrings = $qn_loc_str[$language];
	$invalidField = null;
	$contactCount = 0;

	if( !isset($firstIndex) )
		$firstIndex = 0;

	define( 'USER_PER_PAGE', 6 );
	define( 'ACTION_DELETEFOLDER', 'DELETE' );

	$btnIndex = getButtonIndex( array('addfolderbtn', 'adduserbtn'), $_POST );

	switch ($btnIndex) {
		case 0 :
				redirectBrowser( PAGE_QN_ADDMODFOLDER, array( ACTION=>ACTION_NEW, 'QNF_ID_PARENT'=>base64_encode(TREE_ROOT_FOLDER), OPENER=>base64_encode(PAGE_QN_MANAGER) ) );
	}

	if ( isset($action) && $action == ACTION_DELETEFOLDER ) {
		$targetQNF_ID = base64_decode($QNF_ID);

		$res = $qn_treeClass->deleteFolder( $targetQNF_ID, $currentUser, $kernelStrings, true, "dd_onDeleteFolder", array('ddStrings'=>$ddStrings) );
		if ( PEAR::isError($res) )
			$errorStr = $res->getMessage();
	}

	switch (true) {
		case true : 
					$access = null;
					$hierarchy = null;
					$deletable = null;
					$folders = $qn_treeClass->listFolders( null, TREE_ROOT_FOLDER, $kernelStrings, 0, false, $access, $hierarchy, $deletable );
					if ( PEAR::isError($folders) ) {
						$fatalError = true;
						$errorStr = $folders->getMessage();

						break;
					}

					$userRights = $qn_treeClass->listIdentityRights( $kernelStrings );
					if ( PEAR::isError($userRights) ) {
						$fatalError = true;
						$errorStr = $userRights->getMessage();

						break;
					}

					if ( isset($U_ID) )
						$firstIndex = array_keyIndex( $userRights, $U_ID );

					foreach( $userRights as $key=>$value ) {
						$params = array( 'U_ID'=>base64_encode($key), 'firstIndex'=>$firstIndex, ACTION=>ACTION_EDIT );
						$value['ROW_URL'] = prepareURLStr( PAGE_QN_USERRIGHTS, $params );

						$userRights[$key] = $value;
					}


					foreach( $folders as $key=>$value ) {
						$params = array( 'firstIndex'=>$firstIndex, ACTION=>ACTION_EDIT, 'QNF_ID_PARENT'=>base64_encode($value->QNF_ID_PARENT), 'QNF_ID'=>base64_encode($key), OPENER=>base64_encode(PAGE_QN_MANAGER) );
						$value->ROW_URL = prepareURLStr(PAGE_QN_ADDMODFOLDER, $params);

						$params[ACTION] = ACTION_DELETEFOLDER;
						$value->DELETE_URL = prepareURLStr(PAGE_QN_MANAGER, $params);

						$params = array( 'firstIndex'=>$firstIndex, ACTION=>ACTION_NEW, 'QNF_ID_PARENT'=>base64_encode($key), OPENER=>base64_encode(PAGE_QN_MANAGER) );
						$value->NEW_URL = prepareURLStr(PAGE_QN_ADDMODFOLDER, $params);

						$folders[$key] = $value;
					}

					$userIDs = array_keys( $userRights );
					$maxUserCount = count($userIDs);

					$userRights = refinedSlice( $userRights, $firstIndex, USER_PER_PAGE );

					$prevIndex = $firstIndex - USER_PER_PAGE;
					if ( $prevIndex < 0 ) $prevIndex = 0;

					$nextIndex = $firstIndex + USER_PER_PAGE;
					if ( $nextIndex > $maxUserCount ) $nextIndex = $maxUserCount;
	}

	switch ($btnIndex) {
		case 1 :
				redirectBrowser( PAGE_QN_USERRIGHTS, array( ACTION=>ACTION_NEW, 'U_ID'=>null, 'firstIndex'=>$firstIndex ) );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $QN_APP_ID );

	$preproc->assign( PAGE_TITLE, $qnStrings['rights_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_QN_MANAGER );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	$preproc->assign( "qnStrings", $qnStrings );

	if ( !$fatalError ) {
		$preproc->assign( "folders", $folders );
		$preproc->assign( "foldersNum", count($folders) );
		$preproc->assign( "userRights", $userRights );
		$preproc->assign( "tree_access_mode_names", $tree_access_mode_names );

		$preproc->assign( "showUserSelector", $maxUserCount > USER_PER_PAGE );

		$preproc->assign( "userIDs", $userIDs );
		
		$prev_pageLink = ($firstIndex > 0)? prepareURLStr( PAGE_QN_MANAGER, array("firstIndex"=>$prevIndex) ) : null;
		$preproc->assign( "prev_pageLink", $prev_pageLink );

		$next_pageLink = ($firstIndex < ($maxUserCount-USER_PER_PAGE)) ? prepareURLStr( PAGE_QN_MANAGER, array("firstIndex"=>$nextIndex) ) : null;
		$preproc->assign( "next_pageLink", $next_pageLink );

		$colCount = count($userRights) + 2;

		if ( $next_pageLink ) $colCount++;
		if ( $prev_pageLink ) $colCount++;

		$preproc->assign( "colCount", $colCount );
	}

	$preproc->display( "quicknotesmanager.htm" );
?>