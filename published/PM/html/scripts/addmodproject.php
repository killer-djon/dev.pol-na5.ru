<?php


    define('NEW_CONTACT', 1);
    
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Authorization
	//

	$SCR_ID = "WL";
	$errorStr = null;
	$fatalError = false;
	$metric = metric::getInstance();
	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false, true );
	if ( $fatalError ) {
		$fatalError = false;
		$errorStr = null;
		$SCR_ID = "WL";

		pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );
	}
	
	if (empty($opener))
		$opener = PAGE_PM_WORKLIST;

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	$invalidField = null;
	$saveBtnPressed = false;
	if (!isset($pageRights))
		$pageRights = false;

	if ( isset($lastTab) && strlen($lastTab) && $lastTab != 'null' )
		$activeTab = $lastTab;
	if (!$pageRights)
		$activeTab = "PROJECT";

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, PM_BTN_CLOSEPROJECT, PM_BTN_DELETEPROJECT), $_POST );
	
	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( $opener, array("folder"=>$folder, SORTING_COL=>$sorting, "currentPage"=>$currentPage, "P_ID"=>base64_encode($projectData["P_ID"]) ) );
			
			break;
		}

		case 1 : {
			$projectData["P_MODIFYUSERNAME"] = getUserName( $currentUser, true );
			$saveBtnPressed = true;
			
			if (!$pageRights) {
				$projectID= pm_addmodProject( $action, prepareArrayToStore($projectData), $pmStrings, $kernelStrings );
				$userAccessRights[UR_OBJECTID] = $projectID;
				if ( PEAR::isError($projectID) ) {
					$errorStr = $projectID->getMessage();

					$errCode = $projectID->getCode();

					if ( in_array($errCode, array(ERRCODE_INVALIDFIELD, ERRCODE_INVALIDLENGTH, ERRCODE_INVALIDDATE)) )
						$invalidField = $projectID->getUserInfo();

					break;
				}
				$metric->addAction($DB_KEY, $currentUser, 'PM', 'ADDPROJECT', 'ACCOUNT');
				$userAccessRights[UR_OBJECTID] = $projectID;
				$userAccessRights[UR_PATH] = "/ROOT/PM/PROJECTS";
				$userAccessRights[UR_ACTION] = "EDITUSER";
				$userAccessRights[UR_FIELD] = "userAccessRights";
				$userAccessRights[UR_EDITED] = 1;
			} else {
				$projectID = $projectData["P_ID"];
			}
			
			// Set's the manager rights
			if ($projectData["U_ID_MANAGER"])
				$userAccessRights[$projectData["U_ID_MANAGER"]][$userAccessRights[UR_PATH] . "/" . $userAccessRights[UR_OBJECTID]] = array( UR_TREE_READ=>1, UR_TREE_WRITE=>1, UR_TREE_FOLDER=>1 );
			if ($action == PM_ACTION_NEW || !$pageRights)
				$userAccessRights[$currentUser][$userAccessRights[UR_PATH] . "/" . $userAccessRights[UR_OBJECTID]] = array( UR_TREE_READ=>1, UR_TREE_WRITE=>1, UR_TREE_FOLDER=>1 );
			
			// * Save project access rights
			$userAccessRights[UR_REAL_ID] = $projectID;
			$saveResult =  $UR_Manager->SaveItem( $userAccessRights );
			if ( PEAR::isError( $saveResult ) )
			{
				$errorStr = $saveResult->getMessage();
				break;
			}

			if ($pageRights) {
				$groupAccessRights[UR_REAL_ID] = $projectID;
				$saveResult =  $UR_Manager->SaveItem( $groupAccessRights );
				if ( PEAR::isError( $saveResult ) )
				{
					$errorStr = $saveResult->getMessage();
					break;
				}
			}
			
			$projectData["P_ID"] = $projectID;

			if ( $action == PM_ACTION_NEW )
				$folder = RS_ACTIVE;

			redirectBrowser( $opener, array("folder"=>$folder, SORTING_COL=>$sorting, "currentPage"=>$currentPage, "projectScreen" => PM_SHOW_WORKLIST, "P_ID"=>base64_encode($projectData["P_ID"])) );
		}

		case 2 : {
			redirectBrowser( PAGE_PM_CLOSEPROJECT, array("folder"=>$folder, OPENER=>$opener, ACTION=>PM_ACTION_CLOSE, SORTING_COL=>$sorting, "P_ID"=>base64_encode($projectData["P_ID"]), "currentPage"=>$currentPage) );
			break;
		}

		case 3 : {
			$res = pm_deleteProject( $projectData, $pmStrings, $language, $kernelStrings );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				break;
			}

			redirectBrowser( $opener, array("folder"=>RS_ACTIVE, SORTING_COL=>$sorting, "currentPage"=>$currentPage, "P_ID"=>base64_encode($projectData["P_ID"])) );

			break;
		}
	}

	switch ( true ) {
		case ( true ) :
			if ( (!isset($edited) || !$edited) && ($action == PM_ACTION_NEW) )
			{
				$res = pm_projectAddingPermitted( $kernelStrings, $pmStrings, $action );
				if ( PEAR::isError($res) ) {
					$fatalError = true;
					$errorStr = $res->getMessage ();
					break;
				}
				
				$projectData = pm_initProject();
				$projectName = $pmStrings['amp_newproject_label'];
			}

			if ( (!isset($edited) || !$edited) && ($action == PM_ACTION_MODIFY) )
			{
				$projectData["P_ID"] = base64_decode( $P_ID );

				$res = exec_sql( $qr_pm_select_project, $projectData, $projectData, true );
				if ( PEAR::isError($res) )
				{
					$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

					$fatalError = true;
					break;
				}

				$projectData = prepareArrayToDisplay( $projectData, array("P_DESC") );

				$projectData["P_STARTDATE"] = convertToDisplayDate( $projectData["P_STARTDATE"] );
				$projectData["P_MODIFYDATETIME"] = convertToDisplayDateTime( $projectData["P_MODIFYDATETIME"], false, true, true );

				$projectName = implode( ". ", array( strTruncate( $projectData["C_NAME"], PM_CUST_NAME_LEN ), $projectData["P_DESC"]) );
			}

			$customers_names = array();
			$customers_ids = array();

			$qr = db_query( $qr_pm_select_active_customers );
			if ( PEAR::isError($qr) )
			{
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_CUSTOMERFIELD] );

				$fatalError = true;
				break;
			}

			$customers_names[count($customers_names)] = $pmStrings['amp_selectcust_item'];
			$customers_ids[count($customers_ids)] = null;

			while ( $row = db_fetch_array($qr) )
			{
				$row = prepareArrayToDisplay( $row, array("C_NAME") );

				$customers_names[count($customers_names)] = strTruncate( $row["C_NAME"], PM_CUST_NAME_LEN );

				$customers_ids[count($customers_ids)] = $row["C_ID"];
			}

			@db_free_result( $qr );

			$managers_names = array();
			$managers_ids = array();

			$qr = db_query( $qr_pm_select_managers );
			if ( PEAR::isError($qr) )
			{
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_MANAGERFIELD] );

				$fatalError = true;
				break;
			}

			$managers_names[count($managers_names)] = $pmStrings['amp_selectmanager_item'];
			$managers_ids[count($managers_ids)] = null;

			while ( $row = db_fetch_array($qr) )
			{
				$managers_names[count($managers_names)] = getArrUserName( $row, true );

				$managers_ids[count($managers_ids)] = $row["U_ID"];
			}

			@db_free_result( $qr );

			$works_count = db_query_result( $qr_pm_count_project_works, DB_FIRST, $projectData );
			if ( PEAR::isError($works_count) )
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

			$confirmation_string = pm_makeConfirmationString( $works_count, $language, $pmStrings );

			// Prepare form tabs
			//
			$tabs = array();

			if (!$pageRights) {
				$tabs[] = array( PT_NAME=>$pmStrings['amp_project_title'],
									PT_PAGE_ID=>'PROJECT',
									PT_FILE=>'amp_project.htm',
									PT_CONTROL=>'projectData[P_DESC]'
									);
			} else {
				$tabs[] = array( PT_NAME=>$pmStrings['amp_users_title'],
									PT_PAGE_ID=>'USERS',
									PT_FILE=>'amp_users.htm');

				$tabs[] = array( PT_NAME=>$pmStrings['amp_groups_title'],
									PT_PAGE_ID=>'GROUPS',
									PT_FILE=>'amp_groups.htm' );
			}

			// Prepare data for the Users and Groups tabs
			//
			if ( !isset( $edited ) )
			{
				if ( $action == PM_ACTION_MODIFY )
				{
					$userAccessRights = array( UR_PATH=>$PMRightsManager->GetFullPath(), UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>$projectData["P_ID"], UR_FIELD=>"userAccessRights" );
					$groupAccessRights = array( UR_PATH=>$PMRightsManager->GetFullPath(), UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>$projectData["P_ID"], UR_FIELD=>"groupAccessRights" );
				} else {
					$userAccessRights = array( UR_PATH=>$PMRightsManager->GetFullPath(), UR_ACTION=>UR_ACTION_EDITUSER, UR_OBJECTID=>null, UR_FIELD=>"userAccessRights", UR_COPYFROM=>'ROOT' );
					$groupAccessRights = array( UR_PATH=>$PMRightsManager->GetFullPath(), UR_ACTION=>UR_ACTION_EDITGROUP, UR_OBJECTID=>null, UR_FIELD=>"groupAccessRights", UR_COPYFROM=>'ROOT' );
				}
			}

			if ( $userAccessRights[UR_OBJECTID] == UR_SYS_ID && !isset( $userAccessRights[UR_REAL_ID] ) )
				$userAccessRights[UR_OBJECTID] = null;

			if ( $groupAccessRights[UR_OBJECTID] == UR_SYS_ID && !isset( $groupAccessRights[UR_REAL_ID] ) )
					$groupAccessRights[UR_OBJECTID] = null;

			if ( isset($edited) || $action == PM_ACTION_MODIFY ) {
				//$userAccessRights[$currentUser] = array($PMRightsManager->GetFullPath() => array (1 => 1, 2 => 1, 4=>1));
				$userAccessRights[PM_SELECTED_MANAGER] = $projectData['U_ID_MANAGER'];
			}
			
			$linkedPaths = array ();
			if (!empty($projectData["DF_ID"])) {
				$linkedPaths[] = "/ROOT/DD/FOLDERS/" . $projectData["DF_ID"];
			}
			$userAccessRights[PM_LINKED_PATHS] = $linkedPaths;
			$groupAccessRights[PM_LINKED_PATHS] = $linkedPaths;
			
			
			
			if ($pageRights) {
				$userAccessRightsHtml =  $UR_Manager->RenderItem( $userAccessRights );
				if ( PEAR::isError($userAccessRightsHtml))
				{
					$fatalError = true;
					$errorStr = $userAccessRightsHtml->getMessage();
				}

				$groupAccessRightsHtml =  $UR_Manager->RenderItem( $groupAccessRights );
				if ( PEAR::isError($groupAccessRightsHtml))
				{
					$fatalError = true;
					$errorStr = $groupAccessRightsHtml->getMessage();
				}
			}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );

	$pageTitle = $action == ACTION_NEW ? $pmStrings['amp_addproj_title'] : $pmStrings['amp_editproj_title'];
	if ($pageRights)
		$pageTitle = $kernelStrings["app_treemodfolder_title"];

	$preproc->assign( PAGE_TITLE, $pageTitle );
	$preproc->assign( FORM_LINK, PAGE_PM_ADDMODPROJECT );
	$preproc->assign( ACTION, $action );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( OPENER, $opener );
	$preproc->assign( "folder", $folder );
	$preproc->assign( "currentPage", $currentPage );

	$preproc->assign( "pageRights", $pageRights );
	$preproc->assign( "projectName", $projectName );

	if ( isset($projectData) )
		$preproc->assign( "projectData", $projectData );
	else
		$preproc->assign( "projectData", null );

	if ( isset($edited) )
		$preproc->assign( "edited", $edited );

	if ( !isset($activeTab) )
		$activeTab = ($projectRights) ? 'PROJECT' : 'USERS';

	$preproc->assign( "activeTab", $activeTab );

	$preproc->assign( "customers_names", $customers_names );
	$preproc->assign( "customers_ids", $customers_ids );
	$preproc->assign( "tabs", $tabs );

	$preproc->assign( "managers_names", $managers_names );
	$preproc->assign( "managers_ids", $managers_ids );

	$preproc->assign( "confirmation_string", $confirmation_string );
	$preproc->assign( "works_count", $works_count );

	$preproc->assign( "userAccessRightsHtml", $userAccessRightsHtml );
	$preproc->assign( "groupAccessRightsHtml", $groupAccessRightsHtml );

	$preproc->display("addmodproject.htm");

?>