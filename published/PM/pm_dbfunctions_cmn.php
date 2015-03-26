<?php

	//
	// Project Management DMBS-independent application functions
	//

	function pm_addmodCustomer( $action, $customerData, $pmStrings, $kernelStrings )
	//
	// Examines incoming parameters and inserts (or modifies) customer record into database.
	//
	//		Parameters:
	//			$action - type of action - addition ($action == PM_ACTION_NEW) or modification ($action == PM_ACTION_MODIFY)
	//			$customerData - an associative array containing fields of CUSTOMER record
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//
	//		Returns customer identifier, or PEAR_Error.
	//
	{
		global $qr_pm_add_customer;
		global $qr_pm_modify_customer;
		global $qr_pm_select_customer_status;

		$C_NAME_length = 250;
		$C_ADDRESSSTREET_length = 50;
		$C_ADDRESSCITY_length = 30;
		$C_ADDRESSSTATE_length = 30;
		$C_ADDRESSZIP_length = 10;
		$C_ADDRESSCOUNTRY_length = 30;
		$C_CONTACTPERSON_length = 50;
		$C_PHONE_length = 50;
		$C_FAX_length = 50;
		$C_EMAIL_length = 50;
		$C_MODIFYUSERNAME_length = 50;

		$customerData = trimArrayData( $customerData );

		$requiredFields = array( "C_NAME" );

		if ( PEAR::isError($invalidField = findEmptyField($customerData, $requiredFields)) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$customerData['C_MODIFYUSERNAME'] = substr( $customerData['C_MODIFYUSERNAME'], 0, $C_MODIFYUSERNAME_length );

		if ( PEAR::isError($invalidField = checkStringLengths($customerData, array("C_NAME", "C_ADDRESSSTREET", "C_ADDRESSCITY", "C_ADDRESSSTATE", "C_ADDRESSZIP", "C_ADDRESSCOUNTRY", "C_CONTACTPERSON", "C_PHONE", "C_FAX", "C_EMAIL"),
			array($C_NAME_length, $C_ADDRESSSTREET_length, $C_ADDRESSCITY_length, $C_ADDRESSSTATE_length, $C_ADDRESSZIP_length, $C_ADDRESSCOUNTRY_length, $C_CONTACTPERSON_length, $C_PHONE_length, $C_FAX_length, $C_EMAIL_length))) )
		{
			$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

			return $invalidField;
		}

		$customerData["C_MODIFYDATETIME"] = convertToSqlDateTime( time(), true );

		if ( $action == PM_ACTION_NEW ) {
			if ( PEAR::isError($customerData["C_ID"] = pm_findNextID( "CUSTOMER", "C_ID" )) )
				return PEAR::raiseError( $pmStrings[PM_ERR_MAXCUSTOMERID], PM_ERRCODE_DATAHANDLE );

			$customerData["C_STATUS"] = RS_ACTIVE;

			if ( PEAR::isError($res = exec_sql($qr_pm_add_customer, $customerData, $outputList, false)) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );
		}
		else {
			if ( PEAR::isError($customerData["C_STATUS"] = db_query_result( $qr_pm_select_customer_status, DB_FIRST, $customerData )) )
				return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_CUSTOMERFIELD]), PM_ERRCODE_DATAHANDLE );

			if ( $customerData["C_STATUS"] == RS_DELETED )
				return PEAR::raiseError( $pmStrings['amc_invstatus_message'], PM_ERRCODE_WRONGDATA );

			if ( PEAR::isError($res = exec_sql($qr_pm_modify_customer, $customerData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );
		}

		return $customerData["C_ID"];
	}

	function pm_deleteCustomer( $customerData, $pmStrings, $finalDelete = false, $language, $kernelStrings )
	//
	// Checks if the customer is allowed to be deleted and deletes if it is so
	//
	//		Parameters:
	//			$customerData - customer identifier
	//			$pmStrings - Project Management localization Strings
	//			$finalDelete - if it's true, the customer is deleted; if it's false, the customer record is transfered to the deleted customers list
	//			$language - language
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_delete_customer;
		global $qr_pm_count_customer_active_projects;
		global $qr_pm_delete_customer_permanently;
		global $qr_pm_select_customer_projects;
		global $qr_pm_count_customer_projects;

		if ( PEAR::isError($count = db_query_result($qr_pm_count_customer_active_projects, DB_FIRST, $customerData)) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD]), PM_ERRCODE_DATAHANDLE );

		if ( $count ) {
			if ( $finalDelete )
				$error = $pmStrings['amc_custprojdelete_message'];
			else
				$error = $pmStrings['amc_invopenproj_message'];

			return PEAR::raiseError( $error, PM_ERRCODE_WRONGDATA );
		}

		$customerData["C_MODIFYDATETIME"] = convertToSqlDateTime( time() );

		$query = $qr_pm_delete_customer;
		if ( $finalDelete ) {
			$query = $qr_pm_delete_customer_permanently;

			if ( PEAR::isError($count = db_query_result($qr_pm_count_customer_projects, DB_FIRST, $customerData)) )
				return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD]), PM_ERRCODE_DATAHANDLE );

			if ( $count ) {
				$error = $pmStrings['amc_custprojdelete_message'];

				return PEAR::raiseError( $error, PM_ERRCODE_WRONGDATA );
			}
		}

		if ( PEAR::isError($res = exec_sql($query, $customerData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_restoreCustomer( $customerData, $pmStrings, $kernelStrings )
	//
	// Restores customer
	//
	//		Parameters:
	//			$customerData - customer data
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_restore_customer;

		$customerData = trimArrayData( $customerData );

		$customerData["C_MODIFYDATETIME"] = convertToSqlDateTime( time() );

		if ( PEAR::isError($res = exec_sql($qr_pm_restore_customer, $customerData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_addmodProject( $action, $projectData, $pmStrings, $kernelStrings )
	//
	// Examines incoming data and inserts (or modifies) project record into database
	//
	//		Parameters:
	//			$action - type of action - addition ($action == PM_ACTION_NEW) or modification ($action == PM_ACTION_MODIFY)
	//			$projectData - an associative array containing fields of PROJECT record
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//
	//		Returns project identifier, or PEAR_Error.
	//
	{
		global $qr_pm_add_project;
		global $qr_pm_modify_project;
		global $dateFormats;
		global $qr_pm_select_min_project_work_startdate;

		$P_DESC_length = 250;
		$U_ID_MANAGER_length = 20;

		$projectData = trimArrayData( $projectData );

		$projectData = nullSQLFields( $projectData, array( "P_ENDDATE", "C_ID", "U_ID_MANAGER" ) );

		$requiredFields = array( "P_DESC", "C_ID", "U_ID_MANAGER", "P_STARTDATE" );

		if ( PEAR::isError($invalidField = findEmptyField($projectData, $requiredFields)) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = checkStringLengths($projectData, array("P_DESC", "U_ID_MANAGER"), array($P_DESC_length, $U_ID_MANAGER_length))) ) {
			$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = checkDateFieldsNT($projectData, array("P_STARTDATE"), $projectData ) ) ) {
			$invalidField->message = sprintf( $kernelStrings[ERR_DATEFORMAT], $dateFormats[DATE_DISPLAY_FORMAT] );

			return $invalidField;
		}

		$res = pm_projectAddingPermitted( $kernelStrings, $pmStrings, $action );
		if ( PEAR::isError($res) )
			return $res;

		if ( $action != PM_ACTION_NEW )
		{
			if ( PEAR::isError( $workDate = db_query_result($qr_pm_select_min_project_work_startdate, DB_FIRST, $projectData) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( strlen($workDate) )
			{
				$workDate = sqlTimestamp($workDate);
				$projDate = sqlTimestamp($projectData["P_STARTDATE"]);

				if ( $projDate > $workDate )
					return PEAR::raiseError( $pmStrings['amp_startdateerr_message'], ERRCODE_APPLICATION_ERR );
			}
		}
		
		$projectData["P_DESC"] = stripLineBreaks( $projectData["P_DESC"] );
		$projectData["P_MODIFYDATETIME"] = convertToSqlDateTime( time() );
		
		// read project folder and update it's name if it's exist
		if ($action == PM_ACTION_MODIFY) {
			$existsProjectData = pm_getProjectData( $projectData["P_ID"], $kernelStrings );
			if ($existsProjectData["DF_ID"]) {
				$folderParams = array ("DF_NAME" => $projectData["P_DESC"]);
				$sql = new CUpdateSqlQuery ("DOCFOLDER");
				$sql->addFields($folderParams);
				$sql->addConditions ("DF_ID", $existsProjectData["DF_ID"]);
				$res = db_query ($sql->getQuery(), array ());
				if (PEAR::isError($res))
					return $res;
			}
		}
		

		$projectData = rescueElement( $projectData, "P_BILLABLE", 0 );

		if ( $action == PM_ACTION_NEW )
		{
			if ( PEAR::isError($projectData["P_ID"] = pm_findNextID( "PROJECT", "P_ID" )) )
				return PEAR::isError( $pmStrings[PM_ERR_MAXPROJECTID], PM_ERRCODE_DATAHANDLE );

			if ( PEAR::isError($res = exec_sql($qr_pm_add_project, $projectData, $outputList, false)) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );
		}
		else if ( PEAR::isError($res = exec_sql($qr_pm_modify_project, $projectData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return $projectData["P_ID"];
	}

	function pm_closeProject_checkDateFields( $projectData, $pmStrings, $kernelStrings )
	//
	//  Examines date logistics during the project closing
	//
	//		Parameters:
	//			$projectData - an associative array containing fields of PROJECT record
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		global $qr_pm_select_project_startdate;
		global $qr_pm_select_max_work_enddate;

		if ( PEAR::isError($projectData["P_STARTDATE"] = db_query_result($qr_pm_select_project_startdate, DB_FIRST, $projectData)) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD]), PM_ERRCODE_DATAHANDLE );

		if ( $projectData["P_STARTDATE"] > $projectData["P_ENDDATE"]  )
			return PEAR::raiseError( $pmStrings[PM_ERR_PROJECTSTARTEXCEND], PM_ERRCODE_STARTEXCEND, $_PEAR_default_error_mode, $_PEAR_default_error_options, "P_ENDDATE" );

		if ( $projectData["P_ENDDATE"] > convertToSqlDate( time() ) )
			return PEAR::raiseError( $pmStrings[PM_ERR_PROJECTENDEXCCURR], PM_ERRCODE_ENDEXCCURR, $_PEAR_default_error_mode, $_PEAR_default_error_options, "P_ENDDATE" );

		$maxEndDate = db_query_result( $qr_pm_select_max_work_enddate, DB_FIRST, $projectData );
		if ( PEAR::isError($maxEndDate) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( strlen($maxEndDate) )
			if ( $projectData["P_ENDDATE"] < $maxEndDate )
				return PEAR::raiseError( $pmStrings['cmp_projtaskdateerr_message'], ERRCODE_APPLICATION_ERR );

		return null;
	}

	function pm_forceCloseTasks( $P_ID, $endDate, $pmStrings, $kernelStrings, $language, $U_ID )
	//
	// Closes all open project tasks
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$endDate - close date
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//			$language - user language
	//			$U_ID - user identifier
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pm_select_active_works;

		$qr = db_query( $qr_pm_select_active_works, array('P_ID'=>$P_ID) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while( $workData = db_fetch_array($qr) ) {
			$workData['PW_ENDDATE'] = $endDate;
			$res = pm_closeWork( $workData, $pmStrings, $kernelStrings, $language, $U_ID );
			if ( PEAR::isError($res) )
				return $res;
		}

		db_free_result( $qr );

		return null;
	}

	function pm_closeProject( $projectData, $pmStrings, $kernelStrings, $language, $U_ID, $closeMode )
	//
	// Examines incoming data and closes project
	//
	//		Parameters:
	//			$projectData - an associative array containing field of PROJECT record
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//			$language - user language
	//			$U_ID - user identifier
	//			$closeMode - leave open tasks (0) or close them (1)
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_close_project;
		global $dateFormats;
		global $qr_pm_select_project_active_works_count;
		global $PM_APP_ID;

		$projectData = trimArrayData( $projectData );

		$projectData = nullSQLFields( $projectData, array("P_ENDDATE") );

		$requiredFields = array("P_ENDDATE");

		if ( PEAR::isError($invalidField = findEmptyField($projectData, $requiredFields)) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$inputDate = $projectData['P_ENDDATE'];

		if ( PEAR::isError($invalidField = checkDateFields($projectData, array("P_ENDDATE"), $projectData )) ) {
			$invalidField->message = sprintf( $kernelStrings[ERR_DATEFORMAT], $dateFormats[DATE_DISPLAY_FORMAT] );

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = pm_closeProject_checkDateFields($projectData, $pmStrings, $kernelStrings )) )
			return $invalidField;

		if ( $closeMode ) {
			$res = pm_forceCloseTasks( $projectData['P_ID'], $inputDate, $pmStrings, $kernelStrings, $language, $U_ID );
			if ( PEAR::isError( $res ) )
				return $res;
		}

		$projectData["P_MODIFYDATETIME"] = convertToSqlDateTime( time() );

		if ( PEAR::isError($res = exec_sql($qr_pm_close_project, $projectData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING],  PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_reopenProject( $projectData, $pmStrings, $kernelStrings )
	//
	// Reopens closed project
	//
	//		Parameters:
	//			$projectData - an associative array containing fields of PROJECT record
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_reopen_project;
		global $qr_pm_select_project_customer;

		$projectData = trimArrayData( $projectData );

		$projectData["P_MODIFYDATETIME"] = convertToSqlDateTime( time() );

		if ( PEAR::isError($res = exec_sql($qr_pm_reopen_project, $projectData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		if ( PEAR::isError( $C_ID = db_query_result( $qr_pm_select_project_customer, DB_FIRST, $projectData ) ) )
			return PEAR::raiseError( $pmStrings[PM_ERR_DBEXTRACTION] );

		$customerData = array( "C_MODIFYDATETIME"=>$projectData["P_MODIFYDATETIME"],
								"C_MODIFYUSERNAME"=>$projectData["P_MODIFYUSERNAME"], "C_ID"=>$C_ID );

		if ( PEAR::isError( $res = pm_restoreCustomer( $customerData, $pmStrings, $kernelStrings ) ) )
			return $res;

		return null;
	}

	function pm_projectIsClosed( $projectData, &$kernelStrings )
	//
	// Checks whether the project is closed
	//
	//		Parameters:
	//			$projectData - project data as associative array
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_pm_select_project_is_closed;

		$res = db_query_result($qr_pm_select_project_is_closed, DB_FIRST, $projectData);
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	function pm_deleteProject( $projectData, $pmStrings, $language, $kernelStrings )
	//
	// Deletes project and all corresponding records from database
	//
	//		Parameters:
	//			$projectData - an associative array containing fields of PROJECT record
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_delete_project;
		global $qr_pm_select_all_project_works;
		global $qr_pm_delete_project_work;
		global $qr_pm_delete_project_work_assignment;
		global $qr_pm_count_project_works;
		global $PM_APP_ID;

		$params = array( "P_ID"=>$projectData["P_ID"] );
		if ( PEAR::isError( $res = handleEvent( $PM_APP_ID, "onDeleteProject", $params, $language ) ) )
			return $res;
		
		$qr = db_query( $qr_pm_select_all_project_works, $projectData );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD]), PM_ERRCODE_DATAHANDLE );

		while( $row = db_fetch_array($qr) )
			if ( PEAR::isError($res = pm_deleteWork( array_merge($projectData, array("PW_ID"=>$row["PW_ID"])), $pmStrings, $language, $kernelStrings )) )
				return $res;

		@db_free_result( $qr );

		if ( PEAR::isError($res = exec_sql($qr_pm_delete_project, $projectData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}
	
	
	function pm_onDeleteProject( $params )
	//
	// Handler of application PM onDeleteProject event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		global $UR_Manager;
		
		if ($CALL_TYPE == CT_ACTION) {
			$projectData =  pm_getProjectData( $P_ID, $kernelStrings );
			if ($projectData["DF_ID"]) {
				require_once( WBS_DIR."/published/DD/dd.php" );
				global $dd_treeClass;
				global $currentUser;
				$folderId = $projectData["DF_ID"];
				
				$admin = false;
				$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings );
				$res = $dd_treeClass->deleteFolder( $folderId, $currentUser, $kernelStrings, 
													false, "dd_onDeleteFolder", array('ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, 'U_ID'=>$currentUser), false );
				if ( PEAR::isError($res) )
					$errorStr = $res->getMessage();
				
				/*$usql = new CUpdateSqlQuery ("PROJECT");
				$uparams = array ("DF_ID" => $folderId);
				$usql->addFields ($uparams, null);
				$usql->addConditions ("P_ID", $projectData["P_ID"]);
				$qr = db_query ($usql->getQuery ());
				if ( PEAR::isError($qr) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );*/
			}
			
			$res = $UR_Manager->DeleteRightsLinksTo ("/ROOT/PM/PROJECTS", $projectData["P_ID"]);
			if (PEAR::isError($res))
				return $res;
		}
		
		return true;
	}

	function pm_addmodWork_checkDateFields( $workData, $pmStrings )
	//
	//  Checks date logistics during the work adding/modifying
	//
	//		Parameters:
	//			$workData - an associative array containing fields of PROJECTWORK record
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null, or PEAR_Error.
	//
	{

		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		global $qr_pm_select_project_startdate;

		if ( dateCmpNT( convertToDisplayDateNT($workData["PW_STARTDATE"]), convertToDisplayDateNT($workData["PW_DUEDATE"]) ) > 0 && strlen($workData["PW_DUEDATE"]) )
			return PEAR::raiseError( $pmStrings[PM_ERR_WORKSTARTEXCDUE], PM_ERRCODE_STARTEXCDUE );

		if ( PEAR::isError($workData["P_STARTDATE"] = db_query_result($qr_pm_select_project_startdate, DB_FIRST, $workData)) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD]), PM_ERRCODE_DATAHANDLE );

		if ( $workData["PW_STARTDATE"] && dateCmpNT(convertToDisplayDateNT($workData["PW_STARTDATE"]), convertToDisplayDateNT($workData["P_STARTDATE"]) ) < 0 )
			return PEAR::raiseError( $pmStrings[PM_ERR_P_STARTEXC_PW_START], PM_ERRCODE_P_STARTEXC_PW_START,  $_PEAR_default_error_mode, $_PEAR_default_error_options, "PW_STARTDATE" );

		return null;
	}

	function pm_addmodWork( $action, &$workData, $pmStrings, $kernelStrings, $language )
	//
	// Examines incoming data and inserts (or modifies) work into database
	//
	//		Parameters:
	//			$action - type of action - addition ($action == PM_ACTION_NEW) or modification ($action == PM_ACTION_MODIFY)
	//			$workData - an asociative array containing field of PROJECTWORK record
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//			$language - user language
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_add_work;
		global $qr_pm_modify_work;
		global $qr_pm_delete_work_asgn;
		global $qr_pm_add_asgn;
		global $qr_pm_findWorkId;
		global $PM_APP_ID;
		global $dateFormats;

		$PW_DESC_length = 250;

		if ( isset($workData["ASSIGNED"]) )
			$assigned_users = $workData["ASSIGNED"];
		else
			$assigned_users = null;

		$workData = trimArrayData( $workData );

		$workData = nullSQLFields( $workData, array("PW_STARTDATE", "PW_DUEDATE", "PW_COSTESTIMATE") );

		$requiredFields = array( "PW_DESC");
		if ( $action == PM_ACTION_NEW )
			$requiredFields = array_merge( $requiredFields);

		if ( PEAR::isError($invalidField = findEmptyField($workData, $requiredFields)) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = checkStringLengths($workData, array("PW_DESC"), array($PW_DESC_length))) ) {
			$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = checkDateFieldsNT($workData, array( "PW_STARTDATE", "PW_DUEDATE"), $workData ) ) )
		{
			$invalidField->message = sprintf( $kernelStrings[ERR_DATEFORMAT],$dateFormats[DATE_DISPLAY_FORMAT] );
			return $invalidField;
		}

		if ( PEAR::isError( $invalidField =  checkIntegerFields( $workData, array("PW_COSTESTIMATE"), $kernelStrings, true ) ) )
			return $invalidField;

		$workData["PW_DESC"] = stripLineBreaks( $workData["PW_DESC"] );

		if ( !strlen( $workData["PW_COSTESTIMATE"] ) )
			$workData["PW_COSTCUR"] = null;

		$workData = rescueElement( $workData, "PW_BILLABLE", 0 );

		if ( PEAR::isError($invalidField = pm_addmodWork_checkDateFields($workData, $pmStrings)) )
			return $invalidField;

		if ( $action != PM_ACTION_NEW ) {
			$params = array( "PW_ID"=>$workData["PW_ID"], "P_ID"=>$workData["P_ID"], "PW_STARTDATE"=>$workData["PW_STARTDATE"] );
			if ( PEAR::isError( $res = handleEvent( $PM_APP_ID, "onUpdateWorkStartDate", $params, $language) ) )
				return $res;
		}

		if ( $action == PM_ACTION_NEW ) {
			$res = pm_workAddingPermitted( $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			if ( PEAR::isError($workData["PW_ID"] = db_query_result( $qr_pm_findWorkId, DB_FIRST, $workData )) )
				return PEAR::raiseError( $pmStrings[PM_ERR_MAXWORKID], PM_ERRCODE_DATAHANDLE );

			$workData["PW_ID"] = $workData["PW_ID"] + 1;
			
			if ( PEAR::isError($res = exec_sql($qr_pm_add_work, $workData, $outputList, false)) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

			$params = array( "PW_ID"=>$workData["PW_ID"], "P_ID"=>$workData["P_ID"] );
			if ( PEAR::isError( $res = handleEvent( $PM_APP_ID, "onNewWork", $params, $language) ) )
				return $res;
		}
		else if ( PEAR::isError($res = exec_sql($qr_pm_modify_work, $workData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		if ( PEAR::isError($res = pm_addmodAsgn($action, array("P_ID"=>$workData["P_ID"], "PW_ID"=>$workData["PW_ID"], 	"ASSIGNED"=>$assigned_users), $pmStrings, $language)) )
			return $res;

		return null;
	}

	function pm_workAddingPermitted( &$kernelStrings )
	//
	// Checks whether adding work is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		return null;
	}

	function pm_projectAddingPermitted( &$kernelStrings, &$pmStrings, $action )
	//
	// Checks whether adding work is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$pmStrings - Project Management localization Strings
	//			$action - action type
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		$limit = getApplicationResourceLimits( 'PM' );
		if ( $limit === null )
			return null;

		$sql = "SELECT COUNT(*) FROM PROJECT WHERE P_ID>0";

		$res = db_query_result( $sql, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $action == ACTION_NEW )
		{
			if ( $res >= $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $pmStrings['app_projlimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $pmStrings['app_projlimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}
		else
		{
			if ( $res > $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $pmStrings['app_projlimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $pmStrings['app_projlimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}

		return null;
	}

	function pm_closeWork_checkDateFields( $workData, $pmStrings )
	//
	//  Checks date logistics during the work finishing
	//
	//		Parameters:
	//			$workData - an associative array containing fields of PROJECTWORK record
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		global $qr_pm_select_work_startdate;

		if ( PEAR::isError($workData["PW_STARTDATE"] = db_query_result($qr_pm_select_work_startdate, DB_FIRST, $workData) ) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD]), PM_ERRCODE_DATAHANDLE );

		if ( dateCmpNT(convertToDisplayDate($workData["PW_STARTDATE"]), convertToDisplayDate($workData["PW_ENDDATE"]) ) > 0 )
			return PEAR::raiseError( $pmStrings[PM_ERR_WORKSTARTEXCEND], PM_ERRCODE_STARTEXCEND, $_PEAR_default_error_mode, $_PEAR_default_error_options, "PW_ENDDATE" );

		return null;
	}

	function pm_closeWork( $workData, $pmStrings, $kernelStrings, $language, $U_ID )
	//
	// Examines incoming data and finishes work
	//
	//		Parameters:
	//			$workData - an associative array containing fields of PROJECTWORK record
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//			$language - user language
	//			$U_ID - user identifier
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_finish_work;
		global $PM_APP_ID;
		global $dateFormats;

		$workData = trimArrayData( $workData );

		$workData = nullSQLFields( $workData, array("PW_ENDDATE") );

		$requiredFields = array( "PW_ENDDATE" );

		if ( PEAR::isError($invalidField = findEmptyField($workData, $requiredFields)) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = checkDateFieldsNT($workData, array( "PW_ENDDATE" ), $workData )) ) {
			$invalidField->message = sprintf( $kernelStrings[ERR_DATEFORMAT], $dateFormats[DATE_DISPLAY_FORMAT] );

			return $invalidField;
		}

		if ( PEAR::isError($invalidField = pm_closeWork_checkDateFields($workData, $pmStrings)) )
			return $invalidField;

		/*
		$params = array( "PW_ID"=>$workData["PW_ID"], "P_ID"=>$workData["P_ID"],
							"PW_ENDDATE"=>sqlTimestamp($workData["PW_ENDDATE"]), "U_ID"=>$U_ID );
		if ( PEAR::isError( $res = handleEvent( $PM_APP_ID, "onCloseWork", $params, $language) ) )
			return $res;
		*/

		if ( PEAR::isError($res = exec_sql($qr_pm_finish_work, $workData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_reopenWork( $workData, $pmStrings, $kernelStrings )
	//
	// Reopens work
	//
	//		Parameters:
	//			$workData - an associtative array containing fields of PROJECTWORK record
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_reopen_work;

		if ( PEAR::isError($res = exec_sql($qr_pm_reopen_work, $workData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_deleteWork($workData, $pmStrings, $language, $kernelStrings )
	//
	// Deletes work and all corresponding records from database
	//
	//		Parameters:
	//			$workData - an associative array containing fields of PROJECTWORK record
	//			$pmStrings - Project Management localization Strings
	//			$language - user language
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_delete_work;
		global $qr_pm_delete_work_assignment;
		global $qr_pm_select_work_asgn;
		global $PM_APP_ID;

		$params = array( "P_ID"=>$workData["P_ID"], "PW_ID"=>$workData["PW_ID"] );
		if ( PEAR::isError( $res = handleEvent( $PM_APP_ID, "onDeleteWork", $params, $language ) ) )
			return $res;

		$qr = db_query( $qr_pm_select_work_asgn, $workData );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( sprintf($pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD]), PM_ERRCODE_DATAHANDLE );

		while ( $row = db_fetch_array($qr) ) {
			if ( PEAR::isError($res = pm_deleteAsgn(array("P_ID"=>$workData["P_ID"], "PW_ID"=>$workData["PW_ID"] , "U_ID"=>$row["U_ID"]), $pmStrings, $language)) )
				return $res;
		}

		@db_free_result( $qr );

		if ( PEAR::isError($res = exec_sql($qr_pm_delete_work, $workData, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_addmodAsgn( $action, $asgndata, $pmStrings, $language )
	//
	// Examines incoming data and inserts (or modifies) assignment into database
	//
	//		Parameters:
	//			$action - type of action - addition ($action == PM_ACTION_NEW) or modification ($action == PM_ACTION_MODIFY)
	//			$asgndata - an associative array containing fields of WORKASSIGNMENT record
	//			$pmStrings - Project Management localization Strings
	//			$language - user language
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_add_asgn;
		global $qr_pm_delete_work_asgn;
		global $qr_pm_selectAsgn;
		global $qr_pm_select_work_asgn;
		global $PM_APP_ID;

		$assigned_users = $asgndata["ASSIGNED"];
		$asgndata = trimArrayData( $asgndata );

		$assigned_db = array();

		$qr = db_query( $qr_pm_select_work_asgn, $asgndata );
		while ( $row = db_fetch_array($qr) )
			$assigned_db[count($assigned_db)] = $row["U_ID"];

		db_free_result( $qr );

		$deleted_asgn = null;
		if ( is_array($assigned_db) && is_array($assigned_users) )
			$deleted_asgn = array_diff( $assigned_db, $assigned_users );
		else if ( is_array($assigned_db) && !is_array($assigned_users) )
			$deleted_asgn = $assigned_db;

		if ( is_array($deleted_asgn) ) {
			foreach( $deleted_asgn as $fieldNumber => $fieldValue ) {
				if ( PEAR::isError( $res = pm_deleteAsgn( array("P_ID"=>$asgndata["P_ID"], "PW_ID"=>$asgndata["PW_ID"], "U_ID"=>$fieldValue ), $pmStrings, $language ) ) )
					return $res;
			}
		}

		if ( is_array($assigned_users) && is_array($assigned_db) )
			$assigned_users = array_diff( $assigned_users, $assigned_db );
		else if ( is_array($assigned_db) && !is_array($assigned_users) )
			$assigned_users = null;

		if ( is_array($assigned_users) ) {
			foreach ( $assigned_users as $fieldNumber => $fieldValue )
				if ( PEAR::isError($res = exec_sql($qr_pm_add_asgn, array("P_ID"=>$asgndata["P_ID"], "PW_ID"=>$asgndata["PW_ID"], "U_ID"=>$fieldValue), $outputList, false )) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );
		}

		return null;
	}

	function pm_deleteAsgn( $asgndata, $pmStrings, $language )
	//
	// Deletes assignment from database
	//
	//		Parameters:
	//			$asgndata - an associative array containing fields of WORKASSIGNMENT record
	//			$pmStrings - Project Management localization Strings
	//			$language - user language
	//
	//		Returns null, or PEAR_Error.
	//
	{
		global $qr_pm_delete_asgn;
		global $PM_APP_ID;

		$params = array( "P_ID"=>$asgndata["P_ID"], "PW_ID"=>$asgndata["PW_ID"], "U_ID"=>$asgndata["U_ID"] );
		if ( PEAR::isError( $res = handleEvent( $PM_APP_ID, "onDeleteAssignment", $params, $language ) ) )
			return $res;

		if ( PEAR::isError($res = exec_sql($qr_pm_delete_asgn, $asgndata, $outputList, false)) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING], PM_ERRCODE_DATAHANDLE );

		return null;
	}

	function pm_findNextId( $table, $field )
	//
	// Finds maximum identifier within the database table
	//
	//		Parameters:
	//			$table - database table
	//			$field - field, where maximum identifier is searched for
	//
	//		Returns maximum identifier increased by 1, or PEAR_Error.
	//
	{
		global $qr_pm_findNextId;

		$sql = sprintf( $qr_pm_findNextId, $field, $table );

		if ( PEAR::isError($id = db_query_result($sql, DB_FIRST, null)) )
			return $id;

		return $id+1;
	}

	function pm_projectExists( $projectData )
	//
	//	Checks if the project exists
	//
	//		Parameters:
	//			$projectData - an array containing project identifier
	//
	//		Returns 1, if the project exists, otherwise - 0.
	//
	{
		global $qr_pm_select_project_count;

		return db_query_result( $qr_pm_select_project_count, DB_FIRST, $projectData );
	}

	function pm_workExists( $workData )
	//
	//	Checks if the work exists
	//
	//		Parameters:
	//			$workData - an array containing work identifier
	//
	//		Returns 1, if the work exists, otherwise - 0
	//
	{
		global $qr_pm_select_work_count;

		return db_query_result( $qr_pm_select_work_count, DB_FIRST, $workData );
	}

	function pm_onDeleteUser( $params )
	//
	// Handler of application AA onDeleteUser event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		@include $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/pm_queries_cmn.php") );

		global $pm_loc_str;
		global $loc_str;

		$pmStrings = $pm_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch( $CALL_TYPE ) {
			case CT_APPROVING : {
				$vars = get_defined_vars();
				saveVariables( $vars, "qr_pm" );

				$res = db_query_result( $qr_pm_select_user_open_work, DB_FIRST, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] ) );

				if ( $res )
					return PEAR::raiseError( $pmStrings['app_usertaskerr_message'], ERRCODE_APPLICATION_ERR );

				$res = db_query_result( $qr_pm_select_user_open_project, DB_FIRST, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] ) );

				if ( $res )
					return PEAR::raiseError( $pmStrings['app_usermanerr_message'], ERRCODE_APPLICATION_ERR );

				return EVENT_APPROVED;
			}

			case CT_ACTION : {
				global $qr_pm_delete_user_assignments;

				$res = db_query( $qr_pm_delete_user_assignments, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return null;
			}
		}
	}

	function pm_onRemoveUser( $params )
	//
	// Handler of application AA onRemoveUser event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		@include $appScriptPath;

		eval( prepareFileContent($appDirPath."/pm_queries_cmn.php") );

		global $pm_loc_str;
		global $loc_str;

		$pmStrings = $pm_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch( $CALL_TYPE ) {
			case CT_APPROVING : {

				$res = db_query_result( $qr_pm_select_user_work, DB_FIRST, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] ) );

				if ( $res )
					return PEAR::raiseError( $pmStrings['app_removeusertaskerr_message'], ERRCODE_APPLICATION_ERR );

				$res = db_query_result( $qr_pm_select_user_project, DB_FIRST, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] ) );

				if ( $res )
					return PEAR::raiseError( $pmStrings['app_userremovemanerr_message'], ERRCODE_APPLICATION_ERR );

				return EVENT_APPROVED;
			}

			case CT_ACTION : {
				return null;
			}
		}
	}

	function pm_onDeleteCurrency( $params )
	//
	// Handler of application AA onDeleteCurrency event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		@include $appScriptPath;

		global $pm_loc_str;
		global $loc_str;

		$kernelStrings = $loc_str[$language];
		$pmStrings = $pm_loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$res = db_query_result( $qr_pm_select_work_curency_count, DB_FIRST, array("CUR_ID"=>$CUR_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( $pmStrings['app_curremove_err'], ERRCODE_APPLICATION_ERR );

 				return EVENT_APPROVED;
			}
			case CT_ACTION : {
 				return null;
			}
		}
	}

	function pm_writeUserPMSetting( $currentUser, $projectData, $kernelStrings, $pmStrings, $projects_names_ids = null, $project_screens_ids = null, $projectScreen = null )
	//
	// Stores parameters of application PM into user settings (it is used during the work with Work List) and initializes the project with its data
	//
	//		Parameters:
	//			$currentUser - user identifier
	//			$projectData - an array containing project identifier
	//			$kernelStrings - an array containing strings stored within localization.php in specific language
	//			$pmStrings - Project Management localization Strings
	//			$project_names_ids - an array containing identifiers of all accessible projects
	//			$project_screens_ids - an array containing idenitifiers of screens (work list � work assignments)
	//			$projectScreen - value of this variable equals to identifier of current screen
	//
	//		Returns an array containing project data, or PEAR_Error.
	//
	{
		global $qr_pm_select_project;

		if ( !isset($projectData["P_ID"]) || !strlen($projectData["P_ID"]) )
			$projectData["P_ID"] = getAppUserCommonValue(PM_PM_SECTION, $currentUser, PM_WORKLIST_PROJECTID );

		if ( (!strlen($projectData["P_ID"]) || !in_array($projectData["P_ID"], $projects_names_ids)) && !is_null($projects_names_ids) )
			$projectData["P_ID"] = $projects_names_ids[0]["P_ID"];

		$res = exec_sql( $qr_pm_select_project, $projectData, $projectData, true );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$projectData["P_STATUS"] = pm_compareWithCurDate( convertToDisplayDate($projectData["P_ENDDATE"]) );
		$projectData["P_DESC"] = implode( ". ", array($projectData["C_NAME"], $projectData["P_DESC"]) );
		$projectData["P_MANAGER"] = getUserName( $projectData["U_ID_MANAGER"], true );

		if ( !is_null($projectScreen) )
			$projectData["SCREEN"] = $projectScreen;
		else
			$projectData["SCREEN"] = getAppUserCommonValue(PM_PM_SECTION, $currentUser, PM_WORKLIST_PROJECTSCREEN );

		if ( !strlen($projectData["SCREEN"]) && !is_null($project_screens_ids) )
			$projectData["SCREEN"] = $project_screens_ids[0];

		setAppUserCommonValue(PM_PM_SECTION, $currentUser, PM_WORKLIST_PROJECTID, $projectData["P_ID"], $kernelStrings);
		setAppUserCommonValue(PM_PM_SECTION, $currentUser, PM_WORKLIST_PROJECTSCREEN, $projectData["SCREEN"], $kernelStrings);

		return $projectData;
	}

	function pm_getProjectData( $P_ID, $kernelStrings )
	//
	// Selects project information into array
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_pm_select_project;

		$res = db_query_result( $qr_pm_select_project, DB_ARRAY, array('P_ID'=>$P_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return $res;
	}

	//
	// Gantt functions
	//
	function pm_getGanttPeriodBounds( $interval, $P_ID, &$start, &$end, &$diff, $U_ID, $pmStrings, $kernelStrings, $showCompleteTasks )
	//
	// Returns start and end dates for Gantt chart according to $interval value
	//
	//		Parameters:
	//			$interval - interval to show (GANTT_PROJECT_TODAY, GANTT_PROJECT_TOMONTH, ...)
	//			$P_ID - project identifier
	//			$start, $end - period bounds as timestamp
	//			$diff - date difference
	//			$U_ID - user identifier
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - Kernel localizatoin strings
	//			$showCompleteTasks - include complete tasks into result list
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pm_select_min_project_work_startdate_filtered;
		global $qr_pm_select_max_work_enddate_filtered;
		global $qr_pm_select_max_work_duedate_filtered;
		global $qr_pm_datediff;
		global $qr_pm_completeFilter;

		$projData = pm_getProjectData( $P_ID, $kernelStrings );
		if ( PEAR::isError($projData) )
			return $projData;

		$complete = strlen($projData['P_ENDDATE']);
		$emptyProject = false;

		$current = convertToSQLDate( time() );

		$completeFilter = null;
		if ( !$showCompleteTasks )
			$completeFilter = $qr_pm_completeFilter;

		$qr_pm_select_min_project_work_startdate_filtered = sprintf( $qr_pm_select_min_project_work_startdate_filtered, $completeFilter );
		$qr_pm_select_max_work_enddate_filtered = sprintf( $qr_pm_select_max_work_enddate_filtered, $completeFilter );
		$qr_pm_select_max_work_duedate_filtered = sprintf( $qr_pm_select_max_work_duedate_filtered, $completeFilter );

		$start = db_query_result( $qr_pm_select_min_project_work_startdate_filtered, DB_FIRST, array('P_ID'=>$P_ID) );
		if ( PEAR::isError($start) )
			return $start;

		if ( $complete && $interval == GANTT_PROJECT_TOMONTH ) {
			if ( !strlen($start) ) {
				$start = $projData['P_STARTDATE'];
				$emptyProject = true;
			}

			$end = $projData['P_ENDDATE'];
			$due = db_query_result( $qr_pm_select_max_work_duedate_filtered, DB_FIRST, array('P_ID'=>$P_ID) );
			if ( PEAR::isError($due) )
				return $due;

			$end = max( $end, $due );
		} else {
			if ( strlen($start) ) {
				$end = db_query_result( $qr_pm_select_max_work_enddate_filtered, DB_FIRST, array('P_ID'=>$P_ID) );
				if ( PEAR::isError($end) )
					return $start;

				$due = db_query_result( $qr_pm_select_max_work_duedate_filtered, DB_FIRST, array('P_ID'=>$P_ID) );
				if ( PEAR::isError($due) )
					return $due;
			} else {
				$emptyProject = true;
				$start = $projData['P_STARTDATE'];

				if ( $complete )
					$end = $projData['P_ENDDATE'];
				else
					$end = $current;

				$due = null;
			}

			$end = max( $end, $due, $current );
		}

		$projectEnd = $end;

		$intervals = pm_listGanttIntervals( $U_ID, $pmStrings, $kernelStrings );

		if ( $interval != GANTT_PROJECT_TOMONTH && $interval != GANTT_PROJECT_TODAY )
			if ( !array_key_exists( $interval, $intervals ) )
				$interval = GANTT_PROJECT_TOMONTH;

		switch ( $interval ) {
			case GANTT_PROJECT_TODAY :
							// Align dates to week bounds
							//
							$startTS = strtotime( $start );
							$startWeekDay = date( 'w', $startTS ) - 1;
							$start = strtotime( "$startWeekDay days ago", $startTS );

							$endTS = strtotime( $end );
							$endWeekDay = 7 - date( 'w', $endTS );
							if ( $endWeekDay != 7 )
								$end = strtotime( "+$endWeekDay day", $endTS );
							else
								$end = $endTS;

							break;
			case GANTT_PROJECT_TOMONTH :
							// Align dates to month bounds
							//
							if ( !$emptyProject ) {
								$startTS = strtotime( $start );
								$day = date( 'j', $startTS ) - 1;
								$start = strtotime( "$day days ago", $startTS );
							} else {
								$start = strtotime( $start );
							}

							if ( ($complete && $interval == GANTT_PROJECT_TOMONTH) || $emptyProject ) {
								$end = strtotime( $end );
							} else {
								$endTS = strtotime( $end );
								$endInfo = getdate( $endTS );
								$lastDay = date( "t", $endTS );

								$end = strtotime( sprintf( "%s-%02d-%02d", $endInfo['year'], $endInfo['mon'], $lastDay ) );
							}

							break;
			default:
						$intervalData = $intervals[$interval];

						if ( $intervalData['start'] == 0 ) {
							$startTS = strtotime( $start );
							$day = date( 'j', $startTS ) - 1;
							$start = strtotime( "$day days ago", $startTS );
						} else {
							$start = strtotime( $intervalData['start'] );
						}

						if ( $intervalData['end'] == 0 ) {
							$endTS = strtotime( $end );
							$endInfo = getdate( $endTS );
							$lastDay = date( "t", $endTS );

							$end = strtotime( sprintf( "%s-%02d-%02d", $endInfo['year'], $endInfo['mon'], $lastDay ) );
						} else
							$end = strtotime( $intervalData['end'] );

						if ( $start > $end ) {
							$endTS = strtotime( $projectEnd );
							$endInfo = getdate( $endTS );
							$lastDay = date( "t", $endTS );

							$end = strtotime( sprintf( "%s-%02d-%02d", $endInfo['year'], $endInfo['mon'], $lastDay ) );
							$start = strtotime( sprintf( "%s-%02d-%02d", $endInfo['year'], $endInfo['mon'], 1 ) );
						}
		}

		$startDate = convertToSQLDate( $start );
		$endDate = convertToSQLDate( $end );
		$diff = db_query_result( $qr_pm_datediff, DB_FIRST, array('END'=>$endDate, 'START'=>$startDate) );
		if ( PEAR::isError($diff) )
			return $diff;
	}

	function pm_findProjectsDateBounds( &$start, &$end, $U_ID, $pmStrings, $kernelStrings, $showCompleteTasks )
	//
	// Returns date interval for all existing projects
	//
	//		Parameters:
	//			$start, $end - period bounds as timestamp
	//			$U_ID - user identifier
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - Kernel localizatoin strings
	//			$showCompleteTasks - include complete tasks into result list
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pm_select_projects_ordered;

		$qr = db_query( $qr_pm_select_projects_ordered );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$start = null;
		$end = null;

		while ( $row = db_fetch_array($qr) ) {
			$P_ID = $row['P_ID'];

			$projStart = 0;
			$projEnd = 0;
			$diff = 0;
			$res = pm_getGanttPeriodBounds( GANTT_PROJECT_TOMONTH, $P_ID, $projStart, $projEnd, $diff, $U_ID, $pmStrings, $kernelStrings, $showCompleteTasks );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $start > $projStart || is_null($start) )
				$start = $projStart;

			if ( $end < $projEnd || is_null($end) )
				$end = $projEnd;
		}

		db_free_result( $qr );
	}

	function pm_generateGanttContent( $interval, $P_ID, &$workData, &$ganttSettings, $sorting, $kernelStrings, $pmStrings, $U_ID, $showCompleteTasks )
	//
	// Returns content for Gantt diagram
	//
	//		Parameters:
	//			$interval - interval to show (GANTT_PROJECT_TODAY, GANTT_PROJECT_TOMONTH, ...)
	//			$P_ID - project identifier
	//			$workData - result work data
	//			$ganttSettings - result Gantt settings
	//			$sorting - sorting string
	//			$kernelStrings - Kernel localizatoin strings
	//			$pmStrings - Project Management localization Strings
	//			$U_ID - user identifier
	//			$showCompleteTasks - include complete tasks into result list
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pm_select_gantt_works;
		global $qr_pm_completeFilter;
		global $monthFullNames;

		$projData = pm_getProjectData( $P_ID, $kernelStrings );
		if ( PEAR::isError($projData) )
			return $projData;

		$complete = strlen($projData['P_ENDDATE']);

		$toEnd = $complete && $interval == GANTT_PROJECT_TOMONTH;


		if ( $toEnd )
		{
			$today = $projData['P_ENDDATE'];
			$todayLocal = $projData['P_ENDDATE'];
		}
		else
		{
			$today = convertToSQLDate( time() );
			$todayTime = convertToSQLDateTime( time() );
			$todayLocal = convertToSQLDate( convertTimestamp2Local( time() ) );
		}

		// Calculate chart period bounds
		//
		$start = null;
		$end = null;
		$diff = null;
		$res = pm_getGanttPeriodBounds( $interval, $P_ID, $start, $end, $diff, $U_ID, $pmStrings, $kernelStrings, $showCompleteTasks );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$startDate = convertToSQLDate( $start );
		$endDate = convertToSQLDate( $end );

		$mode = ($diff <= 365) ? 0 : 1;

		// Populate task list
		//
		$params = array();
		$params['P_ID'] = $P_ID;
		$params['START'] = $startDate;
		$params['END'] = $endDate;

		$completeFilter = null;
		if ( !$showCompleteTasks )
			$completeFilter = $qr_pm_completeFilter;

		$qr = db_query( sprintf( $qr_pm_select_gantt_works, $completeFilter, $sorting ), $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$workData = array();
		while ( $row = db_fetch_array($qr) ) {
			// Shift overdue
			//
			$row['ACTUAL_OVERDUE'] = $row['OVERDUE_SPAN'];

			if ( $row['OVERDUE_SPAN'] > 0 )
				if ( $startDate > $row['PW_DUEDATE'] )
					$row['OVERDUE_SPAN'] -= abs($row['DUE_OFFSET']);

			$workData[] = $row;
		}

		@db_free_result( $qr );

		// Prepare date index
		//
		$curDate = $start;

		$months = array();
		$prevMonth = null;
		$todayIndex = null;

		$dateIndex = array();
		$counter = 1;
		$tomorrowTrigger = false;
		while ( $curDate <= $end ) {
			$dayStr = date( 'Y-m-d', $curDate );
			$weekDay = date( 'w', $curDate );
			$month = date( 'n', $curDate );
			$monthKey = date( 'Yn', $curDate );
			$year = date( 'Y', $curDate );

			if ( $prevMonth != $monthKey ) {
				$monthData = array();
				$monthData['cnt'] = 0;

				if ( $diff <= 31 )
					$monthData['name'] = $kernelStrings[$monthFullNames[$month-1]];
				else
					$monthData['name'] = date( 'm/y', $curDate );

				$monthData['int_name'] = sprintf( "%s'%s", $kernelStrings[$monthFullNames[$month-1]], $year );

				$monthData['key'] = $month;
				$months[$monthKey] = $monthData;
			}

			$months[$monthKey]['cnt']++;

			$prevMonth = $monthKey;

			$dateParams = array();
			$dateParams['HOLIDAY'] = $weekDay == 0 || $weekDay == 6;
			$dateParams['INDEX'] = date( 'j', $curDate );

			if ($tomorrowTrigger) {
				$dateParams['TOMORROW'] = true;
				$tomorrowTrigger = false;
			}

			if ( $dayStr == $today ) {
				$dateParams['TODAY'] = true;
				$tomorrowTrigger = true;
			}

			$dateIndex[$dayStr] = $dateParams;

			if ( $dayStr == $todayLocal )
				$todayIndex = $counter - 1;

			$curDate = strtotime( "+1 day", $curDate );
			$counter++;
		}

		$ganttSettings = array();
		$ganttSettings['DATE_INDEX'] = $dateIndex;
		$ganttSettings['MONTHS'] = $months;
		$ganttSettings['DISPLAY_DAYS'] = count($dateIndex) <= 31;
		$ganttSettings['DAY_COUNT'] = count($dateIndex);
		$ganttSettings['TODAY_INDEX'] = $todayIndex;
		$ganttSettings['ROW_COUNT'] = count($workData);

		$monthCount = count($months);
		$mk = array_keys($months);
		if ( $monthCount == 1 )
			$ganttSettings['INTERVAL_TITLE'] = $months[$mk[0]]['int_name'];
		else
			$ganttSettings['INTERVAL_TITLE'] = sprintf( '%s - %s', $months[$mk[0]]['int_name'], $months[$mk[$monthCount-1]]['int_name'] );

		if ( isset( $todayTime ) )
			$today = $todayTime;

		if ( !$toEnd )
			$ganttSettings['TODAY_LABEL'] = sprintf( "%s - %s", $kernelStrings['app_today_text'], pm_makeNiceDate( strtotime($today), $kernelStrings ) );
		else
			$ganttSettings['TODAY_LABEL'] = sprintf( "%s - %s", $pmStrings['pm_ganttcompletedate_label'], pm_makeNiceDate( strtotime($today), $kernelStrings ) );
	}

	function pm_listGanttIntervals( $U_ID, $pmStrings, $kernelStrings )
	//
	// Returns list of gantt diagram intervals
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$pmStrings - Project Management localization Strings
	//			$kernelStrings - Kernel localizatoin strings
	//
	//		Returns array( id=>array('start'=>start, 'end'=>end, 'name'=>name )
	//
	{
		$intervals = readUserCommonSetting( $U_ID, 'ganttIntervals' );

		if ( !strlen($intervals) )
			return array();

		$list = unserialize( base64_decode( $intervals ) );

		foreach( $list as $key=>$data ) {
			$name = array();

			$start = $data['start'];
			if ( $start == 0 )
				$name[] = $pmStrings['pm_intfirstmonth_text'];
			else
				$name[] = pm_makeNiceDate( strtotime($start), $kernelStrings, true );

			$end = $data['end'];
			if ( $end == 0 )
				$name[] = $pmStrings['pm_intlastmonth_text'];
			else {
				$name[] = pm_makeNiceDate( strtotime($end), $kernelStrings, true );
			}

			$name = implode(' - ', $name);
			$data['name'] = $name;
			$list[$key] = $data;
		}

		return $list;
	}

	function pm_saveGanttInterval( $U_ID, $start, $end, $kernelStrings, $pmStrings )
	//
	// Saves interval into user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$start - interval start date
	//			$end - interval end date
	//			$kernelStrings - Kernel localizatoin strings
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns interval identifier or PEAR_Error
	//
	{
		// Do not save "All project" interval
		//
		if ( $start == 0 && $end == 0 )
			return;

		if ( $start != 0 ) {
			$dateArr = array( 'start'=>$start );
			$invalidField = checkDateFields( $dateArr, array( 'start' ), $dateArr );
			if ( PEAR::isError( $invalidField ) ) {
				$invalidField->message = sprintf($kernelStrings[ERR_DATEFORMAT], DATE_DISPLAY_FORMAT);

				return $invalidField;
			}

			$timestamp = null;
			validateInputDate( $start, $timestamp );
			$start = convertToSqlDate( $timestamp );
		}

		if ( $end != 0 ) {
			$dateArr = array( 'end'=>$end );
			$invalidField = checkDateFields( $dateArr, array( 'start' ), $dateArr );
			if ( PEAR::isError( $invalidField ) ) {
				$invalidField->message = sprintf($kernelStrings[ERR_DATEFORMAT], DATE_DISPLAY_FORMAT);

				return $invalidField;
			}

			$timestamp = null;
			validateInputDate( $end, $timestamp );
			$end = convertToSqlDate( $timestamp );
		}

		if ( $start != 0 && $end != 0 && $end < $start )
			return PEAR::raiseError( $pmStrings['dvi_startend_message'], ERRCODE_APPLICATION_ERR );

		$interval = array( 'start'=>$start, 'end'=>$end );

		$intervals = pm_listGanttIntervals( $U_ID, $pmStrings, $kernelStrings );

		$matchIndex = null;
		foreach( $intervals as $key=>$data )
			if ( $data['start'] == $start && $data['end'] == $end )
				$matchIndex = $key;

		if ( !is_null($matchIndex) )
			unset( $intervals[$matchIndex] );

		if ( count($intervals) > 11 )
			$intervals = array_slice( $intervals, 0, 11 );

		$id = uniqid( 'int' );
		$intervals = array_merge( array( $id=>$interval ), $intervals );

		$intervals = base64_encode( serialize( $intervals ) );
		writeUserCommonSetting( $U_ID, 'ganttIntervals', $intervals, $kernelStrings );

		return $id;
	}
	
	function pm_onDeleteDDFolder ($params)
	//
	// Callback function, on DD folder delete
	//
	//		Parameters:
	//			$params - other parameters array
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_pm_unset_project_ddfolder;
		
		extract( $params );
		@include $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );
		
		eval( prepareFileContent($appDirPath."/pm_queries_cmn.php") );
		
		$qr = db_query( $qr_pm_unset_project_ddfolder, array('DF_ID'=>$DF_ID) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		return true;		
	}

	function pm_deleteGanttIntervals( $U_ID, $int_list, $kernelStrings, $pmStrings )
	//
	// Saves interval into user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$int_list - list of intervals to delete
	//			$kernelStrings - Kernel localizatoin strings
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns null or PEAR_Error
	//
	{
		$intervals = pm_listGanttIntervals( $U_ID, $pmStrings, $kernelStrings );
		if ( count($intervals) > 11 )
			$intervals = array_slice( $intervals, 0, 11 );

		$newIntervals = array();
		foreach ( $intervals as $int_id=>$data )
			if ( !in_array( $int_id, $int_list ) )
				$newIntervals[$int_id] = $data;

		$intervals = base64_encode( serialize( $newIntervals ) );
		writeUserCommonSetting( $U_ID, 'ganttIntervals', $intervals, $kernelStrings );

		return null;
	}

	function pm_importCustomers( $U_ID, $file, $importFirstLine, $columns, $nameMap, $kernelStrings, $pmStrings )
	//
	// Imports customers from CSV file
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$file - source file
	//			$importFirstLine - import first line
	//			$columns - importing column indeces
	//			$nameMap - columns map
	//			$kernelStrings - Kernel localizatoin strings
	//			$pmStrings - Project Management localization Strings
	//
	//		Returns number of imported customers or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;
		global $pm_importColumnNames;

		if ( !count($columns) )
			return PEAR::raiseError( $pmStrings['icl_nocolserr_message'], ERRCODE_APPLICATION_ERR );

		$userName = getUserName( $U_ID, true );

		foreach( $columns as $columnID )
			if ( !isset($nameMap[$columnID]) || !strlen($nameMap[$columnID]) )
				return PEAR::raiseError( "", ERRCODE_APPLICATION_ERR,
											$_PEAR_default_error_mode,
											$_PEAR_default_error_options,
											"column".$columnID );

		$separator =  getCSVSeparator( $file, $kernelStrings );
		if ( PEAR::isError($separator) )
			return $separator;

		if ( !in_array( PM_COLUMN_NAME, $nameMap) )
			return PEAR::raiseError( $pmStrings['icl_custnameerr_message'], ERRCODE_APPLICATION_ERR );
		else {
			$found = false;
			foreach( $nameMap as $field_index=>$field_id )
				if ( $field_id == PM_COLUMN_NAME && in_array($field_index, $columns) ) {
					$found = true;
					break;
				}

			if ( !$found )
				return PEAR::raiseError( $pmStrings['icl_custnameerr_message'], ERRCODE_APPLICATION_ERR );
		}

		$handle = fopen ( $file, "r" );
		$index = 0;
		$result = 0;
		while ( $data = fgetcsv ($handle, 1000, $separator ) ) {
			$custData = array();

			foreach( $nameMap as $field_index=>$field_id ) {
				if ( in_array($field_index, $columns) )
					$custData[$field_id] = $data[$field_index];
			}

			$index++;

			if ( !$importFirstLine && $index == 1 )
				continue;

			$custData["C_MODIFYUSERNAME"] = $userName;

			$res = pm_addmodCustomer( ACTION_NEW, $custData, $pmStrings, $kernelStrings );
			if ( $res )
				$result++;
		}
		fclose ($handle);

		return $result;
	}

	
	function pm_createProjectDDFolder ($U_ID, $projectData, $kernelStrings) {
		$rootFolder = pm_getProjectsRootDDFolder ($U_ID, $kernelStrings);
		if (PEAR::isError($rootFolder))
			return $rootFolder;			
		
		$rootFolderId = $rootFolder["DF_ID"];
		
		$sql = new CSelectSqlQuery ("PROJECT");
		$sql->addConditions ("P_ID", $projectData["P_ID"]);
		$projectData = db_query_result ($sql->getQuery (), DB_ARRAY, array ());
		if ( PEAR::isError($projectData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		if ($projectData["DF_ID"])
			return $projectData["DF_ID"];
		
		
		require_once( WBS_DIR."/published/DD/dd.php" );
		global $dd_loc_str;
		global $language;
		global $dd_treeClass;
		global $PMRightsManager;
		global $UR_Manager;
		$ddStrings = $dd_loc_str[$language];
		// If project folder doesn't exist - create
		$action = ACTION_NEW;
		$folderName = ereg_replace( "\\/|\\\|\\?|:|<|>|\\*", "_", $projectData["P_DESC"]);
		$folderData = array ("DF_NAME" =>  $folderName, "DF_SPECIALSTATUS" => FOLDER_SPECIALSTATUS_PM_PROJECT);
		$admin = true;
		$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, "originalData" => $folderData );
		$folderId = $dd_treeClass->addmodFolder( $action, $U_ID, $rootFolderId, prepareArrayToStore($folderData),
													$kernelStrings, $admin, 'dd_onCreateFolder', $callbackParams, true, true );
		
		if (PEAR::isError( $folderId ))
			return $folderId;
		
		$usql = new CUpdateSqlQuery ("PROJECT");
		$uparams = array ("DF_ID" => $folderId);
		$usql->addFields ($uparams, array("DF_ID"));
		$usql->addConditions ("P_ID", $projectData["P_ID"]);
		$qr = db_query ($usql->getQuery ());
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		
		$res = $UR_Manager->SetRightsLink("/ROOT/PM/PROJECTS", $projectData["P_ID"], "/ROOT/DD/FOLDERS", $folderId);
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $res );
				
		return $folderId;
	}
	
	function pm_getProjectsRootDDFolder ($U_ID, $kernelStrings) {
		require_once( WBS_DIR."/published/DD/dd.php" );
		global $dd_loc_str;
		global $language;
		global $dd_treeClass;
		$ddStrings = $dd_loc_str[$language];
		
		$folderId = null;
		do {
			$sql = new CSelectSqlQuery ("DOCFOLDER");
			$sql->addConditions ("DF_SPECIALSTATUS", FOLDER_SPECIALSTATUS_PM_ROOT);
			$row = db_query_result($sql->getQuery(), DB_ARRAY);
			if ($row) {
				$folderId = $row["DF_ID"];
				break;
			}
			
			// If projects root folder doesn't exist
			$action = ACTION_NEW;
			$folderData = array ("DF_NAME" => "Projects Files", "DF_SPECIALSTATUS" => FOLDER_SPECIALSTATUS_PM_ROOT);
			$admin = false;
			$callbackParams = array( 'ddStrings'=>$ddStrings, 'kernelStrings'=>$kernelStrings, "originalData" => $folderData );
			$folderId = $dd_treeClass->addmodFolder( $action, $U_ID, TREE_ROOT_FOLDER, prepareArrayToStore($folderData),
														$kernelStrings, $admin, 'dd_onCreateFolder', $callbackParams, true, true );
			
			if (PEAR::isError( $folderId ))
				return $folderId;
		} while (false);
		
		//return $folderId;
	
		return $dd_treeClass->getFolderInfo($folderId, $kernelStrings);
	}
	
	function pm_getCurrencies()
	{
	    global $qr_pm_select_currency_list;
	    
	    $currencies = array();
	    $res = db_query($qr_pm_select_currency_list);
	    while($row = db_fetch_array($res))
	    {
	        $currencies[] = $row['CUR_ID'];
	    };
	    return $currencies;
	}
	
	function pm_getProjectWorkAssignments($project_id, $work_id)
	{
	    global $qr_pm_select_work_asgn;
	    
	    $uids = array();
	    $res = db_query($qr_pm_select_work_asgn, array('P_ID' => $project_id, 'PW_ID' => $work_id));
	    while($row = db_fetch_array($res))
	    {
	        $uids[] = $row['U_ID'];
	    };
	    return $uids;
	}
?>