<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."kernel/classes/JSON.php" );
	
	require_once( WBS_DIR."/published/IT/it.php" );
	require_once( WBS_DIR."/published/PM/pm.php" );

	//
	// Issue List page script
	//

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "IL";

	pageUserAuthorization( $SCR_ID, $IT_APP_ID, false );

	//
	// Page variables setup
	//
	$issueList = array();

	$kernelStrings = $loc_str[$language];
	$itStrings = $it_loc_str[$language];
	$pmStrings = $pm_loc_str[$language];
	$project_ids = null;
	$project_names= null;
	$work_ids = null;
	$work_names = null;
	$projmanMode = false;
	$assignmentsLink = null;
	$itsLink = null;
	$showAddBtn = false;
	$justExpanded = false;
	$expandedW_ID = null;
	$expandedJID = null;
	$newWorkURL = null;

	$resetPages = false;

	define( "EXPAND", "expand" );
	define( "EXPANDSTATUS", "EXPANDSTATUS" );
	define( "EXPANDLINK", "EXPANDLINK" );
	define( "ROW_TYPE", "ROW_TYPE" );
	define( "ITSSETUPLINK", "ITSSETUPLINK" );

	define( "FILTER_ORGANIZE", -2 );
	define( "APPLY_FILTER", "APPLY_FILTER" );

	define( "HIDE_FILTERS", "hide_filters" );

	define( "COL_CB", "CHECKBOX" );
	define( "GROUP_BY_TASKS", "GBT" );
	define( "GROUP_BY_PROJECT", "GBP" );

	if ( isset($filter) ) {
		it_applyFilter( $currentUser, $filter, $kernelStrings, $itStrings );
		$resetPages = true;
	}

	if ( isset($action) )
		switch ( $action ) {
			case EXPAND :
							$newStatus = ( $curState == IT_WORK_COLLAPSED ) ? IT_WORK_EXPANDED : IT_WORK_COLLAPSED;
							it_saveWorkExpandState( $currentUser, $expP_ID, $expPW_ID, $newStatus, $kernelStrings );

							break;
			case HIDE_FILTERS :
							$foldersHidden = true;
							setAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );
	}

	$btnIndex = getButtonIndex( array("organizeFilters", "viewbtn", "addIssueBtn", "copyissuesbtn", "moveissuesbtn",
										"deleteissuesbtn", "deleteanywaybtn", "sendissuesbtn", "customizewfbtn",
										"printselectedbtn", "setgridmodeview", "setlistmodeview",
										"showFoldersBtn", "remindbtn", "hidehistorybtn", "showhistorybtn" ), $_POST );
	switch ( $btnIndex ) {
		case 0 : {
			redirectBrowser( PAGE_IT_ORGANIZEFILTERS, array("P_ID"=>$P_ID) );

			break;
		}
		case 1 : {
			redirectBrowser( PAGE_IT_ILVIEW, array("P_ID"=>$P_ID) );

			break;
		}
		case 2 :
			$params = array( ACTION=>ACTION_NEW, "P_ID"=>$P_ID );
			if ( isset($curPW_ID) )
				$params['PW_ID'] = $curPW_ID;

			redirectBrowser( PAGE_IT_ADDMODISSUE, $params );

		case 3 :
			if ( !isset($document) )
				break;

			$docList = base64_encode( serialize( array_keys( $document ) ) );
			$params = array( 'P_ID' => $P_ID, 'docList'=>$docList, 'op'=>base64_encode(IT_OPERATION_COPY) );
			redirectBrowser( PAGE_IT_COPYMOVE, $params );

		case 4 :
			if ( !isset($document) )
				break;

			$docList = base64_encode( serialize( array_keys( $document ) ) );
			$params = array( 'P_ID' => $P_ID, 'docList'=>$docList, 'op'=>base64_encode(IT_OPERATION_MOVE) );
			redirectBrowser( PAGE_IT_COPYMOVE, $params );

		case 5 :
			if ( !isset($document) )
				break;

			$docList = array_keys( $document );

			$res = it_deleteMultiIssues( $P_ID, $kernelStrings, $itStrings, $currentUser, $docList, 0 );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				break;
			}

			if ( $res == 1 ) {
				$deleteStatus = 1;
				$savedDocList = base64_encode( serialize( $docList ) );
			} else
				if ( $res == 2 )
					$deleteStatus = 2;

			break;

		case 6:
			$docList = unserialize( base64_decode($savedDocList) );

			$res = it_deleteMultiIssues( $savedP_ID, $kernelStrings, $itStrings, $currentUser, $docList, 1 );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				break;
			}

			break;

		case 7:
			if ( !isset($document) )
				break;

			$document = array_keys( $document );
			$docList = base64_encode( serialize($document) );

			$issueP_ID = null;
			$res = it_getIssuesTask( $document, $kernelStrings, $itStrings, $issueP_ID );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();

				break;
			}

			$URLParams = array("issues"=>$docList, "P_ID"=>$P_ID, "PW_ID"=>$res, OPENER=>PAGE_IT_ISSUELIST );
			redirectBrowser( PAGE_IT_SENDISSUE, $URLParams );

			break;
		case 8:

			redirectBrowser( PAGE_IT_WORKFLOWMANAGER, array("P_ID"=>$P_ID, "SAVEPID"=>1 ) );
		case 9:
			if ( !isset($document) )
				$document = array();

			$document = array_keys( $document );
			$docList = base64_encode( serialize($document) );

			redirectBrowser( PAGE_IT_PRINT, array( "P_ID"=>$P_ID, "issues"=>$docList, "PW_ID"=>$curPW_ID ) );
			break;
		/*case 10:
			setAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_VIEWMODE', IT_LV_GRID, $kernelStrings, $readOnly );

			break;
		case 11:
			setAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_VIEWMODE', IT_LV_LIST, $kernelStrings, $readOnly );

			break;*/
		case 12:
			$foldersHidden = false;
			setAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_FOLDERSHIDDEN', $foldersHidden, $kernelStrings, $readOnly );
			break;
		case 13:
			if ( !isset($document) )
				$document = array();

			$docList = base64_encode( serialize($document) );

			redirectBrowser( PAGE_IT_REMINDER, array( "P_ID"=>$P_ID, "issues"=>$docList ) );
		case 14 :
			// Hide issue history
			//
			it_setDisplayHistoryFlag( $currentUser, 0, $kernelStrings );

			break;
		case 15 :
			// Show issue history
			//
			it_setDisplayHistoryFlag( $currentUser, 1, $kernelStrings );

			break;
	}

	$foldersHidden = getAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_FOLDERSHIDDEN', null, $readOnly );
	
	$issuesCount = 0;
	
	do {
			if ( $fatalError )
				break;

			// Load projects
			//
			$projectList = it_getUserAssignedProjects( $currentUser, $itStrings, 35,
														true, IT_DEFAILT_MAX_CUSTNAME_LEN, false, false, false, true );
			if ( PEAR::isError($projectList) ) {
				$errorStr = $itStrings[IT_ERR_LOADPROJECTS];

				$fatalError = true;
				break;
			}
			
			$P_IDs = array_keys($projectList);
			if (PM_DISABLED) {
				$P_ID = 0;
			} else {
				if (!in_array(0, $P_IDs)) {
					$res = it_createFreeProject($kernelStrings);
					if (PEAR::isError($res)) {
						$errorStr = $res->getMessage ();
						break;
					} elseif ($res == true) {
						// If new project are created - reload projects list
						$projectList = it_getUserAssignedProjects( $currentUser, $itStrings, 35,
							true, IT_DEFAILT_MAX_CUSTNAME_LEN, false, false, false, true );
						$P_IDs = array_keys($projectList);
					}					
				}
				
				
				$selectedWorks = it_loadSelectedWorks( $currentUser );
				
				if ( !isset($P_ID) || !strlen($P_ID) || (!in_array($P_ID, $P_IDs) && $P_ID != GROUP_BY_PROJECT)) {
					$P_ID = it_loadCommonSetting( $currentUser, IT_ISSUELIST_PROJECT );

					if ( $P_ID != GROUP_BY_PROJECT && (!strlen($P_ID) || !in_array($P_ID, $P_IDs) ))
						if ( isset($P_IDs[0]) )
							$P_ID = $P_IDs[0];
						else
							$P_ID = null;
				}
				
				//if ( isset($edited) && $edited )
				if ( $PREV_P_ID != $P_ID ) {
					it_saveCommonSetting( $currentUser, IT_ISSUELIST_PROJECT, $P_ID, $kernelStrings );
					$resetPages = true;
				}
			}
			
				
			$manyProjects = (GROUP_BY_PROJECT === $P_ID );
			
									
			// Check free project assignments
			//if ($P_ID == 0 || $manyProjects) {
				$res = it_checkFreeProjectAssignments ();
				if (PEAR::isError($res)) {
					$fatalError = true;
					$errorStr = $res->getMessage ();
					break;
				}
			//}
			
			$projmanData = it_getProjManData( $P_ID );
			if ( PEAR::isError($projmanData) ) {
				$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];

				$fatalError = true;
				break;
			}

			$projMan = isset( $projmanData["U_ID_MANAGER"] ) ? $projmanData["U_ID_MANAGER"] : null;
			$userIsProjman = is_array($projmanData) && $projMan == $currentUser || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $P_ID, $kernelStrings ), UR_TREE_FOLDER  );

			$userTaskRights = UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $P_ID, $kernelStrings ), UR_TREE_WRITE  );
			
			if ( !is_null($projMan) )
				$managerName = getArrUserName( $projmanData, true );
			else
				$managerName = null;

			// Apply default filter on first load
			//
			it_makeDefaultIssueFilters( $currentUser, $itStrings, $kernelStrings );

			// Load issue list filter
			//
			if ( !READ_ONLY )
				$currentISSF_ID = @it_getCurrentIssueFilter( $currentUser, $kernelStrings );
			else
				if ( READ_ONLY )
					if ( isset($_COOKIE[IT_ISSF_ID]) && strlen($_COOKIE[IT_ISSF_ID]) )
						$currentISSF_ID = $_COOKIE[IT_ISSF_ID];
					else
						$currentISSF_ID = null;

			$filterData = @it_loadIssueFilterData( $currentUser, $currentISSF_ID, $itStrings );
			$filterType = it_getIssueFilterType( $filterData );

			// Load issue list view data
			//
			$viewdata = it_loadIssueListViewData( $currentUser, $kernelStrings );
			if ( PEAR::isError($viewdata) ) {
				$errorStr = $itStrings['il_errloadingviewopt_message'];

				$fatalError = true;
				break;
			}

			/*$viewdata[IT_LV_COLUMNS] = array_merge( array(COL_CB), $viewdata[IT_LV_COLUMNS] );
			$it_list_columns_widths[COL_CB] = 10;

			$columnNames = array( COL_CB=>"&nbsp;" );
			foreach ( $viewdata[IT_LV_COLUMNS] as $key )
				if ( $key != COL_CB )
					$columnNames[$key] = $itStrings[$it_list_columns_names[$key]];
			*/

			$showTransitionsData = $viewdata[IT_LV_DISPLAYISSUEHISTORY];
			
			$listP_ID = ($P_ID != GROUP_BY_PROJECT) ? $P_ID : null;
			
			// Load issue list
			//
			$fullWorkList = it_listActiveProjectUserWorks( $listP_ID, $currentUser, $filterData, null, array_keys($projectList ));
			
			if ( PEAR::isError($fullWorkList) ) {
				$errorStr = $itStrings[IT_ERR_LOADWORKS];

				$fatalError = true;
				break;
			}

			if (!$manyProjects) {
				if ( array_key_exists( $P_ID, $fullWorkList ) )
					$workList = $fullWorkList[$P_ID];
				else
					$workList = array();
			} else {
				$workList = array ();
				foreach ($fullWorkList as $cP_ID => $cWorkList) {
					if (!in_array($cP_ID, $P_IDs)) {
						continue;									
					}
					foreach ($cWorkList as $cPW_ID => &$cWorkRow) {
						$cWorkRow["PW_ID"] = $cPW_ID;
						$cWorkRow["P_ID"] = $cP_ID;
					}
					$workList = array_merge ($workList, $cWorkList);
				}
			}
			
			$workListCopy = $workList;
			if ( $P_ID != $PREV_P_ID )
				$curPW_ID = (isset($selectedWorks[$P_ID]) && strlen($selectedWorks[$P_ID])) ? $selectedWorks[$P_ID] : GROUP_BY_TASKS;
			if (isset($PREV_P_ID) && ($P_ID != $PREV_P_ID || $curPW_ID != $PREV_PW_ID)) {
				$selectedWorks[$P_ID] = $curPW_ID;
				it_saveSelectedWorks( $currentUser, $selectedWorks, $kernelStrings );
			}
			
			if ( $curPW_ID != GROUP_BY_TASKS && $curPW_ID != null) {
				if ( count($workList) )
					$workList = array( $curPW_ID=>$workList[$curPW_ID] );
				else
					$workList = array();
			}
			$manyWorks = ($curPW_ID == GROUP_BY_TASKS || $curPW_ID == null);

			$work_ids = array_keys( $workListCopy );
			$work_names = array();
			foreach( $workListCopy as $key => $value ) {
				$name = strTruncate( $value["PW_DESC"], 45 );
				if ( $value['CLOSED'] )
					$name .= sprintf( " (%s)", $itStrings['il_compltask_text'] );
				$work_names[] = sprintf( "%s: %s", $key, $name );
			}

			$work_ids = array_merge( array(GROUP_BY_TASKS), $work_ids );
			$work_names = array_merge( array($itStrings['il_groupissues_item']), $work_names );
			
			$groupByTasks = $curPW_ID == GROUP_BY_TASKS;

			$hideP_ID = !empty($lastOutedP_ID) ? $lastOutedP_ID : null;
			$hidePW_ID = !empty($lastOutedPW_ID) ? $lastOutedPW_ID : null;
			
			$recordsOnPage = $viewdata[IT_LV_RPP];
			//$recordsOnPage = 3;
			$gettedIssuesCount = 0;
			$canShowMore = false;
			$lastOutedP_ID = 0;
			$lastOutedPW_ID = 0;
			$issuesOffset = (!isset($offset)) ? 0 : $offset;
			$outWorkList = array ();
			
			if ( is_array($workList) ) {
				
				// Count all issues for current view settings
				$projectWorkIds = array ();
				foreach ($workList as $_PW_ID => $PW_DATA) {
					$_P_ID = isset($PW_DATA["P_ID"]) ? $PW_DATA["P_ID"] : $P_ID;
					if (isset($PW_DATA["PW_ID"])) $_PW_ID = $PW_DATA["PW_ID"];
					$projectWorkIds[] = array ("P_ID" => $_P_ID, "PW_ID" => $_PW_ID);
				}
				$totalIssuesCount = it_issueListCount($projectWorkIds, $filterData, $kernelStrings);
				
				
				
				
				foreach( $workList as $_PW_ID => &$PW_DATA ) {
					$_P_ID = isset($PW_DATA["P_ID"]) ? $PW_DATA["P_ID"] : $P_ID;
					if (isset($PW_DATA["PW_ID"])) $_PW_ID = $PW_DATA["PW_ID"];
					
					$PW_DATA["PW_ID"] = $_PW_ID;
					
					if (sizeof($issueList) - $issuesOffset >= $recordsOnPage) {
						$canShowMore = true;
						break;
					}
					
					// Load expanded works list
					//
					//$collapsedWorks = ($manyWorks && !($_PW_ID == 0 && !$manyProjects)) ? it_getCollapsedWorkList( $currentUser, $_P_ID, $kernelStrings ) : array ();
					$collapsedWorks = array ();
					
					$transitionList = it_listWorkTransitions( $_P_ID, $_PW_ID, $itStrings );
					if ( PEAR::isError($transitionList) ) {
						$errorStr = $res;
						$fatalError = true;

						break 2;
					}

					$desc = $PW_DATA["PW_DESC"];

					$workRecord = array( "PROJECT_NAME" => $projectList[$_P_ID], "P_ID" => $_P_ID, "PW_ID"=>$_PW_ID, "PW_DESC"=>prepareStrToDisplay($desc, true), "CLOSED"=>$PW_DATA["CLOSED"], ROW_TYPE=>1 );
					
					if ($manyProjects) {
						if ( PEAR::isError($projmanData = it_getProjManData( $_P_ID )) ) {
							$errorStr = $itStrings[IT_ERR_LOADPROJMANDATA];
							$fatalError = true;
							break;
						}
						$projMan = isset( $projmanData["U_ID_MANAGER"] ) ? $projmanData["U_ID_MANAGER"] : null;
						$userIsProjman = is_array($projmanData) && $projMan == $currentUser || UR_RightsManager::CheckMask( $PMRightsManager->evaluateUserProjectRights($currentUser, $_P_ID, $kernelStrings ), UR_TREE_FOLDER );
						$workRecord["userIsProjman"] = $userIsProjman;
					}

					$addIssueURLParams = array( ACTION=>ACTION_NEW, "P_ID"=>$_P_ID, "PW_ID"=>$_PW_ID );
					$workRecord["addIssueLink"] = prepareURLStr( PAGE_IT_ADDMODISSUE, $addIssueURLParams );
					$workRecord["closed"] = $PW_DATA["CLOSED"];

					$workRecord["PW_ENDDATE"] = convertToDisplayDate($PW_DATA["PW_ENDDATE"], true );

					if ($userIsProjman || $userTaskRights) {
						$opener = sprintf("../../../%s/html/scripts/%s", $IT_APP_ID, PAGE_IT_ISSUELIST);
						$params = array(ACTION=>"modify", "P_ID"=>base64_encode($_P_ID), "PW_ID"=>base64_encode($_PW_ID), "opener"=>$opener );
						$editWorkURL = prepareURLStr( "../../../PM/html/scripts/addmodwork.php", $params );
						$workRecord["editWorkURL"] = $editWorkURL;
					}

					// Work expanding support
					//
					$isWorkCollapsed = in_array( $_PW_ID, $collapsedWorks );
					$workExpanded = ( $isWorkCollapsed ) ? IT_WORK_COLLAPSED : IT_WORK_EXPANDED;

					$workRecord[EXPANDSTATUS] = $workExpanded;
					$urlParams = array( ACTION=>EXPAND, "curState"=>$workExpanded, "expP_ID"=>$_P_ID, "expPW_ID"=>$_PW_ID );
					$anchorName = sprintf( "J%s", $_PW_ID );

					$workRecord[EXPANDLINK] = sprintf( "%s", prepareURLStr( PAGE_IT_ISSUELIST, $urlParams ), $anchorName );

					// Setup ITS link
					//
					if ( $userIsProjman ) {
						$ITSURLParams = array("P_ID"=>$_P_ID, "PW_ID"=>$_PW_ID, OPENER=>PAGE_IT_ISSUELIST );
						$workRecord[ITSSETUPLINK] = prepareURLStr( PAGE_IT_ISSUETRANSITIONSCHEMA, $ITSURLParams );
					}

					$workIndex = count($issueList);
					$outWorkList[$_P_ID][$_PW_ID] = $workRecord;
					
					if ( !$groupByTasks ) {
						if ( $userIsProjman )
							if ( $curPW_ID != GROUP_BY_TASKS ) {
								$itsSetupLink = $workRecord[ITSSETUPLINK];
								$editWorkLink = $workRecord["editWorkURL"];
							}
						else
						if ( $userTaskRights )
							if ( $curPW_ID != GROUP_BY_TASKS ) {
								$editWorkLink = $workRecord["editWorkURL"];
							}


						if ( $curPW_ID != GROUP_BY_TASKS ) {
							$addIssueLink = $workRecord["addIssueLink"];
							$workEndDate = $workRecord["PW_ENDDATE"];
							$workIsClosed = $workRecord["closed"];
						}
					}

					$recordsAdded = 0;
					if ( true ) { //$workExpanded == IT_WORK_EXPANDED ) {
						$addTransitionsData = true;
						if ( !is_null( $res = it_addIssueListWorkRecords($_P_ID, $_PW_ID, $issueList, $recordsAdded, $filterData,
																		$kernelStrings, $itStrings, false, $transitionList,
																		$currentUser, $userIsProjman || $userTaskRights, null, $addTransitionsData, $workIsClosed ) ) ) {
							$errorStr = $res;

							break 2;
						}
						$gettedIssuesCount += $recordsAdded;
					} else {
						//$issueCount = it_workIssueCount( $_P_ID, $_PW_ID, $filterData );
						//$issueList[$workIndex]['issueNum'] = $issueCount;
						//$issueNum += $issueCount;
					}

					/*if ( !$recordsAdded && $workExpanded == IT_WORK_EXPANDED ) {
						$dummy = array();
						$dummy['ROW_TYPE'] = 0;
						$dummy['dummy'] = 1;
						$dummy["PW_ID"] = $_PW_ID;
						$dummy["P_ID"] = $_P_ID;
						$issueList[] = $dummy;
						
						$issueList[$workIndex]['empty'] = 1;
					}*/
					
					if (sizeof($issueList) - $issuesOffset >= $recordsOnPage) {
						$lastOutedP_ID = $_P_ID;
						$lastOutedPW_ID = $_PW_ID;
					} else {
						//print $_PW_ID . " : " . $gettedIssuesCount . "\n";
					}
				}

			}
			//exit;

			if (sizeof($issueList) - $issuesOffset > $recordsOnPage)
				$canShowMore = true;
			$issueList = array_slice ($issueList, $issuesOffset, $recordsOnPage);
			$outedIssuesCount = sizeof($issueList) + $issuesOffset;
			
			
			//print sizeof($issueList);
			//exit;
				
				
				
			if ($manyProjects)
				$userIsProjman = false;

			$recordNum = count($issueList);

			// Fill project list
			//
			$noProjects = false;
			if ( is_array( $projectList ) ) {
				if ( !count($projectList) ) {
					$projectList[null] = $itStrings['il_noprojects_item'];
					$noProjects = true;
				}
				
				$project_ids = array_keys( $projectList );
				$project_names = array_values( $projectList );
				
				if (!$noProjects) {
					$project_ids = array_merge( array("GBP"), $project_ids);
					$project_names = array_merge( array($itStrings['il_groupissuesbyproject_item']), $project_names );
				}
			}	
				

			// Set filters info
			//
			$revokeFiltersURL = null;

			if ( is_null($currentISSF_ID) )
				$filterInfo = $itStrings['il_nofilter_text'];
			else
				$filterInfo = prepareStrToDisplay( $filterData["ISSF_NAME"], true );

			if ( !$manyProjects && ($userIsProjman || $userTaskRights) ) {
				$opener = sprintf("../../../%s/html/scripts/%s", $IT_APP_ID, PAGE_IT_ISSUELIST);
				$newWorkURL = prepareURLStr( "../../../PM/html/scripts/addmodwork.php", array(ACTION=>ACTION_NEW, "P_ID"=>base64_encode($P_ID), "opener"=>$opener ) );
			}

			// Load recently applied filters
			//
			$filters = it_listIssueFilters( $currentUser, $kernelStrings, true, $itStrings );
			if ( PEAR::isError($filters) ) {
				$errorStr = $filters->getMessage();
				$fatalError = true;

				break;
			}

			$recentFilters = array();

			if ( !strlen($currentISSF_ID) )
				$currentISSF_ID = IT_FILTER_ALL;

			$curFilterName = $filters[$currentISSF_ID];

			foreach ( $filters as $ISSF_ID=>$filterName )
				if ( strlen( trim($filterName) ) ) {
					$data = array();
					$data['name'] = prepareStrToDisplay($filterName);
					$data['url'] = prepareURLStr( PAGE_IT_ISSUELIST, array( ACTION=>APPLY_FILTER, "filter"=>$ISSF_ID ) );
					$recentFilters[$ISSF_ID] = $data;
				}

			// Prepare issue menu
			//
			$issueMenu = array();

			$issueMenu[$itStrings['il_addissue_btn']] = sprintf( $processButtonTemplate, 'addIssueBtn' )."||issueAddDialog();";

			if (!PM_DISABLED) {
				if ( !$manyProjects && ($userIsProjman || $userTaskRights)) {
					$issueMenu[$itStrings['il_copyissues_menu']] = sprintf( $processButtonTemplate, 'copyissuesbtn' )."||confirmCopy()";
					$issueMenu[$itStrings['il_moveissues_menu']] = sprintf( $processButtonTemplate, 'moveissuesbtn' )."||confirmMove()";
				} else {
					$issueMenu[$itStrings['il_copyissues_menu']] = null;
					$issueMenu[$itStrings['il_moveissues_menu']] = null;
				}
			}

			$issueMenu[$itStrings['il_deleteissues_menu']] = sprintf( $processButtonTemplate, 'deleteissuesbtn' )."||confirmDelete()";
			$issueMenu[$itStrings['il_forwardissues_menu']] = sprintf( $processButtonTemplate, 'sendissuesbtn' )."||issuesForwardSelected()";
			$issueMenu[] = '-';
			$issueMenu[$itStrings['il_remind_menu']] = sprintf( $processButtonTemplate, 'remindbtn' )."||confirmRemind()";

			$issueMenu[] = '-';

			$issueMenu[$itStrings['il_print_menu']] = sprintf( $processButtonTemplate, 'printselectedbtn' );

			// Prepare view menu
			//
			/*$viewMode = getAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_VIEWMODE', null, $readOnly );
			if ( !strlen($viewMode) )*/
				$viewMode = IT_LV_LIST;

			/*$checked = ($viewMode == IT_LV_GRID) ? "checked" : "unchecked";
			$viewMenu[$itStrings['il_grid_menu']] = sprintf( $processAjaxButtonTemplate, 'setgridmodeview' )."||null||$checked";

			$checked = ($viewMode == IT_LV_LIST) ? "checked" : "unchecked";
			$viewMenu[$itStrings['il_list_menu']] = sprintf( $processAjaxButtonTemplate, 'setlistmodeview' )."||null||$checked";

			$viewMenu[] = '-';*/
			$btnName = ($showTransitionsData) ? "hidehistorybtn" : "showhistorybtn";
			$menuTitle = ($showTransitionsData) ? "il_hidehistory_menu" : "il_displayhistory_menu";
			$viewMenu[$itStrings[$menuTitle]] = sprintf( $processButtonTemplate, $btnName ) . "||clearHistoryStatuses()";

			$viewMenu[] = '-';

			$viewMenu[$itStrings['il_custview_menu']] = sprintf( $processButtonTemplate, 'viewbtn' );

			// Prepare filters menu
			//
			$filtersMenu = array();

			$addFilterURL = prepareURLStr( PAGE_IT_ISSUEFILTERS, array( ACTION=>ACTION_NEW,
												"P_ID"=>$P_ID, OPENER=>PAGE_IT_ISSUELIST ) );

			if ( $currentISSF_ID == IT_FILTER_ALL )
				$modFilterURL = null;
			else
				$modFilterURL = prepareURLStr( PAGE_IT_ISSUEFILTERS, array( ACTION=>ACTION_EDIT, "ISSF_ID"=>$currentISSF_ID,
												"P_ID"=>$P_ID, OPENER=>PAGE_IT_ISSUELIST ) );

			$filtersMenu[$itStrings['il_addfilter_menu']] = $addFilterURL;
			$filtersMenu[$itStrings['il_modfilter_menu']] = $modFilterURL;
			$filtersMenu[$itStrings['il_orgfilters_menu']] = prepareURLStr(PAGE_IT_ORGANIZEFILTERS, array("P_ID"=>$P_ID) );

			$params = array();
			$params[ACTION] = HIDE_FILTERS;
			$closeFoldersLink = prepareURLStr( PAGE_IT_ISSUELIST, $params );

			// Prepare process menu
			//
			
			$canTools = checkUserFunctionsRights( $currentUser, $IT_APP_ID, APP_CANTOOLS_RIGHTS, $kernelStrings );
			$canReports = checkUserFunctionsRights( $currentUser, $IT_APP_ID, APP_CANREPORTS_RIGHTS, $kernelStrings );
			$toolsMenu[$itStrings['il_customizeworkflow_menu']] = sprintf( $processButtonTemplate, 'customizewfbtn' );
			$toolsMenu[$itStrings['tl_screen_long_name']] = prepareURLStr(PAGE_IT_TEMPLATELIST, array ());
			$reportsMenu[$itStrings['iss_screen_long_name']] = prepareURLStr(PAGE_IT_STATISTICS, array ());
			$reportsMenu[$itStrings['fa_screen_long_name']] = prepareURLStr(PAGE_IT_FILEATTACHMENT, array ());
			
			
			// Support pages
			//
			/*if ( !isset($currentPage) || !strlen($currentPage) ) {
				if ( !$resetPages )
					$currentPage = getAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_CURRENTPAGE', null, $readOnly );
				else
					$currentPage = 1;

				if ( !strlen($currentPage) )
					$currentPage = 1;
			}

			setAppUserCommonValue( $IT_APP_ID, $currentUser, 'IT_CURRENTPAGE', $currentPage, $kernelStrings, $readOnly );*/

			$pages = null;
			$pageCount = 0;
			$showPageSelector = false;
			//$issueList = addPagesSupport( $issueList, $viewdata[IT_LV_RPP], $showPageSelector, $currentPage, $pages, $pageCount );
			$newOffsetLimit = $issuesOffset + sizeof($issueList);
			$showMoreLink = ($canShowMore) ? "<a href='javascript:void(0)' onClick='showNextPage($newOffsetLimit, $lastOutedP_ID, $lastOutedPW_ID)'>" . $itStrings["il_showmore_link"] . "...</a>" : "";
				
			if ($pages) {
				foreach( $pages as $key => $value ) {
					$params = array();
					$params[PAGES_CURRENT] = $value;
	
					$URL = prepareURLStr( PAGE_IT_ISSUELIST, $params );
					$pages[$key] = array( $value, $URL );
				}
			}

		// Read initial folder tree panel width
		//
		if ( isset($_COOKIE['splitterView'.$IT_APP_ID.$currentUser]) )
			$treePanelWidth = (int)$_COOKIE['splitterView'.$IT_APP_ID.$currentUser];
		else
			$treePanelWidth = 200;
		
		$worksForDataStore = array ();
		$prevPId = null;
		foreach ($workListCopy as $cId => $cWork) {
			if ($cWork["CLOSED"])
				continue;
			$pId = isset($cWork["P_ID"]) ? $cWork["P_ID"] : $P_ID;
			$pwId = isset($cWork["P_ID"]) ? $cWork["PW_ID"] : $cId;
			$desc = ($pwId != 0) ? $cWork["PW_DESC"] : $itStrings["app_freeissues_label"];
			$workDescOffset = ($pId) ? $pwId . ":  ": "";
			if ($manyProjects) {
				if ($pId != $prevPId)
					$worksForDataStore[] = array ("notask", "<b>" . $projectList[$pId] . "</b>", $pId, 0);
				$worksForDataStore[] = array ($pId . "-" . $pwId, $workDescOffset . $desc, $pId, $pwId);
			} else {
				$worksForDataStore[] = array ($pId . "-" . $pwId, $workDescOffset . $desc, $pId, $pwId);
			}
			$prevPId = $pId;
		}
		$json = new Services_JSON();
		$works_data_js = $json->encode($worksForDataStore);
		
		// Load users data starts
		if ( PEAR::isError( $qr = db_query( $qr_pm_select_asgn_list) ) ) {
			$errorStr = sprintf( $pmStrings[PM_ERR_DBEXTRACTION], $pmStrings[PM_ERR_ASGNFIELD] );
			$fatalError = true;
			break;
		}

		while( $row = db_fetch_array( $qr ) ) {
			$available_users[$row["U_ID"]] = array ("id" => $row["U_ID"], "name" => getUserName($row["U_ID"]));
		}
		
		// Load project is complete
		$projectIsComplete = pm_projectIsClosed( array("P_ID", $P_ID), $kernelStrings );
		
		// Load currencies
		$currency_ids = listCurrency();
		if ( PEAR::isError($currency_ids) ) {
			$errorStr = $pmStrings['amt_curlisterr_message'];
			$fatalError = true;
			break;
		}

		$currencyList = array ();
		foreach ($currency_ids as $cId => $cCurrency)
			$currencyList[] = "['" . $cId . "']";
		
		$currenciesListStr = join(",", $currencyList);
	} while (false);
	
	//
	// Page implementation
	//
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $IT_APP_ID );

	$preproc->assign( "itStrings", $itStrings );
	$preproc->assign( "pmStrings", $pmStrings );

	$preproc->assign( PAGE_TITLE, $itStrings['il_screen_long_name'] );
	$preproc->assign( FORM_LINK, PAGE_IT_ISSUELIST );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		/*$preproc->assign( PAGES_SHOW, $showPageSelector );
		$preproc->assign( PAGES_PAGELIST, $pages );
		$preproc->assign( PAGES_CURRENT, $currentPage );
		$preproc->assign( PAGES_NUM, $pageCount );*/
		
		$preproc->assign ("showMoreLink", $showMoreLink);
		
		$preproc->assign( "viewMenu", $viewMenu );
		$preproc->assign( "toolsMenu", $toolsMenu );
		$preproc->assign( "reportsMenu", $reportsMenu );

		$preproc->assign( "project_ids", $project_ids );
		$preproc->assign( "project_names", $project_names );
		$preproc->assign( "userIsProjman", $userIsProjman );

		//$preproc->assign( "it_list_columns_widths", $it_list_columns_widths );

		$preproc->assign( "P_ID", $P_ID );
		$preproc->assign( "PREV_P_ID", $P_ID );

		$preproc->assign( "viewMode", $viewMode );
		$preproc->assign( "treePanelWidth", $treePanelWidth );

		$preproc->assign( "issueMenu", $issueMenu );
		$preproc->assign( "filtersMenu", $filtersMenu );
		$preproc->assign( "closeFoldersLink", $closeFoldersLink );
		$preproc->assign( "canTools", $canTools );
		$preproc->assign( "canReports", $canReports );
		$preproc->assign( "toolsMenu", $toolsMenu );
		$preproc->assign( "reportsMenu", $reportsMenu );

		if ( isset($curPW_ID) )
			$preproc->assign( "PREV_PW_ID", $curPW_ID );

		if ( $curPW_ID != GROUP_BY_TASKS  ) {
			$preproc->assign( "workComplete", $workRecord['CLOSED'] );
		}

		$preproc->assign( "work_ids", $work_ids );
		$preproc->assign( "work_names", $work_names );
		$preproc->assign( "works_data_js", $works_data_js );

		if ( isset($revokeFiltersURL) )
			$preproc->assign( "revokeFiltersURL", $revokeFiltersURL );

		if ( isset($filters) && is_array($filters) )
			$preproc->assign( "filterCount", count($filters) );
				
		$preproc->assign( "issueList", $issueList );
		$preproc->assign( "filterInfo", $filterInfo );

		$preproc->assign( "projectList", $projectList );
		$preproc->assign ("noProjects", $noProjects);
		$preproc->assign( "curFilterName", $curFilterName );
		$preproc->assign( "hideLeftPanel", $foldersHidden );
		$preproc->assign( "numDocuments", $recordNum );

		$preproc->assign( "newWorkURL", $newWorkURL );

		$preproc->assign( "filters", $recentFilters );

		$preproc->assign( "ISSF_ID", $currentISSF_ID );

		$preproc->assign( "managerName", $managerName );
		$preproc->assign( "viewdata", $viewdata );

		$preproc->assign( "showTransitionsData", $showTransitionsData );

		/*$colCount = count($viewdata[IT_LV_COLUMNS]);
		if ( $viewdata[IT_LV_DISPLAYSENDLINKS] )
			$colCount++;

		$preproc->assign( "visibleColumnNum", $colCount );
		$preproc->assign( "columnNames", $columnNames );*/

		if ( isset($deleteStatus) ) {
			$preproc->assign( "deleteStatus", $deleteStatus );
			if ( $deleteStatus == 1 )
				$preproc->assign( "savedDocList", $savedDocList );
		}

		if ( $curPW_ID != GROUP_BY_TASKS ) {
			$preproc->assign( "curPW_ID", $curPW_ID );

			if ( isset($itsSetupLink) )
				$preproc->assign( "itsSetupLink", $itsSetupLink );

			if ( isset($addIssueLink) )
				$preproc->assign( "addIssueLink", $addIssueLink );

			if ( isset($workEndDate) )
				$preproc->assign( "workEndDate", $workEndDate );

			if ( isset($workIsClosed) )
				$preproc->assign( "workIsClosed", $workIsClosed );

			if ( isset($editWorkLink) )
				$preproc->assign( "editWorkLink", $editWorkLink );
		}
		
		$preproc->assign( "dateDisplayFormat", DATE_DISPLAY_FORMAT );
		$preproc->assign ("availableUsers", $available_users);
		$preproc->assign ("manyProjects", $manyProjects);
		$preproc->assign ("manyWorks", $manyWorks);
		$preproc->assign ("pmDisabled", PM_DISABLED);
		$preproc->assign ("projectIsComplete", $projectIsComplete);
		$preproc->assign ("currenciesListStr", $currenciesListStr);
		$preproc->assign ("currentTimestamp", convertTimestamp2Local( mktime()));
		$preproc->assign ("outWorkList", $outWorkList);
		
		$preproc->assign ("hideP_ID", $hideP_ID);
		$preproc->assign ("hidePW_ID", $hidePW_ID);
		
		$preproc->assign ("outedIssuesCount", $outedIssuesCount);
		$preproc->assign ("totalIssuesCount", $totalIssuesCount);
		$preproc->assign ("issuesCountMessage", sprintf($itStrings["il_issuescount_label"], "<span id='issuesCount'>$outedIssuesCount</span>", "<span id='totalIssuesCount'>" . $totalIssuesCount . "</span>"));
	}
	
	if ($preproc->get_template_vars('ajaxAccess')) {
		require_once( "../../../common/html/includes/ajax.php" );
		$ajaxRes = array ();
		if (empty($onlyIssues)) {
			$ajaxRes["toolbar"] = simple_ajax_get_toolbar ("issuelist_toolbar.htm", $preproc);
			$ajaxRes["rightContent"] = $preproc->fetch( "issuelist_rightpanel.htm" );
			print simple_ajax_encode($ajaxRes);
		} else {
			$preproc->assign("onlyIssues", true);
			print $preproc->fetch( "ilist_list.htm");
		}
		exit;
	}
	$preproc->display( "issuelist.htm" );
?>