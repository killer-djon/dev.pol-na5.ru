<?php

	//
	// Issue Tracking constants
	//

	//
	// Attachments directory
	//
	define( "IT_ATTACHMENTS_DIR", sprintf( "%s/it/attachments", WBS_ATTACHMENTS_DIR ) );
	define( "IT_TRANSITIONSDIR_NAME", "transitions" );

	//
	// Pages names
	//

	define( "PAGE_IT_TEMPLATELIST", "templatelist.php" );
	define( "PAGE_IT_ISSUELIST", "issuelist.php" );
	define( "PAGE_IT_ADDMODISSUE", "addmodissue.php" );
	define( "PAGE_IT_ISSUE", "issue.php" );
	define( "PAGE_IT_SENDISSUE", "sendissue.php" );
	define( "PAGE_IT_ASSIGNMENTS", "itassignments.php" );
	define( "PAGE_IT_ISSUETRANSITIONSCHEMA", "issuetransitionschema.php" );
	define( "PAGE_IT_ADDMODTRANSITION", "addmodtransition.php" );
	define( "PAGE_IT_FILLFROMTEMPLATE", "fillfromtemplate.php" );
	define( "PAGE_IT_GETTRANSITIONFILE", "gettransitionfile.php" );
	define( "PAGE_IT_GETISSUEFILE", "getissuefile.php" );	
	define( "PAGE_IT_FILEATTACHMENT", "fileattachment.php" );
	define( "PAGE_IT_ISSUEFILTERS", "issuefilters.php" );
	define( "PAGE_IT_STATISTICS", "statistics.php" );	
	define( "PAGE_IT_DYNAMIC", "dynamic.php" );	
	define( "PAGE_IT_CUSTOMIZEDYNAMIC", "dynamicsetup.php" );	
	define( "PAGE_IT_ORGANIZEFILTERS", "organizefilters.php" );	
	define( "PAGE_IT_REMINDER", "reminder.php" );
	define( "PAGE_IT_ILVIEW", "issuelistview.php" );	
	define( "PAGE_IT_SAVETEMPLATE", "savetemplate.php" );	
	define( "PAGE_IT_SELECTTEMPLATE", "selecttemplate.php" );	
	define( "PAGE_IT_TEMPLATE", "workflowtemplate.php" );
	define( "PAGE_IT_DELETETRANSITION", "deletetransition.php" );	
	define( "PAGE_IT_COPYMOVE", "copymove.php" );
	define( "PAGE_IT_PRINT", "print.php" );
	define( "PAGE_IT_WORKFLOWMANAGER", "workflowmanager.php" );

	//
	// Issue colors constants
	//

	$it_colorOffset = 214;

	$it_styles = array( 0=>array("#A52A2A", 0, 'app_isstylebrown_name'),
						1=>array("#A52A2A", 1, 'app_isstylebrownb_name'),
						2=>array("#006400", 0, 'app_isstyledarkgreen_name'),
						3=>array("#006400", 1, 'app_isstyledarkgreenb_name'),
						4=>array("#000000", 0, 'app_isstyleblack_name'),
						5=>array("#000000", 1, 'app_isstyleblackb_name'),
						6=>array("#666600", 0, 'app_isstylehazel_name'),
						7=>array("#666600", 1, 'app_isstylehazelb_name'),
						8=>array("#0000FF", 0, 'app_isstyleblue_name'),
						9=>array("#0000FF", 1, 'app_isstyleblueb_name'),
						10=>array("#FF0000", 0, 'app_isstyred_name'),
						11=>array("#FF0000", 1, 'app_isstyredb_name'),
						);

	define( "IT_DEF_CLOSED_COLOR", 0 );
	define( "IT_MISSED_STATE_COLOR", 4 );
	define( "IT_FREEISSUES_PROJECT", "0" );
	define( "IT_FREEISSUES_WORK", "0" );

	//
	// Assignment options
	//

	define( "IT_ASSIGNMENTOPT_NOTAPPLICABLE", -1 );
	define( "IT_ASSIGNMENTOPT_NOTREQUIRED", 0 );
	define( "IT_ASSIGNMENTOPT_SELECTABLE", 1 );
	define( "IT_ASSIGNMENTOPT_NOTSELECTABLE", 2 );

	define( "IT_NOASSIGNMENT", -1 );

	$it_assignment_options = array(
									IT_ASSIGNMENTOPT_SELECTABLE => 'dws_reqselopt_name',
									IT_ASSIGNMENTOPT_NOTSELECTABLE => 'dws_reqnotselopt_name',
									IT_ASSIGNMENTOPT_NOTREQUIRED => 'dws_notrequiredopt_name',
									IT_ASSIGNMENTOPT_NOTAPPLICABLE => 'dws_notapplicableopt_name',
								);

	$it_assignment_chart_names = array(
									IT_ASSIGNMENTOPT_SELECTABLE => 'cw_selectable_label',
									IT_ASSIGNMENTOPT_NOTSELECTABLE => 'cw_notselectable_label',
								);

	define( "IT_SENDER_OPTION", "!sender!" );

	//
	// Issue Transition Schema consts
	//

	define( "IT_END_STATUS", -1 );
	define( "IT_FIRST_STATUS", 1 );

	define( "IT_TRANSITIONS_SEPARATOR", "!^!" );

	define( "IT_TRANSITION_START", "START" );
	define( "IT_TRANSITION_END", "END" );
	define( "IT_PARITY", "PARITY" );
	define( "IT_CELLTYPE", "CELLTYPE" );
	define( "IT_TRANSITION_DEFAULT", "DEFAULT" );

	define( "IT_TRANSITION_START_NAME", "Start" );
	define( "IT_TRANSITION_COMPLETE_NAME", "Complete" );

	//
	// Issue Priority values
	//

	define( "IT_ISSUE_PRIORIY_LOW", 0 );
	define( "IT_ISSUE_PRIORIY_NORMAL", 1 );
	define( "IT_ISSUE_PRIORIY_HIGH", 2 );

	$it_issue_priority_names = array( IT_ISSUE_PRIORIY_LOW => 'app_issuelow_text', IT_ISSUE_PRIORIY_NORMAL => 'app_issuenormal_text', IT_ISSUE_PRIORIY_HIGH => 'app_issuehigh_text');
	$it_issue_priority_short_names = array( IT_ISSUE_PRIORIY_LOW => 'app_issuelowshort_text', IT_ISSUE_PRIORIY_NORMAL => 'app_issuenormalshort_text', IT_ISSUE_PRIORIY_HIGH => 'app_issuehighshort_text');

	//
	// Issue transition types
	//

	define( "IT_NEXT_TRANSITION", 1 );
	define( "IT_PREV_TRANSITION", 0 );

	// Error strings codes
	//

	define( "IT_ERR_ISSUERIGHTS", 'app_invissuerights_message' );
	define( "IT_ERR_LOADPROJECTS", 'app_errloadingprojects_message' );
	define( "IT_ERR_LOADWORKS", 'app_errloadingtasks_message' );
	define( "IT_ERR_LOADITRIGHTS", 'app_errloadingrights_message' );
	define( "IT_ERR_LOADITS", 'app_errloadingworkflow_message' );
	define( "IT_ERR_LOADPROJMANDATA", 'app_errloadingmanager_message' );
	define( "IT_ERR_LOADWORKASSIGNMENTS", 'app_errloadingtaskasgnmt_message' );
	define( "IT_ERR_WORKNOTFOUND", 'app_tasknotfound_message' );
	define( "IT_ERR_LOADTEMPLATELIST", 'app_errloadingtmpls_message' );	
	define( "IT_ERR_TEMPLATENOTFOUND", 'app_templnotfound_message' );
	define( "IT_ERR_SCHEMAINUSE", 'st_incomptemplate_message' );	
	define( "IT_ERR_LOADISSUEDATA", 'app_errloadingissue_message' );	
	define( "IT_ERR_LOADITL", 'app_errloadingitl_message' );
	define( "IT_ERR_LOADISSUEASSIGNMENTS", 'app_errloadingasgnmt_message' );
	define( "IT_ERR_LOADISSUEALLOWEDTRANSITIONS", 'app_errloadingtrlist_message' );	
	define( "IT_ERR_ISMREPORT", 'iss_noreport_message' );	
	define( "IT_ERR_ISMPARAMS", 'iss_invstatparams_message' );	

	//
	// Issue list consts
	// 

	define( "IT_ILIST_ALLWORKS", -1 );
	define( "IT_ISSUELIST_PROJECT", "IT_ISSUELIST_PROJECT" );
	define( "IT_ISSUELIST_WORK", "IT_ISSUELIST_WORK" );

	define( "IT_WORK_EXPANDED", "expanded" );
	define( "IT_WORK_COLLAPSED", "collapsed" );

	define( "IT_XML_ISSUELIST_WORKSTATES", "ISSUELIST_WORKSTATES" );
	define( "IT_XML_ISSUELIST_WORKSTATE", "ISSUELIST_WORKSTATE" );	
	define( "IT_XML_ISSUELIST_WORKSTATE_PID", "P_ID" );	
	define( "IT_XML_ISSUELIST_WORKSTATE_PWID", "PW_ID" );	
	define( "IT_XML_ISSUELIST_WORKSTATE_STATE", "STATE" );
	define( "IT_XML_ISSUELIST_WORKSTATES_SELECTED", "SELECTED_WORKS" );

	//
	// Issue list view consts
	//

	define( "IT_LV_WORKVIEW", "WORKVIEW" );
	define( "IT_LV_SHOWSTATUS", "SHOWSTATUS" );
	define( "IT_LV_SHOWSENDER", "SHOWSENDER" );
	define( "IT_LV_SHOWASSIGNEE", "SHOWASSIGNEE" );
	define( "IT_LV_DISPLAYSENDLINKS", "DISPLAYSENDLINKS" );
	define( "IT_LV_RESTRICTDESCRIPTION", "RESTRICTDESCRIPTION" );	
	define( "IT_LV_DESCLENGTH", "DESCLENGTH" );
	define( "IT_LV_COLUMNS", "COLUMNS" );
	define( "IT_LV_RPP", "RECORSPERPAGE" );
	define( "IT_LV_HIDETASKCOMBO", "HIDETASKCOMBO" );
	define( "IT_LV_DISPLAYISSUEHISTORY", "DISPLAYISSUEHISTORY" );

	define( "IT_XML_LV_DATA", "LISTVIEWDATA" );

	define( "IT_LV_LIST", "LIST" );
	define( "IT_LV_GRID", "GRID" );

	define( "IT_COLUMN_NUM", "I_NUM" );
	define( "IT_COLUMN_PRIORITY", "I_PRIORITY" );
	define( "IT_COLUMN_DATE", "I_STARTDATE" );
	define( "IT_COLUMN_STATUS", "I_STATUSCURRENT" );
	define( "IT_COLUMN_DESCRIPTION", "I_DESC" );
	define( "IT_COLUMN_SENDER", "U_ID_SENDER" );
	define( "IT_COLUMN_ASSIGNED", "U_ID_ASSIGNED" );
	define( "IT_COLUMN_AUTHOR", "U_ID_AUTHOR" );
	define( "IT_COLUMN_PENDING", "I_STATUSCURRENTDATE" );

	$it_list_columns_names = array( IT_COLUMN_NUM => 'il_idfield_title',
									IT_COLUMN_PRIORITY => 'il_priorityfield_title', 
									IT_COLUMN_DATE => 'il_datefield_title', 
									IT_COLUMN_STATUS => 'il_statusfield_title', 
									IT_COLUMN_DESCRIPTION => 'il_descfield_title', 
									IT_COLUMN_AUTHOR => 'il_authorfield_title',
									IT_COLUMN_SENDER => 'il_senderfield_title', 
									IT_COLUMN_ASSIGNED => 'il_assignedfield_title',
									IT_COLUMN_PENDING => 'il_pendingfield_title'	
	 );

	$it_list_columns_widths = array( IT_COLUMN_NUM => 10,
									IT_COLUMN_PRIORITY => 5,
									IT_COLUMN_DATE => 20, 
									IT_COLUMN_STATUS => 30,
									IT_COLUMN_DESCRIPTION => '90%',
									IT_COLUMN_AUTHOR => 30,
									IT_COLUMN_SENDER => 30, 
									IT_COLUMN_ASSIGNED => 30 );
	//
	// Issue Filters consts
	//

	define( "IT_FILTER_XML_SECTION", "ISSUEFILTERS" );
	define( "IT_FILTER_XML_FILTER", "ISSUEFILTER" );
	define( "IT_FILTER_XML_FILTER_ID", "ID" );
	define( "IT_FILTER_XML_FILTER_NAME", "NAME" );
	define( "IT_FILTER_XML_FILTER_TIMESTAMP", "TIMESTAMP" );
	define( "IT_FILTER_XML_FILTERS_CURRENT", "CURRENTFILTER" );
	define( "IT_FILTER_XML_DEF_FILTERS_CREATED", "DEFFILTERSCREATED" );
	define( "IT_FILTER_XML_DEFAULT_FILTER", "DEFAULT" );	

	define( "IT_FILTER_ACTIVEWORKS", 0 );
	define( "IT_FILTER_CLOSEDWORKS", 1 );
	define( "IT_FILTER_ALLWORKS", 2 );

	define( "IT_FILTER_OPENISSUES", 0 );
	define( "IT_FILTER_COMPLETEISSUES", 1 );
	define( "IT_FILTER_ALLISSUES", 2 );

	define( "IT_FT_WORKS", 0 );
	define( "IT_FT_MIXED", 1 );
	define( "IT_FILTER_ALL", "NO" );
	define( "IT_FILTER_THIS_USER", "THIS_USER" );

	define( "IT_FILTER_ISSUE_DELIMITER", "!^!" );
	define( "IT_FILTER_NOTASSIGED", "!notassigned!" );

	//
	// Default filters
	//

	$it_defaultFilters = array(
								array(
									"ISSF_NAME" => 'il_openissuesfilter_title',
									"ISSF_WORKSTATE" => IT_FILTER_ACTIVEWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_OPENISSUES,
								),
								array(
									"ISSF_NAME" =>'il_todayfilter_title',
									"ISSF_WORKSTATE" => IT_FILTER_ALLWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_ALLISSUES,
									"ISSF_WORKSTATE_CREATEDAY_OPT" => 1,
									"ISSF_LASTDAYS" => 1,
								),
								array(
									"ISSF_NAME" =>'il_lastweekfilter_title',
									"ISSF_WORKSTATE_CREATEDAY_OPT" => 1,
									"ISSF_WORKSTATE" => IT_FILTER_ALLWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_ALLISSUES,
									"ISSF_LASTDAYS" => 7,
								),
								array(
									"ISSF_NAME" =>'il_imassigneefilter_title',
									"ISFF_U_ID_ASSIGNED" => IT_FILTER_THIS_USER,
									"ISSF_WORKSTATE" => IT_FILTER_ACTIVEWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_OPENISSUES,
								),
								array(
									"ISSF_NAME" =>'il_imauthorfilter_title',
									"ISFF_U_ID_AUTHOR" => IT_FILTER_THIS_USER,
									"ISSF_WORKSTATE" => IT_FILTER_ACTIVEWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_OPENISSUES,
								),
								array(
									"ISSF_NAME" =>'il_imsenderfilter_title',
									"ISFF_U_ID_SENDER" => IT_FILTER_THIS_USER,
									"ISSF_WORKSTATE" => IT_FILTER_ACTIVEWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_OPENISSUES,
								),
								array(
									"ISSF_NAME" =>'il_pendweekfitler_title',
									"ISSF_PENDING" => 7,
									"ISSF_WORKSTATE_CREATEDAY_OPT" => 0,
									"ISSF_WORKSTATE" => IT_FILTER_ACTIVEWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_OPENISSUES,
								),
								array(
									"ISSF_NAME" =>'il_pendmonthfitler_title',
									"ISSF_PENDING" => 30,
									"ISSF_WORKSTATE_CREATEDAY_OPT" => 0,
									"ISSF_WORKSTATE" => IT_FILTER_ACTIVEWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_OPENISSUES,
								),
								array(
									"ISSF_NAME" =>'il_completefitler_title',
									"ISSF_WORKSTATE" => IT_FILTER_ALLWORKS,
									"ISSF_ISSUE_COMPLETE" => IT_FILTER_COMPLETEISSUES,
								)
	);


	//
	// Issue Statistics Matrix constants
	// 

	define( "IT_ISM_ALL_PROJECTS", -1 );
	define( "IT_ISM_KNOWN_PROJECT", 1 );

	define( "IT_ISM_VIEW_PROJECT_STATUS", 0 );
	define( "IT_ISM_VIEW_PROJECT_ASSIGNED", 1 );
	define( "IT_ISM_VIEW_PROJECT_PRIORITY", 2 );
	define( "IT_ISM_VIEW_WORK_STATUS", 3 );
	define( "IT_ISM_VIEW_WORK_ASSIGNED", 4 );
	define( "IT_ISM_VIEW_WORK_PRIORITY", 5 );
	define( "IT_ISM_VIEW_ASSIGNED_STATUS", 6 );
	define( "IT_ISM_VIEW_ASSIGNED_PRIORITY", 7 );

	define( "IT_ISM_REPORT_PARAM1", "param1" );
	define( "IT_ISM_REPORT_PARAM2", "param2" );

	define( "IT_ISM_COLID_FIX", "data" );
	define( "IT_ISM_ROWID_FIX", "<null>" );

	$IT_ISM_VIEW_NAMES = array( IT_ISM_VIEW_PROJECT_STATUS => 'iss_projstatus_view', 
								IT_ISM_VIEW_PROJECT_ASSIGNED => 'iss_projperson_view', 
								IT_ISM_VIEW_PROJECT_PRIORITY => 'iss_projprior_view', 
								IT_ISM_VIEW_WORK_STATUS => 'iss_taskstatus_view', 
								IT_ISM_VIEW_WORK_ASSIGNED => 'iss_taslperson_view', 
								IT_ISM_VIEW_WORK_PRIORITY => 'iss_taskprior_view',
								IT_ISM_VIEW_ASSIGNED_STATUS => 'iss_personstatus_view', 
								IT_ISM_VIEW_ASSIGNED_PRIORITY => 'iss_personprior_view' );

	$IT_ISM_COLUMN_TITLES = array( IT_ISM_VIEW_PROJECT_STATUS => array('iss_project_title', 'iss_status_title'),
								IT_ISM_VIEW_PROJECT_ASSIGNED => array('iss_project_title', 'iss_person_title'),
								IT_ISM_VIEW_PROJECT_PRIORITY => array('iss_project_title', 'iss_priority_title'),
								IT_ISM_VIEW_WORK_STATUS => array('iss_description_title', 'iss_status_title'),
								IT_ISM_VIEW_WORK_ASSIGNED => array('iss_description_title', 'iss_person_title'),
								IT_ISM_VIEW_WORK_PRIORITY => array('iss_description_title', 'iss_priority_title'),
								IT_ISM_VIEW_ASSIGNED_STATUS => array('iss_person_title', 'iss_status_title'),
								IT_ISM_VIEW_ASSIGNED_PRIORITY => array('iss_person_title', 'iss_priority_title') );

	$IT_ISM_VIEWS_ALLOWED = array( 0=>array(IT_ISM_VIEW_PROJECT_STATUS, 
												IT_ISM_VIEW_PROJECT_ASSIGNED, 
												IT_ISM_VIEW_PROJECT_PRIORITY, 
												IT_ISM_VIEW_ASSIGNED_STATUS, 
												IT_ISM_VIEW_ASSIGNED_PRIORITY),
									1=>array(IT_ISM_VIEW_WORK_STATUS,
												IT_ISM_VIEW_WORK_ASSIGNED,
												IT_ISM_VIEW_WORK_PRIORITY,
												IT_ISM_VIEW_ASSIGNED_STATUS,
												IT_ISM_VIEW_ASSIGNED_PRIORITY ) );

	$IT_ISM_QUERIES = array( IT_ISM_VIEW_PROJECT_STATUS => array(IT_ISM_ALL_PROJECTS=>$qr_it_ism_proj_status),
							IT_ISM_VIEW_PROJECT_ASSIGNED => array(IT_ISM_ALL_PROJECTS=>$qr_it_ism_proj_assigned),
							IT_ISM_VIEW_PROJECT_PRIORITY => array(IT_ISM_ALL_PROJECTS=>$qr_it_ism_proj_priority),
							IT_ISM_VIEW_ASSIGNED_STATUS => array(IT_ISM_ALL_PROJECTS=>$qr_it_ism_assigned_status,
																IT_ISM_KNOWN_PROJECT=>$qr_it_ism_assigned_status_proj),
							IT_ISM_VIEW_ASSIGNED_PRIORITY => array(IT_ISM_ALL_PROJECTS=>$qr_it_ism_assigned_priority, 
																	IT_ISM_KNOWN_PROJECT=>$qr_it_ism_assigned_priority_proj),
							IT_ISM_VIEW_WORK_STATUS => array(IT_ISM_KNOWN_PROJECT=>$qr_it_ism_work_status),
							IT_ISM_VIEW_WORK_ASSIGNED => array(IT_ISM_KNOWN_PROJECT=>$qr_it_ism_work_assigned),
							IT_ISM_VIEW_WORK_PRIORITY => array(IT_ISM_KNOWN_PROJECT=>$qr_it_ism_work_priority ) );

	$IT_ISM_PARAM_NAMES = array( IT_ISM_VIEW_PROJECT_STATUS => array( "P_ID", "I_STATUSCURRENT" ),
								IT_ISM_VIEW_PROJECT_ASSIGNED => array( "P_ID", "U_ID_ASSIGNED" ),
								IT_ISM_VIEW_PROJECT_PRIORITY => array( "P_ID", "I_PRIORITY" ),
								IT_ISM_VIEW_ASSIGNED_STATUS => array( "U_ID_ASSIGNED", "I_STATUSCURRENT" ),
								IT_ISM_VIEW_ASSIGNED_PRIORITY => array( "U_ID_ASSIGNED", "I_PRIORITY" ),
								IT_ISM_VIEW_WORK_STATUS => array( "PW_ID", "I_STATUSCURRENT" ),
								IT_ISM_VIEW_WORK_ASSIGNED => array( "PW_ID", "U_ID_ASSIGNED" ),
								IT_ISM_VIEW_WORK_PRIORITY => array( "PW_ID", "I_PRIORITY" ) );

	//
	// Issue Dynamic Report constants
	//

	define( "IT_IDR_ALL_PROJECTS", -1 );
	define( "IT_IDR_ALL_WORKS", -1 );
	
	define( "IT_IDR_XML_ROOT", "IDR" );
	define( "IT_IDR_XML_PID", "P_ID" );
	define( "IT_IDR_XML_PWID", "PW_ID" );
	define( "IT_IDR_XML_STATUS", "STATUS" );
	define( "IT_IDR_XML_STATUSNAME", "NAME" );

	//
	// Issue file types
	//

	define( "IT_FT_ISSUE", 0 );
	define( "IT_FT_TRANSITION", 1);

	//
	// Common IT consts
	//

	define( "IT_DEFAILT_MAX_PROJNAME_LEN", 30 );
	define( "IT_DEFAILT_MAX_CUSTNAME_LEN", 30 );

	// 
	// Issue cookies consts
	//

	define( "IT_ISSF_ID", "it_issf_id_cookie" );

	//
	// Operation codes
	//

	define( "IT_OPERATION_COPY", 'copy' );
	define( "IT_OPERATION_MOVE", 'move' );
?>