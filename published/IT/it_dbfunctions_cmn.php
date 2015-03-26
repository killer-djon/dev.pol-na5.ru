<?php

	//
	// Issue Tracking DMBS-independent application functions
	//

	function it_saveTemplateSchema( $templateData, $P_ID, $PW_ID, $kernelStrings, $itStrings )
	//
	// Saves issue transition template schema
	//
	//		Parameters:
	//			$templateData - array containing template name
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_templatenameexists;
		global $qr_it_createtemplate;
		global $qr_it_getmaxITT_ID;
		global $qr_it_inserttemplatestate;

		$requiredFields = array( "ITT_NAME" );

		if ( PEAR::isError( $invalidField = findEmptyField($templateData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$invalidField = checkStringLengths($templateData, array("ITT_NAME"), array(100));
		if ( PEAR::isError($invalidField) ) {
			$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

			return $invalidField;
		}

		$templateExists = db_query_result( $qr_it_templatenameexists, DB_FIRST, $templateData );
		if ( PEAR::isError($templateExists) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $templateExists )
			return PEAR::raiseError( $itStrings['svt_templateexists_message'], ERRCODE_APPLICATION_ERR );

		$ITT_ID = db_query_result( $qr_it_getmaxITT_ID, DB_FIRST, array() );
		if ( PEAR::isError($ITT_ID) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$ITT_ID = incID($ITT_ID);

		$templateData['ITT_ID'] = $ITT_ID;
		$templateData['ITT_STATUS'] = 0;

		$res = db_query( $qr_it_createtemplate, $templateData );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$transSchema = it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings );
		if ( PEAR::isError($transSchema) )
			return $transSchema;

		foreach( $transSchema as $stateData ) {
			$params = array();

			$params["ITT_ID"] = $ITT_ID;
			$params["ITS_NUM"] = $stateData["ITS_NUM"];
			$params["ITTS_STATUS"] = $stateData["ITS_STATUS"];
			$params["ITTS_ALLOW_EDIT"] = $stateData["ITS_ALLOW_EDIT"];
			$params["ITTS_ALLOW_DELETE"] = $stateData["ITS_ALLOW_DELETE"];
			$params["ITTS_ASSIGNMENTOPTION"] = $stateData["ITS_ASSIGNMENTOPTION"];

			$params["ITTS_ASSIGNED"] = $stateData["U_ID_ASSIGNED"];

			$params["ITTS_COLOR"] = $stateData["ITS_COLOR"];
			$params["ITTS_ALLOW_DEST"] = $stateData["ITS_ALLOW_DEST"];
			$params["ITTS_DEFAULT_DEST"] = $stateData["ITS_DEFAULT_DEST"];

			$res = db_query( $qr_it_inserttemplatestate, $params );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		return null;
	}

	function it_loadTemplateIssueTransitionSchema( $ITT_ID, $itStrings )
	//
	// Loads Template Issue Transition Schema into array
	//
	//		Parameters:
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns array of transition states or PEAR_Error
	//
	{
		global $qr_it_selecttemplateschematransitions_full;

		$params = array( "ITT_ID"=>$ITT_ID );

		$qr = db_query( $qr_it_selecttemplateschematransitions_full, $params );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITS] );

		$transMatrix = array();

		while ( $row = db_fetch_array($qr) )
			$transMatrix[] = $row;

		db_free_result( $qr );

		return $transMatrix;
	}

	function it_setTemplateAsDefault( $ITT_ID, $kernelStrings )
	//
	// Sets template as template as default
	//
	//		Parameters:
	//			$ITT_ID - template identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_setTemplateDefault;
		global $qr_it_resetTemplateDefault;

		$res = db_query($qr_it_resetTemplateDefault);
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$params = array( 'ITT_DEFAULT'=>1, 'ITT_ID'=>$ITT_ID );
		$res = db_query($qr_it_setTemplateDefault, $params);
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function it_getDefaultTemplate( $kernelStrings )
	//
	// Returns default workflow template or null if template is not found
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns template identifier or PEAR_Error
	//
	{
		global $qr_it_selectDefaultTemplate;

		$ITT_ID = db_query_result( $qr_it_selectDefaultTemplate, DB_FIRST );
		if ( PEAR::isError($ITT_ID) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !strlen($ITT_ID) )
			return null;

		return $ITT_ID;
	}

	function it_isEndState( $P_ID, $PW_ID, $ITS_NUM, $itStrings )
	//
	// Checks if ITS_NUM is a last (close) state in workflow
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$ITS_NUM - state number
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		$workflow = it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings );
		if ( PEAR::isError($workflow) )
			return $workflow;

		$workflowLength = count($workflow);
		if ( !$workflowLength )
			return false;

		$lastState = $workflow[$workflowLength-1];

		return $lastState["ITS_NUM"] == $ITS_NUM;
	}

	function it_deleteWorkflowTemplate( $ITT_ID, $kernelStrings, $itStrings )
	//
	// Deletes workflow template
	//
	//		Parameters:
	//			$ITT_ID - template identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_delete_template;
		global $qr_it_delete_templatestatues;

		$params = array( "ITT_ID"=>$ITT_ID );

		$defaultTemplateID = it_getDefaultTemplate( $kernelStrings );
		if ( PEAR::isError($defaultTemplateID) )
			return $defaultTemplateID;

		if ( $defaultTemplateID == $ITT_ID )
			return PEAR::raiseError( $itStrings['wt_defdel_message'], ERRCODE_APPLICATION_ERR );

		$res = db_query( $qr_it_delete_template, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$res = db_query( $qr_it_delete_templatestatues, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function it_compareProjects( $a, $b )
	//
	// Internal function for project list sorting
	//
	//		Parameters:
	//			$a - information about first project
	//			$b - information about second project
	//
	//		Returns -1, 0, or 1 depending on compare result
	//
	{
		$val1 = $a[2].$a[3];
		$val2 = $b[2].$b[3];

		if ($val1 == $val2)
			return 0;

		return ($val1 > $val2) ? 1 : -1;
	}

	function it_getUserAssignedProjects( $U_ID, $itStrings, $truncateLen = null, $addCustNames = false,
											$custNameTruncateLen = null, $closedProjects = false, $attachClosedTag = false,
											$allProjects = false, $addManagerName = false, $openFirst = false, $managerOnly = false )
	//
	// Returns list of projects, which have work assignments for user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$truncateLen - project name length
	//			$addCustNames - if it is true, customer name is added to the beginning of project name
	//			$custNameTruncateLen - customer name length
	//			$closedProjects - if it is true, closed projects are also returned
	//			$attachClosedTag - if it is true, string "(closed)" is attached to project name
	//			$itStrings - Issue Tracking localization strings
	//			$allProjects - if it is true, whole project list is returned
	//			$addManagerName - attach manager name to the end of result strings
	//			$openFirst - place complete projects after open projects
	//			$managerOnly - returns only given manager projects
	//
	//		Returns project list in the form on an array( P_ID=>C_NAME.P_DESC ), or PEAR_Error
	//
	{
		global $qr_it_assigned_projects;
		global $qr_it_assigned_projects_custumers;
		global $qr_it_allassignedprojects;
		global $qr_it_allassignedprojects_custumers;
		global $qr_it_opened_manager_projects;
		global $qr_it_manager_projects;
		global $qr_it_select_all_projects;
		global $qr_it_select_all_projects_customers;
		global $qr_it_select_all_projects_customers_activefirst;

		global $PMRightsManager;
		
		if (PM_DISABLED) {
			return array (0 => "Free Issues");
		}

		$m_qr = null;
		$qr = null;

		$ur_projects = array();

		if ( !$allProjects )
		{
			$ur_projects =  $PMRightsManager->getUserProjects( $U_ID, $itStrings );

			if ( PEAR::isError($ur_projects) )
				return $ur_projects;

			if ( !$closedProjects ) {
				$m_qr = db_query( $qr_it_opened_manager_projects, array( "U_ID"=>$U_ID ) );

				if ( PEAR::isError($m_qr) )
					return $m_qr;

				if ( !$managerOnly ) {
					if ( !$addCustNames )
						$qr = db_query( $qr_it_assigned_projects, array( "U_ID"=>$U_ID ) );
					else
						$qr = db_query( $qr_it_assigned_projects_custumers, array( "U_ID"=>$U_ID ) );
				}
			} else {
				$m_qr = db_query( $qr_it_manager_projects, array( "U_ID"=>$U_ID ) );

				if ( PEAR::isError($m_qr) )
					return $m_qr;

				if ( !$managerOnly ) {
					if ( !$addCustNames )
						$qr = db_query( $qr_it_allassignedprojects, array( "U_ID"=>$U_ID, "U_ID"=>$U_ID ) );
					else
						$qr = db_query( $qr_it_allassignedprojects_custumers, array( "U_ID"=>$U_ID, "U_ID"=>$U_ID ) );
				}
			}
		} else {
			if ( !$addCustNames )
				$qr = db_query( $qr_it_select_all_projects, array() );
			else {
				if ( !$openFirst )
					$qr = db_query( $qr_it_select_all_projects_customers, array() );
				else
					$qr = db_query( $qr_it_select_all_projects_customers_activefirst, array() );
			}
		}

		if ( PEAR::isError($qr) )
			return $qr;

		$managerProjects = array();
		$otherProjects = array();

		if ( !is_null($m_qr) ) {
			while ( $row = db_fetch_array( $m_qr ) )
				$managerProjects[$row["C_ID"]."|".$row["P_ID"]] = array( $row["P_ID"], $row["C_ID"], $row["C_NAME"], $row["P_DESC"], $row["P_ENDDATE"] );
			@db_free_result( $m_qr );
		}

		if ( !is_null($qr) ) {
			while ( $row = db_fetch_array( $qr ) )
				$otherProjects[$row["C_ID"]."|".$row["P_ID"]] = array( $row["P_ID"], $row["C_ID"], $row["C_NAME"], $row["P_DESC"], $row["P_ENDDATE"] );
			@db_free_result( $qr );
		}
		
		foreach( $ur_projects as $row )
		{
			if ( UR_RightsManager::CheckMask($row["PA_RIGHT"], UR_TREE_FOLDER) )
			{
				if ( !$closedProjects && !is_null( $row["CLOSED"]  ) )
					continue;

				$managerProjects[$row["C_ID"]."|".$row["P_ID"]] = array( $row["P_ID"], $row["C_ID"], $row["C_NAME"], $row["P_DESC"], $row["P_ENDDATE"] );
			}
			else
			if ( !$managerOnly && UR_RightsManager::CheckMask($row["PA_RIGHT"], UR_TREE_WRITE) )
			{
				if ( !$closedProjects && !is_null( $row["CLOSED"]  ) )
					continue;

				$otherProjects[$row["C_ID"]."|".$row["P_ID"]] = array( $row["P_ID"], $row["C_ID"], $row["C_NAME"], $row["P_DESC"], $row["P_ENDDATE"] );
			}
		}
		
		$projects = array_merge( $managerProjects, $otherProjects );
		uasort( $projects, "it_compareProjects" );

		$result = array();

		foreach( $projects as $projKey=>$projData ) {
			if ( !$addCustNames)
				$C_NAME = "";
			else
				if ( !is_null($custNameTruncateLen) )
					$C_NAME = strTruncate( $projData[2], $custNameTruncateLen );
				else
					$C_NAME = $projData[2];

			if ( strlen($C_NAME) )
				$C_NAME = sprintf( "%s. ", $C_NAME );

			if ( !is_null($truncateLen) )
				$result[$projData[0]] = $C_NAME.strTruncate( $projData[3], $truncateLen );
			else
				$result[$projData[0]] = $C_NAME.$projData[3];

			if ( $attachClosedTag) {
				if ( strlen( $projData[4] ) ) {
					$closedTS = sqlTimestamp( $projData[4] );
					if ( $closedTS <= time() )
						$result[$projData[0]] = $result[$projData[0]].sprintf( " (%s)", $itStrings['il_completeproj_text'] );
				}
			}

			if ( $addManagerName) {
				$projmanData = it_getProjManData( $projData[0] );
				$managerName = getArrUserName( $projmanData, true );
				$result[$projData[0]] .= sprintf( " (%s)", $managerName );
			}
			
			if ($projData[0] == 0)
				$result[$projData[0]] = $itStrings["app_freeissues_label"];
		}
		
		// Move Free project to the start of array
		if (isset($result[0]))
		{
			$newRes = array ();
			$newRes[0] = $result[0];
			foreach ($result as $key => $value)
				if ($key != 0) $newRes[$key] = $value;
			$result = $newRes;
		}
		
		return $result;
	}

	function it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings, $autoCreate = false )
	//
	// Loads Issue Transition Schema into array
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$itStrings - Issue Tracking localization strings
	//			$autoCreate - create schema if it is not exists
	//
	//		Returns array of transition states or PEAR_Error
	//
	{
		global $qr_it_issue_real_transitions;
		global $qr_it_schematransitionscount;
		global $qr_it_initWorkflow;
		global $qr_it_copyDefaultTransitions;

		$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, );

		$res = db_query_result( $qr_it_schematransitionscount, DB_FIRST, $params );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITS] );
		
		if ( !$res && $autoCreate ) {
			if ($P_ID == 0 && $PW_ID == 0)
				$res = db_query ($qr_it_copyDefaultTransitions, null);
			else
				$res = db_query( $qr_it_initWorkflow, $params );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $itStrings[IT_ERR_LOADITS] );
		}

		$qr = db_query( $qr_it_issue_real_transitions, $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITS] );

		$transMatrix = array();

		while ( $row = db_fetch_array($qr) )
			$transMatrix[] = $row;

		db_free_result( $qr );

		return $transMatrix;
	}

	function it_listActiveProjectUserWorks( $P_ID, $U_ID, $filterData, $truncateLen = null, $projectsIds = null )
	//
	// Returns list of works accessible to user.
	//		Allows to trim work description at the certain position.
	//		For project manager all active works are returned.
	//		If P_ID = null, list of all works accessible to user is returned
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$U_ID - user identifier
	//			$filterData - information about issue list filter
	//			$truncateLen - work description length
	//			$projectsIds - hack, if P_ID is null - select works for projectList
	//
	//		Returns list of works in the form of an array( P_ID=>array( PW_ID=>array("PW_DESC"=>PW_DESC, "CLOSED"=>0),... ), ... ), or PEAR_Error
	//
	{
		global $qr_it_project_active_works;
		global $qr_it_all_project_works;
		global $qr_it_all_user_works;
		global $qr_it_all_manager_works;
		global $PMRightsManager;
		
		if (PM_DISABLED)
			return array (0 => array (0 => it_getFreeWorkData() ));

		$filterStr = it_prepareWorkFilterSQL( $filterData );
		$result = array();

		if ( !is_null($P_ID) ) {
			// One project
			//
			$projmanData = it_getProjManData( $P_ID );
			if ( PEAR::isError($projmanData) )
				return $projmanData;

			$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );
			if ($P_ID == 0)
				$userIsProjman = true;

			if ( $userIsProjman )
				$qr = db_query( sprintf($qr_it_all_project_works, $filterStr), array("P_ID"=>$P_ID, "U_ID"=>$U_ID) );
			else {
				$qr = db_query( sprintf($qr_it_project_active_works, $filterStr), array("P_ID"=>$P_ID, "U_ID"=>$U_ID) );
			}
			
			if ( PEAR::isError($qr) )
				return $qr;
			

			while ( $row = db_fetch_array($qr) ) {
				$PW_DESC = $row["PW_DESC"];
				if ( !is_null($truncateLen) )
					$PW_DESC = strTruncate( $row["PW_DESC"], $truncateLen );

				$closed = it_checkWorkEndDate($row["PW_ENDDATE"]);

				$result[$row["P_ID"]][$row["PW_ID"]] = array( "PW_DESC"=>$PW_DESC, "CLOSED"=>$closed, "PW_ENDDATE"=>$row["PW_ENDDATE"] );
			}
			
			db_free_result( $qr );
		} else {
			if ($projectsIds) {
				$result =array ();
				foreach ($projectsIds as $cId) {
					$prRes = it_listActiveProjectUserWorks ($cId, $U_ID, $filterData, $truncateLen, $projectList);
					foreach ($prRes as $cKey => $cValue) {
						$result[$cKey] = $cValue;						
					}
				}
			}
			
			// All projects
			//
			/*$queries = array( $qr_it_all_user_works, $qr_it_all_manager_works );

			for ( $i = 0; $i < count($queries); $i++ ) {
				$query = $queries[$i];

				$qr = db_query( sprintf($query, $filterStr), array("U_ID"=>$U_ID) );
				if ( PEAR::isError($qr) )
					return $qr;

				while ( $row = db_fetch_array($qr) ) {
					$PW_DESC = $row["PW_DESC"];
					if ( !is_null($truncateLen) )
						$PW_DESC = strTruncate( $row["PW_DESC"], $truncateLen );

					$closed = it_checkWorkEndDate($row["PW_ENDDATE"]);

					if (!isset($result[$row["P_ID"]][$row["PW_ID"]]))
						$result[$row["P_ID"]][$row["PW_ID"]] = array( "PW_DESC"=>$PW_DESC, "CLOSED"=>$closed, "PW_ENDDATE"=>$row["PW_ENDDATE"] );
				}
				
				db_free_result( $qr );
			}*/

		}

		return $result;
	}

	function it_listAllWorks( $P_ID, $truncateLen = null )
	//
	// Returns whole list of project works
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$truncateLen - work description length
	//
	//		Returns array(PW_ID=>PW_DESC...), or PEAR_Error
	//
	{
		global $qr_it_select_works;

		$qr = db_query( $qr_it_select_works, array("P_ID"=>$P_ID) );
		if ( PEAR::isError($qr) )
			return $qr;

		$result = array();
		while ( $row = db_fetch_array($qr) ) {
			$workDesc = (is_null($truncateLen)) ? $row["PW_DESC"] : strTruncate( $row["PW_DESC"], $truncateLen );
			$result[$row["PW_ID"]] = $workDesc;
		}

		db_free_result( $qr );

		return $result;
	}

	function it_workIsClosed( $P_ID, $PW_ID, $kernelStrings )
	//
	// Checks if the work is finished
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns value of boolean type
	//
	{
		global $qr_it_selectwork;
		
		if (PM_DISABLED)
			return false;

		$workdata = db_query_result( $qr_it_selectwork, DB_ARRAY, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
		if ( PEAR::isError($workdata) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return it_checkWorkEndDate($workdata["PW_ENDDATE"]);
	}

	function it_loadITSData( $P_ID, $PW_ID, $ITS_STATUS = null )
	//
	// Returns state of transitions scheme
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$ITS_STATUS - name of state, which needs data.
	//				If this parameter is null, function returns first state in scheme
	//
	//	Returns record ISSUETRANSITIONSCHEMA, or PEAR_Error
	//
	{
		global $qr_it_select_first_transition_num;
		global $qr_it_select_transition_byitsnum;
		global $qr_it_select_transition_bystatusname;
		global $qr_it_copyDefaultTransitions;

		$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID );

		if ( is_null( $ITS_STATUS ) ) {
			$params["ITS_NUM"] = IT_FIRST_STATUS;

			$startStateRecord = db_query_result( $qr_it_select_transition_byitsnum, DB_ARRAY, $params );
			if ( PEAR::isError($startStateRecord) )
				return $startStateRecord;
			
			// 
			if (!$startStateRecord && $P_ID == 0 && $PW_ID == 0) {
				if ( PEAR::isError($res = db_query( $qr_it_copyDefaultTransitions)) ) {
					return $res;
				}
				$startStateRecord = db_query_result( $qr_it_select_transition_byitsnum, DB_ARRAY, $params );
			}
				

			$startStates = explode( IT_TRANSITIONS_SEPARATOR, $startStateRecord["ITS_ALLOW_DEST"] );

			$result = array();

			foreach( $startStates as $statusName ) {
				$statusName = trim($statusName);
				if ( !strlen($statusName) )
					continue;

				$params["ITS_STATUS"] = $statusName;

				$transitionRecord = db_query_result( $qr_it_select_transition_bystatusname, DB_ARRAY, $params );
				if ( PEAR::isError($transitionRecord) )
					return $transitionRecord;

				$result[] = $transitionRecord;
			}
			
			return $result;
		} else {
			$params["ITS_STATUS"] = $ITS_STATUS;

			return db_query_result( $qr_it_select_transition_bystatusname, DB_ARRAY, $params );
		}
	}
	
	function it_getFreeWorkData () {
		return array ("P_ID" => 0, "PW_ID" => 0, "PW_DESC" => "Free Issues");
	}
	
	function it_getFreeWorkAssignments ($itStrings, $kernelStrings) {
		$res = listUsers($kernelStrings);
		return array_keys($res);
	}

	function it_initIssue( $P_ID, $PW_ID, $kernelStrings, $itStrings, &$ITSData, $U_ID )
	//
	// Initializes issue array in accordance with settings of first state of transitions scheme:
	//		fills fields P_ID, PW_ID, I_STATUSCURRENT, U_ID_ASSIGNED, I_PRIORITY, I_STARTDATE
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$ITSData - information about first state of transitions scheme
	//			$startStatus - initial issue status
	//			$U_ID - user identifier
	//
	//	Returns filled array, or PEAR_Error
	//
	{
		global $qr_it_selectwork;
		global $PMRightsManager;

		$issueData = array();

		$issueData["P_ID"] = $P_ID;
		$issueData["PW_ID"] = $PW_ID;
		$issueData["I_PRIORITY"] = IT_ISSUE_PRIORIY_NORMAL;
		$issueData["I_STARTDATE"] = displayDate( time() );

		$projmanData = it_getProjManData( $issueData["P_ID"] );
		if ( PEAR::isError($projmanData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		$workdata = (PM_DISABLED) ?
			it_getFreeWorkData () :
			db_query_result( $qr_it_selectwork, DB_ARRAY, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
		
		if ( PEAR::isError($ITSData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( it_checkWorkEndDate($workdata["PW_ENDDATE"]) )
			return PEAR::raiseError( $itStrings['il_addcompltask_message'], ERRCODE_APPLICATION_ERR );

		if ( !$userIsProjman ) {
			$ITSData = it_loadITSData( $P_ID, $PW_ID );
			if ( PEAR::isError($ITSData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !is_array($ITSData) || !count($ITSData) )
				return PEAR::raiseError( $itStrings['cw_noworkflow_message'], ERRCODE_APPLICATION_ERR );

			$ITSData = $ITSData[0];
		} else {
			$transitions = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings, false );
			if ( PEAR::isError($transitions) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$keys = array_keys( $transitions );

			$ITSData = $transitions[$keys[0]];
			$defaultTransition = $ITSData['ITS_DEFAULT_DEST'];

			if ( strlen($defaultTransition) && array_key_exists( $defaultTransition, $transitions ) )
				$ITSData = $transitions[$defaultTransition];
			else {
				$ITSData = it_loadITSData( $P_ID, $PW_ID );
				if ( PEAR::isError($ITSData) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( !is_array($ITSData) || !count($ITSData) )
					return PEAR::raiseError( $itStrings['cw_noworkflow_message'], ERRCODE_APPLICATION_ERR );

				$ITSData = $ITSData[0];
			}
		}

		$issueData["I_STATUSCURRENT"] = $ITSData["ITS_STATUS"];

		if ( $ITSData["ITS_ASSIGNMENTOPTION"] == IT_ASSIGNMENTOPT_NOTAPPLICABLE )
			$issueData["U_ID_ASSIGNED"] = null;
		else
			$issueData["U_ID_ASSIGNED"] = $ITSData["U_ID_ASSIGNED"];
		
		return $issueData;
	}

	function it_listAllowedIssueAssignments( $P_ID, $PW_ID, $itStrings, $kernelStrings, $ITSData, $sender )
	//
	// Returns list of user identifiers, who can be assigned to issue.
	//		Considers Assignment Option of transitions scheme.
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$itStrings - Issue Tracking localization strings
	//			$kernelStrings - Kernel localization strings
	//			$ITSData - information about state from ISSUETRANSITIONSCHEMA, the result of function it_loadITSData()
	//			$sender - issue sender
	//
	//		Returns array( U_ID => Username ), or PEAR_Error
	//
	{
		global $qr_it_select_issue_assignments;

		$assignmentOption = $ITSData["ITS_ASSIGNMENTOPTION"];
		$U_ID_Assigned = $ITSData["U_ID_ASSIGNED"];

		$result = array();

		switch ( $assignmentOption ) {
			case IT_ASSIGNMENTOPT_NOTAPPLICABLE : return array( null => IT_NOASSIGNMENT );
			case IT_ASSIGNMENTOPT_NOTSELECTABLE : {
					if ( $U_ID_Assigned != IT_SENDER_OPTION )
						return array( $U_ID_Assigned => getUserName($U_ID_Assigned)/*, true */);
					else
						return array( $sender => getUserName($sender, true) );
			}
			case IT_ASSIGNMENTOPT_NOTREQUIRED : $result[null] = IT_NOASSIGNMENT;
			case IT_ASSIGNMENTOPT_SELECTABLE : {
				
				if (!PM_DISABLED) {
					$qr = db_query( $qr_it_select_issue_assignments, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
					if ( PEAR::isError($qr) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

					while ( $row = db_fetch_array($qr) )
						$result[$row["U_ID"]] = getArrUserName($row, true);
					
					db_free_result( $qr );
				} else {
					$userIds = array_keys(listUsers($kernelStrings));
					foreach ($userIds as $cId)
						$result[$cId] = getUserName($cId, true);
				}

				break;
			}
		}

		return $result;
	}

	function it_addmodIssue( $action, $issueData, $kernelStrings, $itStrings, $ITSData, $U_ID )
	//
	// Examines incoming data and inserts (or modifies) issue into the database
	//
	//		Parameters:
	//			$action - action type
	//			$issueData - an associative array containing fields of ISSUE record
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$ITSData - information about state from ISSUETRANSITIONSCHEMA, the result of function it_loadITSData()
	//			$U_ID - identifier of user, who modifies issue
	//
	//		Returns issue identifier, or PEAR_Error
	//
	{
		global $qr_it_addissue;
		global $qr_it_maxissueid;
		global $qr_it_updateissue;
		global $qr_it_maxissuenum;
		global $qr_it_select_issue;
		global $qr_it_select_issuestatusinfo;
		global $PMRightsManager;

		$I_DESC_length = 10000;
		$assignmentOption = $ITSData["ITS_ASSIGNMENTOPTION"];

		$issueData = trimArrayData( $issueData );
		$issueData = nullSQLFields( $issueData, array( "U_ID_ASSIGNED" ) );

		$requiredFields = array( "I_DESC" );

		$projmanData = it_getProjManData( $issueData["P_ID"] );
		if ( PEAR::isError($projmanData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		// Check if modifications allowed
		//
		if ( ($action == ACTION_EDIT && !$ITSData["ITS_ALLOW_EDIT"]) && !$userIsProjman )
			return PEAR::raiseError( $itStrings['ami_erreditingstate_message'], ERRCODE_APPLICATION_ERR );

		if ( $action == ACTION_EDIT )
			$res = it_getUserITRights( $issueData["P_ID"], $issueData["PW_ID"], $U_ID, $issueData["I_ID"] );
		else
			$res = it_getUserITRights( $issueData["P_ID"], $issueData["PW_ID"], $U_ID );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITRIGHTS] );

		if ( !$res )
			return PEAR::raiseError( $itStrings[IT_ERR_ISSUERIGHTS], ERRCODE_APPLICATION_ERR );

		if ( $action == ACTION_EDIT && !$userIsProjman ) {
			$res = it_workIsClosed( $issueData["P_ID"], $issueData["PW_ID"], $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			if ( $res )
				return PEAR::raiseError( $itStrings['ami_erreditcompltask_message'], ERRCODE_APPLICATION_ERR );
		}
		

		// Check required fields
		//
		if ( $assignmentOption == IT_ASSIGNMENTOPT_NOTSELECTABLE || $assignmentOption == IT_ASSIGNMENTOPT_SELECTABLE )
			$requiredFields = array_merge( $requiredFields, array( "U_ID_ASSIGNED" ) );

		if ( PEAR::isError( $invalidField = findEmptyField($issueData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
			
			return $invalidField;
		}

		if ( !array_key_exists("U_ID_ASSIGNED", $issueData) || !strlen( $issueData["U_ID_ASSIGNED"] ) )
			$issueData["U_ID_ASSIGNED"] = null;

		// Check field lengths
		//
		$invalidField = checkStringLengths($issueData, array("I_DESC"), array($I_DESC_length));
		if ( PEAR::isError($invalidField) ) {
			$invalidField->message = sprintf($itStrings["il_desctoolong_error"], $I_DESC_length);

			return $invalidField;
		}

		// Check date fields
		//
		$invalidField = checkDateFields( $issueData, array( "I_STARTDATE" ), $issueData );
		if ( PEAR::isError( $invalidField ) ) {
			$invalidField->message = sprintf($kernelStrings[ERR_DATEFORMAT], DATE_DISPLAY_FORMAT);

			return $invalidField;
		}

		// Add/modify data
		//
		if ( $action == ACTION_NEW ) {
			$res = it_issueAddingPermitted( $kernelStrings, $itStrings, $action );
			if ( PEAR::isError($res) )
				return $res;

			$I_ID = db_query_result( $qr_it_maxissueid );
			if ( PEAR::isError( $I_ID ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$I_ID = incID( $I_ID );

			$I_NUM = db_query_result( $qr_it_maxissuenum, DB_FIRST, $issueData );
			if ( PEAR::isError( $I_NUM ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$I_NUM = incID( $I_NUM );

			$issueData["I_STATUSCURRENT"] = $ITSData["ITS_STATUS"];
			$issueData["I_STARTDATE"] = convertToSqlDate( time() );
			$issueData["I_ID"] = $I_ID;
			$issueData["I_NUM"] = $I_NUM;
			$issueData["U_ID_AUTHOR"] = $U_ID;
			$res = exec_sql( $qr_it_addissue, $issueData, $outputList, false );
		} else {
			// Load current issue data
			//
			$prevIssueData = db_query_result( $qr_it_select_issuestatusinfo, DB_ARRAY, array("I_ID"=>$issueData["I_ID"]) );
			if ( PEAR::isError($prevIssueData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$res = exec_sql( $qr_it_updateissue, $issueData, $outputList, false );
		}

		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Set status for new issue
		//
		if ( $action == ACTION_NEW ) {
			$itlData = array( "I_ID"=>$issueData["I_ID"], "ITL_STATUS"=>$ITSData["ITS_STATUS"], "U_ID_SENDER"=>$U_ID,
								"U_ID_ASSIGNED"=>$issueData["U_ID_ASSIGNED"], "ITL_DESC"=>null );

			$res = it_setIssueStatus( $itlData, $kernelStrings, $itStrings, $U_ID, $ITSData, IT_NEXT_TRANSITION, $action );
			if ( PEAR::isError( $res ) )
				return $res;
		} else {
			// Notifications
			//
			if ( $prevIssueData["U_ID_ASSIGNED"] != $issueData["U_ID_ASSIGNED"]) {
				it_sendAssignmentNotification( $issueData["I_ID"], $kernelStrings, $U_ID );
			}
			else
				if ( $prevIssueData["I_DESC"] != $issueData["I_DESC"] ||
					$prevIssueData["I_PRIORITY"] != $issueData["I_PRIORITY"] ||
					$prevIssueData["I_STATUSCURRENT"] != $issueData["I_STATUSCURRENT"] ||
					$prevIssueData["I_ATTACHMENT"] != $issueData["I_ATTACHMENT"] ) {
    				    it_sendIssueModifyNotification( $issueData["I_ID"], $prevIssueData, $kernelStrings, $U_ID );
				}

			// Modification record
			//
			if ( PEAR::isError( $res = it_logIssueModification( $prevIssueData, $issueData, $kernelStrings, $itStrings, $U_ID ) ) )
				return $res;
		}

		return $issueData["I_ID"];
	}

	function it_issueAddingPermitted( &$kernelStrings, &$itStrings, $action )
	//
	// Checks whether adding issue is permitted
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$action - action
	//
	//		Returns null or PEAR_Error
	//
	{
		global $currentUser;

		$limit = getApplicationResourceLimits( 'IT' );
		if ( $limit === null )
			return null;

		$sql = "SELECT COUNT(*) FROM ISSUE";

		$res = db_query_result( $sql, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $action == ACTION_NEW )
		{
			if ( $res >= $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $itStrings['app_issueslimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $itStrings['app_issueslimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}
		else
		{
			if ( $res > $limit )
			{
				if ( hasAccountInfoAccess($currentUser) )
					$Message = sprintf( $itStrings['app_issueslimit_message'], $limit )." ".getUpgradeLink( $kernelStrings );
				else
					$Message = sprintf( $itStrings['app_issueslimit_message'], $limit )." ".$kernelStrings['app_referadmin_message'];

				return PEAR::raiseError( $Message, ERRCODE_APPLICATION_ERR );
			}
		}

		return null;

	}
	
	function it_logAddComment ($commentData, $kernelStrings, $itStrings, $U_ID) {
		global $qr_it_add_comment, $qr_it_get_max_itl,$qr_it_get_last_comment;
		/**
		* GET MAX ITL_ID number
		*/
		
		if (strlen($commentData["COMMENT"]) > 10000)
			return PEAR::raiseError($itStrings["il_commenttoolong_error"]);
		
		
		$param = array( 'I_ID' => $commentData['I_ID'] );
		$res = db_query($qr_it_get_max_itl, $param);
		if (PEAR::isError( $res ))
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		$row = db_fetch_array($res, MYSQL_NUM);
		$max = (int)$row['MAX'];
		/**
		* INSERT COMMENT
		*/
		$param = array(
			'I_ID' => $commentData['I_ID'],
			'ITL_ID' => $max+1,
			'ITL_STATUS' => $commentData['ITS_STATUS'],
			'ITL_DESC' => 'cmnt',
			'ITL_OLDCONTENT' => $commentData['COMMENT'],
			'U_ID_SENDER' => $commentData['U_ID_SENDER'],
		);
		$res = db_query($qr_it_add_comment, $param);
		if (PEAR::isError( $res ))
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		
		$param = array( 'ITL_ID' => $max+1, 'I_ID' => $commentData['I_ID'],);
		$res = db_query($qr_it_get_last_comment, $param);
		if (PEAR::isError( $res ))
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		
		//RET LAST INSERTED
		return db_fetch_array($res, MYSQL_NUM);
	}


	function it_logIssueModification( $srcRecord, $destRecord, $kernelStrings, $itStrings, $U_ID )
	//
	// Adds record describing editing to the log of issue transitions
	//
	//		Parameters:
	//			$srcRecord - record before modification
	//			$destRecord - record after modification
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$U_ID - identifier of user, who modifies issue
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_insertissuemodificationlog;
		global $qr_it_maxitlid;
		global $qr_it_selectITLRecord;
		global $it_issue_priority_names;

		if ( $srcRecord["I_DESC"] == $destRecord["I_DESC"] &&
			 $srcRecord["I_ATTACHMENT"] == $destRecord["I_ATTACHMENT"] &&
			 $srcRecord["I_PRIORITY"] == $destRecord["I_PRIORITY"] &&
			 $srcRecord["U_ID_ASSIGNED"] == $destRecord["U_ID_ASSIGNED"] )
			return null;

		// Load previous ITL record
		//
		$ITL_ID = db_query_result( $qr_it_maxitlid, DB_FIRST, $srcRecord );
		if ( PEAR::isError( $ITL_ID ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$res = exec_sql( $qr_it_selectITLRecord, array("I_ID"=>$srcRecord["I_ID"], "ITL_ID"=>$ITL_ID), $prevITLData, true);
		if ( PEAR::isError( $ITL_ID ) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITL] );

		$ITL_ID = incID( $ITL_ID );

		$itldata["I_ID"] = $srcRecord["I_ID"];
		$itldata["ITL_ID"] = $ITL_ID;
		$itldata["ITL_DATETIME"] = convertToSqlDateTime( time() );
		$itldata["ITL_STATUS"] = $prevITLData["ITL_STATUS"];
		$itldata["U_ID_ASSIGNED"] = $prevITLData["U_ID_ASSIGNED"];
		$itldata["U_ID_SENDER"] = $U_ID;

		$itldata["ITL_OLDCONTENT"] = "";

		$modifiedFields = array();
		if ( $srcRecord["I_DESC"] != $destRecord["I_DESC"] )
			$modifiedFields[] = sprintf( "%s: %s", $itStrings['is_olddesc_text'], $srcRecord["I_DESC"] );

		if ( $srcRecord["I_PRIORITY"] != $destRecord["I_PRIORITY"] )
			$modifiedFields[] = sprintf( "%s: %s", $itStrings['is_prchanged_text'],
								sprintf( $itStrings['is_fromto_text'],
										$itStrings[$it_issue_priority_names[$srcRecord["I_PRIORITY"]]],
										$itStrings[$it_issue_priority_names[$destRecord["I_PRIORITY"]]]) );

		if ( $srcRecord["U_ID_ASSIGNED"] != $destRecord["U_ID_ASSIGNED"] ) {
			$prevAssignee = (strlen($srcRecord["U_ID_ASSIGNED"])) ? getUserName($srcRecord["U_ID_ASSIGNED"], true) :  $itStrings['is_na_text'];
			$newAssignee = (strlen($destRecord["U_ID_ASSIGNED"])) ? getUserName($destRecord["U_ID_ASSIGNED"], true) :  $itStrings['is_na_text'];

			$modifiedFields[] = sprintf( "%s: %s", $itStrings['is_asmtchanged_text'], sprintf( $itStrings['is_fromto_text'], $prevAssignee, $newAssignee ) );
		}

		if ( $srcRecord["I_ATTACHMENT"] != $destRecord["I_ATTACHMENT"] ) {
			$attachmentsData = listAttachedFiles( base64_decode($srcRecord["I_ATTACHMENT"]) );


			$attachedFiles = array();
			if ( count($attachmentsData) )
				for ( $i = 0; $i < count($attachmentsData); $i++ ) {
					$fileData = $attachmentsData[$i];

					$attachedFiles[] = $fileData["screenname"];
				}

			$destAttachmentsData = listAttachedFiles( base64_decode($destRecord["I_ATTACHMENT"]) );

			$destAttachedFiles = array();
			if ( count($destAttachmentsData) )
				for ( $i = 0; $i < count($destAttachmentsData); $i++ ) {
					$fileData = $destAttachmentsData[$i];

					$destAttachedFiles[] = $fileData["screenname"];
				}

			$deletedFiles = array_diff( $attachedFiles, $destAttachedFiles );

			$newFiles = array_diff( $destAttachedFiles, $attachedFiles );

			if ( count($newFiles) )
				$modifiedFields[] = sprintf( "%s: %s", $itStrings['is_newfiles_text'], implode( "; ", $newFiles) );

			if ( count($deletedFiles) )
				$modifiedFields[] = sprintf( "%s: %s", $itStrings['is_filesremoved_text'], implode( "; ", $deletedFiles) );
		}

		$itldata["ITL_OLDCONTENT"] = $itldata["ITL_OLDCONTENT"].implode( "\r\n", $modifiedFields );

		$res = exec_sql( $qr_it_insertissuemodificationlog, $itldata, $outputlist, false);
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $itStrings['is_erraddinglog_message'] );

		return null;
	}

	function it_getIssueAllowedTransitions( $I_ID, $P_ID = null, $PW_ID = null )
	//
	// Returns issue states, to which transition from the current state is possible.
	//		Considers settings of current transition.
	//
	//		Parameters:
	//			$I_ID - issue identifier
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//
	//		Returns array of status names
	//			or PEAR_Error
	//
	{
		global $qr_it_select_issue;
		global $qr_it_previtsstatus;
		global $qr_it_nextitsstatus;
		global $qr_it_select_transition_byitsnum;

		if ( !is_null($I_ID) ) {
			// Load issue data to obtain current status
			//
			$res = exec_sql( $qr_it_select_issue, array( "I_ID"=>$I_ID ), $issuedata, true);
			if ( PEAR::isError( $res ) )
				return $res;

			$P_ID = $issuedata["P_ID"];
			$PW_ID = $issuedata["PW_ID"];

			$currentStatus = $issuedata["I_STATUSCURRENT"];
		} else
			$currentStatus = null;

		$itsData = it_loadITSData( $P_ID, $PW_ID, $currentStatus );
		if ( PEAR::isError( $itsData ) )
			return $itsData;

		if ( is_null($currentStatus) )
			if ( is_array($itsData) ) {
				$result = array();
				foreach( $itsData as $statusData )
					$result[] = $statusData["ITS_STATUS"];

				return $result;
			}

		if ( is_null($itsData) )
			return array();

		$allowedDest = $itsData["ITS_ALLOW_DEST"];
		if ( !strlen($allowedDest) )
			return array();

		return explode( IT_TRANSITIONS_SEPARATOR, $allowedDest );
	}

	function it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings, $omitBoundary = false, $namesOnly = false )
	//
	// Returns list containing issue transitions for the certain work in the form of array
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$kernelStrings - Kernel localization strings
	//			$omitBoundary - omit boundary states (start, complete)
	//			$namesOnly - use integer values as keys and status names as values
	//
	//		Returns an array with keys equal to ITS_STATUS and elements equal to ISSUETRANSITIONSHEMA records
	//
	{
		global $qr_ir_select_work_transitions;

		$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID );
		$qr = db_query( $qr_ir_select_work_transitions, $params );
		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		while ( $row = db_fetch_array($qr) ) {
			if ( !$namesOnly )
				$result[$row["ITS_STATUS"]] = $row;
			else
				$result[] = $row['ITS_STATUS'];
		}

		db_free_result( $qr );

		if ( $omitBoundary ) {
			$keys = array_keys($result);
			unset( $result[$keys[0]] );
			unset( $result[$keys[count($keys)-1]] );
		}

		return $result;
	}

	function it_getStateNames( $P_ID, $PW_ID, $stateList, $kernelStrings )
	//
	// Returns names and color codes for states listed in $stateList array
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$stateList - list of state names
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array containing names and corresponding color codes or PEAR_Error
	//
	{
		global $qr_it_select_transition_bystatusname;

		$result = array();

		foreach( $stateList as $state ) {
			$stateData = db_query_result( $qr_it_select_transition_bystatusname, DB_ARRAY, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_STATUS"=>$state ) );
			if ( PEAR::isError( $stateData ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$result[$state] = $stateData["ITS_COLOR"];
		}

		return $result;
	}

	function it_getPossibleAllowedTransitionList( $P_ID, $PW_ID, $ITS_NUM, $itStrings )
	//
	// Return all transitions from schema, excluding Start and current transitions
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$ITS_NUM - current transition number
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns array of allowed transitions or PEAR_Error
	//
	{
		$transitionList = it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings );
		if ( PEAR::isError($transitionList) )
			return $transitionList;

		$result = array();

		$keys = array_keys($transitionList);
		if ( !count($keys) )
			return $result;

		$lastKey = $keys[count($keys)-1];
		$lastNum = $transitionList[$lastKey]["ITS_NUM"];

		foreach( $transitionList as $key=>$transitiondata )
			if ( $transitiondata['ITS_NUM'] != $ITS_NUM && $transitiondata['ITS_NUM'] != IT_FIRST_STATUS )
				//if ( !($ITS_NUM == IT_FIRST_STATUS && $transitiondata['ITS_NUM'] == $lastNum) )
					$result[$transitiondata['ITS_STATUS']] = $transitiondata;

		return $result;
	}

	function it_setIssueStatus( $itldata, $kernelStrings, $itStrings, $U_ID, $ITSData, $STATUS, $action = ACTION_EDIT )
	//
	// Transfers issue to new state. Adds record to IssueTransitionLog
	//
	//		Parameters:
	//			$itldata - an array containing data for ISSUETRANSITIONLOG
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$U_ID - user identifier
	//			$ITSData - information about state from ISSUETRANSITIONSCHEMA, the result of function it_loadITSData()
	//			$STATUS - destination status
	//			$action - action type
	//
	//		Returns ITL_ID, or PEAR_Error
	//
	{
		global $qr_it_maxitlid;
		global $qr_it_updateissuestatus;
		global $qr_it_insertitl;
		global $qr_it_select_issue;
		global $qr_it_updateCloseDate;
		global $qr_it_updateIssueComment;
		global $PMRightsManager;

		$itldata = trimArrayData( $itldata );

		// Load issue data
		//
		$res = exec_sql( $qr_it_select_issue, $itldata, $issuedata, true );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[IT_ERR_LOADISSUEDATA] );

		// Load destination status data
		//
		$nextITSData = it_loadITSData( $issuedata["P_ID"], $issuedata["PW_ID"], $itldata["ITL_STATUS"] );
		if ( PEAR::isError( $nextITSData ) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITS] );

		$assignmentOption = $nextITSData["ITS_ASSIGNMENTOPTION"];

		$res = it_workIsClosed( $issuedata["P_ID"], $issuedata["PW_ID"], $kernelStrings );
		if ( PEAR::isError($res) )
			return $res;

		if ( $res )
			return PEAR::raiseError( $itStrings['fwi_errsend_message'], ERRCODE_APPLICATION_ERR );

		// Check input data
		//
		$requiredFields = array( "I_ID", "ITL_STATUS", "U_ID_SENDER" );

		if ( $assignmentOption == IT_ASSIGNMENTOPT_NOTSELECTABLE || $assignmentOption == IT_ASSIGNMENTOPT_SELECTABLE )
			$requiredFields = array_merge( $requiredFields, array( "U_ID_ASSIGNED" ) );

		if ( PEAR::isError( $invalidField = findEmptyField($itldata, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$projmanData = it_getProjManData( $issuedata["P_ID"] );
		if ( PEAR::isError($projmanData) )
			return $projmanData;

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $issuedata["P_ID"], $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		if ( !isset( $itldata["U_ID_ASSIGNED"] ) || !strlen( $itldata["U_ID_ASSIGNED"] ) )
			$itldata["U_ID_ASSIGNED"] = null;

		if ( $action != ACTION_NEW && !$userIsProjman ) {
			// Load allowed transitions and check if new status is allowed
			//
			$allowedTransitions = it_getIssueAllowedTransitions( $itldata["I_ID"] );
			if ( PEAR::isError( $allowedTransitions ) )
				return PEAR::raiseError( $itStrings[IT_ERR_LOADISSUEALLOWEDTRANSITIONS] );

			$statusIDs = array_keys( $allowedTransitions );

			if ( !in_array( $itldata["ITL_STATUS"], $statusIDs ) )
				return PEAR::raiseError( $itStrings['fwi_errsendnostate_message'], ERRCODE_APPLICATION_ERR );
		}

		// Check field lengths
		//
		if ( $action != ACTION_NEW ) {
			$invalidField = checkStringLengths( $itldata, array("ITL_DESC"), array(2000) );
			if ( PEAR::isError($invalidField) ) {
				$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

				return $invalidField;
			}
		}

		// Check if user has correct rights
		//
		if ( $action == ACTION_EDIT )
			$res = it_getUserITRights( $issuedata["P_ID"], $issuedata["PW_ID"], $U_ID, $issuedata["I_ID"] );
		else
			$res = it_getUserITRights( $issuedata["P_ID"], $issuedata["PW_ID"], $U_ID );

		if ( PEAR::isError($res) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITRIGHTS] );

		if ( !$res )
			return PEAR::raiseError( $itStrings[IT_ERR_ISSUERIGHTS], ERRCODE_APPLICATION_ERR );

		// Add ITL record
		//
		$ITL_ID = db_query_result( $qr_it_maxitlid, DB_FIRST, $itldata );
		if ( PEAR::isError( $ITL_ID ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$ITL_ID = incID( $ITL_ID );

		$itldata["ITL_ID"] = $ITL_ID;
		$itldata["ITL_DATETIME"] = convertToSqlDateTime( time() );
		$itldata["ITL_ISRETURN"] = 0;
		$itldata["ITL_STATUS"] = $itldata["ITL_STATUS"];

		$res = exec_sql( $qr_it_insertitl, $itldata, $outputlist, false);
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Load current issue data
		//
		$prevIssueData = db_query_result( $qr_it_select_issue, DB_ARRAY, array("I_ID"=>$itldata["I_ID"]) );
		if ( PEAR::isError($prevIssueData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Update issue current status, assigned user and sender
		//
		$issuedata["I_STATUSCURRENT"] = $itldata["ITL_STATUS"];
		$issuedata["U_ID_ASSIGNED"] = $itldata["U_ID_ASSIGNED"];
		$issuedata["U_ID_SENDER"] = $itldata["U_ID_SENDER"];
		$issuedata["I_STATUSCURRENTDATE"] = $itldata["ITL_DATETIME"];

		$res = exec_sql( $qr_it_updateissuestatus, $issuedata, $outputlist, false);
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Set comment to issue
		//
		$res = db_query( $qr_it_updateIssueComment, $itldata );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Set I_CLOSEDATE value
		//
		$workflow = it_loadIssueTransitionSchema( $issuedata["P_ID"], $issuedata["PW_ID"], $itStrings );
		if ( PEAR::isError( $workflow ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$workflowLength = count($workflow);
		$lastState = $workflow[$workflowLength-1];

		if ( $lastState["ITS_STATUS"] == $itldata["ITL_STATUS"] ) {
			$date = convertToSqlDateTime( time() );
			$params = array( "I_CLOSEDATE"=>$date, "I_ID"=>$issuedata["I_ID"] );
		} else {
			$params = array( "I_CLOSEDATE"=>null, "I_ID"=>$issuedata["I_ID"] );
		}

		$res = db_query( $qr_it_updateCloseDate, $params );
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Send notifications
		//
		it_sendAssignmentNotification( $itldata["I_ID"], $kernelStrings, $U_ID, $itldata["ITL_DESC"] );

		return $ITL_ID;
	}

	function it_closeWorkIssues( $P_ID, $PW_ID, $PW_ENDDATE, $U_ID, $kernelStrings, $itStrings )
	//
	// Closes all issues linked to task
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$PW_ENDDATE - close date
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_selectopenissues;
		global $qr_it_select_issue;

		$workflow = it_loadIssueTransitionSchema( $P_ID, $PW_ID, $itStrings );
		if ( PEAR::isError( $workflow ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$workflowLength = count($workflow);
		$lastState = $workflow[$workflowLength-1];

		$senddata = array();
		$senddata["U_ID_SENDER"] = $U_ID;
		$senddata["ITL_STATUS"] = $lastState['ITS_STATUS'];

		$params = array( 'P_ID'=>$P_ID, 'PW_ID'=>$PW_ID );

		$qr = db_query( $qr_it_selectopenissues, $params );
		if ( PEAR::isError( $qr ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array( $qr ) ) {
			$senddata["I_ID"] = $row['I_ID'];
			$senddata["U_ID_ASSIGNED"] = null;
			$senddata["ITL_DESC"] = null;

			$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$row['I_ID']), $issuedata, true );
			if ( PEAR::isError( $res ) )
				return $res;

			$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $issuedata["I_STATUSCURRENT"] );
			if ( PEAR::isError($ITS_Data) || is_null($ITS_Data) )
				return $ITS_Data;

			$ITL_ID = it_setIssueStatus( $senddata, $kernelStrings, $itStrings, $U_ID, $ITS_Data, $lastState['ITS_STATUS'] );
			if ( PEAR::isError( $ITL_ID ) )
				return $ITL_ID;
		}

		db_free_result( $qr );

		return null;
	}

	function it_deleteIssue( $I_ID, $kernelStrings, $itStrings, $U_ID, $mode = 0 )
	//
	// Deletes issue and all corresponding records
	//
	//		Parameters:
	//			$I_ID - issue identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$U_ID - user identifier
	//			$mode - delete mode - 0 - delete with rights checking, 1 - delete everything
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_select_issue;
		global $qr_it_deleteissue;
		global $qr_it_deleteissuetranslog;
		global $IT_APP_ID;
		global $PMRightsManager;

		// Load issue data to obtain current status
		//
		$res = exec_sql( $qr_it_select_issue, array( "I_ID"=>$I_ID ), $issuedata, true);
		if ( PEAR::isError( $res ) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADISSUEDATA] );

		if ( !isset($issuedata['I_ID']) || !strlen($issuedata['I_ID']) )
			return null;

		// Check if user has correct rights
		//
		$projmanData = it_getProjManData( $issuedata["P_ID"] );
		if ( PEAR::isError($projmanData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( !$projmanData["U_ID_MANAGER"] || $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		$res = it_getUserITRights( $issuedata["P_ID"], $issuedata["PW_ID"], $U_ID, $I_ID );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $itStrings[IT_ERR_LOADITRIGHTS] );

		if ( !$res )
			return PEAR::raiseError( $itStrings[IT_ERR_ISSUERIGHTS], ERRCODE_APPLICATION_ERR );

		$currentStatus = $issuedata["I_STATUSCURRENT"];

		if ( $mode == 0 ) {
			$res = it_workIsClosed( $issuedata["P_ID"], $issuedata["PW_ID"], $kernelStrings );
			if ( PEAR::isError($res) )
				return $res;

			if ( $res && !$userIsProjman )
				return PEAR::raiseError( $itStrings['is_errordelcompltask_message'], ERRCODE_APPLICATION_ERR );

			$itsData = it_loadITSData( $issuedata["P_ID"], $issuedata["PW_ID"], $currentStatus );
			if ( PEAR::isError( $itsData ) || is_null($itsData) )
				return PEAR::raiseError( $itStrings[IT_ERR_LOADITS] );

			if ( !$itsData["ITS_ALLOW_DELETE"] && !$userIsProjman )
				return PEAR::raiseError( $itStrings['is_errordelworkflow_message'], ERRCODE_APPLICATION_ERR );
		}

		// Delete database records
		//
		if ( PEAR::isError( exec_sql( $qr_it_deleteissuetranslog, $issuedata, $output, false) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( PEAR::isError( exec_sql( $qr_it_deleteissue, $issuedata, $output, false) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete issue files
		//
		$attachmentsPath = fixDirPath( IT_ATTACHMENTS_DIR );
		$attachmentsPath = sprintf( "%s/%s/%s/%s", $attachmentsPath, $issuedata["P_ID"], $issuedata["PW_ID"], $I_ID );

		if ( file_exists($attachmentsPath) ) {
			$fileCount = 0;
			$totalSize = 0;

			dirInfo( $attachmentsPath, $fileCount, $totalSize );

			if ( $totalSize > 0 ) {
				$QuotaManager = new DiskQuotaManager();
				$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $IT_APP_ID, -1*$totalSize );
				$QuotaManager->Flush( $kernelStrings );
			}
		}

		@removeDir( $attachmentsPath );

		return null;
	}

	function it_getUserITRights( $P_ID, $PW_ID, $U_ID, $I_ID = null )
	//
	// Checks user rights to work with issues
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$U_ID - user identifier
	//			$I_ID - issue identifier
	//
	//		Returns value of boolean type, or PEAR_Error
	//
	{
		global $qr_it_active_issue_assignment;
		global $qr_it_workexists;
		global $qr_it_user_issue_assignment;
		global $PMRightsManager;
		
		if (PM_DISABLED)
			return true;

		$projmanData = it_getProjManData( $P_ID );
		if ( PEAR::isError($projmanData) )
			return $projmanData;

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		if ($userIsProjman)
			return it_workExists( $P_ID, $PW_ID );

		$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "U_ID"=>$U_ID, "I_ID"=>$I_ID );

		$workAssigned = db_query_result( $qr_it_active_issue_assignment, DB_FIRST, $params );
		if ( PEAR::isError($workAssigned) )
			return $workAssigned;

		if ( is_null($I_ID) || !strlen($I_ID) )
			return $workAssigned;

		return db_query_result( $qr_it_user_issue_assignment, DB_FIRST, $params );
	}

	function it_getProjManData( $P_ID )
	//
	// Returns information about project manages - user identifier and fields containing name, lastname
	//
	//		Parameters:
	//			$P_ID - project identifier
	//
	//		Returns an associative array, or PEAR_Error
	//
	{
		if ($P_ID == 0)
			return array ();
		
		global $qr_it_select_project_manager_name;

		$params = array( "P_ID"=>$P_ID );

		if ( PEAR::isError( $res = exec_sql( $qr_it_select_project_manager_name, $params, $output, true) ) )
			return $res;

		return $output;
	}

	function it_getProjectName( $P_ID, $projTruncateLen = null, $appendCustName = false, $custTruncateLen = null )
	//
	// Returns project name
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$projTruncateLen - maximum length of project name, all symbols after are trimmed
	//			$appendCustName - if it is true, customer name is attached to the beginnig of project name
	//			$custTruncateLen - maximum length of customer name, all symbols after are trimmed
	//
	//		Returns string with project name, or PEAR_Error
	//
	{
		global $qr_it_selectprojectname;
		
		global $itStrings; // dirty hack, must be fixed, but there is many places with this function calling
		if ($P_ID == 0)
			return $itStrings["app_freeissues_label"];
		
		$data = db_query_result( $qr_it_selectprojectname, DB_ARRAY, array( "P_ID"=>$P_ID ) );
		if ( PEAR::isError($data) )
			return $data;

		if ( !$appendCustName )
			return strTruncate( $data["P_DESC"], $projTruncateLen );
		else
			return sprintf( "%s. %s", strTruncate( $data["C_NAME"], $custTruncateLen ), strTruncate( $data["P_DESC"], $projTruncateLen ) );
	}

	function it_getWorkDescription( $P_ID, $PW_ID )
	//
	// Returns project's work description
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//
	//		Returns string with work description, or PEAR_Error
	//
	{
		global $qr_it_selectprojectworksdesc;
		if ($P_ID === '0' & $PW_ID === '0')
			return "";

		return db_query_result( $qr_it_selectprojectworksdesc, DB_FIRST, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
	}
	
	function it_getWorkAssignments ($P_ID, $PW_ID) {
		global $qr_it_selectworkassignments;
		
		if (PM_DISABLED) {
			return it_getFreeWorkAssignments();
		}
		
		$params = array("P_ID" => $P_ID, "PW_ID" => $PW_ID);
		if ( PEAR::isError( $aQr = db_query( $qr_it_selectworkassignments, $params ) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$assignments = array();

		while ( $row = db_fetch_array($aQr) )
			$assignments[] = $row['U_ID'];

		db_free_result( $aQr );
		
		return $assignments;
	}

	function it_workExists( $P_ID, $PW_ID )
	//
	// Checks if the work exists
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//
	//		Returns value of boolean type, or PEAR_Error
	//
	{
		global $qr_it_workexists;
		if (PM_DISABLED)
			return ($P_ID == 0 && $PW_ID == 0);

		return db_query_result( $qr_it_workexists, DB_FIRST, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
	}

	//
	// Issue transition schemas
	//
	function it_issueWithStatusExists( $P_ID, $PW_ID, $ITS_NUM )
	//
	// Checks if issues with ceratin status exist with the work context
	//
	//		Parameters:
	//			$P_ID - work identifier
	//			$PW_ID - issue identifier
	//			$ITS_NUM - number of state
	//
	//		Returns number of issues, or PEAR_Error
	//
	{
		global $qr_it_issuestatuscount;

		return db_query_result( $qr_it_issuestatuscount, DB_FIRST, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>$ITS_NUM ) );

	}

	function it_deleteIssueTransition( $transdata, $kernelStrings, $itStrings )
	//
	// Deletes state from transitions scheme
	//
	//		Parameters:
	//			$transdata - an associative array containing fields of ISSUETRANSITIONSCHEMA record
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_deletetransition;
		global $qr_it_selectissueworktransitionnums;
		global $qr_it_updateissueworktransitionnum;
		global $qr_it_selectwork;
		global $qr_it_updatetransitionalloweddest;
		global $qr_it_select_transition_byitsnum;

		$workdata = db_query_result( $qr_it_selectwork, DB_ARRAY, $transdata );
		if ( PEAR::isError($workdata) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$res = it_issueWithStatusExists( $transdata["P_ID"], $transdata["PW_ID"], $transdata["ITS_NUM"] );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $res )
			return PEAR::raiseError( $itStrings['dws_delstatissues_message'], ERRCODE_APPLICATION_ERR );

		$transdata = db_query_result( $qr_it_select_transition_byitsnum, DB_ARRAY, $transdata );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Delete references to transition
		//
		$transitionSchema = it_loadIssueTransitionSchema( $transdata["P_ID"], $transdata["PW_ID"], $itStrings );
		if ( PEAR::isError($transitionSchema) )
			return $transitionSchema;

		foreach( $transitionSchema as $key=>$transitionData ) {
			$destTransitions = $transitionData['ITS_ALLOW_DEST'];

			if ( !strlen($destTransitions) )
				continue;

			$destTransitions = explode( IT_TRANSITIONS_SEPARATOR, $destTransitions );
			if ( !count($destTransitions) )
				continue;

			if ( !in_array( $transdata["ITS_STATUS"], $destTransitions ) )
				continue;

			$transitionList = array();
			foreach( $destTransitions as $destTransitionName )
				if ( $destTransitionName != $transdata["ITS_STATUS"] )
					$transitionList[] = $destTransitionName;

			$transitionList = implode( IT_TRANSITIONS_SEPARATOR, $transitionList );
			$params = $transdata;
			$params["ITS_NUM"] = $transitionData["ITS_NUM"];
			$params["ITS_ALLOW_DEST"] = $transitionList;

			if ( PEAR::isError( db_query($qr_it_updatetransitionalloweddest, $params) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		// Delete transition
		//
		if ( PEAR::isError( exec_sql($qr_it_deletetransition, $transdata, $outputList, false) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$orderData = null;

		// Re-sort transitions
		//
		$qr = db_query( $qr_it_selectissueworktransitionnums, $transdata );
		$key = null;

		while ( $row = db_fetch_array( $qr ) )
			$orderData[$row["ITS_NUM"]] = $row["ITS_NUM"];

		db_free_result( $qr );

		if ( count($orderData) ) {
			$orderData = collapseArray( $orderData );

			foreach( $orderData as $old_num => $new_num ) {
				$params = array( "NEW_NUM"=>$new_num, "ITS_NUM"=>$old_num, "P_ID"=>$transdata["P_ID"],
									"PW_ID"=>$transdata["PW_ID"] );
				if ( PEAR::isError( db_query($qr_it_updateissueworktransitionnum, $params) ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}
		}

		return null;
	}

	function it_transferIssues( $P_ID, $PW_ID, $srcITS_NUM, $destITS_Num, $kernelStrings )
	//
	// Transfers issues from one to another status
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$srcITS_NUM - source status identifier
	//			$destITS_Num - destination status identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_select_transition_byitsnum;
		global $qr_it_selectIssuesByStatus;
		global $qr_it_updateStatus;

		$params = array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID, "ITS_NUM"=>$srcITS_NUM );

		$srcTransdata = db_query_result( $qr_it_select_transition_byitsnum, DB_ARRAY, $params );
		if ( PEAR::isError($srcTransdata) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$params["ITS_NUM"] = $destITS_Num;
		$destTransdata = db_query_result( $qr_it_select_transition_byitsnum, DB_ARRAY, $params );
		if ( PEAR::isError($srcTransdata) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$params = array( "I_STATUSCURRENT"=>$srcTransdata["ITS_STATUS"], "P_ID"=>$P_ID, "PW_ID"=>$PW_ID );
		$qr = db_query( $qr_it_selectIssuesByStatus, $params );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array( $qr ) ) {
			$issueParams = array( "I_STATUSCURRENT"=>$destTransdata["ITS_STATUS"], "I_ID"=>$row["I_ID"] );

			$res = db_query( $qr_it_updateStatus, $issueParams );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		db_free_result($qr);

		return null;
	}

	function it_getNextTransitionNum( $transdata )
	//
	// Returns next value of transition number within issue transitions scheme
	//
	//		Parameters:
	//			$transdata - an associative array containing fields of ISSUETRANSITIONSCHEMA record
	//
	//		Return next value $ITS_NUM, or PEAR_Error
	//
	{
		global $qr_it_maxtransitionnum;

		if ( PEAR::isError( $res = exec_sql($qr_it_maxtransitionnum,  $transdata, $outputList, true) ) )
			return $res;

		$ITS_NUM = $outputList["NEXT_NUM"];
		$ITS_NUM = incID($ITS_NUM);

		return $ITS_NUM;
	}

	function it_updateStatusName( $prevStatusName, $transdata, $kernelStrings, $itStrings )
	//
	// Updates issue transitions status name in references
	//
	//		Parameters:
	//			$prevStatusName - status name before update
	//			$transdata - an associative array containing fields of ISSUETRANSITIONSCHEMA record
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_updatetransitionalloweddest;
		global $qr_it_updateIssueStatusName;
		global $qr_it_updatetransitiondefaultddest;

		$transitionSchema = it_loadIssueTransitionSchema( $transdata["P_ID"], $transdata["PW_ID"], $itStrings );
		if ( PEAR::isError($transitionSchema) )
			return $transitionSchema;

		foreach( $transitionSchema as $key=>$transitionData ) {
			$destTransitions = $transitionData['ITS_ALLOW_DEST'];

			if ( !strlen($destTransitions) )
				continue;

			$destTransitions = explode( IT_TRANSITIONS_SEPARATOR, $destTransitions );
			if ( !count($destTransitions) )
				continue;

			if ( !in_array( $prevStatusName, $destTransitions ) )
				continue;

			$transitionList = array();
			foreach( $destTransitions as $destTransitionName )
				if ( $destTransitionName == $prevStatusName )
					$transitionList[] = $transdata["ITS_STATUS"];
				else
					$transitionList[] = $destTransitionName;

			$transitionList = implode( IT_TRANSITIONS_SEPARATOR, $transitionList );

			$params = $transdata;
			$params["ITS_NUM"] = $transitionData["ITS_NUM"];
			$params["ITS_ALLOW_DEST"] = $transitionList;

			if ( PEAR::isError( db_query($qr_it_updatetransitionalloweddest, $params) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}

		$params = array();
		$params["P_ID"] = $transdata["P_ID"];
		$params["PW_ID"] = $transdata["PW_ID"];
		$params["I_STATUSCURRENT"] = $transdata["ITS_STATUS"];
		$params["PREVSTATUSNAME"] = $prevStatusName;

		if ( PEAR::isError( db_query($qr_it_updateIssueStatusName, $params) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$params = array();
		$params["P_ID"] = $transdata["P_ID"];
		$params["PW_ID"] = $transdata["PW_ID"];
		$params["PREV_NAME"] = $prevStatusName;
		$params["ITS_DEFAULT_DEST"] = $transdata["ITS_STATUS"];
		if ( PEAR::isError( db_query($qr_it_updatetransitiondefaultddest, $params) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function it_addmodIssueTransition( $action, $transdata, $kernelStrings, $itStrings )
	//
	// Examines incoming data and inserts (or modifies) state into transitions scheme
	//
	//		Parameters:
	//			$action - action type - addition ($action == ACTION_NEW) or modification (ACTION_EDIT)
	//			$transdata - an associative array containing fields of ISSUETRANSITIONSCHEMA record
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_inserttransition_full;
		global $qr_it_shifttransitions;
		global $qr_it_updatetransition;
		global $qr_it_selectissueworktransitionnums;
		global $qr_it_updateissueworktransitionnum;
		global $qr_it_selectissueworktransition;
		global $qr_it_selectwork;
		global $qr_it_selectTransitionShemaCountByStatus;
		global $qr_it_updatetranscolor;

		// Check if work exists
		//
		$res = it_workExists( $transdata["P_ID"], $transdata["PW_ID"] );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !$res )
			return PEAR::raiseError( $itStrings[IT_ERR_WORKNOTFOUND], ERRCODE_APPLICATION_ERR );

		// Check if work opened
		//
		if (PM_DISABLED) {
			$endState = false;
		} else {
			$workdata = db_query_result( $qr_it_selectwork, DB_ARRAY, $transdata );
			if ( PEAR::isError($workdata) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$endState = it_isEndState( $transdata["P_ID"], $transdata["PW_ID"], $transdata["ITS_NUM"], $itStrings );
			if ( PEAR::isError($endState) )
				return $endState;
		}

		if ( $action == ACTION_EDIT ) {
			if ( PEAR::isError( exec_sql($qr_it_selectissueworktransition, $transdata, $oldTransdata, true) ) )
				return PEAR::raiseError( $itStrings['dws_errloadtrans_message'], ERRCODE_APPLICATION_ERR );

			if ( $oldTransdata["ITS_STATUS"] != $transdata["ITS_STATUS"] )
				if ( $endState || $transdata["ITS_NUM"] == IT_FIRST_STATUS )
					return PEAR::raiseError( $itStrings['dws_startendname_message'], ERRCODE_APPLICATION_ERR );
		}

		$requiredFields = array( "ITS_STATUS" );

		if ( $transdata["ITS_NUM"] != IT_FIRST_STATUS && !$endState ) {
			$assignmentOption = $transdata["ITS_ASSIGNMENTOPTION"];
			if ( $assignmentOption == IT_ASSIGNMENTOPT_NOTSELECTABLE )
				$requiredFields = array_merge( $requiredFields, array( "U_ID_ASSIGNED" ) );

			if ( PEAR::isError( $invalidField = findEmptyField($transdata, $requiredFields) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

				return $invalidField;
			}
		}

		$transdata = rescueElement( $transdata, "ITS_ALLOW_EDIT", 0 );
		$transdata = rescueElement( $transdata, "ITS_ALLOW_DELETE", 0 );

		if ( $action == ACTION_NEW ) {
			$res = db_query_result( $qr_it_selectTransitionShemaCountByStatus, DB_FIRST, $transdata );
			if ( PEAR::isError( $res ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( $res )
				return PEAR::raiseError( $itStrings['dws_statenameexists_message'], ERRCODE_APPLICATION_ERR );

			if ( PEAR::isError( $ITS_NUM = it_getNextTransitionNum($transdata) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$transdata["ITS_NUM"] = $ITS_NUM;

			$res = exec_sql( $qr_it_inserttransition_full, $transdata, $outputList, false );
			if ( PEAR::isError( $res ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		} else {
			$ITS_NUM = $transdata["ITS_NUM"];

			if ( !$endState )
				$res = exec_sql( $qr_it_updatetransition, $transdata, $outputList, false );
			else
				$res = exec_sql( $qr_it_updatetranscolor, $transdata, $outputList, false );

			if ( PEAR::isError( $res ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$res = it_updateStatusName( $oldTransdata["ITS_STATUS"], $transdata, $kernelStrings, $itStrings );
			if ( PEAR::isError( $res ) )
				return $res;
		}

		if ( $transdata["ITS_NUM"] != IT_FIRST_STATUS && !$endState ) {
			// Re-sort transitions
			//
			if ( $transdata["PREV_PREVNUM"] != $transdata["ITS_PREVNUM"] ) {
				$orderData = array();

				// Load ordering array
				//
				if ( PEAR::isError( $qr = db_query($qr_it_selectissueworktransitionnums, $transdata) ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$key = null;

				while ( $row = db_fetch_array( $qr ) ) {
					$orderData[$row["ITS_NUM"]] = $row["ITS_NUM"];

					if ( $row["ITS_NUM"] == $ITS_NUM )
						$key = $ITS_NUM;
				}

				db_free_result( $qr );

				if ( $transdata["ITS_PREVNUM"] == IT_END_STATUS ) {
					if ( PEAR::isError( $ITS_NUM = it_getNextTransitionNum($transdata) ) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

					$transdata["ITS_NUM"] = $ITS_NUM;
					$transdata["ITS_PREVNUM"] = $ITS_NUM;
				} else
					if ( $action == ACTION_EDIT )
						if ( PEAR::isError( $ITS_NUM = it_getNextTransitionNum($transdata) ) )
							return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$orderData = resortArray( $orderData, $key, $transdata["ITS_PREVNUM"] ) ;

				// Shift transitions
				//
				$shiftFactor = $ITS_NUM + 10;
				$sql = sprintf($qr_it_shifttransitions, $shiftFactor);
				if ( PEAR::isError( db_query($sql, $transdata) ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				foreach( $orderData as $old_num => $new_num ) {
					$params = array( "NEW_NUM"=>$new_num, "ITS_NUM"=>$old_num + $shiftFactor,
										"P_ID"=>$transdata["P_ID"], "PW_ID"=>$transdata["PW_ID"] );

					if ( PEAR::isError( db_query($qr_it_updateissueworktransitionnum, $params) ) )
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}
		}

		return null;
	}

	function it_templateExists( $ITT_ID )
	//
	// Checks if the template exists
	//
	//		Parameters:
	//			$ITT_ID - template identifier
	//
	//		Returns value of boolean type
	//
	{
		global $qr_it_templateexists;

		return db_query_result( $qr_it_templateexists, DB_FIRST, array ("ITT_ID"=>$ITT_ID) );
	}

	function it_checkIssueTemplateStates( $P_ID, $PW_ID, $ITT_ID, &$templateStateNames )
	//
	// Checks if issue transitions scheme can be filled with states from transitions scheme template.
	//		Checks if issues in states, absent in template, does not exist
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$ITT_ID - template identifier
	//			$templateStateNames - a list of template states
	//
	//		Returns value of boolean type, or PEAR_Error
	//
	{
		global $qr_it_selecttemplateschematransitions_full;
		global $qr_it_workissuestates;

		$qr = db_query( $qr_it_selecttemplateschematransitions_full, array( "ITT_ID"=>$ITT_ID ) );
		if ( PEAR::isError($qr) )
			return $qr;

		$templateStates = array();
		while ( $row = db_fetch_array( $qr ) )
			$templateStates[] = $row["ITTS_STATUS"];

		db_free_result( $qr );

		$qr = db_query( $qr_it_workissuestates, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
		if ( PEAR::isError($qr) )
			return $qr;

		$workStates = array();
		while ( $row = db_fetch_array( $qr ) )
			$workStates[] = $row["I_STATUSCURRENT"];

		db_free_result( $qr );

		$templateStateNames = array();

		$templateStateCount = count($templateStates);
		if ( $templateStateCount > 1 )
			for ( $i = 1; $i < $templateStateCount-1; $i++ )
				$templateStateNames[] = $templateStates[$i];

		for ( $i = 0; $i < count($workStates); $i++ )
			if ( !in_array( $workStates[$i], $templateStates ) ) {
				$templateStateNames = $workStates;

				return false;
			}

		return true;
	}

	function it_fillSchemaFromTemplate( $P_ID, $PW_ID, $ITT_ID, $kernelStrings, $itStrings )
	//
	// Fills transitions scheme with transitions from the template
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$ITT_ID - template identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_deleteschematransitions;
		global $qr_it_selecttemplateschematransitions_full;
		global $qr_it_inserttransition_full;
		global $qr_it_select_template;
		global $qr_it_selectworkassignments;

		// Check if work exists
		//
		$res = it_workExists( $P_ID, $PW_ID );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !$res )
			return PEAR::raiseError( $itStrings[IT_ERR_WORKNOTFOUND], ERRCODE_APPLICATION_ERR );

		// Check if template exists
		//
		$res = it_templateExists( $ITT_ID );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !$res )
			return PEAR::raiseError( $itStrings[IT_ERR_TEMPLATENOTFOUND], ERRCODE_APPLICATION_ERR );

		// Check if no schema statuses are in use
		//
		$res = it_checkIssueTemplateStates( $P_ID, $PW_ID, $ITT_ID, $templateStates = array() );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( !$res ) {
			$templateData = db_query_result( $qr_it_select_template, DB_ARRAY, array( "ITT_ID"=>$ITT_ID ) );
			if ( PEAR::isError($res) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			return PEAR::raiseError( sprintf($itStrings[IT_ERR_SCHEMAINUSE], $templateData["ITT_NAME"], implode(", ", $templateStates) ), ERRCODE_APPLICATION_ERR );
		}

		// Delete all schema transitions
		//
		$params = array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID);
		if ( PEAR::isError( $res = db_query($qr_it_deleteschematransitions, $params) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		// Load work assignments list
		//
		if ( PEAR::isError( $aQr = db_query( $qr_it_selectworkassignments, $params ) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$assignments = array();

		while ( $row = db_fetch_array($aQr) )
			$assignments[] = $row['U_ID'];

		db_free_result( $aQr );

		// Insert template transitions
		//
		$qr = db_query( $qr_it_selecttemplateschematransitions_full, array( "ITT_ID"=>$ITT_ID ) );
		if ( PEAR::isError($qr) )
			return $qr;

		while ( $row = db_fetch_array($qr) ) {
			if ( $row["ITTS_ASSIGNED"] != IT_SENDER_OPTION )
				$assignee = (in_array( $row["ITTS_ASSIGNED"], $assignments )) ? $row["ITTS_ASSIGNED"] : null;
			else
				$assignee = $row["ITTS_ASSIGNED"];

			$params = array( "P_ID"=>$P_ID,
							"PW_ID"=>$PW_ID,
							"ITS_NUM"=>$row["ITS_NUM"],
							"ITS_STATUS"=>$row["ITTS_STATUS"],
							"ITS_ALLOW_EDIT"=>$row["ITTS_ALLOW_EDIT"],
							"ITS_ALLOW_DELETE"=>$row["ITTS_ALLOW_DELETE"],
							"ITS_ASSIGNMENTOPTION"=>$row["ITTS_ASSIGNMENTOPTION"],
							"U_ID_ASSIGNED"=>$assignee,
							"ITS_ALLOW_DEST"=>$row["ITTS_ALLOW_DEST"],
							"ITS_DEFAULT_DEST"=>$row["ITTS_DEFAULT_DEST"],
							"ITS_COLOR"=>$row["ITTS_COLOR"] );

			if ( PEAR::isError( db_query( $qr_it_inserttransition_full, $params) ) ) {
				db_free_result( $qr );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}

		}

		db_free_result( $qr );

		return null;
	}

	function it_getITLData( $I_ID, $ITL_ID )
	//
	// Returns data about transition from the log of issue transitions
	//
	//		Parameters:
	//			$I_ID - issue identifier
	//			$ITL_ID - transition identifier
	//
	//		Returns record ISSUETRANSITIONLOG, or PEAR_Error
	//
	{
		global $qr_it_issuetranslogtransition;

		$params = array( "I_ID"=>$I_ID, "ITL_ID"=>$ITL_ID );
		return db_query_result( $qr_it_issuetranslogtransition, DB_ARRAY, $params );
	}

	function it_getAttachedFilesList( $P_ID, $U_ID, $kernelStrings, $itStrings )
	//
	// Returns list of files attached to the issues
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns an associative array,
	//			array( array( FILE_INFO, PW_ID, I_ID, I_NUM, I_DESC, TYPE ) ),
	//			where FILE_INFO is information about file in the same form as in function addAttachedFile(),
	//			TYPE - file type: IT_FT_ISSUE, IT_FT_TRANSITION
	//			In case of error occurence, returns PEAR_Error
	//
	{
		global $qr_it_all_project_issue_attachments;
		global $qr_it_active_issue_attachments;
		global $qr_it_all_project_trans_attachments;
		global $qr_it_active_trans_attachments;
		global $PMRightsManager;

		$fileList = array();

		$projmanData = it_getProjManData( $P_ID );
		if ( PEAR::isError($projmanData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		// Loading issue attachments
		//
		if ( $userIsProjman )
			$qr = db_query( $qr_it_all_project_issue_attachments, array("P_ID"=>$P_ID) );
		else
			$qr = db_query( $qr_it_active_issue_attachments, array("P_ID"=>$P_ID, "U_ID"=>$U_ID) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array( $qr ) ) {
			$attachment = $row["I_ATTACHMENT"];

			$issueFileList = listAttachedFiles( @base64_decode($attachment) );
			for ( $i = 0; $i < count($issueFileList); $i++ )
				$fileList[] = array( "FILE_INFO"=>$issueFileList[$i], "PW_ID"=>$row["PW_ID"],
										"I_ID"=>$row["I_ID"], "I_NUM"=>$row["I_NUM"], "I_DESC"=>$row["I_DESC"], "TYPE"=>IT_FT_ISSUE );
		}

		@db_free_result( $qr );

		// Loading transition attachments
		//
		if ( $userIsProjman )
			$qr = db_query( $qr_it_all_project_trans_attachments, array("P_ID"=>$P_ID) );
		else
			$qr = db_query( $qr_it_active_trans_attachments, array("P_ID"=>$P_ID, "U_ID"=>$U_ID) );

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array( $qr ) ) {
			$attachment = $row["ITL_ATTACHMENT"];

			$issueFileList = listAttachedFiles( @base64_decode($attachment) );
			for ( $i = 0; $i < count($issueFileList); $i++ )
				$fileList[] = array( "FILE_INFO"=>$issueFileList[$i], "PW_ID"=>$row["PW_ID"],
										"I_ID"=>$row["I_ID"], "I_NUM"=>$row["I_NUM"], "I_DESC"=>$row["I_DESC"],
										"ITL_ID"=>$row["ITL_ID"], "TYPE"=>IT_FT_TRANSITION  );
		}

		@db_free_result( $qr );

		return $fileList;
	}
	
	function it_issueListCount ($projectWorkIds, $filterData) {
		
		if (!$projectWorkIds)
			return 0;
			
		$worksSqlParts = array();
		foreach ($projectWorkIds as $cRow)
			$worksSqlParts[] = "(I.P_ID=" . $cRow["P_ID"] . " AND I.PW_ID=" . $cRow["PW_ID"] . ")";
		$worksSql = join(" OR ", $worksSqlParts);
		
		$startSql = "SELECT COUNT(I.I_ID) AS cnt FROM ISSUE I, ISSUETRANSITIONSCHEMA ITS WHERE (%s) AND ITS_STATUS=I.I_STATUSCURRENT AND ITS.P_ID=I.P_ID AND ITS.PW_ID=I.PW_ID %s";
		$filterSql = it_prepareIssueFilterSql($filterData);
		
		$sql = sprintf( $startSql, $worksSql, $filterSql );
		$cnt = db_query_result($sql, DB_FIRST);
		
		return $cnt;
	}

	function it_prepareIssueListQuery( $P_ID, $PW_ID, $filterData, $sql = null )
	//
	// Prepares query, obtaining issue list
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - issue identifier
	//			$filterData - information about issue list filter
	//
	//	Returns query identifier (DB_Result), or PEAR_Error
	//
	{
		global $qr_it_select_issues;
		global $qr_it_search_issues;

		$filterSql = it_prepareIssueFilterSQL( $filterData );
		
		$sql = sprintf( $qr_it_search_issues, $filterSql );

		return execPreparedQuery( $sql, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
	}

	function it_workIssueCount( $P_ID, $PW_ID, $filterData, $selectedIssues = null )
	//
	// Returns number of issues within the work, with the regard for search criteria
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - issue identifier
	//			$filterData - information about issue list filter
	//			$selectedIssues - list of selected issues
	//
	//		Returns number of issues
	//
	{
		global $qr_it_search_issuescount;
		global $qr_it_search_issuescount_selected;

		$filterSql = it_prepareIssueFilterSQL( $filterData );

		if ( is_null($selectedIssues) )
			$sql = sprintf( $qr_it_search_issuescount, $filterSql );
		else {
			$idFilter = sprintf( "'%s'", implode( "','", $selectedIssues ) );
			$sql = sprintf( $qr_it_search_issuescount_selected, $idFilter, $filterSql );
		}

		return db_query_result( $sql, DB_FIRST, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
	}

	function it_sendAssignmentNotification( $I_ID, $kernelStrings, $U_ID, $comment = null )
	//
	// Sends notification about assignment to issue
	//
	//		Parameters:
	//			$I_ID - issue identifier
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - identifier of user, who modifies issue
	//			$comment - comments for issue transition
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_select_issuestatusinfo;
		global $it_loc_str;
		global $loc_str;
		global $IT_APP_ID;
		global $it_issue_priority_names;

		// Load issue data
		//
		$issueData = db_query_result( $qr_it_select_issuestatusinfo, DB_ARRAY, array("I_ID"=>$I_ID) );
		if ( PEAR::isError($issueData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$issueColor = it_getIssueHTMLStyle( $issueData["ITS_COLOR"] );
		$recipientU_ID = $issueData["U_ID_ASSIGNED"];

		if ( !strlen($recipientU_ID) )
			return null;

		// Load recipient settings
		//
		$language = readUserCommonSetting( $recipientU_ID, LANGUAGE );
		$language = $language ? $language : DEF_LANG_ID;
		$mailFormat = "html";
		$userITStrings = $it_loc_str[$language];
		$userKernelStrings = $loc_str[$language];

		// Load project and task descriptions
		//
		$projName = it_getProjectName( $issueData["P_ID"], null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
		$workDesc = it_getWorkDescription( $issueData["P_ID"], $issueData["PW_ID"] );

		// Prepare common issue parameters
		//
		$messageHeader = sprintf( $userITStrings['ami_mailasgngreetings_text'], getUserName( $recipientU_ID, true ) );

		$filePath = sprintf( "%spublished/IT/includes/mail.txt", WBS_DIR );
		$bodyTemplate = file_get_contents($filePath);
		
		
		$sqlDate = sqlTimestamp( $issueData["I_STATUSCURRENTDATE"] );

		$issueText = $issueData["I_DESC"];

		$priorityColors = array( IT_ISSUE_PRIORIY_LOW => "#0000FF", IT_ISSUE_PRIORIY_NORMAL => "#000000", IT_ISSUE_PRIORIY_HIGH => "#FF0000" );
		$priorityName = $userITStrings[$it_issue_priority_names[$issueData["I_PRIORITY"]]];
		$priorityColor = $priorityColors[$issueData["I_PRIORITY"]];

		if ( !is_null($comment) && strlen($comment) )
			$comment = sprintf( $userITStrings['ami_mailasgncomment_label'], $comment );

		if ( $mailFormat == MAILFORMAT_HTML )
			$issueText = prepareStrToDisplay( $issueText, true );
		
		// Load attachments list
		//
		$attachmentsData = listAttachedFiles( base64_decode($issueData["I_ATTACHMENT"]) );

		$attachedFiles = array();
		if ( count($attachmentsData) ) {
			for ( $i = 0; $i < count($attachmentsData); $i++ ) {
				$fileData = $attachmentsData[$i];
				$fileName = $fileData["name"];
				$fileSize = formatFileSizeStr( $fileData["size"] );

				$attachedFiles[] = sprintf( "%s (%s)", $fileData["screenname"], $fileSize );
			}
		}

		if ( !count($attachedFiles) )
			$attachedFiles = sprintf( "<br><br>%s: %s", $userITStrings['ami_attachments_label'], $userITStrings['ami_na_label'] );
		else
			$attachedFiles = sprintf( "<br><br>%s: %s", $userITStrings['ami_attachments_label'], implode( ", ", $attachedFiles ) );
		
		$projNameText = (!PM_DISABLED) ?
			sprintf("<b>%s</b>: %s<br>", $userITStrings['ami_mailproject_label'], $projName) : "";
		if ($issueData["PW_ID"]) {
			$taskText = sprintf("<b>%s</b>: %s - %s<br><br>", $userITStrings['ami_task_label'], $issueData["PW_ID"], $workDesc);
			$issueFullNum = sprintf( "%s.%s", $issueData["PW_ID"], $issueData["I_NUM"] );
		} else {
			$taskText = "";
			$issueFullNum = $issueData["I_NUM"];			
		}
		
		// Make body text
		//
		$messageBody = sprintf( $bodyTemplate,
							$userITStrings['ami_mailheader_text'],
							$projNameText,
							$taskText,
							$issueText,
							$userITStrings['ami_issue_label'], $issueFullNum,
							$userITStrings['ami_priority_label'], $priorityColor, $priorityName,
							$userITStrings['ami_mailstatus_label'], $issueColor, $issueData["I_STATUSCURRENT"],
							$attachedFiles,
							$comment ? $comment : ""
							);
					
		$subject = $it_loc_str[$language]['ami_mailasgnsubject_text'];
		
		@sendWBSMail( $recipientU_ID, null, $U_ID, $subject, $issueData["I_PRIORITY"], $messageBody, $kernelStrings, "onIssueAssignment", $IT_APP_ID, $messageHeader, true, null, $userITStrings['ami_mailsentby_text'] );
	}

	function it_sendIssueModifyNotification( $I_ID, $prevData, $kernelStrings, $U_ID )
	//
	// Sends notification about issue modification
	//
	//		Parameters:
	//			$I_ID - issue identifier
	//			$prevData - issue data before modification
	//			$kernelStrings - Kernel localization strings
	//			$U_ID - identifier of user, who modifies issue
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_select_issuestatusinfo;
		global $it_loc_str;
		global $loc_str;
		global $IT_APP_ID;
		global $it_issue_priority_names;

		// Load issue data
		//
		$issueData = db_query_result( $qr_it_select_issuestatusinfo, DB_ARRAY, array("I_ID"=>$I_ID) );
		if ( PEAR::isError($issueData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$issueColor = it_getIssueHTMLStyle( $issueData["ITS_COLOR"] );
		$recipientU_ID = $issueData["U_ID_ASSIGNED"];

		if ( !strlen($recipientU_ID) )
			return null;

		// Load recipient settings
		//
		$language = readUserCommonSetting( $recipientU_ID, LANGUAGE );
		$mailFormat = readUserCommonSetting( $recipientU_ID, MAILFORMAT );
		$userITStrings = $it_loc_str[$language];
		$userKernelStrings = $loc_str[$language];

		// Load project and task descriptions
		//
		$projName = it_getProjectName( $issueData["P_ID"], null, true, IT_DEFAILT_MAX_CUSTNAME_LEN );
		$workDesc = it_getWorkDescription( $issueData["P_ID"], $issueData["PW_ID"] );

		// Prepare common issue parameters
		//
		$messageHeader = sprintf( $userITStrings['rem_remindheader_text'], getUserName( $recipientU_ID, true ) );

		$filePath = sprintf( "%spublished/IT/includes/modifymail.txt", WBS_DIR );
		$bodyTemplate = file( $filePath );
		$bodyTemplate = implode( "", $bodyTemplate );
		$sqlDate = sqlTimestamp( $issueData["I_STATUSCURRENTDATE"] );

		$issueText = $issueData["I_DESC"];

		$priorityColors = array( IT_ISSUE_PRIORIY_LOW => "#0000FF", IT_ISSUE_PRIORIY_NORMAL => "#000000", IT_ISSUE_PRIORIY_HIGH => "#FF0000" );
		$priorityName = $userITStrings[$it_issue_priority_names[$issueData["I_PRIORITY"]]];
		$priorityColor = $priorityColors[$issueData["I_PRIORITY"]];

		// Load attachments list
		//
		$attachmentsData = listAttachedFiles( base64_decode($issueData["I_ATTACHMENT"]) );

		$attachedFiles = array();
		if ( count($attachmentsData) ) {
			for ( $i = 0; $i < count($attachmentsData); $i++ ) {
				$fileData = $attachmentsData[$i];
				$fileName = $fileData["name"];
				$fileSize = formatFileSizeStr( $fileData["size"] );

				$attachedFiles[] = sprintf( "%s (%s)", $fileData["screenname"], $fileSize );
			}
		}

		if ( !count($attachedFiles) )
			$attachedFiles = sprintf( "<br><br>%s: %s", $userITStrings['ami_attachments_label'], $userITStrings['ami_na_label'] );
		else
			$attachedFiles = sprintf( "<br><br>%s:<br>%s", $userITStrings['ami_attachments_label'], implode( "; ", $attachedFiles ) );

		// Prepare common old issue parameters
		//
		$prevIssueText = $prevData["I_DESC"];

		$prevPriorityName = $userITStrings[$it_issue_priority_names[$prevData["I_PRIORITY"]]];
		$prevPriorityColor = $priorityColors[$prevData["I_PRIORITY"]];

		$prevIssueColor = it_getIssueHTMLStyle( $prevData["ITS_COLOR"] );

		// Load prev attachments list
		//
		$prevAttachmentsData = listAttachedFiles( base64_decode($prevData["I_ATTACHMENT"]) );

		$prevAttachedFiles = array();
		if ( count($prevAttachmentsData) ) {
			for ( $i = 0; $i < count($prevAttachmentsData); $i++ ) {
				$fileData = $prevAttachmentsData[$i];
				$fileName = $fileData["name"];
				$fileSize = formatFileSizeStr( $fileData["size"] );

				$prevAttachedFiles[] = sprintf( "%s (%s)", $fileData["screenname"], $fileSize );
			}
		}

		if ( !count($prevAttachedFiles) )
			$prevAttachedFiles = sprintf( "<br><br>%s: %s", $userITStrings['ami_attachments_label'], $userITStrings['ami_na_label'] );
		else
			$prevAttachedFiles = sprintf( "<br><br>%s:<br>%s", $userITStrings['ami_attachments_label'], implode( "; ", $prevAttachedFiles ) );


		// Make body text
		//
		$messageBody = sprintf( $bodyTemplate,
							$userITStrings['ami_mailheader_text'],
							$userITStrings['ami_mailproject_label'], $projName,
							$userITStrings['ami_task_label'], $issueData["PW_ID"], $workDesc,
							$userITStrings['ami_issue_label'], sprintf( "%s.%s", $issueData["PW_ID"], $issueData["I_NUM"] ),
							$userITStrings['ami_mailnewedition_label'],
							$issueText,
							$userITStrings['ami_priority_label'], $priorityColor, $priorityName,
							$userITStrings['ami_mailstatus_label'], $issueColor, $issueData["I_STATUSCURRENT"],
							$attachedFiles,
							$userITStrings['ami_mailoldedition_label'],
							$prevIssueText,
							$userITStrings['ami_priority_label'], $prevPriorityColor, $prevPriorityName,
							$userITStrings['ami_mailstatus_label'], $prevIssueColor, $prevData["I_STATUSCURRENT"],
							$prevAttachedFiles
							);

		$subject = $it_loc_str[$language]['ami_mailsubject_text'];

		@sendWBSMail( $recipientU_ID, null, $U_ID, $subject, $issueData["I_PRIORITY"], $messageBody, $kernelStrings, "onIssueAssignment", $IT_APP_ID, $messageHeader, true, null, $userITStrings['ami_mailsentby_text'] );
	}

	function it_getUserIssueStatusList( $U_ID, $kernelStrings )
	//
	// Returns list containing names of states for works issues of all projects that are accessible by the user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns an array containing list of states, assorted with the regard for state name; or PEAR_Error
	//
	{
		global $qr_it_selectprojects;

		$params = array( "U_ID"=>$U_ID );
		$statusList = array();

		$qr = db_query( $qr_it_selectprojects, array() );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		while ( $row = db_fetch_array($qr) ) {
			$P_ID = $row["P_ID"];

			$works = it_listActiveProjectUserWorks( $P_ID, $U_ID, null );
			if ( PEAR::isError($works) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !isset($works[$P_ID]) )
				continue;

			$works = $works[$P_ID];
			foreach( $works as $PW_ID=>$work ) {
				$list = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings, true );

				$statusList = array_merge( $statusList, $list );
			}
		}

		db_free_result( $qr );

		ksort( $statusList, SORT_STRING );
		$result = array_keys( $statusList );

		return $result;
	}

	function it_getIssueStatusList()
	//
	// Returns list containing names of states for works issues of all projects
	//
	//		Parameters:
	//			none
	//
	//		Returns an array containing list of states, assorted with the regard for state name; or PEAR_Error
	//
	{
		global $qr_it_select_issue_states;

		if ( PEAR::isError( $qr = db_query($qr_it_select_issue_states, array()) ) )
			return $qr;

		$statusList = array();
		while ( $row = db_fetch_array( $qr ) )
			$statusList[] = $row["ITS_STATUS"];

		@db_free_result( $qr );

		return $statusList;
	}

	function it_getUserProjectsAssignmentsList( $U_ID, $kernelStrings )
	//
	// Returns list containing assignments for projects that are accessible by the user
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns list containing user in the form of an array( U_ID1=>$USERNAME1, U_ID2=>$USERNAME2,... )
	//
	{
		global $qr_it_manager_projects_workassignments;
		global $qr_it_assigned_workassignments;

		$queries = array( $qr_it_manager_projects_workassignments, $qr_it_assigned_workassignments );

		$params = array( "U_ID"=>$U_ID );
		$userList = array();
		for ( $i = 0; $i < count($queries); $i++ ) {
			if ( PEAR::isError( $qr = db_query($queries[$i], $params) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			while ( $row = db_fetch_array( $qr ) )
				$userList[$row["U_ID"]] = getArrUserName($row, true);

			@db_free_result( $qr );
		}

		uasort( $userList, "ra_cmp" );

		return $userList;
	}

	function it_addmodIssueFilter( $action, $filterData, $kernelStrings, $itStrings )
	//
	// Adds or modifies issue list filter
	//
	//		Parameters:
	//			$action - action type - addition ($action == ACTION_NEW) or modification ($action == ACTION_EDIT)
	//			$filterData - information about filter, record ISSUEFILTER
	//				List of latent states (ISSF_HIDDENSTATES) should be transmitted in the form of an array with the names of states.
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns new filter identifier, or PEAR_Error
	//
	{
		global $qr_it_insert_filter;
		global $qr_it_select_max_filterID;
		global $qr_it_update_filter;

		$ISSF_NAME_Len = 250;
		$ISSF_SEARCHSTRING_Len = 255;

		$filterData = trimArrayData( $filterData );
		$filterData = nullSQLFields( $filterData, array( "ISSF_SEARCHSTRING", "ISSF_DAYSAGO", "ISSF_PENDING", "ISSF_LASTDAYS" ) );

		$requiredFields = array( "ISSF_NAME", "U_ID" );

		if ( $filterData['ISSF_WORKSTATE_CREATEDAY_OPT'] == 1 )
			$requiredFields = array_merge( $requiredFields, array( "ISSF_LASTDAYS" ) );
		else
			if ( $filterData['ISSF_WORKSTATE_CREATEDAY_OPT'] == 2 )
				$requiredFields = array_merge( $requiredFields, array( "ISSF_DAYSAGO" ) );

		if ( PEAR::isError( $invalidField = findEmptyField($filterData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		$invalidField = checkStringLengths($filterData, array("ISSF_NAME", "ISSF_SEARCHSTRING"), array($ISSF_NAME_Len, $ISSF_SEARCHSTRING_Len));
		if ( PEAR::isError($invalidField) ) {
			$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

			return $invalidField;
		}

		if ( PEAR::isError( $res = checkIntegerFields( $filterData, array( "ISSF_DAYSAGO", "ISSF_PENDING", "ISSF_LASTDAYS" ), $kernelStrings ) ) )
			return $res;

		$filterData = nullSQLFields( $filterData, array( "ISSF_OVERDUE", "ISSF_PENDING" ) );

		if ( is_array($filterData["ISSF_HIDDENSTATES"]) )
			$filterData["ISSF_HIDDENSTATES"] = implode( IT_FILTER_ISSUE_DELIMITER, $filterData["ISSF_HIDDENSTATES"] );
		else
			$filterData["ISSF_HIDDENSTATES"] = null;

		$filterData["ISSF_MODIFYDATETIME"] = convertToSqlDate( time() );

		if ( $action == ACTION_NEW ) {
			if ( PEAR::isError( $ISSF_ID = db_query_result( $qr_it_select_max_filterID, DB_FIRST, $filterData ) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			$ISSF_ID = incID( $ISSF_ID );
			$filterData["ISSF_ID"] = $ISSF_ID;

			if ( PEAR::isError( exec_sql($qr_it_insert_filter, $filterData, $outputList, false) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			it_applyFilter( $filterData['U_ID'], $ISSF_ID, $kernelStrings, $itStrings );
		} else {
			if ( PEAR::isError( exec_sql($qr_it_update_filter, $filterData, $outputList, false) ) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			it_renameIssueFilter( $filterData, $kernelStrings );
		}

		return $filterData["ISSF_ID"];
	}

	function it_applyFilter( $U_ID, $ISSF_ID, $kernelStrings, $itStrings )
	//
	// Applies filter in the list of user issue filters
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$ISSF_ID - filter identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;

		if ( $ISSF_ID == IT_FILTER_ALL )
			return it_revokeIssueFilter( $U_ID, $kernelStrings );

		setAppUserCommonValue($IT_APP_ID, $U_ID, IT_FILTER_XML_FILTERS_CURRENT, $ISSF_ID, $kernelStrings);
	}

	function it_compareFileNames( $a, $b )
	//
	// Internal function for assorting filter list of issues
	//
	//		Parameters:
	//			$a - information about first project
	//			$b - information about second project
	//
	//		Returns -1, 0 or 1, depending on compare result
	//
	{
		$val1 = $a[1];
		$val2 = $b[1];

		if ($val1 == $val2)
			return 0;

		return ($val1 > $val2) ? 1 : -1;
	}

	function it_listIssueFilters( $U_ID, $kernelStrings, $addNoFilterItem = false, $itStrings = null )
	//
	// Returns list user issue list filters
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$addNoFilterItem - add extra item in the begining
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns filter list, assorted with the regard for order of use, in the form of an array( ISSF_ID=>ISSF_NAME )
	//
	{
		global $qr_it_select_filters;

		$qr = db_query( $qr_it_select_filters, array('U_ID'=>$U_ID) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();

		if ( $addNoFilterItem )
			$result[IT_FILTER_ALL] = $itStrings['il_allissuesfilter_title'];

		while ( $row = db_fetch_array($qr) )
			$result[$row["ISSF_ID"]] = $row["ISSF_NAME"];

		db_free_result( $qr );

		return $result;
	}

	function it_makedefaultissuefilters( $U_ID, $itStrings, $kernelStrings )
	//
	// Makes default issue filters
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$itStrings - Issue Tracking localization strings
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;
		global $it_defaultFilters;

		$filtersCreated = getAppUserCommonValue($IT_APP_ID, $U_ID, IT_FILTER_XML_DEF_FILTERS_CREATED);
		if ( $filtersCreated )
			return null;

		setAppUserCommonValue($IT_APP_ID, $U_ID, IT_FILTER_XML_DEF_FILTERS_CREATED, 1, $kernelStrings);

		$lastTimeStamp = time();

		$defaultFilterData = array(
									"ISSF_WORKSTATE" => IT_FILTER_ALLWORKS,
									"ISSF_HIDDENSTATES" => array(),
									"ISFF_U_ID_ASSIGNED" => null,
									"ISFF_U_ID_SENDER" => null,
									"ISFF_DAYSOLD" => null,
									"ISSF_SEARCHSTRING" => null,
									"ISSF_PENDING" => null,
									"ISFF_U_ID_AUTHOR" => null,
									"ISSF_ISSUE_COMPLETE" => 0,
									"ISSF_WORKSTATE_CREATEDAY_OPT" => 0,
									"ISSF_LASTDAYS" => null,
									"ISSF_DAYSAGO" => null
								);

		$firstID = null;
		foreach ( $it_defaultFilters as $filterData ) {
			$filterData = array_merge( $defaultFilterData, $filterData );

			$filterData["U_ID"] = $U_ID;
			$filterData["ISSF_NAME"] = $itStrings[$filterData["ISSF_NAME"]];

			if ( $filterData["ISFF_U_ID_SENDER"] == IT_FILTER_THIS_USER )
				$filterData["ISFF_U_ID_SENDER"] = $U_ID;

			if ( $filterData["ISFF_U_ID_ASSIGNED"] == IT_FILTER_THIS_USER )
				$filterData["ISFF_U_ID_ASSIGNED"] = $U_ID;

			if ( $filterData["ISFF_U_ID_AUTHOR"] == IT_FILTER_THIS_USER )
				$filterData["ISFF_U_ID_AUTHOR"] = $U_ID;

			$ISSF_ID = it_addmodIssueFilter( ACTION_NEW, $filterData, $kernelStrings, $itStrings );
			if ( PEAR::isError($ISSF_ID) )
				return $ISSF_ID;

			if ( is_null($firstID) )
				$firstID = $ISSF_ID;
		}

		it_applyFilter( $U_ID, IT_FILTER_ALL, $kernelStrings, $itStrings );

		return null;
	}

	function it_revokeIssueFilter( $U_ID, $kernelStrings )
	//
	// Revokes filter in the issue list
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;

		setAppUserCommonValue($IT_APP_ID, $U_ID, IT_FILTER_XML_FILTERS_CURRENT, "", $kernelStrings);
		
	}

	function it_deleteIssueFilter( $U_ID, $ISSF_ID, $kernelStrings )
	//
	// Deletes filter from filter list and from the list of earlier used filters
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$ISSF_ID - filter identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		//
		// Delete record from recently used filters list
		//

		global $IT_APP_ID;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if (!is_null($settingsElement)) {
			if ( !$settingsElement )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );
	
			$itNode = getElementByTagname( $settingsElement, $IT_APP_ID );
			if ( !$itNode )
				$itNode = @create_addElement( $dom, $settingsElement, $IT_APP_ID );
	
			if ( !$itNode )
				return PEAR::raiseError( $kernelStrings[ERR_XML] );
	
			$filtersNode = getElementByTagname( $itNode, IT_FILTER_XML_SECTION );
			if ( $filtersNode ) {
				$filters = @$filtersNode->get_elements_by_tagname( IT_FILTER_XML_FILTER );
	
				$curFilter = @$filtersNode->get_attribute( IT_FILTER_XML_FILTERS_CURRENT );
				if ($curFilter == $ISSF_ID)
					$filtersNode->set_attribute( IT_FILTER_XML_FILTERS_CURRENT, "" );
	
				if ( is_array($filters) )
					for ( $i = 0; $i < count($filters); $i++ ) {
						$filter = $filters[$i];
	
						$filterID = @$filter->get_attribute(IT_FILTER_XML_FILTER_ID);
						if ( $filterID == $ISSF_ID ) {
							$filter->unlink_node();
	
							break;
						}
					}
			}
	
			$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
			if ( PEAR::isError( $res ) )
				return $res;
		}

		//
		// Delete record from filter list
		//

		global $qr_it_delete_filter;

		$params = array( "U_ID"=>$U_ID, "ISSF_ID"=>$ISSF_ID );
		if ( PEAR::isError( exec_sql($qr_it_delete_filter, $params, $outputList, false) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;
	}

	function it_renameIssueFilter( $filterData, $kernelStrings )
	//
	// Renames filter in the list of earlier used filters
	//
	//		Parameters:
	//			$filterData - information about filter, record ISSUEFILTER
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;

		$U_ID = $filterData["U_ID"];
		$ISSF_ID = $filterData["ISSF_ID"];

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$itNode = getElementByTagname( $settingsElement, $IT_APP_ID );
		if ( !$itNode )
			$itNode = @create_addElement( $dom, $settingsElement, $IT_APP_ID );

		if ( !$itNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$filtersNode = getElementByTagname( $itNode, IT_FILTER_XML_SECTION );
		if ( $filtersNode ) {
			$filters = @$filtersNode->get_elements_by_tagname( IT_FILTER_XML_FILTER );

			if ( is_array($filters) )
				for ( $i = 0; $i < count($filters); $i++ ) {
					$filter = $filters[$i];

					$filterID = @$filter->get_attribute(IT_FILTER_XML_FILTER_ID);
					if ( $filterID == $ISSF_ID ) {
						$filter->set_attribute( IT_FILTER_XML_FILTER_NAME, base64_encode($filterData["ISSF_NAME"]) );

						break;
					}
				}
		}

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError( $res ) )
			return $res;

		return null;
	}

	function it_getCurrentIssueFilter( $U_ID, $kernelStrings )
	//
	// Returns current filter of issue list
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns identifier of current user, or PEAR_Error
	//
	{
		global $IT_APP_ID;

		return getAppUserCommonValue($IT_APP_ID, $U_ID, IT_FILTER_XML_FILTERS_CURRENT);
	}

	function it_loadIssueFilterData( $U_ID, $ISSF_ID, $itStrings )
	//
	// Returns settings of issue list filter
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$ISSF_ID - filter identifier
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns record of ISSUEFILTER table, or PEAR_Error
	//
	{
		global $qr_it_select_filter;

		$params = array( "U_ID"=>$U_ID, "ISSF_ID"=>$ISSF_ID );

		$filterData = db_query_result($qr_it_select_filter, DB_ARRAY, $params);
		if (PEAR::isError($filterData))
			return PEAR::raiseError( $itStrings['il_errfilterloading_message'] );

		return $filterData;
	}

	function it_getIssueFilterType( $filterData )
	//
	// Returns type of issue list filter
	//
	//		Parameters:
	//			$filterData - filter settings
	//
	//		Returns one of the following values:
	//			IT_FT_WORKS - filter only for use with works
	//			IT_FT_MIXED - filter for use with issues and works
	//			null - filter is not defined
	//
	{
		if ( !is_array($filterData) )
			return null;

		if ( !strlen($filterData["ISSF_HIDDENSTATES"]) &&
			 !strlen($filterData["ISFF_U_ID_ASSIGNED"]) &&
			 !strlen($filterData["ISFF_U_ID_SENDER"]) &&
			 !strlen($filterData["ISFF_DAYSOLD"]) &&
			 !strlen($filterData["ISSF_SEARCHSTRING"]) )
			return IT_FT_WORKS;
		else
			return IT_FT_MIXED;
	}

	function it_prepareWorkFilterSQL( $filterData )
	//
	// Prepares SQL query for filtration of work list
	//
	//		Parameters:
	//			$filterData - filter settings
	//
	//		Returns string
	//
	{
		global $qr_it_filterActiveWorksChunk;
		global $qr_it_filterClosedWorksChunk;
		global $qr_it_openedWorksChunk;

		$filterChunks = array();

		if ( !is_null( $filterData ) ) {
			$filterChunks[] = $qr_it_openedWorksChunk;
			$filterChunks[] = $qr_it_filterActiveWorksChunk;
		}

		if ( count($filterChunks) )
			return sprintf( " AND (%s) ", implode( " AND ", $filterChunks ) );
		else
			return "";
	}

	//
	// IT XML Settings
	//

	function it_loadCommonSetting( $U_ID, $paramName )
	//
	// Loads attribute of application IT from user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$paramName - parameter's name
	//
	//		Returns value of parameter, or null
	//
	{
		global $IT_APP_ID;
		
		return getAppUserCommonValue($IT_APP_ID, $U_ID, $paramName);
	}

	function it_loadSelectedWorks( $U_ID )
	//
	// Loads attribute of application IT from user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$paramName - parameter's name
	//
	//		Returns value of parameter, or null
	//
	{
		global $IT_APP_ID;
		$selected = getAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATES_SELECTED);
		if ($selected) {
			return unserialize( $selected );
		} else {
			return array();	
		}

	}

	function it_saveCommonSetting( $U_ID, $paramName, $paramValue, $kernelStrings )
	//
	// Saves attribute of application IT in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$paramName - parameter's name
	//			$paramValue - parameter's value
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null
	//
	{
		global $IT_APP_ID;

		setAppUserCommonValue($IT_APP_ID, $U_ID, $paramName, $paramValue, $kernelStrings);
	}

	function it_saveSelectedWorks( $U_ID, $paramValue, $kernelStrings )
	//
	// Saves attribute of application IT in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$paramValue - parameter's value
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null
	//
	{
		global $IT_APP_ID;
		setAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATES_SELECTED, serialize($paramValue), $kernelStrings);
	}
	//
	// IT Reports
	//

	function it_getISMData( $P_ID, $V_ID, &$rowNames, &$colNames, &$reportData, $itStrings, $kernelStrings )
	//
	// Returns data of report Issue Statistics Matrix
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$V_ID - report type. One of the following values: IT_ISM_VIEW_PROJECT_STATUS..IT_ISM_VIEW_ASSIGNED_PRIORITY
	//			$rowNames - names of rows. Array array( row_id1=>row_name1, row_id2=>row_name2,.. )
	//			$colNames - names of columns. Array array( col_id1=>col_name1, col_id2=>col_name2,.. )
	//			$reportData - array containing report data. Two-dimensional array array( [col_id1,row_id1]=>value1, [col_id2,row_id2]=>value2 )
	//			$itStrings - Issue Tracking localization strings
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns the number of report rows, or PEAR_Error
	//
	{
		global $IT_ISM_QUERIES;
		global $it_issue_priority_names;

		if ( !is_array($reportData) )
			$reportData = array();

		$rowNames = array();
		$colNames = array();

		$P_Selector = ($P_ID == IT_ISM_ALL_PROJECTS) ? IT_ISM_ALL_PROJECTS : IT_ISM_KNOWN_PROJECT;

		$query = $IT_ISM_QUERIES[$V_ID][$P_Selector];
		if ( !strlen($query) )
			return PEAR::raiseError( $itStrings[IT_ERR_ISMREPORT], ERRCODE_APPLICATION_ERR );

		$params = array( "P_ID"=>$P_ID );

		if ( PEAR::isError( $qr = db_query($query, $params) ) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$rowCount = 0;
		while ( $row = db_fetch_array($qr) ) {
			$rowCount++;

			$ROW_ID = $row["ROW_ID"];
			$COL_ID = IT_ISM_COLID_FIX.$row["COL_ID"];

			if ( !strlen($ROW_ID) )
				$ROW_ID = IT_ISM_ROWID_FIX;

			switch ($V_ID) {
				case IT_ISM_VIEW_WORK_STATUS :
				case IT_ISM_VIEW_PROJECT_STATUS : {
					if ( $V_ID != IT_ISM_VIEW_PROJECT_STATUS )
						$rowNames[$ROW_ID] = sprintf( "%s. %s", $ROW_ID, $row["ROW_NAME"] );
					else
						$rowNames[$ROW_ID] = sprintf( "%s. %s", $row["C_NAME"], $row["ROW_NAME"] );

					$colNames[$COL_ID] = $row["COL_NAME"];
					break;
				}
				case IT_ISM_VIEW_WORK_ASSIGNED :
				case IT_ISM_VIEW_PROJECT_ASSIGNED : {
					if ( $V_ID != IT_ISM_VIEW_PROJECT_ASSIGNED )
						$rowNames[$ROW_ID] = sprintf( "%s. %s", $ROW_ID, $row["ROW_NAME"] );
					else
						$rowNames[$ROW_ID] = sprintf( "%s. %s", $row["C_NAME"], $row["ROW_NAME"] );

					$colNames[$COL_ID] = getArrUserName($row, true);

					break;
				}
				case IT_ISM_VIEW_WORK_PRIORITY :
				case IT_ISM_VIEW_PROJECT_PRIORITY : {
					if ( $V_ID != IT_ISM_VIEW_PROJECT_PRIORITY )
						$rowNames[$ROW_ID] = sprintf( "%s. %s", $ROW_ID, $row["ROW_NAME"] );
					else
						$rowNames[$ROW_ID] = sprintf( "%s. %s", $row["C_NAME"], $row["ROW_NAME"] );

					$colNames[$COL_ID] = $itStrings[$it_issue_priority_names[$row["COL_ID"]]];
					break;
				}
				case IT_ISM_VIEW_ASSIGNED_STATUS : {
					$rowNames[$ROW_ID] = getArrUserName($row, true);
					$colNames[$COL_ID] = $row["COL_NAME"];
					break;
				}
				case IT_ISM_VIEW_ASSIGNED_PRIORITY : {
					$rowNames[$ROW_ID] = getArrUserName($row, true);
					$colNames[$COL_ID] = $itStrings[$it_issue_priority_names[$row["COL_ID"]]];
				}
			}

			if ( !array_key_exists($ROW_ID, $reportData)  )
				$reportData[$ROW_ID] = array();

			$reportData[$ROW_ID][$COL_ID] = $row["I_COUNT"];
		}

		if ( is_array($colNames) )
			if ( (in_array($V_ID, array(IT_ISM_VIEW_PROJECT_PRIORITY, IT_ISM_VIEW_WORK_PRIORITY, IT_ISM_VIEW_ASSIGNED_PRIORITY)) ) )
				ksort( $colNames );
			else {
				uasort( $colNames, "ra_cmp" );

				if ( (in_array($V_ID, array(IT_ISM_VIEW_WORK_ASSIGNED, IT_ISM_VIEW_PROJECT_ASSIGNED)) ) )
					foreach( $colNames as $key => $value ) {
						if ( !strlen($value) )
							$colNames[$key] = $itStrings['iss_noassignee_text'];
					}
			}

		if ( !in_array($V_ID, array( IT_ISM_VIEW_WORK_STATUS, IT_ISM_VIEW_WORK_ASSIGNED, IT_ISM_VIEW_WORK_PRIORITY )) )
		if ( is_array($rowNames) )
			uasort( $rowNames, "ra_cmp" );

		if ( (in_array($V_ID, array(IT_ISM_VIEW_ASSIGNED_STATUS, IT_ISM_VIEW_ASSIGNED_PRIORITY)) ) )
			foreach( $rowNames as $key => $value )
				if ( !strlen($rowNames[$key]) )
					$rowNames[$key] = $itStrings['iss_noassignee_text'];

		@db_free_result($qr);

		return $rowCount;
	}

	function it_getISMIssueReportProjectsQuery( $V_ID, $param1, $param2, $P_ID, $itStrings )
	//
	// Returns result of query for obtaining project list and issue list printed report for Issue Statistics Matrix
	//
	//		Parameters:
	//			$V_ID - report type. One of the following values: IT_ISM_VIEW_PROJECT_STATUS..IT_ISM_VIEW_ASSIGNED_PRIORITY
	//			$param1, $param2, $P_ID - parameters, received from report Issue Statistics Matrix
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns result of query implementation, or PEAR_Error
	//
	{
		global $qr_it_ism_issuerep_projects;
		global $qr_it_ism_issuerep_proj_chunk;

		$query = null;

		switch ($V_ID) {
			case IT_ISM_VIEW_PROJECT_STATUS :
			case IT_ISM_VIEW_PROJECT_ASSIGNED :
			case IT_ISM_VIEW_PROJECT_PRIORITY : {
				$P_ID = $param1;

				$query = sprintf( $qr_it_ism_issuerep_projects, sprintf($qr_it_ism_issuerep_proj_chunk, $P_ID) );
				break;
			}
			case IT_ISM_VIEW_ASSIGNED_PRIORITY :
			case IT_ISM_VIEW_ASSIGNED_STATUS : {
				if ( $P_ID == IT_ISM_ALL_PROJECTS )
					$query = sprintf( $qr_it_ism_issuerep_projects, "" );
				else
					$query = sprintf( $qr_it_ism_issuerep_projects, sprintf($qr_it_ism_issuerep_proj_chunk, $P_ID) );

				break;
			}
			case IT_ISM_VIEW_WORK_STATUS :
			case IT_ISM_VIEW_WORK_ASSIGNED :
			case IT_ISM_VIEW_WORK_PRIORITY : {
				$query = sprintf( $qr_it_ism_issuerep_projects, sprintf($qr_it_ism_issuerep_proj_chunk, $P_ID) );
				break;
			}
		}

		if ( is_null($query) )
			return PEAR::raiseError( $itStrings[IT_ERR_ISMPARAMS] );

		return db_query($query);
	}

	function it_getISMIssueReportWorksQuery( $V_ID, $param1, $param2, $P_ID, $itStrings )
	//
	// Returns result of query for obtaining project list and issue list printed report for Issue Statistics Matrix
	//
	//		Parameters:
	//			$V_ID - report type. One of the following values: IT_ISM_VIEW_PROJECT_STATUS..IT_ISM_VIEW_ASSIGNED_PRIORITY
	//			$param1, $param2, $P_ID - parameters, received from report Issue Statistics Matrix
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns query string, or PEAR_Error
	//
	{
		global $qr_it_ism_issuerep_works;
		global $qr_it_ism_issuerep_work_chunk;

		$query = null;

		switch ($V_ID) {
			case IT_ISM_VIEW_PROJECT_STATUS :
			case IT_ISM_VIEW_PROJECT_ASSIGNED :
			case IT_ISM_VIEW_PROJECT_PRIORITY :
			case IT_ISM_VIEW_ASSIGNED_PRIORITY :
			case IT_ISM_VIEW_ASSIGNED_STATUS : {
				$P_ID = $param1;

				$query = sprintf( $qr_it_ism_issuerep_works, "" );
				break;
			}
			case IT_ISM_VIEW_WORK_STATUS :
			case IT_ISM_VIEW_WORK_ASSIGNED :
			case IT_ISM_VIEW_WORK_PRIORITY : {
				$PW_ID = $param1;

				$query = sprintf( $qr_it_ism_issuerep_works, sprintf($qr_it_ism_issuerep_work_chunk, $PW_ID) );
				break;
			}
		}

		if ( is_null($query) )
			return PEAR::raiseError( $itStrings[IT_ERR_ISMPARAMS] );

		return $query;
	}

	function it_saveIDRSettings( $U_ID, $P_ID, $PW_ID, $statusList, $kernelStrings, $itStrings )
	//
	// Saves settings of Issue Dynamic Report in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$statusList - list containing states for report
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$itNode = getElementByTagname( $settingsElement, $IT_APP_ID );
		if ( !$itNode )
			$itNode = @create_addElement( $dom, $settingsElement, $IT_APP_ID );

		if ( !$itNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$idrRoot = getElementByTagname( $itNode, IT_IDR_XML_ROOT );
		if ( $idrRoot )
			$idrRoot->unlink_node();

		$idrRoot = @create_addElement( $dom, $itNode, IT_IDR_XML_ROOT );

		$idrRoot->set_attribute( IT_IDR_XML_PID, $P_ID );
		$idrRoot->set_attribute( IT_IDR_XML_PWID, $PW_ID );

		if ( is_array($statusList) )
			for ( $i = 0; $i < count($statusList); $i++ ) {
				$status = @create_addElement( $dom, $idrRoot, IT_IDR_XML_STATUS );

				$status->set_attribute( IT_IDR_XML_STATUSNAME, base64_encode($statusList[$i]) );
			}

		$res = saveUserSettingsDOM( $U_ID, $dom, $settingsElement, $kernelStrings );
		if ( PEAR::isError( $res ) )
			return $res;

		return null;
	}

	function it_loadIDRSettings( $U_ID, &$P_ID, &$PW_ID, &$statusList, $kernelStrings, $itStrings )
	//
	// Loads settings of Issue Dynamic Report from user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$statusList - list containing states for report
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;

		$P_ID = IT_IDR_ALL_PROJECTS;
		$PW_ID = IT_IDR_ALL_WORKS;
		$statusList = array();

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$itNode = getElementByTagname( $settingsElement, $IT_APP_ID );
		if ( !$itNode )
			$itNode = @create_addElement( $dom, $settingsElement, $IT_APP_ID );

		if ( !$itNode )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$idrRoot = getElementByTagname( $itNode, IT_IDR_XML_ROOT );
		if ( !$idrRoot )
			return null;

		$P_ID = @$idrRoot->get_attribute(IT_IDR_XML_PID);
		$PW_ID = @$idrRoot->get_attribute(IT_IDR_XML_PWID);

		$stList = @$idrRoot->get_elements_by_tagname( IT_IDR_XML_STATUS );

		if ( is_array($stList) )
			for ( $i = 0; $i < count($stList); $i++ ) {
				$status = $stList[$i];

				$statusName = base64_decode($status->get_attribute(IT_IDR_XML_STATUSNAME));
				$statusList[] = $statusName;
			}

		return null;
	}

	function it_getRemindUserIssueCounts( $issueList, $kernelStrings )
	//
	// Returns the number of user issues, for screen Remind
	//
	//		Parameters:
	//			$issueList - list containing identifiers of issues
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array( U_ID1=>array( USER_NAME=>USER_NAME, count=count )... ),
	//			or PEAR_Error
	//
	{
		global $qr_it_count_user_issues;

		$issueFilterStr = implode( ",", $issueList );

		$qr = db_query( sprintf( $qr_it_count_user_issues, $issueFilterStr ) );
		if ( PEAR::isError( $qr ) )
			return $kernelStrings[ERR_QUERYEXECUTING];

		$result = array();

		while ( $row = db_fetch_array( $qr ) )
			$result[$row["U_ID_ASSIGNED"]] = array( "USER_NAME"=>getArrUserName($row, true), "count"=>$row["ISSUECOUNT"] );

		@db_free_result( $qr );

		return $result;
	}

	function it_sendRemindNotification($issueList, $comment, $headersOnly, $kernelStrings, $itStrings, $senderU_ID )
	//
	// Sends notifications about issues
	//
	//		Parameters:
	//			$issueList - list containing identifiers of issues
	//			$comment - comment to letter
	//			$headersOnly - drop issue descriptions
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$senderU_ID - identifier of message sender
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_idr_select_issueusers;
		global $it_loc_str;

		if ( !is_array($issueList) || !count($issueList) )
			return PEAR::raiseError( $itStrings['rem_emptysend_message'], ERRCODE_APPLICATION_ERR );

		$issueFilterStr = implode( ",", $issueList );
		$sql = sprintf( $qr_it_idr_select_issueusers, $issueFilterStr );

		$qr = db_query( $sql );
		if ( PEAR::isError( $qr ) )
			return $kernelStrings[ERR_QUERYEXECUTING];

		while ( $row = db_fetch_array( $qr ) ) {
			$U_ID = $row["U_ID_ASSIGNED"];
			$language = @readUserCommonSetting( $U_ID, LANGUAGE );

			$itStrings = $it_loc_str[$language];

			$messageHeader = sprintf( $itStrings['rem_remindheader_text'], getUserName( $U_ID, true ) );

			$messageStr = sprintf( "%s<br><br>", $itStrings['rem_remindertitle_text'] );

			$subject = $it_loc_str[$language]['rem_remindersubject_text'];

			$userStr = it_prepareUserRemindNotification( $row["U_ID_ASSIGNED"], $comment, $headersOnly, $issueList, $kernelStrings, $itStrings );
			if ( PEAR::isError( $userStr ) )
				return $kernelStrings[ERR_QUERYEXECUTING];

			$messageStr .= $userStr;

			@sendWBSMail( $U_ID, null, $senderU_ID, $subject, 1, $messageStr, $kernelStrings, null, null, $messageHeader, true, null, $itStrings['rem_sentby_text'], true );
		}

		db_free_result( $qr );
	}

	function it_saveWorkExpandState( $U_ID, $P_ID, $PW_ID, $state, $kernelStrings )
	//
	// Saves state of issue list work's string - expanded/collapsed - in user settings
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$state - state - one of values: IT_WORK_EXPANDED, IT_WORK_COLLAPSED
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;
		
	
		$sql = "SELECT VALUE FROM USER_SETTINGS WHERE U_ID = '!U_ID!' AND NAME LIKE 'STATE'";
		$works = db_query($sql, array('U_ID' => $U_ID));
				
		$workFound = false;
		if ( is_array($works) )
			for ( $i = 0; $i < count($works); $i++ ) {
				$work = $works[$i];

				$curP_ID = getAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATE_PID );
				$curPW_ID = getAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATE_PWID );
				if ( $curP_ID == $P_ID && $curPW_ID == $PW_ID ) {
					setAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATE_STATE, $state, $kernelStrings);
					$workFound = true;
					break;
				}
			}

		if ( !$workFound ) {
			setAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATE_PID, $P_ID, $kernelStrings);
  			setAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATE_PWID, $PW_ID, $kernelStrings);			
	  		setAppUserCommonValue($IT_APP_ID, $U_ID, IT_XML_ISSUELIST_WORKSTATE_STATE, $state, $kernelStrings);			
		}

	}

	function it_getCollapsedWorkList( $U_ID, $P_ID, $kernelStrings )
	//
	// Returns list of issue list collapsed works
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$P_ID - project identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array containing identifiers of works
	//
	{
		global $IT_APP_ID;

		$result = array();

		$settingsElement = getUserSettingsRoot( $U_ID, $dom );
		if ( !$settingsElement )
			return $result;

		$itNode = getElementByTagname( $settingsElement, $IT_APP_ID );
		if ( !$itNode )
			return $result;

		$statesNode = getElementByTagname( $itNode, IT_XML_ISSUELIST_WORKSTATES );
		if ( !$statesNode )
			return $result;

		$works = @$statesNode->get_elements_by_tagname( IT_XML_ISSUELIST_WORKSTATE );
		if ( !$works )
			return $result;

		if ( is_array($works) )
			for ( $i = 0; $i < count($works); $i++ ) {
				$work = $works[$i];

				$curP_ID = @$work->get_attribute( IT_XML_ISSUELIST_WORKSTATE_PID );
				$curPW_ID = @$work->get_attribute( IT_XML_ISSUELIST_WORKSTATE_PWID );
				$curState = @$work->get_attribute( IT_XML_ISSUELIST_WORKSTATE_STATE );

				if ( ($curP_ID == $P_ID || $P_ID === null ) && $curState == IT_WORK_COLLAPSED )
					$result[] = $curPW_ID;
			}
		return $result;
	}
	
	
	function it_countIssueListWorkRecords($workProjectIds) {
		
		$qr = it_prepareIssueListQuery( $P_ID, $PW_ID, $filterData );
		
		
		@db_free_result( $qr );
	}

	function it_addIssueListWorkRecords( $P_ID, $PW_ID, &$issueList, &$recordsAdded, $filterData,
											$kernelStrings, $itStrings, $print = false,
											$workTransitionList = null, $U_ID = null, $projman = null,
											$selectedIssues = null, $addTransitionsData = false, $workIsClosed = false )
	//
	// Adds issue to issue list. It is used both in printed and screen versions of issue list.
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$issueList - issue list
	//			$recordsAdded - the number of added works
	//			$filterData - information about issue list filter
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$print - if it is true, issue data is in format of printed report
	//			$workTransitionList - issue transitions list, returned by function it_listWorkTransitions()
	//			$U_ID - user identifier
	//			$projman - defines whether the user is the project manager
	//			$selectedIssues - list of selected issues
	//			$addTransitionsData - add transition data to the issue list
	//
	//		Returns null, or PEAR_Error
	//
	{
		global $qr_it_select_issues;
		global $it_issue_priority_short_names;
		global $qr_it_selectWorkIssueTransitions, $qr_it_selectWorkIssuesGBP;
		
		$UNASSIGNED = "&lt;<font color=red>".$itStrings['is_na_text']."</font>&gt;";
		$UNASSIGNED_PRINT = "&lt;".$itStrings['is_na_text']."&gt;";

		$counter = 0;

		// Load issue transitions
		//
		if ( $addTransitionsData ) {
			$issueTransitions = array();

			$qr = db_query( $qr_it_selectWorkIssueTransitions, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
			if ( PEAR::isError( $qr ) )
				return $itStrings['il_errloadinglist_message'];

			while ( $row = db_fetch_array($qr) ) {
				if ( !array_key_exists($row['I_ID'], $issueTransitions) )
					$issueTransitions[$row['I_ID']] = array();

				// Process textual data
				//
				$row['DISPLAY_NUM'] = /*$PW_ID.".".$row['I_NUM'].".".*/ $row['ITL_ID'];
				$row['STATUS_COLOR'] = it_getIssueHTMLStyle( $row["ITS_COLOR"] );
				$row["ITL_DATETIME_UF"] = convertToUserFriendlyDateTime( $row["ITL_DATETIME"], $kernelStrings );
				$row['ITL_DATETIME'] = convertToDisplayDateTime( $row["ITL_DATETIME"], false, true, true );
				
				if ( !strlen($row['U_ID_ASSIGNED']) )
					$row["ASSIGNED_NAME"] = ( $print ) ? $UNASSIGNED_PRINT : $UNASSIGNED;
				else {
					$assignedNameParts = array( "C_LASTNAME"=>$row["A_LASTNAME"], "C_MIDDLENAME"=>$row["A_MIDDLENAME"], "C_FIRSTNAME"=>$row["A_FIRSTNAME"], "C_EMAILADDRESS"=>$row["A_EMAILADDRESS"] );
					$row["ASSIGNED_NAME"] = getArrUserName( $assignedNameParts, true );
				}

				$senderNameParts = array( "C_LASTNAME"=>$row["S_LASTNAME"], "C_MIDDLENAME"=>$row["S_MIDDLENAME"], "C_FIRSTNAME"=>$row["S_FIRSTNAME"], "C_EMAILADDRESS"=>$row["S_EMAILADDRESS"] );
				$row["SENDER_NAME"] = getArrUserName( $senderNameParts, true );

				$row["MODIFYRECORD"] = strlen( $row["ITL_OLDCONTENT"] );

				// Process attachments
				//
				$attachmentsData = listAttachedFiles( base64_decode($row["ITL_ATTACHMENT"]) );
				$attachedFiles = array();
				if ( count($attachmentsData) ) {
					for ( $i = 0; $i < count($attachmentsData); $i++ ) {
						$fileData = $attachmentsData[$i ];
						$params = array( "I_ID"=>$row['I_ID'], "ITL_ID"=>$row["ITL_ID"], "fileName"=>urlencode(base64_encode($fileData["name"])) );
						$fileURL = prepareURLStr( PAGE_IT_GETTRANSITIONFILE, $params );

						$fileSize = formatFileSizeStr( $fileData["size"] );
						if ( !$print )
							$attachedFiles[] = sprintf( "<a href=\"%s\" target=\"_blank\">%s</a> %s",
																$fileURL, $fileData["screenname"], $fileSize );
						else
							$attachedFiles[] = $fileData["screenname"];
					}
					$row["FILE_DATA"] = sprintf( "%s: %s", $itStrings['is_files_label'], implode( ", ", $attachedFiles ) );
				}
				$issueTransitions[$row['I_ID']][] = $row;
			}

			db_free_result( $qr );
		}

		// Load issue list
		//
		if ((string)$P_ID != 'GBP') {
			$qr = it_prepareIssueListQuery( $P_ID, $PW_ID, $filterData );
		}
		else {
			$qr = execPreparedQuery( $qr_it_selectWorkIssuesGBP );
		}

		if ( PEAR::isError( $qr ) )
			return $itStrings['il_errloadinglist_message'];
		while ( $row = db_fetch_array( $qr ) ) {

			if ( !is_null($selectedIssues) )
				if ( !in_array($row["I_ID"], $selectedIssues) )
					continue;

			$currentRecord = prepareArrayToDisplay( $row );

			$params = array( "I_ID"=>$currentRecord["I_ID"], "P_ID"=>$P_ID, "PW_ID"=>$PW_ID );

			$currentRecord["ROW_URL"] = prepareURLStr( PAGE_IT_ISSUE, $params );
			$currentRecord["ROW_COLOR"] = it_getIssueHTMLStyle( $currentRecord["ITS_COLOR"] );

			$currentRecord["I_STARTDATE_UF"] = convertToUserFriendlyDate($currentRecord["I_STARTDATE"], $kernelStrings, $enableConversion = true);
			$currentRecord["I_STATUSCURRENTDATE_UF"] = convertToUserFriendlyDateTime( $currentRecord["I_STATUSCURRENTDATE"], $kernelStrings, $enableConversion = true, $agoFormat = true );
			
			$currentRecord["I_STARTDATE"] = convertToDisplayDate( $currentRecord["I_STARTDATE"], true );
			$currentRecord["ROW_TYPE"] = 0;
			$currentRecord["DISPLAY_NUM"] =  ($PW_ID != 0) ? sprintf( "%s.%s", $PW_ID, $currentRecord["I_NUM"] ) : $currentRecord["I_NUM"] ;
			$currentRecord["DESC_LEN"] = strlen( $currentRecord["I_DESC"] );
			$currentRecord["index"] = $counter;
			$currentRecord["CTATUS_CODE"] = base64_encode($row["I_STATUSCURRENT"]);
			if ( $currentRecord["I_PRIORITY"] != IT_ISSUE_PRIORIY_NORMAL )
				$currentRecord["PRIORITY_NAME"] = $itStrings[$it_issue_priority_short_names[$currentRecord["I_PRIORITY"]]];

			if ( $addTransitionsData ) {
				$currentRecord["TRANSITION_LOG"] = $issueTransitions[$currentRecord["I_ID"]];
			}
			if ( (!is_null($workTransitionList) && !$print) || $projman  )
				if ( $projman || ($currentRecord["U_ID_ASSIGNED"] == $U_ID) || ($currentRecord["U_ID_SENDER"] == $U_ID) ) {

					if ( !$projman )
						$closestStates = it_getStatusAllowedTransitions( $row["I_STATUSCURRENT"], $workTransitionList );
					else {
						$closestStates = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings );
						array_shift($closestStates);
					}

					$currentRecord["ALLOWED_DESTS"] = $closestStates;
					$currentRecord["ALLOWED_DESTS_COUNT"] = count($closestStates);

					$issues = array( $currentRecord["I_ID"] );
					$issues = base64_encode( serialize($issues) );
					$URLParams = array("issues"=>$issues, "P_ID"=>$currentRecord["P_ID"], "PW_ID"=>$currentRecord["PW_ID"], OPENER=>PAGE_IT_ISSUELIST );
					$currentRecord["SEND_URL"] = prepareURLStr( PAGE_IT_SENDISSUE, $URLParams );
			}
			
			// User rights
			$rights = it_getUserITRights( $P_ID, $PW_ID, $U_ID, $currentRecord["I_ID"] );
			$ITS_Data = it_loadITSData( $P_ID, $PW_ID, $currentRecord["I_STATUSCURRENT"] );
			$currentRecord["ALLOW_DELETE"] = $rights && ($projman || ($ITS_Data["ITS_ALLOW_DELETE"] && !$workIsClosed));
			$currentRecord["ALLOW_MODIFY"] = $rights && ($projman || ($ITS_Data["ITS_ALLOW_EDIT"] && !$workIsClosed ));

			// Attachments
			//
			$attachmentsData = listAttachedFiles( base64_decode($row["I_ATTACHMENT"]) );
			$attachedFiles = array();
			if ( count($attachmentsData) ) {
				for ( $i = 0; $i < count($attachmentsData); $i++ ) {
					$fileData = $attachmentsData[$i];
					$fileName = $fileData["name"];
					$fileSize = formatFileSizeStr( $fileData["size"] );

					$noCache = base64_encode( uniqid( "file" ) );
					$params = array( "I_ID"=>$currentRecord["I_ID"], "fileName"=>urlencode(base64_encode($fileName)), "nocache"=>$noCache );
					$fileURL = prepareURLStr( PAGE_IT_GETISSUEFILE, $params );

					if ( !$print )
						$attachedFiles[] = sprintf( "<a href=\"%s\" target=\"_blank\"><span class=issueList_files_a>%s (%s)</span></a>", $fileURL, $fileData["screenname"], $fileSize );
					else
						$attachedFiles[] = sprintf( "%s (%s)", $fileData["screenname"], $fileSize );
				}
			}
			if ( !count($attachedFiles) )
				$attachedFiles = null;
			else
				$attachedFiles = sprintf( "<nobr>%s:</nobr> %s", $itStrings['is_files_label'], implode( ", ", $attachedFiles ) );

			$currentRecord["ATTACHEDFILES"] = $attachedFiles;

			// Assignments
			//
			if ( strlen( $currentRecord["U_ID_ASSIGNED"] ) ) {
				$assignedNameParts = array( "C_LASTNAME"=>$row["A_LASTNAME"], "C_MIDDLENAME"=>$row["A_MIDDLENAME"], "C_FIRSTNAME"=>$row["A_FIRSTNAME"], "C_EMAILADDRESS"=>$row["A_EMAILADDRESS"] );
				$currentRecord["U_ID_ASSIGNED"] = getArrUserName($assignedNameParts, true);
			} else
				$currentRecord["U_ID_ASSIGNED"] = ( $print ) ? $UNASSIGNED_PRINT : $UNASSIGNED;

			if ( strlen( $currentRecord["U_ID_SENDER"] ) ) {
				$assignedNameParts = array( "C_LASTNAME"=>$row["S_LASTNAME"], "C_MIDDLENAME"=>$row["S_MIDDLENAME"], "C_FIRSTNAME"=>$row["S_FIRSTNAME"], "C_EMAILADDRESS"=>$row["S_EMAILADDRESS"] );
				$currentRecord["U_ID_SENDER"] = getArrUserName($assignedNameParts, true);
			} else
				$currentRecord["U_ID_SENDER"] = ( $print ) ? $UNASSIGNED_PRINT : $UNASSIGNED;

			if ( strlen( $currentRecord["U_ID_AUTHOR"] ) ) {
				$assignedNameParts = array( "C_LASTNAME"=>$row["AU_LASTNAME"], "C_MIDDLENAME"=>$row["AU_MIDDLENAME"], "C_FIRSTNAME"=>$row["AU_FIRSTNAME"], "C_EMAILADDRESS"=>$row["AU_EMAILADDRESS"] );
				$currentRecord["U_ID_AUTHOR"] = getArrUserName($assignedNameParts, true);
			} else
				$currentRecord["U_ID_AUTHOR"] = ( $print ) ? $UNASSIGNED_PRINT : $UNASSIGNED;

			$issueList[] = $currentRecord;
			$counter++;
		}
//				$currentRecord["TRANSITION_LOG"]["ITL_COMMENTFLAG"] = $issueTransitions[$currentRecord["I_ID"]][0]['ITL_COMMENTFLAG'];
//				$row['ITL_COMMENTFLAG'] = $row['ITL_COMMNT'];
//print'<pre>';print_r($issueList);die;
		
		@db_free_result( $qr );

		$recordsAdded = $counter;

		return null;
	}

	//
	// Issue List View functions
	//

	function it_initIssueListViewData()
	//
	// Assigns parameters of issue list view mode to default values
	//
	//		Returns filled array with fields
	//			WORKVIEW, SHOWSTATUS, SHOWSENDER, SHOWASSIGNEE, DISPLAYSENDLINKS, RESTRICTDESCRIPTION, DESCLENGTH
	//
	{
		global $it_list_columns_names;

		$result = array();

		$result[IT_LV_WORKVIEW] = 0;
		$result[IT_LV_SHOWSTATUS] = 1;
		$result[IT_LV_SHOWSENDER] = 0;
		$result[IT_LV_SHOWASSIGNEE] = 1;
		$result[IT_LV_DISPLAYSENDLINKS] = 1;
		$result[IT_LV_RESTRICTDESCRIPTION] = 1;
		$result[IT_LV_DESCLENGTH] = 1500;
		$result[IT_LV_COLUMNS] = array_keys($it_list_columns_names);
		$result[IT_LV_RPP] = 30;
		$result[IT_LV_DISPLAYISSUEHISTORY] = 0;

		return $result;
	}

	function it_loadIssueListViewData( $U_ID, $kernelStrings )
	//
	// Loads settings of issue list view mode from user settings
	//
	//	Parameters:
	//		$U_ID - user identifier
	//		$kernelStrings - Kernel localization strings
	//
	//	Returns an array containing settings of issue list view mode, or PEAR_Error
	//
	{
		global $IT_APP_ID;
		global $it_list_columns_names;

		$settings = getAppUserSettings($U_ID, $IT_APP_ID);

		$result[IT_LV_WORKVIEW] = isset($settings[IT_LV_WORKVIEW]) ? $settings[IT_LV_WORKVIEW] : 0;
		$result[IT_LV_SHOWSTATUS] = isset($settings[IT_LV_SHOWSTATUS]) ? $settings[IT_LV_SHOWSTATUS] : 1;
		$result[IT_LV_SHOWSENDER] = isset($settings[IT_LV_SHOWSENDER]) ? $settings[IT_LV_SHOWSENDER] : 1;
		$result[IT_LV_SHOWASSIGNEE] = isset($settings[IT_LV_SHOWASSIGNEE]) ? $settings[IT_LV_SHOWASSIGNEE] : 1;
		$result[IT_LV_DISPLAYSENDLINKS] = $settings[IT_LV_DISPLAYSENDLINKS];
		$result[IT_LV_RESTRICTDESCRIPTION] = $settings[IT_LV_RESTRICTDESCRIPTION];
		$result[IT_LV_DESCLENGTH] = $settings[IT_LV_DESCLENGTH];
		$result[IT_LV_DISPLAYISSUEHISTORY] = $settings[IT_LV_DISPLAYISSUEHISTORY];

		$result[IT_LV_RPP] = $settings[IT_LV_RPP];

		if ( !strlen($result[IT_LV_RPP]) )
			$result[IT_LV_RPP] = 30;
		
		// Added by timur-kar (don't know real value)
		if ( !strlen($result[IT_LV_DESCLENGTH]) )
			$result[IT_LV_DESCLENGTH] = "";

		$result[IT_LV_COLUMNS] = $settings[IT_LV_COLUMNS];
		if ( strlen($result[IT_LV_COLUMNS]) )
			$result[IT_LV_COLUMNS] = unserialize( base64_decode($result[IT_LV_COLUMNS]) );
		else
			$result[IT_LV_COLUMNS] = array_keys( $it_list_columns_names );

		return $result;
	}

	function it_saveIssueListViewData( $U_ID, $listViewData, $kernelStrings )
	//
	// Saves settings of issue list view mode in user settings
	//
	//	Parameters:
	//		$U_ID - user identifier
	//		$listViewData - an array containing settings of issue list view mode
	//		$kernelStrings - Kernel localization strings
	//
	//	Returns null, or PEAR_Error
	//
	{
		global $IT_APP_ID;
		
		$listViewData = rescueElement( $listViewData, IT_LV_WORKVIEW, 0 );
		$listViewData = rescueElement( $listViewData, IT_LV_SHOWSTATUS, 0 );
		$listViewData = rescueElement( $listViewData, IT_LV_SHOWSENDER, 0 );
		$listViewData = rescueElement( $listViewData, IT_LV_SHOWASSIGNEE, 0 );
		$listViewData = rescueElement( $listViewData, IT_LV_DISPLAYSENDLINKS, 0 );
		$listViewData = rescueElement( $listViewData, IT_LV_RESTRICTDESCRIPTION, 0 );
		$listViewData = rescueElement( $listViewData, IT_LV_DISPLAYISSUEHISTORY, 0 );		
		
		foreach ($listViewData as $name => $value)
		{
			setAppUserCommonValue($IT_APP_ID, $U_ID, $name, $value);
		}
		
		return null;
	}

	function it_setDisplayHistoryFlag( $U_ID, $value, $kernelStrings )
	//
	// Sets show issue history flag
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$value - flag value
	//		$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $IT_APP_ID;

		setAppUserCommonValue($IT_APP_ID, $U_ID, IT_LV_DISPLAYISSUEHISTORY, $value, $kernelStrings);

	}

	//
	// Copy/Move issues
	//

	function it_getMissedStatusList( $P_ID, $PW_ID, $issueList, $kernelStrings )
	//
	// Returns a list of workflow statuses which exists
	//		in workflows of issueList list and doesn't exists in PW_ID work
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$issueList - list of issues
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array of missed status names or PEAR_Error
	//
	{
		// Load destination task status list
		//
		$destStatuses = it_listWorkTransitions( $P_ID, $PW_ID, $kernelStrings );
		if ( PEAR::isError($destStatuses) )
			return $destStatuses;

		$destStatuses = array_keys($destStatuses);

		// Load issue list status list
		//
		global $qr_it_select_issue;

		$srcStatuses = array();
		foreach ( $issueList as $key=>$I_ID ) {
			$issueData = db_query_result( $qr_it_select_issue, DB_ARRAY, array('I_ID'=>$I_ID) );
			if ( PEAR::isError($issueData) )
				return $issueData;

			$srcStatuses[$issueData['I_STATUSCURRENT']] = 1;
		}

		$srcStatuses = array_keys( $srcStatuses );

		// Find out and return missed statuses
		//
		return array_diff( $srcStatuses, $destStatuses );
	}

	function it_makeAssignment( $U_ID, $P_ID, $PW_ID )
	//
	// Creates task assignment if it doesn't exists
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$P_ID - destination project identifier
	//			$PW_ID - destination work identifier
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_select_issue_assignments;
		global $qr_it_insertworkassignment;

		if ( !strlen($U_ID) )
			return null;

		$qr = db_query( $qr_it_select_issue_assignments, array( 'P_ID'=>$P_ID, 'PW_ID'=>$PW_ID ) );
		if ( PEAR::isError($qr) )
			return $qr;

		$found = false;
		while ( $row = db_fetch_array($qr) )
			if ( $U_ID == $row['U_ID'] ) {
				$found = true;
				break;
			}

		db_free_result($qr);

		if ( $found )
			return null;

		$params = array();
		$params['P_ID'] = $P_ID;
		$params['PW_ID'] = $PW_ID;
		$params['U_ID'] = $U_ID;

		$res = db_query( $qr_it_insertworkassignment, $params );
		if ( PEAR::isError($res) )
			return $res;

		return null;
	}

	function it_copyMoveIssues( $P_ID, $PW_ID, $issueList, $replacements, $operation, $kernelStrings )
	//
	// Copies or moves issues to another work
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$PW_ID - work identifier
	//			$issueList - list of issues
	//			$replacements - list of missed status replacements
	//			$operation - operation to perform - IT_OPERATION_COPY, IT_OPERATION_MOVE
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $qr_it_select_issue;
		global $qr_it_maxissueid;
		global $qr_it_maxissuenum;
		global $qr_it_add_issue_full;
		global $qr_it_maxitlid;
		global $qr_it_selecttransitionlog;
		global $qr_it_insertitl;
		global $qr_it_moveissue;
		global $qr_it_maxitlid;
		global $qr_it_insertitl;
		global $qr_it_deleteissue;
		global $IT_APP_ID;

		$QuotaManager = new DiskQuotaManager();

		$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
		if ( PEAR::isError($TotalUsedSpace) )
			return $TotalUsedSpace;

		foreach ( $issueList as $I_ID ) {
			// Load issue data
			//
			$issueData = db_query_result( $qr_it_select_issue, DB_ARRAY, array('I_ID'=>$I_ID) );
			if ( PEAR::isError($issueData) ) {
				$QuotaManager->Flush( $kernelStrings );
				return $issueData;
			}

			if ( $operation == IT_OPERATION_MOVE )
				if ( $issueData['P_ID'] == $P_ID && $issueData['PW_ID'] == $PW_ID )
					continue;

			$srcP_ID = $issueData["P_ID"];
			$srcPW_ID = $issueData["PW_ID"];

			$issueStatus = $issueData['I_STATUSCURRENT'];
			$replaced = false;
			if ( array_key_exists($issueStatus, $replacements) ) {
				$replaced = true;
				$issueData['I_STATUSCURRENT'] = $replacements[$issueStatus];
			}

			if ( $operation == IT_OPERATION_COPY ) {
				// Copy issue
				//
				$newI_ID = db_query_result( $qr_it_maxissueid );
				if ( PEAR::isError( $newI_ID ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return $newI_ID;
				}

				$newI_ID = incID( $newI_ID );
				$targetID = $newI_ID;

				$params = array();
				$params['P_ID'] = $P_ID;
				$params['PW_ID'] = $PW_ID;
				$I_NUM = db_query_result( $qr_it_maxissuenum, DB_FIRST, $params );
				if ( PEAR::isError( $I_NUM ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return $I_NUM;
				}

				$I_NUM = incID( $I_NUM );

				$issueData["I_ID"] = $newI_ID;
				$issueData["I_NUM"] = $I_NUM;
				$issueData["P_ID"] = $P_ID;
				$issueData["PW_ID"] = $PW_ID;

				$res = exec_sql( $qr_it_add_issue_full, $issueData, $outputList, false );
				if ( PEAR::isError( $res ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return $res;
				}

				// Copy issue log
				//
				$qr = db_query( $qr_it_selecttransitionlog, array( 'I_ID'=>$I_ID ) );
				while ( $itl_record = db_fetch_array($qr) ) {
					$itl_record['I_ID'] = $newI_ID;

					$ITL_ID = db_query_result( $qr_it_maxitlid, DB_FIRST, $itl_record );
					if ( PEAR::isError( $ITL_ID ) ) {
						$QuotaManager->Flush( $kernelStrings );
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
					}

					$ITL_ID = incID( $ITL_ID );
					$itl_record['ITL_ID'] = $ITL_ID;

					$res = db_query( $qr_it_insertitl, $itl_record );
					if ( PEAR::isError($res) ) {
						$QuotaManager->Flush( $kernelStrings );
						return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
					}
				}
				db_free_result( $qr );
			} else {
				// Move Issue
				//
				$params = array();
				$params['P_ID'] = $P_ID;
				$params['PW_ID'] = $PW_ID;
				$I_NUM = db_query_result( $qr_it_maxissuenum, DB_FIRST, $params );
				if ( PEAR::isError( $I_NUM ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return $I_NUM;
				}

				$I_NUM = incID( $I_NUM );

				$params = array();
				$params['I_ID'] = $I_ID;
				$params['P_ID'] = $P_ID;
				$params['PW_ID'] = $PW_ID;
				$params['I_NUM'] = $I_NUM;
				$params['I_STATUSCURRENT'] = $issueData['I_STATUSCURRENT'];
				$targetID = $I_ID;

				$res = db_query( $qr_it_moveissue, $params );
				if ( PEAR::isError($res) ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				$newI_ID = $I_ID;
			}

			// Copy attached files
			//
			$attachmentsPath = it_getIssueAttachmentsDir( $srcP_ID, $srcPW_ID, $I_ID );
			$newPath = it_getIssueAttachmentsDir( $P_ID, $PW_ID, $newI_ID );

			$res = @forceDirPath( $newPath, $fdError );
			if ( !$res ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $kernelStrings[ERR_CREATEDIRECTORY] );
			}

			@rmdir( $newPath );

			if ( file_exists($attachmentsPath) ) {

				if ( $operation != IT_OPERATION_MOVE ) {
					$fileCount = 0;
					$totalSize = 0;
					dirInfo( $attachmentsPath, $fileCount, $totalSize );

					$TotalUsedSpace += $QuotaManager->GetSpaceUsageAdded();

					// Check if the user disk space quota is not exceeded
					//
					if ( $QuotaManager->SystemQuotaExceeded($TotalUsedSpace + $totalSize) ) {
						db_query( $qr_it_deleteissue, array('I_ID'=>$newI_ID) );
						$QuotaManager->Flush( $kernelStrings );
						return $QuotaManager->ThrowNoSpaceError( $kernelStrings );
					}
				}

				@copyDir( $attachmentsPath, $newPath );

				if ( $operation != IT_OPERATION_MOVE ) {
					$QuotaManager->AddDiskUsageRecord( SYS_USER_ID, $IT_APP_ID, $totalSize );
				}
			}

			if ( $operation == IT_OPERATION_MOVE )
				removeDir($attachmentsPath);

			// Make log record in case of status replacement
			//
			if ( $replaced ) {
				$params = array( 'I_ID'=>$targetID );
				$ITL_ID = db_query_result( $qr_it_maxitlid, DB_FIRST, $params );
				if ( PEAR::isError( $ITL_ID ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}

				$ITL_ID = incID( $ITL_ID );

				$itldata = $issueData;
				$itldata["I_ID"] = $targetID;
				$itldata["ITL_ID"] = $ITL_ID;
				$itldata["ITL_DESC"] = null;
				$itldata["ITL_ATTACHMENT"] = null;
				$itldata["ITL_DATETIME"] = convertToSqlDateTime( time() );
				$itldata["ITL_ISRETURN"] = 0;
				$itldata["ITL_STATUS"] = $issueData['I_STATUSCURRENT'];

				$res = exec_sql( $qr_it_insertitl, $itldata, $outputlist, false);
				if ( PEAR::isError( $res ) ) {
					$QuotaManager->Flush( $kernelStrings );
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
				}
			}

			// Prepare assignments
			//
			$res = it_makeAssignment( $issueData['U_ID_ASSIGNED'], $P_ID, $PW_ID );
			if ( PEAR::isError( $res ) ) {
				$QuotaManager->Flush( $kernelStrings );
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
			}
		}

		$QuotaManager->Flush( $kernelStrings );

		return null;
	}

	function it_deleteMultiIssues( $P_ID, $kernelStrings, $itStrings, $U_ID, $issueList, $mode )
	//
	// Deletes multiple issues
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$U_ID - user identifier
	//			$issueList - list of issues
	//			$mode - delete mode - 0 - delete with rights checking, 1 - delete everything
	//
	//		Return values:
	//				1 = Workflow settings do not allow to delete some of selected issues.
	//				2 = Some of selected issues could not be deleted.
	//				null = no errors ocurried
	//
	{
		global $qr_it_select_issue;
		global $PMRightsManager;

		$status = null;

		$projmanData = it_getProjManData( $P_ID );
		if ( PEAR::isError($projmanData) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$userIsProjman = !is_null($projmanData) && is_array($projmanData) && ( $projmanData["U_ID_MANAGER"] == $U_ID || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($U_ID, $P_ID, $kernelStrings ), array( UR_TREE_FOLDER, UR_TREE_WRITE ) ) );

		foreach ( $issueList as $I_ID ) {
			$res = it_deleteIssue( $I_ID, $kernelStrings, $itStrings, $U_ID, $mode );
			if ( PEAR::isError($res) ) {
				if ( $res->getCode() < ERRCODE_APPLICATION_ERR )
					return $res;
				else
					$status = 1; // Some of selected issues could not be deleted
			}
		}

		if ( $status )
			if ( $userIsProjman )
				return 1;
			else
				return 2;

		return null;
	}

	function it_getProjectDefaultTask( $U_ID, $P_ID )
	//
	// Loads default task ID for project
	//
	//		Parameters:
	//			$U_ID - user identidier
	//			$P_ID - project identifier
	//
	//		Returns task identifier or null
	//
	{
		global $IT_APP_ID;
		global $readOnly;

		$defWorks = getAppUserCommonValue( $IT_APP_ID, $U_ID, 'defaultNewTask', null, $readOnly );
		if ( strlen($defWorks) )
			$defWorks = unserialize( base64_decode($defWorks) );
		else
			$defWorks = array();

		if ( array_key_exists($P_ID, $defWorks) )
			return $defWorks[$P_ID];
		else
			return null;
	}

	function it_setProjectDefaultTask( $U_ID, $P_ID, $PW_ID, $kernelStrings )
	//
	// Saves default task ID for project
	//
	//		Parameters:
	//			$U_ID - user identidier
	//			$P_ID - project identifier
	//			$PW_ID - task identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns null
	//
	{
		global $IT_APP_ID;
		global $readOnly;

		$defWorks = getAppUserCommonValue( $IT_APP_ID, $U_ID, 'defaultNewTask', null, $readOnly );
		if ( strlen($defWorks) )
			$defWorks = unserialize( base64_decode($defWorks) );
		else
			$defWorks = array();

		$defWorks[$P_ID] = $PW_ID;

		$defWorks = base64_encode( serialize($defWorks) );
		setAppUserCommonValue( $IT_APP_ID, $U_ID, 'defaultNewTask', $defWorks, $kernelStrings, $readOnly );

		return null;
	}

	function it_getIssuesTask( $issueList, $kernelStrings, $itStrings, &$P_ID )
	//
	// Determintes a task to which issues belongs
	//
	//		Parameters:
	//			$issueList - array of issue IDs
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localiazation strings
	//			$P_ID - project identifier
	//
	//		Returns task identifier or PEAR_Error
	//
	{
		global $qr_it_select_issue;

		$PW_ID = null;
		$P_ID = null;

		foreach ( $issueList as $I_ID ) {
			$issueData = db_query_result( $qr_it_select_issue, DB_ARRAY, array('I_ID'=>$I_ID) );
			if ( PEAR::isError($issueData) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

			if ( !is_null($PW_ID) && ($PW_ID != $issueData['PW_ID'] || $P_ID != $issueData['P_ID']) )
				return PEAR::raiseError( $itStrings['fwi_difftasks_message'], ERRCODE_APPLICATION_ERR );

			$PW_ID = $issueData['PW_ID'];
			$P_ID = $issueData['P_ID'];
		}

		return $PW_ID;
	}

	function it_senMultiIssueStatus( $issueList, $itldata, $kernelStrings, $itStrings, $U_ID, $ITSData, $STATUS, $action = ACTION_EDIT )
	//
	// Transfers multiple issues to new state. Adds record to IssueTransitionLog
	//
	//		Parameters:
	//			$issueList = list of issue identifiers
	//			$itldata - an array containing data for ISSUETRANSITIONLOG
	//			$kernelStrings - Kernel localization strings
	//			$itStrings - Issue Tracking localization strings
	//			$U_ID - user identifier
	//			$ITSData - information about state from ISSUETRANSITIONSCHEMA, the result of function it_loadITSData()
	//			$STATUS - destination status
	//			$action - action type
	//
	//		Returns array(I_ID=>ITL_ID), or PEAR_Error
	//
	{
		$result = array();

		foreach( $issueList as $I_ID ) {
				$itldata['I_ID'] = $I_ID;

				$res = it_setIssueStatus( $itldata, $kernelStrings, $itStrings, $U_ID, $ITSData, $STATUS, $action );
				if ( PEAR::isError($res) )
					return $res;

				if ( is_null($res) )
					continue;

				$result[$I_ID] = $res;
		}

		return $result;
	}

	function it_listProjectWorks( $P_ID, $kernelStrings )
	//
	// Returns list of project works
	//
	//		Parameters:
	//			$P_ID - project identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array or PEAR_Error
	//
	{
		global $qr_it_select_project_works_ordered;
		
		if (PM_DISABLED) {
			return array (it_getFreeWorkData());
		}

		$qr = db_query( $qr_it_select_project_works_ordered, array( 'P_ID'=>$P_ID ) );
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		$result = array();
		while ( $row = db_fetch_array($qr) )
			$result[$row['PW_ID']] = $row;

		db_free_result($qr);

		return $result;
	}

	//
	// Event handlers
	//

	function it_onDeleteUser( $params )
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
		require $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$vars = get_defined_vars();
				saveVariables( $vars, "qr_it" );

				$res = db_query_result( $qr_it_userIssueAssigned, DB_ARRAY, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( sprintf($itStrings['app_delassignee_message'], $res["P_DESC"], $res["PW_ID"], $res["I_NUM"]), ERRCODE_APPLICATION_ERR );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				// Clear user IT dependences
				//
				global $qr_it_delete_user_schema_dependences;
				global $qr_it_delete_user_templateschema_dependences;

				$res = db_query( $qr_it_delete_user_schema_dependences, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_templateschema_dependences, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return null;
			}
		}
	}

	function it_onRemoveUser( $params )
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
		require $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$vars = get_defined_vars();
				saveVariables( $vars, "qr_it" );

				$res = db_query_result( $qr_it_userIssueAssigned, DB_ARRAY, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( sprintf($itStrings['app_delassignee_message'], $res["P_DESC"], $res["PW_ID"], $res["I_NUM"]), ERRCODE_APPLICATION_ERR );

				$res = db_query_result( $qr_it_userIssueSender, DB_ARRAY, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( sprintf($itStrings['app_delsender_message'], $res["P_DESC"], $res["PW_ID"], $res["I_NUM"]), ERRCODE_APPLICATION_ERR );

				$res = db_query_result( $qr_it_userTransitionAssigned, DB_ARRAY, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( sprintf($itStrings['app_delworkflowuser_message'], $res["P_DESC"], $res["PW_ID"], $res["ITS_STATUS"]), ERRCODE_APPLICATION_ERR );

				$res = db_query_result( $qr_it_userIssueLogAssigned, DB_ARRAY, array("ASSIGNED"=>$U_ID, "SENDER"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( sprintf($itStrings['app_delitluser_message'], $res["P_DESC"], $res["PW_ID"], $res["I_STATUSCURRENT"]), ERRCODE_APPLICATION_ERR );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				// Clear user IT dependences
				//
				global $qr_it_delete_user_schema_dependences;
				global $qr_it_delete_user_templateschema_dependences;
				global $qr_it_delete_user_filters;
				global $qr_it_delete_user_sender_assignments;
				global $qr_it_delete_user_assignee_assignments;
				global $qr_it_delete_user_log_assignments;
				global $qr_it_delete_user_log_sender_assignments;

				$res = db_query( $qr_it_delete_user_schema_dependences, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_templateschema_dependences, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_filters, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_sender_assignments, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_assignee_assignments, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_log_assignments, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				$res = db_query( $qr_it_delete_user_log_sender_assignments, array("U_ID"=>$U_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return null;
			}
		}
	}

	function it_onNewWork( $params )
	//
	// Handler of application PM onNewWork event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		@include $appScriptPath;

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {

				$vars = get_defined_vars();
				saveVariables( $vars, "qr_it" );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				$ITT_ID = it_getDefaultTemplate($kernelStrings);
				if ( PEAR::isError( $ITT_ID ) )
					return $ITT_ID;

				if ( is_null($ITT_ID) )
					return null;

				$res = it_fillSchemaFromTemplate( $P_ID, $PW_ID, $ITT_ID, $kernelStrings, $itStrings );
				if ( PEAR::isError( $res ) )
					return $res;

				return null;
			}
		}
	}

	function it_onDeleteWork( $params )
	//
	// Handler of application PM onDeleteWork event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call type.
	//
	{
		extract( $params );
		@require $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {

				$res = db_query_result( $qr_it_work_issue_count, DB_FIRST, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
				if ( PEAR::isError( $res ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( $itStrings['app_dellinkedtask_message'], ERRCODE_APPLICATION_ERR );

				$vars = get_defined_vars();
				saveVariables( $vars, "qr_it" );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				// Delete transition schema
				//
				global $qr_it_delete_work_transitions;

				$res = db_query( $qr_it_delete_work_transitions, array( "P_ID"=>$P_ID, "PW_ID"=>$PW_ID ) );
				if ( PEAR::isError( $res ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return null;
			}
		}
	}

	function it_onCloseWork( $params )
	//
	// Handler of application PM onCloseWork event
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

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$vars = get_defined_vars();
				saveVariables( $vars, "qr_it" );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				$res = it_closeWorkIssues( $P_ID, $PW_ID, $PW_ENDDATE, $U_ID, $kernelStrings, $it_loc_str );

				return $res;
			}
		}
	}

	function it_onDeleteAssignment( $params )
	//
	// Handler of application PM onDeleteAssignment event
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

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$res = db_query_result( $qr_it_assignmentIssueAssigned, DB_ARRAY, array("U_ID"=>$U_ID, "P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( sprintf($itStrings['app_delusedassignment_message'], getUserName($U_ID, true), $res["P_DESC"], $res["PW_ID"], $res["I_NUM"]), ERRCODE_APPLICATION_ERR );

				$vars = get_defined_vars();
				saveVariables( $vars, "qr_it" );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {
				global $qr_it_delete_asn_schema_dependences;

				$res = db_query( $qr_it_delete_asn_schema_dependences, array("U_ID"=>$U_ID, "P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
				if ( PEAR::isError($res) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return null;
			}
		}
	}

	function it_onDeleteProject( $params )
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
		@include $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {
				$res = db_query_result( $qr_it_select_proj_issue_count, DB_FIRST, array( "P_ID"=>$P_ID ) );
				if ( PEAR::isError( $res ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				if ( $res )
					return PEAR::raiseError( $itStrings['app_dellinkedproj_message'], ERRCODE_APPLICATION_ERR );

				return EVENT_APPROVED;
			}
			case CT_ACTION : {

				return null;
			}
		}
	}

	function it_onUpdateWorkStartDate( $params )
	//
	// Handler of application PM onUpdateWorkStartDate event
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

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING : {

				return EVENT_APPROVED;
			}
			case CT_ACTION : {

				return null;
			}
		}
	}


	function it_onCompleteRequest( $params )
	//
	// Handler of PM onCompleteRequest event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns string, or null, or PEAR_Error
	//
	{
		extract( $params );
		@include $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		$res = db_query_result( $qr_it_openissuesexists, DB_FIRST, array("P_ID"=>$P_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $res )
			return $itStrings['app_complissuesproj_message'];

		return null;
	}

	function it_onCompleteTaskRequest( $params )
	//
	// Handler of PM onCompleteTaskRequest event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns string, or null, or PEAR_Error
	//
	{
		extract( $params );
		@include $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );

		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		$res = db_query_result( $qr_it_opentaslissuesexists, DB_FIRST, array("P_ID"=>$P_ID, "PW_ID"=>$PW_ID) );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		if ( $res )
			return $itStrings['app_complissuestask_message'];

		return null;
	}
	
	function it_createFreeProject ($kernelStrings) {
		global $qr_it_copyDefaultTransitions;
		
		$queries = array (
			"INSERT INTO CUSTOMER SET C_ID=0, C_NAME='<no customer>', C_STATUS=1",
			"INSERT INTO PROJECT SET C_ID=0, P_ID=0, P_DESC='<not linked to project>', P_STARTDATE=NOW()",
			"INSERT INTO PROJECTWORK SET PW_ID=0, PW_DESC='<not linked to project>', P_ID=0",
			"INSERT INTO WORKASSIGNMENT (P_ID, PW_ID, U_ID) SELECT 0,0,U_ID FROM WBS_USER WHERE U_STATUS=0",
			$qr_it_copyDefaultTransitions
		);
		
		$hasProj = db_query_result("SELECT COUNT(*) AS cnt FROM PROJECT WHERE P_ID=0", DB_FIRST);
		if ($hasProj)
			return false;
		
		$commonRes = true;
		foreach ($queries as $query) {
			$res = db_query($query, array());
			if ( PEAR::isError($res) )
				$commonRes = $res;
				//return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING]);
		}
		return $commonRes;
	}
	
	function it_checkFreeProjectAssignments () {
		return false;
		$qr = db_query( "INSERT INTO WORKASSIGNMENT (P_ID, PW_ID, U_ID) SELECT 0,0,U_ID FROM WBS_USER WHERE U_STATUS=0 AND U_ID NOT IN (SELECT U_ID FROM WORKASSIGNMENT WHERE P_ID=0 AND PW_ID=0);", array());

		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

		return null;		
	}
	
	function it_modWorkAssignments ($P_ID, $PW_ID, $assignments, $kernelStrings) {
		$params = array ("P_ID" => $P_ID, "PW_ID" => $PW_ID);
		$qr = db_query( "DELETE FROM WORKASSIGNMENT WHERE P_ID='!P_ID!' AND PW_ID='!PW_ID!'", $params);
		if ( PEAR::isError($qr) )
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		
		foreach ($assignments as $cId) {
			$params["U_ID"] = $cId;
			$qr = db_query( "INSERT INTO WORKASSIGNMENT (P_ID, PW_ID, U_ID) VALUES ('!P_ID!','!PW_ID!', '!U_ID!')", $params);
			
			if ( PEAR::isError($qr) )
				return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}
		
		return true;
	}
	
	
	function it_getWorkData($P_ID, $PW_ID, $kernelStrings) {
		if (PM_DISABLED)
			return it_getFreeWorkData();
		$workData = db_query_result( "SELECT PW.*, P.P_ENDDATE, P.P_DESC, P.P_ID, P.U_ID_MANAGER, C.C_NAME FROM PROJECTWORK PW, PROJECT P, CUSTOMER C WHERE PW.PW_ID = '!PW_ID!' AND PW.P_ID='!P_ID!' AND P.P_ID = '!P_ID!' AND C.C_ID = P.C_ID", DB_ARRAY, array("P_ID" => $P_ID, "PW_ID" => $PW_ID));
		if ( PEAR::isError($workData) || !$workData) {
			return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );
		}
		return $workData;		
	}
	
	function it_onUninstallProjects($params) {
		extract( $params );
		@require $appScriptPath;
		eval( prepareFileContent( $appScriptPath ) );

		eval( prepareFileContent($appDirPath."/it_queries.php") );
		eval( prepareFileContent($appDirPath."/it_queries_cmn.php") );
		
		global $it_loc_str;
		global $loc_str;

		$itStrings = $it_loc_str[$language];
		$kernelStrings = $loc_str[$language];

		switch ($CALL_TYPE) {
			case CT_APPROVING:
				return EVENT_APPROVED;
			case CT_ACTION : {
				// Delete transition schema
				//
				global $qr_it_deleteProjectsIssues;

				$res = db_query( $qr_it_deleteProjectsIssues, array( ) );
				if ( PEAR::isError( $res ) )
					return PEAR::raiseError( $kernelStrings[ERR_QUERYEXECUTING] );

				return null;
			}
		}
	}
		
		

	function it_onDeleteCurrency( $params )
	//
	// Handler of application AA onDeleteCurrency event
	//
	//		Parameters:
	//			$params - an array containing handler parameters
	//
	//		Returns null, or PEAR_Error, or EVENT_APPROVED, depending on call tyoe.
	//
	{
		extract( $params );

		switch ($CALL_TYPE) {
			case CT_APPROVING : {

				return EVENT_APPROVED;
			}
			case CT_ACTION : {

				return null;
			}
		}
	}

?>