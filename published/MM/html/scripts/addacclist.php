<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/CM/cm.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "UC";

	pageUserAuthorization( $SCR_ID, $CM_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$cmStrings = $cm_loc_str[$language];
	$invalidField = null;
	$included_contacts_names = array();
	$notincluded_contacts_names = array();
	$included_groups_names = array();
	$notincluded_groups_names = array();

	$btnIndex = getButtonIndex( array(BTN_SAVE, BTN_CANCEL), $_POST );

	// Decode list identifier
	//
	$targetList = isset($CL_ID) ? base64_decode( $CL_ID ) : null;

	switch ($btnIndex) {
		case 0:
			$ContactList = new ContactList();
	
			$listdata['CL_MODIFYUSERNAME'] = getUserName( $currentUser, true );
	
			if ( $action == ACTION_EDIT )
				$listdata['CL_ID'] = $targetList;
	
			if ( !isset($listdata['CL_SHARED']) ) {
				$listdata['CL_SHARED'] = false;
				$listdata['CL_OWNER_U_ID'] = $currentUser;
			}
	
			$params = array( s_datasource=>s_form );
			$res = $ContactList->loadFromArray( prepareArrayToStore($listdata), $kernelStrings, true, $params );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				$invalidField = $res->getUserInfo();
	
				break;
			}
	
			// Save entry
			//
			$res = $ContactList->saveEntry( $action, $kernelStrings );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				$invalidField = $res->getUserInfo();
	
				break;
			}
	
			// Set contact list folders
			//
			if ( !isset($listdata['folders']) )
				$listdata['folders'] = array();
	
			$ContactList->setFolders( $listdata['folders'], $kernelStrings );
	
			$params = array();
			if ( $action == ACTION_NEW ) {
				$params['selectedList'] = base64_encode($res);
				$params['switchObjectType'] = base64_encode(CM_OT_LISTS);
			}
	
			// Set contact list contacts
			//
			if ( !isset($included_contacts) )
				$included_contacts = array();
	
			$ContactList->setContacts( $currentUser, $included_contacts, $kernelStrings );
	
			// Set contact list groups
			//
			if ( !isset($included_groups) )
				$included_groups = array();
	
			$ContactList->setGroups( $included_groups, $kernelStrings );
	
			redirectBrowser( PAGE_CM_CONTACTS, $params );
		case 1 :
			redirectBrowser( PAGE_CM_CONTACTS, array() );
	}

	switch (true) {
		case true :
			if ( $action == ACTION_NEW ) {
				if ( !isset($edited) )
					$listdata = array();
				if (!sizeof($included_contacts)) //if $included_contacts we are get via $_POST
					$included_contacts = array();
			} else {
				if ( $action == 'edit' || isset($edited) ) {
					$ContactList = new ContactList();
					$res = $ContactList->loadEntry( $targetList, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();
						break;
					}
					$listdata = (array)$ContactList;
					if (!sizeof($included_contacts)) //if $included_contacts we are get via $_POST
						$included_contacts = array_keys($ContactList->contacts);
					$included_groups = array_keys($ContactList->groups);
				}
			}
			
			// Load folders for the Folders tab
			//
			$access = null;
			$hierarchy = null;
			$deletable = null;
			$folders = $cm_groupClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $kernelStrings, 0, false,
													$access, $hierarchy, $deletable );
			if ( PEAR::isError($folders) ) {
				$fatalError = true;
				$errorStr = $folders->getMessage();

				break;
			}
			
			//
			// Load contacts for the Contacts tab
			//
			$CM_CONTACT_LIMIT = empty($showAllflag)
			? CM_CONTACT_LIMIT	//enable limit if
			: 0			//$showAllflag (method POST)
			;			//0 - disable limit

			$included_contacts = sizeof($included_contacts) == 0
			? array(-1)		//return -1 if no
			: $included_contacts	//included or if
			;			//NEW contact list
			
			$contacts = cm_listLimitContacts( $currentUser, $kernelStrings, $included_contacts, $CM_CONTACT_LIMIT );
			if ( PEAR::isError($contacts) ) {
				$fatalError = true;
				$errorStr = $contacts->getMessage();
				break;
			}
			$count = $contacts['COUNT']; //save 4 arrays for old code compatibility
			$included_contacts = array_keys($contacts['INC']);
			$notincluded_contacts = array_keys($contacts['EXC']);
			$included_contacts_names = array_values($contacts['INC']);
			$notincluded_contacts_names = array_values($contacts['EXC']);
			
			
			// Load groups for the Groups tab
			//
			$groups = listUserGroups( $kernelStrings, false );
			if ( PEAR::isError($groups) ) {
				$fatalError = true;
				$errorStr = $groups->getMessage();

				break;
			}

			foreach( $groups as $UG_ID=>$groupData )
				$groups[$UG_ID] = $groupData[UG_NAME];

			$fullGroupListIDs = array_keys($groups);

			$notincluded_groups = array();

			if ( !isset($included_groups) )
				$included_groups = array();

			$notincluded_groups = array_diff( $fullGroupListIDs, $included_groups );

			foreach( $included_groups as $key )
				$included_groups_names[] = $groups[$key];

			foreach( $notincluded_groups as $key )
				$notincluded_groups_names[] = $groups[$key];

			// Prepare form tabs
			//
			$tabs = array();

			$tabs[] = array( PT_NAME=>$cmStrings['cml_list_tab'],
								PT_PAGE_ID=>'LIST',
								PT_FILE=>'aml_listtab.htm' );
			$tabs[] = array( PT_NAME=>$cmStrings['cml_folders_tab'],
								PT_PAGE_ID=>'FOLDERS',
								PT_FILE=>'aml_folderstab.htm' );
			$tabs[] = array( PT_NAME=>$cmStrings['cml_contacts_tab'],
								PT_PAGE_ID=>'CONTACTS',
								PT_FILE=>'aml_contactstab.htm' );
			$tabs[] = array( PT_NAME=>$cmStrings['cml_groups_tab'],
								PT_PAGE_ID=>'GROUP',
								PT_FILE=>'aml_grouptab.htm' );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $CM_APP_ID );

	$title = ($action == ACTION_NEW) ? $cmStrings['cml_addpage_title'] : $cmStrings['cml_modpage_title'];
	
	$preproc->assign("CCOUNT", $count); //get contacts count
	$preproc->assign("CLIMIT", $CM_CONTACT_LIMIT);
	
	
	if ($CM_CONTACT_LIMIT == 0)
		$preproc->assign( 'activetab', 'CONTACTS' );
	else
		$preproc->assign( 'activetab', '' );
	
	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_CM_ADDMODLIST );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "kernelStrings", $kernelStrings );
	
	$cmStrings['cm_load_cont_number'] = sprintf ($cmStrings['cm_load_cont_number'], $CM_CONTACT_LIMIT, $count);
	
	$preproc->assign( "cmStrings", $cmStrings );
	$preproc->assign( ACTION, $action );

	if ( !$fatalError ) {
		$preproc->assign( 'folders', $folders );
		$preproc->assign( 'tabs', $tabs );
		$preproc->assign( 'listdata', $listdata );

		$preproc->assign( "included_contacts", $included_contacts );
		$preproc->assign( "notincluded_contacts", $notincluded_contacts );
		$preproc->assign( "included_contacts_names", $included_contacts_names );
		$preproc->assign( "notincluded_contacts_names", $notincluded_contacts_names );

		$preproc->assign( "included_groups", $included_groups );
		$preproc->assign( "notincluded_groups", $notincluded_groups );

		$preproc->assign( "included_groups_names", $included_groups_names );
		$preproc->assign( "notincluded_groups_names", $notincluded_groups_names );

		if ( $action == ACTION_EDIT ) {
			$preproc->assign( "CL_ID", $CL_ID );
			$preproc->assign( "targetList", $targetList );
		}
	}

	$preproc->display( "addmodlist.htm" );
?>