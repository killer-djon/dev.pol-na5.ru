<?php

	//
	// Project Management constants
	//

	//
	// Pages names
	//
	define( "PAGE_PM_CUSTOMERLIST", "customerlist.php" );
	define( "PAGE_PM_ADDMODCUSTOMER", "addmodcustomer.php" );
	define( "PAGE_PM_PROJECTLIST", "projectlist.php" );
	define( "PAGE_PM_ADDMODPROJECT", "addmodproject.php" );
	define( "PAGE_PM_CLOSEPROJECT", "closeproject.php" );
	define( "PAGE_PM_ADDMODWORK", "addmodwork.php" ) ;
	define( "PAGE_PM_ADDMODASGN", "addmodasgn.php" );
	define( "PAGE_PM_CLOSEWORK", "closework.php" );
	define( "PAGE_PM_CLOSEDPROJECTS", "closedprojects.php" );
	define( "PAGE_PM_REOPENPROJECT", "reopenproject.php" );
	define( "PAGE_PM_RESTORECUSTOMER", "restorecustomer.php" );
	define( "PAGE_PM_DELETEDCUSTOMERS", "deletedcustomers.php" );
	define( "PAGE_PM_WORKLIST", "worklist.php" );
	define( "PAGE_PM_GANTT_CHART", "ganttchart.php" );
	define( "PAGE_PM_WORKASSIGNMENTS", "workassignments.php" );
	define( "PAGE_PM_FINISHWORK", "completework.php" );
	define( "PAGE_PM_PROJECTSTATISTICS", "projectstatistics.php" );
	define( "PAGE_PM_DEFINEINTERVAL", "defineinterval.php" );
	define( "PAGE_PM_VIEWPROJECT", "viewproject.php" );
	define( "PAGE_PM_IMPORT", "import.php" );
	define( "PAGE_PM_IMPORT_TASKS", "import_tasks.php" );
	define( "PAGE_PM_EXPORT_TASKS", "export_tasks.php" );
	
	//
	// Actions names
	//

	define( "PM_ACTION_RESTORE", "restore" );
	define( "PM_ACTION_REVIEW", "review" );
	define( "PM_ACTION_MODIFY", "modify" );
	define( "PM_ACTION_CHART",  "chart" );
	define( "PM_ACTION_NEW", "new" );
	define( "PM_ACTION_DELETE", "delete" );
	define( "PM_ACTION_CLOSE", "close" );
	define( "PM_ACTION_REMOVE", "remove" );
	define( "PM_ACTION_ADD", "add" );
	define( "PM_ACTION_NEW_PROJECT", "new_project" );
	define( "PM_ACTION_VIEW_WORKLIST", "view_list" );
	define( "PM_ACTION_NO_PROJECT", "no_project" );

	//
	// General constants
	//

	define( "PM_SELECTFIELD_ID", NULL );
	define( "PM_EXIST_ID", 1 );

	define( "PM_PAGE", "page" );
	define( "PM_BTN_SUBMIT", "Submit" );

	define( "PM_MAXPROJECTLENGTH", 50 );
	define( "PM_ENDOFTEXT", "..." );

	define( "PM_ROWPERPAGE", 30 );
	define( "PM_VALIDMANAGER", 0 );
	define( "PM_INVALIDMANAGER", 1 );

	define( "PM_YEAR_DAYS", 365 );
	define( "PM_LONG_YEAR_DAYS", 366 );

	define( "PM_CUST_NAME_LEN", 30 );

	//Project statistics screen

	define( "PM_SHOW_ALL_PROJECTS", 0 );
	define( "PM_SHOW_CLOSED_PROJECTS", 1 );
	define( "PM_SHOW_OPENED_PROJECTS", 2 );
	define( "PM_SHOW_WORK_COUNT", 0 );
	define( "PM_SHOW_ESTIMATED_COST", 1 );
	define( "PM_SHOW_ASSIGNED_USERS", 2 );

	//Work list screen

	define( "PM_SHOW_GANTT", 2 );
	define( "PM_SHOW_WORKLIST", 0 );
	define( "PM_SHOW_WORKASSIGNMENTS", 1 );

	//
	// Button constants
	//

	define( "PM_BTN_DELETEDCUSTOMERS", "delcsbtn" );
	define( "PM_BTN_ADDCUSTOMER", "addcbtn" );
	define( "PM_BTN_ACTIVECUSTOMERS", "actcsbtn" );
	define( "PM_BTN_CLOSEDPROJECTS", "clspsbtn" );
	define( "PM_BTN_ADDPROJECT", "addpbtn" );
	define( "PM_BTN_ACTIVEPROJECTS", "actpsbtn" );
	define( "PM_BTN_ADDWORK", "addwbtn" );
	define( "PM_BTN_GANTTCHART", "ganttbtn" );
	define( "PM_BTN_WORKLIST", "wrklistbtn" );
	define( "PM_BTN_CLOSEPROJECT", "clspbtn" );
	define( "PM_BTN_DELETEPROJECT", "delpbtn" );
	define( "PM_BTN_DELETECUSTOMER", "delcbtn" );	
	define( "PM_BTN_RESTORECUSTOMER", "rstcbtn" );
	define( "PM_BTN_CLOSE", "clsbtn" );
	define( "PM_BTN_REOPENPROJECT", "rpnpbtn" );
	define( "PM_BTN_REOPENWORK", "rpnwbtn" );
	define( "PM_BTN_DELETEWORK", "delwbtn" );
	define( "PM_BTN_FINISHWORK", "finwbtn" );

	//
	// Error names
	//

	define( "PM_ERR_DBEXTRACTION", 'app_dbextraction_message' );
	define( "PM_ERR_PROJECTFIELD", 'app_projectfield_message' );
	define( "PM_ERR_WORKFIELD", 'app_taskfield_message' );
	define( "PM_ERR_ASGNFIELD", 'app_asgmtfield_message' );
	define( "PM_ERR_CUSTOMERFIELD", 'app_custfield_message' );
	define( "PM_ERR_MANAGERFIELD", 'app_managerfield_message' );
	define( "PM_ERR_MAXCUSTOMERID", 'app_maxciderr_message' );
	define( "PM_ERR_MAXPROJECTID", 'app_maxpiderr_message' );
	define( "PM_ERR_MAXWORKID", 'app_maxwiderr_message' );
	define( "PM_ERR_CUSTOMERHASPROJECTS", 'amc_invopenproj_message' );
	define( "PM_ERR_PROJECTSTARTEXCEND", 'app_startenddateerr_message' );
	define( "PM_ERR_WORKSTARTEXCEND", 'amt_startenderr_message' );
	define( "PM_ERR_WORKSTARTEXCDUE", 'amt_taskstartdue_message' );
	define( "PM_ERR_P_STARTEXC_PW_START", 'amt_taskstartproj_message' );
	define( "PM_ERR_PROJECTENDEXCCURR", 'app_endcurdateerr_message' );

	//
	// Error code
	//

	define( "PM_ERRCODE_DATEEXIST", 1002 );
	define( "PM_ERRCODE_STARTEXCEND", 1003 );
	define( "PM_ERRCODE_DATAHANDLE", 1004 );
	define( "PM_ERRCODE_STARTEXCDUE", 1005 );
	define( "PM_ERRCODE_P_STARTEXC_PW_START", 1006 );
	define( "PM_ERRCODE_ENDEXCCURR", 1007 );
	define( "PM_ERRCODE_WRONGDATA", 1008 );

	//
	// XML constants
	//

	define( "PM_WORKLIST_PROJECTID", "PROJECTID" );
	define( "PM_WORKLIST_PROJECTSCREEN", "PROJECTSCREEN" );
	define( "PM_PM_SECTION", "PM" );
	define( "PM_WORKLIST_SECTION", "WORKLIST" );

	//
	// Import support
	//

	define( "PM_COLUMN_NAME", "C_NAME" );
	define( "PM_COLUMN_ADDRESS_STREET", "C_ADDRESSSTREET" );
	define( "PM_COLUMN_ADDRESS_CITY", "C_ADDRESSCITY" );
	define( "PM_COLUMN_ADDRESS_STATE", "C_ADDRESSSTATE" );
	define( "PM_COLUMN_ADDRESS_ZIP", "C_ADDRESSZIP" );
	define( "PM_COLUMN_ADDRESS_COUNTRY", "C_ADDRESSCOUNTRY" );
	define( "PM_COLUMN_PERSON", "C_CONTACTPERSON" );
	define( "PM_COLUMN_PHONE", "C_PHONE" );
	define( "PM_COLUMN_FAX", "C_FAX" );
	define( "PM_COLUMN_EMAIL", "C_EMAIL" );
	
	define( "PM_WORKS_ON_PAGE", 30 );

	$pm_importColumnNames = array( PM_COLUMN_NAME => 'amc_custname_title', 
									PM_COLUMN_ADDRESS_STREET => 'amc_streetaddress_title', 
									PM_COLUMN_ADDRESS_CITY => 'amc_city_title', 
									PM_COLUMN_ADDRESS_STATE => 'amc_state_title', 
									PM_COLUMN_ADDRESS_ZIP => 'amc_zip_title', 
									PM_COLUMN_ADDRESS_COUNTRY => 'amc_country_title', 
									PM_COLUMN_PERSON => 'amc_contname_title', 
									PM_COLUMN_PHONE => 'amc_phone_title', 
									PM_COLUMN_FAX => 'amc_fax_title', 
									PM_COLUMN_EMAIL => 'amc_email_title' );

	//
	// Gantt constants
	//

	define( "GANTT_PROJECT_TODAY", -2 );
	define( "GANTT_PROJECT_TOMONTH", -1 );

	// Access rights
	//

	define( "PM_RIGHT_NORIGHTS", -1 );
	define( "PM_RIGHT_READ", 1 );
	define( "PM_RIGHT_READWRITE", 2 );
	define( "PM_RIGHT_READWRITEFOLDER",4 );

	$pm_accessRightsLongNames = array( PM_RIGHT_READ=>'app_vright_label', PM_RIGHT_READ | PM_RIGHT_READWRITE =>'app_vtright_label', PM_RIGHT_READ | PM_RIGHT_READWRITE | PM_RIGHT_READWRITEFOLDER =>'app_vtpright_label' );
	$pm_accessRightsShortNames = array( PM_RIGHT_READ=>'app_vrightshort_label', PM_RIGHT_READ | PM_RIGHT_READWRITE =>'app_vtrightshort_label', PM_RIGHT_READ | PM_RIGHT_READWRITE | PM_RIGHT_READWRITEFOLDER =>'app_vtprightshort_label' );
	
	
	// Task Import
	
	define('PM_TASK_COLUMN_PROJECT_ID', 'P_ID');
	define('PM_TASK_COLUMN_TASK_ID', 'PW_ID');
	define('PM_TASK_COLUMN_DESCR', 'PW_DESC');
	define('PM_TASK_COLUMN_STARTDATE', 'PW_STARTDATE');
	define('PM_TASK_COLUMN_DUEDATE', 'PW_DUEDATE');
	define('PM_TASK_COLUMN_ENDDATE', 'PW_ENDDATE');
	define('PM_TASK_COLUMN_BILLABLE', 'PW_BILLABLE');
	define('PM_TASK_COLUMN_COSTESTIMATE', 'PW_COSTESTIMATE');
	define('PM_TASK_COLUMN_COSTCUR', 'PW_COSTCUR');
	define('PM_TASK_ASSIGN', 'ASSIGN');
	
	$pm_importTaskColumnNames = array(
	    PM_TASK_COLUMN_DESCR         => 'app_description_label'
	   ,PM_TASK_COLUMN_STARTDATE     => 'amr_startdate_label'
	   ,PM_TASK_COLUMN_DUEDATE       => 'amr_duedate_label'
	   ,PM_TASK_COLUMN_ENDDATE       => 'amr_completedate_label'
	   ,PM_TASK_COLUMN_BILLABLE      => 'amp_billable_label'
	   ,PM_TASK_COLUMN_COSTESTIMATE  => 'amr_cost_label'
	   ,PM_TASK_COLUMN_COSTCUR       => 'lbl_currency'
	   ,PM_TASK_ASSIGN               => 'amt_assignments_caption'
	);

	$iconv_encoding_charsets = array(
            'ascii',
            'big5',
            'cp1250',
            'cp1251',
            'cp1252',
            'cp1253',
            'cp1254',
            'cp1257',
            'cp850',
            'cp852',
            'cp866',
            'cp932',
            'euc-kr',
            'gbk',
            'koi8-r',
            'koi8-u',
            'iso-8859-1',
            'iso-8859-2',
            'iso-8859-3',
            'iso-8859-4',
            'iso-8859-5',
            'iso-8859-7',
            'iso-8859-9',
            'iso-8859-10',
            'iso-8859-13',
            'iso-8859-14',
            'iso-8859-15',
            'iso-8859-16',
            'macgreek',
            'machebrew',
            'maccentraleurope',
            'macroman',
            'shift_jis',
            'utf-8',
    );
	
    $date_formats = array(
        'DD.MM.YYYY'
       ,'MM.DD.YYYY'
       ,'MM/DD/YYYY'
       ,'YYYY-MM-DD'
    );
    
    // Task Export
?>