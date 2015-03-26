<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/IT/it.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";
	$invalidField = null;

	if ( !isset($missedStatusesFound) )
		$missedStatusesFound = false;

	$operation = base64_decode( $op );

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];

	$btnIndex = getButtonIndex( array(BTN_SAVE, 'finishbtn', BTN_CANCEL ), $_POST );

	switch ( $btnIndex ) {
		case 0 : 
					$keyData = explode( "-", $destTask );

					if ( count($keyData) != 2 )
						break;

					$destP_ID = $keyData[0];
					$destPW_ID = $keyData[1];

					$issueList = unserialize( base64_decode( $docList ) );
					$missedStatuses = it_getMissedStatusList( $destP_ID, $destPW_ID, $issueList, $kernelStrings );
					if ( PEAR::isError($missedStatuses) ) {
						$errorStr = $destTransitions->getMessage();

						break;
					}

					if ( count( $missedStatuses ) ) {
						$missedStatusesFound = true;
						$missedStatuses = base64_encode( serialize($missedStatuses) );
						break;
					}

					$issueList = unserialize( base64_decode( $docList ) );
					$res = it_copyMoveIssues( $destP_ID, $destPW_ID, $issueList, array(), $operation, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						break;
					}

					redirectBrowser( PAGE_IT_ISSUELIST, array() );
		case 1 :
					foreach( $replacement as $status_id => $replacement_id ) {
						if ( !strlen($replacement_id) ) {
							$invalidField = $status_id;
							break 2;
						}
					}

					$replacementList = array();
					foreach( $replacement as $status_id => $replacement_id )
						$replacementList[base64_decode($status_id)] = base64_decode($replacement_id);

					$issueList = unserialize( base64_decode( $docList ) );

					$res = it_copyMoveIssues( $destP_ID, $destPW_ID, $issueList, $replacementList, $operation, $kernelStrings );
					if ( PEAR::isError($res) ) {
						$errorStr = $res->getMessage();

						break;
					}

		case 2 : 
					redirectBrowser( PAGE_IT_ISSUELIST, array() );
	}

	switch ( true ) {
		case true :
					// Load project list
					//
					$projects = it_getUserAssignedProjects( $currentUser, $itStrings, IT_DEFAILT_MAX_PROJNAME_LEN, 
																true, IT_DEFAILT_MAX_CUSTNAME_LEN, false, false,
																false, false, false, true );
					if ( PEAR::isError($projects) ) {
						$fatalError = true;
						$errorStr = $kernelStrings[ERR_QUERYEXECUTING];

						break;
					}

					if ( !count($projects) ) {
						$fatalError = true;
						$errorStr = $itStrings['cm_noprojects_message'];

						break;
					}

					//
					// Load source task IDs
					//

					$issueList = unserialize( base64_decode( $docList ) );
					$srcTasks = array();

					foreach ( $issueList as $I_ID ) {
						$res = exec_sql( $qr_it_select_issue, array("I_ID"=>$I_ID), $issuedata, true );
						if ( PEAR::isError( $res ) ) {
							$errorStr = $itStrings[IT_ERR_LOADISSUEDATA];
	
							$fatalError = true;
							break;
						}

						$srcTasks[] = $issuedata["PW_ID"];
					}

					$taskList = array();

					$dummyRecord = array();
					$dummyRecord['NAME'] = sprintf( "&lt;%s&gt;", $itStrings['cm_select_item'] );
					$dummyRecord['TYPE'] = 0;
					$taskList[] = $dummyRecord;

					$isMove = $operation == IT_OPERATION_MOVE;

					foreach( $projects as $key=>$data ) {
						$projRecord = array();
						$projRecord['NAME'] = prepareStrToDisplay($data);
						$projRecord['TYPE'] = 0;

						$projTasks = it_listActiveProjectUserWorks( $key, $currentUser, null, 50 );

						if ( count( $projTasks ) ) {
							$projTasks = $projTasks[$key];
							$tasks = array();
							
							foreach( $projTasks as $task_id=>$task_data ) {
								if ( !$task_data["CLOSED"] && !( $isMove && $key == $P_ID && in_array($task_id, $srcTasks) ) )
									$tasks[$task_id] = $task_data['PW_DESC'];
							}

							if ( count($tasks) ) {
								$taskList[] = $projRecord;
								foreach( $tasks as $task_id=>$task_data ) {
									$taskRecord = array();
									$taskRecord['NAME'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".sprintf( "%s: %s", $task_id, prepareStrToDisplay($task_data) );
									$taskRecord['TYPE'] = 1;
									$ID = sprintf( "%s-%s", $key, $task_id );
									$taskList[$ID] = $taskRecord;
								}
							}
						}

					}

					// Process missed statuses
					//
					if ( $missedStatusesFound ) {
						// Make status keys
						//
						$missedStatuses = unserialize( base64_decode($missedStatuses) );
						$mS = array();
						foreach( $missedStatuses as $status )
							$ms[base64_encode($status)] = $status;

						$missedStatuses = $ms;

						// Load existing statuses
						//
						$destStatuses = it_listWorkTransitions( $destP_ID, $destPW_ID, $kernelStrings, true );
						if ( PEAR::isError($destStatuses) )
							return $destStatuses;

						$destStatus_names = array();
						$destStatus_names = array_keys($destStatuses);

						$destStatus_ids = array();
						foreach( $destStatus_names as $key=>$name )
							$destStatus_ids[] = base64_encode($name);

						$destStatus_names = array_merge( array(sprintf( "&lt;%s&gt;", $itStrings['cm_select_item'] )), $destStatus_names );
						$destStatus_ids = array_merge( array(null), $destStatus_ids );
					}
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$title = ( $operation == IT_OPERATION_COPY ) ? $itStrings['cm_pagecopy_title'] :  $itStrings['cm_pagemove_title'];
	$label = ( $operation == IT_OPERATION_COPY ) ? $itStrings['cm_copytotask_label'] :  $itStrings['cm_movetotask_label'];

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( PAGE_TITLE, $title );
	$preproc->assign( FORM_LINK, PAGE_IT_COPYMOVE );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( INVALID_FIELD, $invalidField );

	if ( !$fatalError ) {
		$preproc->assign( "op", $op );
		$preproc->assign( "docList", $docList );

		$preproc->assign( "label", $label );
		$preproc->assign( "P_ID", $P_ID );

		$preproc->assign( "taskList", $taskList );

		if ( isset($destP_ID) )
			$preproc->assign( "destP_ID", $destP_ID );

		if ( isset($destPW_ID) )
			$preproc->assign( "destPW_ID", $destPW_ID );

		if ( isset($destTask) )
			$preproc->assign( "destTask", $destTask );

		if ( $missedStatusesFound ) {
			$preproc->assign( "missedStatusesFound", $missedStatusesFound );
			$preproc->assign( "missedStatuses_displ", $missedStatuses );
			$preproc->assign( "missedStatuses", base64_encode( serialize($missedStatuses) ) );

			$preproc->assign( "destStatus_names", $destStatus_names );
			$preproc->assign( "destStatus_ids", $destStatus_ids );

			if ( isset($replacement) )
				$preproc->assign( "replacement", $replacement );

		}
	}

	$preproc->display( "copymove.htm" );

?>