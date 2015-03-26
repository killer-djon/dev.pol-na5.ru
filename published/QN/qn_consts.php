<?php

	//
	// Address Book application constants
	//

	// Page names
	//
	define( "PAGE_QN_ADDMODFOLDER", "addmodfolder.php" );
	define( "PAGE_QN_QUICKNOTES", "quicknotes.php" );	
	define( "PAGE_QN_ADDMODNOTE", "addmodnote.php" );	
	define( "PAGE_QN_VIEW", "view.php" );
	define( "PAGE_QN_NOTE", "note.php" );
	define( "PAGE_QN_GETNOTEFILE", "getnotefile.php" );
	define( "PAGE_QN_COPYMOVE", "copymove.php" );
	define( "PAGE_QN_MANAGER", "quicknotesmanager.php" );
	define( "PAGE_QN_USERRIGHTS", "userrights.php" );	
	define( "PAGE_QN_PRINT", "print.php" );
	define( "PAGE_QN_ACCESSRIGHTS", 'accessrightsinfo.php' );
	define( "PAGE_QN_TPLLIST", "quicknotestemplates.php" );
	define( "PAGE_QN_ADDMODTPL", "addmodtpl.php" );

	// Records per page
	//
	define( "QN_RECORDS_PER_PAGE", 20 );

	// Attachments directory
	//
	define( "QN_ATTACHMENTS_DIR", sprintf( "%s/qn/attachments", WBS_ATTACHMENTS_DIR ) );

	//
	// Note view options
	//

	define( "QN_GRID_VIEW", 2 );
	define( "QN_LIST_VIEW", 1 );

	define( "QN_COLUMN_SUBJECT", "QN_SUBJECT" );
	define( "QN_COLUMN_MODIFYDATE", "QN_MODIFYDATETIME" );
	define( "QN_COLUMN_MODIFYUSER", "QN_MODIFYUSERNAME" );
	define( "QN_COLUMN_CONTENT", "QN_CONTENT" );
	define( "QN_COLUMN_ATTACHEDFILES", "ATTACHEDFILES" );
	define( "QN_COLUMN_FOLDER", "QNF_NAME" );

	$qn_columns = array(
							QN_COLUMN_CONTENT,
							QN_COLUMN_ATTACHEDFILES,
							QN_COLUMN_MODIFYUSER,
							QN_COLUMN_MODIFYDATE
						);

	$qn_columnNames = array(
							QN_COLUMN_SUBJECT => 'app_subject_field',
							QN_COLUMN_CONTENT => 'app_content_field',
							QN_COLUMN_ATTACHEDFILES => 'app_file_field',
							QN_COLUMN_MODIFYUSER => 'app_author_field',
							QN_COLUMN_MODIFYDATE => 'app_date_field',
							QN_COLUMN_FOLDER => 'app_folder_field'
							);

	
?>