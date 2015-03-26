<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "WL";

	if ( !isset($prevP_ID) )
		$prevP_ID = null;
	if (!isset($screenApp))
		$screenApp = null;

	if ( !isset($projectData) )
		$projectData = null;

	if ( !isset($firstIndex) )
		$firstIndex = 1;

	define( 'USERS_PER_PAGE', 6 );

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";

	pageUserAuthorization( $SCR_ID, $PM_APP_ID, false );
	
	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	$projectsFound = false;

	$btnIndex = getButtonIndex( array(PM_BTN_ADDWORK, PM_BTN_GANTTCHART, 'editprojbtn', 'showGantt', 'showDetails',
										'showAssignments', 'defineInterval', 'hidecompletetasks', 'showcompletetasks',
										'completeprojbtn', 'deleteprojbtn', 'resumeprojbtn', 'completeTasks', 'resumeTasks', 'deleteTasks', 'createddfolder', 'editprojrightsbtn' ), $_POST );

	switch( $btnIndex ) {
		case 0 : {
			$userRights = $PMRightsManager->evaluateUserProjectRights( $currentUser, $projectData["P_ID"], $kernelStrings );

			if ( $userRights < PM_RIGHT_READWRITE ) {
				$errorStr = $pmStrings['pm_invaddtask_message'];

				break;
			}

			if ( $projectData["P_STATUS"] == RS_ACTIVE )
				redirectBrowser( PAGE_PM_ADDMODWORK, array(ACTION=>ACTION_NEW, SORTING_COL=>$sorting, "P_ID"=>base64_encode($projectData["P_ID"]), "firstIndex"=>$firstIndex, "opener"=>PAGE_PM_WORKLIST, "list_action"=>$action) );
			else
				$errorStr = $pmStrings['pm_comlpraddtask_message'];

			break;
		}

		case 1: break;
		case 2 :
				$params = array();
				$params['folder'] = null;
				$params['currentPage'] = null;
				$params['P_ID'] = base64_encode( $projectData["P_ID"] );
				$params[OPENER] = PAGE_PM_WORKLIST;
				$params[ACTION] = PM_ACTION_MODIFY;
				$params[SORTING_COL] = null;

				$projData = pm_getProjectData( $projectData["P_ID"], $kernelStrings );
				if ( !strlen($projData['P_ENDDATE']) )
					redirectBrowser( PAGE_PM_ADDMODPROJECT, $params );
				else
					redirectBrowser( PAGE_PM_REOPENPROJECT, $params );
		case 3 : $projectScreen = PM_SHOW_GANTT; break;
		case 4 : $projectScreen = PM_SHOW_WORKLIST; break;
		case 5 : $projectScreen = PM_SHOW_WORKASSIGNMENTS; break;
		case 6 : redirectBrowser( PAGE_PM_DEFINEINTERVAL, array() );
		case 7 :
				writeUserCommonSetting( $currentUser, 'showCompleteTasks', 0, $kernelStrings );
				break;
		case 8 :
				writeUserCommonSetting( $currentUser, 'showCompleteTasks', 1, $kernelStrings );
				break;
		case 9 :
				// Complete
				//
				redirectBrowser( PAGE_PM_CLOSEPROJECT, array("folder"=>null, OPENER=>PAGE_PM_WORKLIST, ACTION=>PM_ACTION_CLOSE, SORTING_COL=>$sorting, "P_ID"=>base64_encode($projectData["P_ID"]), "currentPage"=>null) );
				break;
		case 10 :
				// Delete
				//
				$res = pm_deleteProject( $projectData, $pmStrings, $language, $kernelStrings );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}
		case 11 :
				// Resume
				//
				$projectData["P_MODIFYUSERNAME"] = getUserName( $currentUser, true );
				$res = pm_reopenProject( prepareArrayToStore($projectData), $pmStrings, $kernelStrings );
				if ( PEAR::isError( $res ) ) {
					$errorStr = $res->getMessage();

					break;
				}
		case 12 :
			// Complete Tasks
			//
			if (!empty($tasks) && is_array($tasks)) {
				foreach ($tasks as $taskId) {
					$workData["P_ID"] = $projectData["P_ID"];
					$workData["PW_ID"] = $taskId;
					
					/*$res = pm_closeWork( prepareArrayToStore($workData), $pmStrings, $kernelStrings, $language, $currentUser );
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();

						break 2;
					}*/
				}
			}
			break;
		case 13 :
			// Resume Tasks
			//
			
			break;
		case 14 :
			// Delete Tasks
			//
			if (!empty($tasks) && is_array($tasks)) {
				foreach ($tasks as $taskId) {
					$workData["P_ID"] = $projectData["P_ID"];
					$workData["PW_ID"] = $taskId;
					
					//$res = pm_deleteWork( prepareArrayToStore($workData), $pmStrings, $language, $kernelStrings, $currentUser );
					$res = pm_deleteWork( prepareArrayToStore($workData), $pmStrings, $language, $kernelStrings);
					
					if ( PEAR::isError( $res ) ) {
						$errorStr = $res->getMessage();
						break 2;
					}
				}
			}
			
			break;
		case 15 :
			// Create DD Folder for project (createddfolder)
			//
			$res = pm_createProjectDDFolder ($currentUser, $projectData, $kernelStrings);			
			if ( PEAR::isError( $res ) ) {
				$errorStr = $res->getMessage();
				break;
			}
			break;
		case 16:
			$params = array();
			$params['folder'] = null;
			$params['currentPage'] = null;
			$params['P_ID'] = base64_encode( $projectData["P_ID"] );
			$params[OPENER] = PAGE_PM_WORKLIST;
			$params[ACTION] = PM_ACTION_MODIFY;
			$params[SORTING_COL] = null;
			$params["pageRights"] = true;

			redirectBrowser( PAGE_PM_ADDMODPROJECT, $params );
			break;
	}

	switch( true ) {
		case ( true ) : {
			
			$userRights = $PMRightsManager->evaluateUserProjectRights( $currentUser, $projectData["P_ID"], $kernelStrings );
			
			// Project menu
			//
			//$canAddProject = checkUserFunctionsRights( $currentUser, $PM_APP_ID, "CANADDPROJECT", $kernelStrings );
			$canManageCustomers = checkUserFunctionsRights( $currentUser, $PM_APP_ID, "CANMANAGECUSTOMERS", $kernelStrings );
			$canProjectList = checkUserFunctionsRights( $currentUser, $PM_APP_ID, "CANPROJECTLIST", $kernelStrings );
			
			$projectMenu = array();
			if ($canProjectList) {
				$projectMenu[$pmStrings['pl_addproj_btn']] = prepareURLStr( PAGE_PM_ADDMODPROJECT, array ("action" => ACTION_NEW ));
				$projectMenu[] = "-";
						
				$projectMenu[$pmStrings['pm_plist_menu']] = prepareURLStr( PAGE_PM_PROJECTLIST, array ());
				$projectMenu[] = "-";			
			}
			if ($canManageCustomers) {
				$projectMenu[$pmStrings['pm_clist_menu']] = prepareURLStr( PAGE_PM_CUSTOMERLIST, array ());
				$projectMenu[] = "-";
			}
			
			
			
			// Task menu
			//
			$taskMenu = array();
			
			//$taskMenu[$pmStrings['pm_completetasks_menu']] = sprintf( $processButtonTemplate, 'completeTasks' ) . "||checkTasksSelected()";
			//$taskMenu[$pmStrings['pm_resumetasks_menu']] = sprintf( $processButtonTemplate, 'resumeTasks' ) . "||checkTasksSelected()";
			
			
			// Reports Menu
			//
			$reportsMenu = array ();
			$canReports = checkUserFunctionsRights( $currentUser, $PM_APP_ID, APP_CANREPORTS_RIGHTS, $kernelStrings );
			
			$reportsMenu[$pmStrings['prs_taskcount_tab']] = prepareURLStr(PAGE_PM_PROJECTSTATISTICS, array ());
			$reportsMenu[$pmStrings['prs_estcost_tab']] = prepareURLStr(PAGE_PM_PROJECTSTATISTICS, array ("actionName" => "showCost"));
			//$reportsMenu[$pmStrings['prs_assignments_tab']] = prepareURLStr(PAGE_PM_PROJECTSTATISTICS, array ("actionName" => "showAssignments"));

			

			//
			// Page implementation
			//

			if ( isset($list_action) )
				$action = $list_action;

			if( !isset($action) || !strlen($action) )
				$action = PM_ACTION_VIEW_WORKLIST;

			if ( !isset($firstIndex) || !$firstIndex || $prevP_ID != $projectData["P_ID"] ) {
				$firstIndex = 1;
			}

			if ( !isset($sorting) || !strlen($sorting) )
				$sorting = "PW_STARTDATE asc";
			else
				$sorting = base64_decode( $sorting );

			$sortClause = $sorting;

			//
			// "Project:" combobox
			//

			$projects_names = array();
			$projects_names_ids = array();
/*
			$qr = db_query( $qr_pm_select_projects_ordered );
			if ( PEAR::isError($qr) ) {
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_PROJECTFIELD] );

				$fatalError = true;
				break;
			}
*/
			$projects = $PMRightsManager->getUserProjects( $currentUser, $kernelStrings );
			if ( PEAR::isError($projects) ) {
				$errorStr = $projects->getMessage();

				$fatalError = true;
				break;
			}

			$closedFound = false;

			foreach ( $projects as $row ) {
				$row = prepareArrayToDisplay( $row, array("P_DESC") );

				if ( strlen($row["P_DESC"]) > PM_MAXPROJECTLENGTH ) {
					$row["P_DESC"] = substr( $row["P_DESC"], 0, PM_MAXPROJECTLENGTH );
					$row["P_DESC"] .= PM_ENDOFTEXT;
				}

				$project_name = implode( ". ", array(strTruncate( $row["C_NAME"], PM_CUST_NAME_LEN ), $row["P_DESC"]) );

				if ( (strlen($row["P_ENDDATE"])) && dateCmp(convertToDisplayDate($row["P_ENDDATE"]), displayDate(time())) <= 0 ) {

					$project_name = $project_name . " (" . $pmStrings['app_completeproj_label'] . ")";

					if ( !$closedFound ) {
						$closedFound = true;
						$projects_names[] = $pmStrings['pm_listseparator_item'];
						$projects_names_ids[] = null;
					}
				}

				$projects_names[] = $project_name;
				$projects_names_ids[] = $row["P_ID"];
			}

			if ( !isset($projects_names) || !count($projects_names) ) {
				$taskMenu[$pmStrings['pm_addtask_menu']] = null;
				$taskMenu[$pmStrings['pm_deletetasks_menu']] = null;
				$taskMenu[] = "-";
				$taskMenu[$pmStrings['pm_importtasks_menu']] = null;
				$taskMenu[$pmStrings['pm_exporttasks_menu']] = null;
				
				$action = PM_ACTION_NO_PROJECT;

				$errorStr = $pmStrings['pm_noprojects_message'];
				//$fatalError = true;
				break;
			}

			//
			// "Show:" combobox
			//

			$project_screens = array();
			$project_screens_ids = array();

			$project_screens_ids[] = PM_SHOW_GANTT;
			$project_screens_ids[] = PM_SHOW_WORKLIST;
			$project_screens_ids[] = PM_SHOW_WORKASSIGNMENTS;

			if ( isset($projectData["P_ID"]) || isset($P_ID) ) {
				if ( isset($P_ID) && !isset($projectData["P_ID"]) )
					$projectData["P_ID"] = base64_decode($P_ID);
			} else
				$projectData = array();

			if ( !isset($projectScreen) )
				$projectScreen = null;

			$projectData = pm_writeUserPMSetting( $currentUser, $projectData, $kernelStrings, $pmStrings, $projects_names_ids, $project_screens_ids, $projectScreen );
			if ( PEAR::isError($projectData) ) {
				$errorStr  = $projectData->getMessage();

				$fatalError = true;
				break;
			}

			$userRights = $PMRightsManager->evaluateUserProjectRights( $currentUser, $projectData["P_ID"], $kernelStrings );
			if ( PEAR::isError($userRights) ) {
				$errorStr  = $userRights->getMessage();

				$fatalError = true;
				break;
			}
			
			/*$works_count = db_query_result( $qr_pm_count_project_works, DB_FIRST, $projectData );
			if ( PEAR::isError($works_count) )
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );
			*/
			$confirmation_string = pm_makeConfirmationString( $works_count, $language, $pmStrings );

			$isManager = $PMRightsManager->getProjectManager( $projectData["P_ID"]) == $currentUser;

			if ( !$isManager )
				$rightsStr = $pmStrings['pm_rights_label'].": ".$pmStrings[$pm_accessRightsShortNames[$userRights]]." - ".$pmStrings[$pm_accessRightsLongNames[$userRights]];
			else
				$rightsStr = $pmStrings['pm_rights_label'].": ".$pmStrings['pm_manager_label'];
			
			$showEditBtn = $isManager || $userRights >= PM_RIGHT_READWRITEFOLDER;
			
			$showComplete = readUserCommonSetting( $currentUser, 'showCompleteTasks' );
			if ( !strlen($showComplete) )
				$showComplete = 1;
			
			
			if (!$projects_names || (!$isManager && $userRights < PM_RIGHT_READWRITE )) {
				$taskMenu[$pmStrings['pm_addtask_menu']] = "";  
				$taskMenu[$pmStrings['pm_deletetasks_menu']] = "";
				$taskMenu[] = "-";
				$taskMenu[$pmStrings['pm_importtasks_menu']] = "";
				$taskMenu[$pmStrings['pm_exporttasks_menu']] = "#||exportWorks();";
			} else {
				$taskMenu[$pmStrings['pm_addtask_menu']] = "#||addWork(this)";  //sprintf( $processButtonTemplate, 'addwbtn' ); // TODO: add onClick=checkAddWork
				$taskMenu[$pmStrings['pm_deletetasks_menu']] = "#||deleteWorks();";//sprintf( $processButtonTemplate, 'deleteTasks' ) . "||confirmTaskDelete()";
				$taskMenu[] = "-";
				$taskMenu[$pmStrings['pm_importtasks_menu']] = prepareURLStr(PAGE_PM_IMPORT_TASKS, array('project_id' => $projectData["P_ID"]));
				$taskMenu[$pmStrings['pm_exporttasks_menu']] = "#||exportWorks();";
			}
			
			/*switch( $projectData["SCREEN"] ) {
				case( PM_SHOW_WORKLIST ) : {
					$project_works = array();

					$qr = db_query( sprintf( $qr_pm_select_project_works_ordered, $sortClause ), $projectData );
					if ( PEAR::isError($qr) ) {
						$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

						$fatalError = true;
						break;
					}

					$totalCost = array();
					while ( $row = db_fetch_array($qr) ) {
						$curRecord = prepareArrayToDisplay( $row );

						$curRecord["PW_STARTDATE"] = convertToDisplayDate( $curRecord["PW_STARTDATE"] );
						$curRecord["PW_DUEDATE"] = convertToDisplayDate( $curRecord["PW_DUEDATE"] );
						$curRecord["PW_ENDDATE"] = convertToDisplayDate( $curRecord["PW_ENDDATE"] );

						$curRecord["PW_STATUS"] = ( (dateCmp($curRecord["PW_DUEDATE"], displayDate(time())) < 0 && !strlen($curRecord["PW_ENDDATE"]) && strlen($curRecord["PW_DUEDATE"])) || (dateCmp($curRecord["PW_DUEDATE"], $curRecord["PW_ENDDATE"]) < 0) ) ? RS_DELETED : RS_ACTIVE;

						if ( strlen($curRecord["PW_COSTESTIMATE"]) ) {
							if ( !array_key_exists( $curRecord["PW_COSTCUR"], $totalCost ) )
								$totalCost[$curRecord["PW_COSTCUR"]] = 0;
							$totalCost[$curRecord["PW_COSTCUR"]] += $curRecord["PW_COSTESTIMATE"];

							$curRecord["PW_COSTESTIMATE"] = formatFloat( $curRecord["PW_COSTESTIMATE"], 2, "." );
							$curRecord["PW_COSTESTIMATE"] = implode( " ", array($curRecord["PW_COSTESTIMATE"], $curRecord["PW_COSTCUR"]) );
						} else
							$curRecord["PW_COSTESTIMATE"] = null;

						$curRecord["ROW_URL"] = prepareURLStr( PAGE_PM_ADDMODWORK, array(ACTION=>PM_ACTION_MODIFY,  SORTING_COL=>base64_encode($sorting), "P_ID"=>base64_encode($curRecord["P_ID"]), "PW_ID"=>base64_encode($curRecord["PW_ID"]), "opener"=>PAGE_PM_WORKLIST, "list_action"=>$action) );

						$project_works[count($project_works)] = $curRecord;
					}
					foreach ( $totalCost as $cur=>$value )
						$totalCost[$cur]  = "<nobr>".implode( " ", array($value, $cur) )."</nobr>";

					$totalCost = implode( "<br>", $totalCost );

					@db_free_result( $qr );

					$params = array();
					$params['sorting'] = base64_encode($sortClause);
					$params['P_ID'] = base64_encode($projectData["P_ID"]);
					
					break;
				}
				case( PM_SHOW_WORKASSIGNMENTS ) : {
					$users = array();

					$qr = db_query( $qr_pm_select_work_asgnlist, $projectData );
					if ( PEAR::isError($qr) ) {
						$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

						$fatalError = true;
						break;
					}

					while ( $row = db_fetch_array($qr) )
						$users[$row['U_ID']] = nl2br(getArrUserName( $row, true ));

					@db_free_result( $qr );

					$userAssignments = array();
					$users = array_unique( $users );
					$users_count = count($users);

					if ( isset($userShift) && strcmp($userShift, $pmStrings['pm_selectuser_item']) ) {
						foreach ( $users as $userNumber => $userValue ) {
							if ( !strcmp($userNumber, $userShift ) )
								break;
							$firstIndex++;
						}
					}

					if ( ($firstIndex > $users_count) || $users_count < (USERS_PER_PAGE + 1) )
						$firstIndex = 1;

					$project_works = array();

					$qr = db_query( $qr_pm_select_project_works, $projectData );
					if ( PEAR::isError($qr) ) {
						$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_WORKFIELD] );

						$fatalError = true;
						break;
					}

					while ( $row = db_fetch_array($qr) ) {
						$curRecord = prepareArrayToDisplay( $row );

						$curRecord["P_ID"] = $projectData["P_ID"];

						$curRecord["ROW_URL"] = prepareURLStr( PAGE_PM_ADDMODWORK, array(ACTION=>PM_ACTION_MODIFY, SORTING_COL=>base64_encode($sorting), "P_ID"=>base64_encode($projectData["P_ID"]), "PW_ID"=>base64_encode($curRecord["PW_ID"]),  "firstIndex"=>$firstIndex, "opener"=>PAGE_PM_WORKLIST, "list_action"=>$action) );

						$work_users = array();

						$work_qr =  db_query( $qr_pm_select_work_asgn, $curRecord );
						if ( PEAR::isError($qr) ) {
							$errorStr = sprintf( $pmStrings[PM_ERR_EXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );

							$fatalError = true;
							break;
						}

						while ( $work_row = db_fetch_array($work_qr) )
							$work_users[count($work_users)] = $work_row["U_ID"];

						@db_free_result($work_qr);

						$work_users_exist = array();

						foreach( $users as $fieldNumber => $fieldValue ) {
							$curU_ID = $fieldNumber;

							if ( !isset($userAssignments[$curU_ID]) )
								$userAssignments[$curU_ID] = 0;

							if ( in_array($fieldNumber, $work_users) ) {
								$work_users_exist[$curU_ID] = PM_EXIST_ID;
								$userAssignments[$curU_ID] ++;
							} else
								$work_users_exist[$curU_ID] = 0;
						}

						$work_users = $work_users_exist;
						$work_users = refinedSlice( $work_users, $firstIndex-1, USERS_PER_PAGE );

						$curRecord["ASGN"] = $work_users;

						$project_works[count($project_works)] = $curRecord;
					}

					@db_free_result($qr);

					$userlist = array();
					$userlist[] = $pmStrings['pm_selectuser_item'];

					$cbUsers = array();
					foreach( $users as $key=>$value )
						$cbUsers[$key] = strip_tags($value);

					$userlist = array_merge( $userlist, $cbUsers );

					$users = refinedSlice( $users, $firstIndex-1, USERS_PER_PAGE );

					$prevIndex = $firstIndex - USERS_PER_PAGE;
					if ( $prevIndex < 1 )
						$prevIndex = 1;

					$nextIndex = $firstIndex + USERS_PER_PAGE;
					if ( $nextIndex > $users_count )
						$nextIndex = $users_count;

					$totalColCount = count( $users ) + 3;
					$userCount = count( $users );

					$nextLink = ( ($firstIndex+USERS_PER_PAGE < $users_count+1) && count($project_works) ) ? prepareURLStr( PAGE_PM_WORKLIST, array(ACTION=>$action, SORTING_COL=>base64_encode($sorting), "P_ID"=>base64_encode($projectData["P_ID"]), "firstIndex"=>$nextIndex) ) : null;
					$prevLink = ( $firstIndex > 1 && count($project_works) ) ? prepareURLStr( PAGE_PM_WORKLIST, array(ACTION=>$action, SORTING_COL=>base64_encode($sorting), "P_ID"=>base64_encode($projectData["P_ID"]), "firstIndex"=>$prevIndex) ) : null;

					if ( $nextLink )
						$totalColCount += 1;
					if ( $prevLink )
						$totalColCount += 1;

					if ( !count($users) )
						$users[] = "&lt;".$pmStrings['pm_noassignemtns_message']."&gt;";

					$params = array();
					$params['sorting'] = base64_encode($sortClause);
					$params['P_ID'] = base64_encode($projectData["P_ID"]);
					$params['firstIndex'] = $firstIndex;

					//$printURL = prepareURLStr( "../../reports/assignments.php", $params );
					$printURL = prepareURLStr( "../../reports/projdetails.php", $params );

					break;
				}
				case PM_SHOW_GANTT :

					// Diagram Intervals
					//
					if ( isset($curGanttInterval) && strlen( $curGanttInterval ) )
						writeUserCommonSetting( $currentUser, 'ganttInterval', $curGanttInterval, $kernelStrings );

					$curGanttInterval = readUserCommonSetting( $currentUser, 'ganttInterval' );
					if ( !strlen($curGanttInterval) )
						$curGanttInterval = GANTT_PROJECT_TOMONTH;

					$intervals = pm_listGanttIntervals( $currentUser, $pmStrings, $kernelStrings );
					if ( $curGanttInterval != -1 && !array_key_exists( $curGanttInterval, $intervals ) )
						$curGanttInterval = GANTT_PROJECT_TOMONTH;

					$project_works = null;
					$ganttSettings = null;
					$res = pm_generateGanttContent( $curGanttInterval, $projectData["P_ID"], $project_works,
												$ganttSettings, $sortClause, $kernelStrings, $pmStrings, $currentUser,
												$showComplete );
					if ( PEAR::isError($res) ) {
						$fatalError = true;
						$errorStr = $res->getMessage();

						break;
					}

					foreach ( $project_works as $key=>$data ) {
						$data["ROW_URL"] = prepareURLStr( PAGE_PM_ADDMODWORK, array(ACTION=>PM_ACTION_MODIFY, SORTING_COL=>base64_encode($sorting), "P_ID"=>base64_encode($data["P_ID"]), "PW_ID"=>base64_encode($data["PW_ID"]),  "firstIndex"=>$firstIndex, "opener"=>PAGE_PM_WORKLIST, "list_action"=>$action) );

						$dates = array();
						$dates[] = sprintf( "%s: %s", $pmStrings['pm_startdate_label'], convertToDisplayDate($data['PW_STARTDATE']) );

						if ( strlen($data['PW_DUEDATE']) )
							$dates[] = sprintf( "%s: %s", $pmStrings['pm_duedate_label'], convertToDisplayDate($data['PW_DUEDATE']) );

						if ( strlen($data['PW_ENDDATE']) )
							$dates[] = sprintf( "%s: %s", $pmStrings['pm_completedate_label'], convertToDisplayDate($data['PW_ENDDATE']) );

						$data['title'] = implode( ", ", $dates );

						$project_works[$key] = $data;
					}

					// Interval menu
					//
					$intervalMenu = array();
					$checked = ($curGanttInterval == GANTT_PROJECT_TOMONTH) ? "checked" : "unchecked";;
					$link = prepareURLStr( PAGE_PM_WORKLIST, array( 'curGanttInterval'=>-1 ) );
					$intervalMenu[$pmStrings['pm_fittoproject_menu']] = $link."||null||$checked";

					foreach ( $intervals as $key=>$data ) {
						$checked = ($curGanttInterval == $key) ? "checked" : "unchecked";;
						$intervalMenu[$data['name']] = prepareURLStr( PAGE_PM_WORKLIST, array( 'curGanttInterval'=>$key ) )."||null||$checked";;
					}

					$intervalMenu['-'] = null;
					$intervalMenu[$pmStrings['pm_definterval_menu']."..."] = sprintf( $processButtonTemplate, 'defineInterval' );

					$intervalMenu[null] = '-';

					if ( $showComplete )
						$intervalMenu[$pmStrings['pm_hidecomplete_menu']] = sprintf( $processButtonTemplate, 'hidecompletetasks' );
					else
						$intervalMenu[$pmStrings['pm_showcomplete_menu']] = sprintf( $processButtonTemplate, 'showcompletetasks' );

					// Print URL
					//
					$params = array();
					$params['P_ID'] = base64_encode($projectData["P_ID"]);
					$printURL = prepareURLStr( "../../reports/projdetails.php", $params );
					//$params['interval'] = base64_encode($curGanttInterval);

					//$printURL = prepareURLStr( sprintf("../../reports/%s", PAGE_PM_GANTT_CHART), $params );
					$printURL = prepareURLStr( "../../reports/projdetails.php", $params );

					break;
			}*/


			$canAddWorks = $userRights >= PM_RIGHT_READWRITE;
			
			// View menu
			//
			$viewMenu = array();

			//$checked = ($projectData["SCREEN"] == PM_SHOW_GANTT) ? "checked" : "unchecked";
			//$viewMenu[] = array( 'caption'=>$pmStrings['pm_gantt_tab'], 'link'=>sprintf( $processButtonTemplate, 'showGantt' )."||$checked" );

			$checked = ($projectData["SCREEN"] != PM_SHOW_WORKASSIGNMENTS) ? "checked" : "unchecked";
			$viewMenu[] = array( 'caption'=>$pmStrings['pm_details_tab'], 'link'=>sprintf( $processButtonTemplate, 'showDetails' )."||$checked" );

			$checked = ($projectData["SCREEN"] == PM_SHOW_WORKASSIGNMENTS) ? "checked" : "unchecked";
			$viewMenu[] = array( 'caption'=>$pmStrings['pm_assignments_tab'], 'link'=>sprintf( $processButtonTemplate, 'showAssignments' )."||$checked" );

			$projectIsComplete = pm_projectIsClosed( $projectData, $kernelStrings );

			$fullProjectAccess = false;
			if ( $isManager || $userRights >= PM_RIGHT_READWRITEFOLDER ) {
				$fullProjectAccess = true;
				$projectMenu[$pmStrings['pm_edit_menu']] = sprintf( $processButtonTemplate, 'editprojbtn' );
				$projectMenu[$pmStrings['pm_editrights_menu']] = sprintf( $processButtonTemplate, 'editprojrightsbtn' );

				if ( !$projectIsComplete )
					$projectMenu[$pmStrings['pm_complete_menu']] = sprintf( $processButtonTemplate, 'completeprojbtn' )."||confirmComplete()";
				else
					$projectMenu[$pmStrings['pm_resume_menu']] = sprintf( $processButtonTemplate, 'resumeprojbtn' )."||confirmResume()";

				$projectMenu[$pmStrings['pm_delete_menu']] = sprintf( $processButtonTemplate, 'deleteprojbtn' )."||confirmDeletion()";
			} else {
				$projectMenu[$pmStrings['pm_edit_menu']] = null;
				$projectMenu[$pmStrings['pm_editrights_menu']] = null;
				if ( !$projectIsComplete )
					$projectMenu[$pmStrings['pm_complete_menu']] = null;
				else
					$projectMenu[$pmStrings['pm_resume_menu']] = null;
				$projectMenu[$pmStrings['pm_delete_menu']] = null;
			}

			// Print URL
			//
			$params = array();
			$params['P_ID'] = base64_encode($projectData["P_ID"]);
			$printURL = prepareURLStr( "../../reports/projdetails.php", $params );
			
			$projectMenu[] = '-';
			$projectMenu[$kernelStrings['app_print_btn']] = $printURL."||null||null||_blank";
			
			if (!checkUserAccessRights( $currentUser, "UNG", "UG", false))
					unset($projectMenu[$pmStrings['pm_editrights_menu']]);
			
			// Load users data starts
			if ( PEAR::isError( $qr = db_query( $qr_pm_select_asgn_list ) ) ) {
				$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );
				$fatalError = true;
				break;
			}

			while( $row = db_fetch_array( $qr ) ) {
				$available_users[$row["U_ID"]] = array ("id" => $row["U_ID"], "name" => getUserName($row["U_ID"]));
			}
			// Load users data finished
		}
		
		
		$currency_ids = listCurrency();
		if ( PEAR::isError($currency_ids) ) {
			$errorStr = $pmStrings['amt_curlisterr_message'];

			$fatalError = true;
			break;
		}

		$currencyList = array ();
		foreach ($currency_ids as $cId => $cCurrency) {
			$currencyList[] = "['" . $cId . "']";
		}
		
		$currenciesListStr = join(",", $currencyList);
	}
	
	$screenEmptyToolbar = false;
	$installedDD = in_array ("DD", $host_applications);
	if (!$installedDD)
		$projectData["DF_ID"] = null;
	if ($screenApp == "DD") {
		if (empty($projectData["DF_ID"])) {
			$screenEmptyToolbar = true;
		}
	}
	
	$installedIT = in_array ("IT", $host_applications);
	if ($installedIT)
		$installedIT = checkUserAccessRights( $currentUser, "IL", "IT", false );
	//print_r($host_applications);
	//exit;
	
	$intervalMenu = array ();
	if ( $showComplete )
		$intervalMenu[$pmStrings['pm_hidecomplete_menu']] = "#||ShowHideCompleteTasks (this)";
	else
		$intervalMenu[$pmStrings['pm_showcomplete_menu']] = "#||ShowHideCompleteTasks (this)";
	
	$checked = ($projectData["SCREEN"] != PM_SHOW_WORKASSIGNMENTS) ? "checked" : "unchecked";
	$intervalMenu[$pmStrings['pm_details_tab']] = sprintf( $processButtonTemplate, 'showDetails' ) . "||null||$checked";;
	$checked = ($projectData["SCREEN"] == PM_SHOW_WORKASSIGNMENTS) ? "checked" : "unchecked";
	$intervalMenu[$pmStrings['pm_assignments_tab']] = sprintf( $processButtonTemplate, 'showAssignments' ) . "||null||$checked";
	
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $PM_APP_ID );

	$preproc->assign( "pmStrings", $pmStrings );
	
	$preproc->assign( PAGE_TITLE, $pmStrings['pm_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_PM_WORKLIST );
	$preproc->assign( ACTION, $action );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( SORTING_COL, $sorting );
	$preproc->assign( "screenApp", $screenApp );
	$preproc->assign( "screenEmptyToolbar", $screenEmptyToolbar );
	$preproc->assign( "installedDD", $installedDD);
	$preproc->assign( "installedIT", $installedIT);
	if (isset($selectedPW_ID))
		$preproc->assign("selectedPW_ID", $selectedPW_ID);
	$preproc->assign( "onWebasystServer", onWebasystServer());
	$preproc->assign( "hasControlAccess", hasAccountInfoAccess($currentUser));
	if ($screenApp) {
		$preproc->assign( "encDF_ID", base64_encode($projectData["DF_ID"]));
	}
	$preproc->assign( "firstIndex", $firstIndex );
	$preproc->assign( HELP_TOPIC, "worklist.htm");

	$preproc->assign( "genericLinkUnsorted", prepareURLStr( PAGE_PM_WORKLIST, array(ACTION=>$action)) );
	if ( isset($printURL) )
		$preproc->assign( "printURL", $printURL );

	if ( !$fatalError ) {
		
		$preproc->assign( "projects_names", $projects_names );
		$preproc->assign( "projects_names_ids", $projects_names_ids );
		//$preproc->assign( "works_count", $works_count );
		$preproc->assign( "confirmation_string", $confirmation_string );

		$preproc->assign( "projectMenu", $projectMenu );
		$preproc->assign( "taskMenu", $taskMenu );
		$preproc->assign( "canReports", $canReports );
		$preproc->assign( "reportsMenu", $reportsMenu );
		$preproc->assign( "rightsStr", $rightsStr );

		$preproc->assign( "project_works", $project_works );
		$preproc->assign( "canAddWorks", $canAddWorks );
		$preproc->assign( "fullProjectAccess", $fullProjectAccess);

		$preproc->assign( "projectData", $projectData );
		$preproc->assign( "isActive", $projectData["P_STATUS"] == RS_ACTIVE );

		$preproc->assign( "prevP_ID", $projectData["P_ID"] );

		$preproc->assign( "showEditBtn", $showEditBtn );

		if ( $projectData["SCREEN"] == PM_SHOW_WORKLIST ) {
			$preproc->assign( "totalCost", $totalCost );
		}

		if ( $projectData["SCREEN"] == PM_SHOW_WORKASSIGNMENTS ) {
			$preproc->assign( "users", $users );

			if( isset($work_users) )
				$preproc->assign( "work_users", $work_users );
			else
				$preproc->assign( "work_users", null );

			$preproc->assign( "userlist", $userlist );
			$preproc->assign( "userlistIDs", array_keys($userlist) );

			$preproc->assign( "showUserSelector", count($userlist) > USERS_PER_PAGE );

			$preproc->assign( "nextLink", $nextLink );
			$preproc->assign( "prevLink", $prevLink );
			$preproc->assign( "totalColCount", $totalColCount+2 );
			$preproc->assign( "userCount", $userCount );
			$preproc->assign( "currencyIds", $currency_ids );
			$preproc->assign( "userAssignments", $userAssignments );
		}

		if ( $projectData["SCREEN"] == PM_SHOW_GANTT ) {
			$preproc->assign( "ganttSettings", $ganttSettings );
		}
		$preproc->assign( "intervalMenu", $intervalMenu );
	}
	
	$preproc->assign ("printURL", $printURL);
	$preproc->assign ("showComplete", $showComplete ? 1 : 0);
	$preproc->assign ("availableUsers", $available_users);
	$preproc->assign( "dateDisplayFormat", DATE_DISPLAY_FORMAT );
	$preproc->assign( "userRights", $userRights );
	$preproc->assign ("projectIsComplete", $projectIsComplete);
	$preproc->assign ("currenciesListStr", $currenciesListStr);
	$preproc->assign ("worksOnPage", PM_WORKS_ON_PAGE);
	$preproc->assign ("currentTimestamp", convertTimestamp2Local( mktime()));
	
	if ( isset($viewMenu) )
		$preproc->assign( "viewMenu", $viewMenu );

	$preproc->display("worklist.htm");
?>
