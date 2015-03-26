<?php

	//
	// Document Depot constants
	//

	// ZOHO
	//define( "SECRET_KEY", '' );

	// Page names
	//
	define( "PAGE_DD_CATALOG", 'catalog.php' );
	define( "PAGE_DD_DIRECTORYBUILDER", 'directorybuilder.php' );
	define( "PAGE_DD_USERRIGHTS", 'userrights.php' );
	define( "PAGE_DD_ADDMODFOLDER", 'addmodfolder.php' );
	define( "PAGE_DD_FOLDER", 'folder.php' );
	define( "PAGE_DD_ADDFILE", 'addfile.php' );
	define( "PAGE_DD_GETFILE", 'getfolderfile.php' );
	define( "PAGE_DD_GETHISTORYFILE", 'gethistoryfile.php' );
	define( "PAGE_DD_RESTORE", 'restore.php' );
	define( "PAGE_DD_COPYMOVE", 'copymove.php' );
	define( "PAGE_DD_FILEPROPERTIES", 'fileproperties.php' );
	define( "PAGE_DD_MODIFYFILE", 'modifyfile.php' );
	define( "PAGE_DD_VIEW", 'view.php' );
	define( "PAGE_DD_RECYCLED", 'service.php' );
	define( "PAGE_DD_ACCESSRIGHTS", 'accessrightsinfo.php' );
	define( "PAGE_DD_UPLOADARCHIVE", 'uploadarchive.php' );
	define( "PAGE_DD_CREATEARCHIVE", 'createarchive.php' );
	define( "PAGE_DD_GETARCHIVE", 'getarchive.php' );
	define( "PAGE_DD_SENDEMAIL", 'sendemail.php' );
	define( "PAGE_DD_FILEDESC", 'filedesc.php' );
	define( "PAGE_DD_CHECKOUT", 'checkout.php' );
	define( "PAGE_DD_PROPAGATEACCESSRIGHTS", 'propagaterights.php' );
	define( "PAGE_DD_REPORTS", 'reports.php' );
	define( "PAGE_DD_REP_SPACEBYUSERS", 'rep_spacebyusers.php' );
	define( "PAGE_DD_REP_FILETYPESSTATS", 'rep_filetypesstats.php' );
	define( "PAGE_DD_REP_RECENTUPLOADS", 'rep_recentuploads.php' );
	define( "PAGE_DD_REP_FOLDERSSUMMARY", 'rep_folderssummary.php' );
	define( "PAGE_DD_REP_FREQUPLFILES", 'rep_frequpdfiles.php' );
	define( "PAGE_DD_SETVEROVERRIDEPARAMS", 'setvoparams.php' );
	define( "PAGE_DD_SETEMAILPARAMS", 'setemailparams.php' );

	// Notifications constants
	//
	define( "DD_MAIL_NOTIFICATION", "OnFolderUpdate" );

	// Directories
	//
	define( "DD_FILES_DIR", sprintf( WBS_ATTACHMENTS_DIR."/dd/files" ) );
	define( "DD_HISTORY_DIR", sprintf( WBS_ATTACHMENTS_DIR."/dd/history" ) );
	define( "DD_ATTACHMENT_FILE_LIMIT_BYTES", 2*1024*1024 ); // 2MB

	//
	// Document operations
	//

	define( "DD_DELETEDOC", 10 );
	define( "DD_RESTOREDOC", 11 );
	define( "DD_ADDDOC", 12 );

	define( "DD_DELETEFOLDER", 13 );
	define( "DD_ADDFOLDER", 14 );

	//
	// File view options
	//

	define( "DD_GRID_VIEW", 0 );
	define( "DD_LIST_VIEW", 1 );
	define( "DD_THUMBLIST_VIEW", 2 );
	define( "DD_THUMBTILE_VIEW", 3 );

	define( "DD_FLDVIEW_GLOBAL", 'global' );
	define( "DD_FLDVIEW_LOCAL", 'local' );

	define( "DD_COLUMN_DESC", "DL_DESC" );
	define( "DD_COLUMN_FILETYPE", "DL_FILETYPE" );
	define( "DD_COLUMN_FILESIZE", "DL_FILESIZE" );
	define( "DD_COLUMN_UPLOADDATE", "DL_UPLOADDATETIME" );
	define( "DD_COLUMN_UPLOADUSER", "DL_UPLOADUSERNAME" );
	define( "DD_COLUMN_MODIFYDATETIME", "DL_MODIFYDATETIME" );
	define( "DD_COLUMN_MODIFYUSERNAME", "DL_MODIFYUSERNAME" );
	define( "DD_COLUMN_DELETED", "DL_DELETEDATETIME" );
	define( "DD_COLUMN_CHECKDATETIME", "DL_CHECKDATETIME" );

	$dd_columns = array(
							DD_COLUMN_DESC,
							DD_COLUMN_FILETYPE,
							DD_COLUMN_FILESIZE,
							DD_COLUMN_UPLOADDATE,
							DD_COLUMN_UPLOADUSER,
							DD_COLUMN_MODIFYDATETIME,
							DD_COLUMN_MODIFYUSERNAME
						);

	$dd_listModeColumns = array(
							DD_COLUMN_FILETYPE,
							DD_COLUMN_DESC,
							DD_COLUMN_FILESIZE,
							DD_COLUMN_UPLOADDATE,
							DD_COLUMN_UPLOADUSER
						);

	$dd_columnNames = array(
								DD_COLUMN_DESC => 'app_desc_field',
								DD_COLUMN_FILETYPE => 'app_type_field',
								DD_COLUMN_FILESIZE => 'app_size_field',
								DD_COLUMN_UPLOADDATE => 'app_uploaddate_field',
								DD_COLUMN_UPLOADUSER => 'app_owner_field',
								DD_COLUMN_MODIFYDATETIME => 'app_modifieddate_field',
								DD_COLUMN_MODIFYUSERNAME => 'app_modifiedby_field',
								DD_COLUMN_CHECKDATETIME => 'app_uploaddate_field'
							);

	// File upload Errors
	//
	define ( "DD_FILE_UPLOAD_ERR_OK", 0 );
	define ( "DD_FILE_UPLOAD_ERR_INI_SIZE", 1 );
	define ( "DD_FILE_UPLOAD_ERR_FORM_SIZE", 2);
	define ( "DD_FILE_UPLOAD_ERR_PARTIAL", 3 );
	define ( "DD_FILE_UPLOAD_ERR_NO_FILE", 4 );
	define ( "DD_FILE_UPLOAD_ERR_NO_TMP_DIR", 6 );


	$dd_uploadErrors = array( 
							DD_FILE_UPLOAD_ERR_OK => 'add_screen_upload_err_success',
							DD_FILE_UPLOAD_ERR_INI_SIZE => 'add_screen_upload_err_maxfile_exceed',
							DD_FILE_UPLOAD_ERR_FORM_SIZE => 'add_screen_upload_err_maxfilehtml_exceed',
							DD_FILE_UPLOAD_ERR_PARTIAL => 'add_screen_upload_err_part',
							DD_FILE_UPLOAD_ERR_NO_FILE => 'add_screen_upload_err_nofile',
							DD_FILE_UPLOAD_ERR_NO_TMP_DIR => 'add_screen_upload_err_tempmissed'
						);

	//
	// Thumbnails
	//

	define( "DD_THUMBNAILSTATE", "THUMBNAILSTATE" );
	define( "DD_THUMBENABLED", "ENABLED" );
	define( "DD_THUMBDISABLED", "DISABLED" );

	// Version control
	//

	define( "DD_VERSIONCONTROLSTATE", "VERSIONCONTROLSTATE" );
	define( "DD_VCENABLED", "ENABLED" );
	define( "DD_VCDISABLED", "DISABLED" );
	define( "DD_VERSIONOVERRIDEENABLED", "VERSIONOVERRIDEENABLED" );
	define( "DD_ZOHOEDITSTATE", "ZOHOEDITSTATE");
	define( "DD_ZOHOENABLED", "ENABLED" );
	define( "DD_ZOHODISABLED", "DISABLED" );
	define( "DD_MAXVERSIONNUM", "MAXVERSIONNUM" );
	define( "DD_ZOHOSECRETKEY", "ZOHOSECRETKEY");

	define( "DD_CHECK_IN", "IN" );
	define( "DD_CHECK_OUT", "OUT" );

	define( "DD_SKIP_FILES", "skip" );
	define( "DD_REPLACE_FILES", "replace" );

	$dd_knownImageFormats = array('jpg', 'gif', 'png');

	// Archives
	//
	define( "DD_LIMIT_EXPIRE_TIME", 200 );				// in seconds
	define( "DD_FILE_LIMIT", 52428800  );				// in bites ~50 Mb + 00
	//define( "DD_LIMIT_DOWNLOAD_SIZE", 1073741824);//1073741824 );	// in bites 1GB
	define( "DD_LIMIT_DOWNLOAD_SIZE", 314572800);//314572800 );	// in bites 300MB
	define( "DD_CREATEARCHIVE_FILES", 0 );
	define( "DD_CREATEARCHIVE_FOLDER", 1 );
	define( "DD_CREATEARCHIVE_ENTIRE", 2 );

	// Email settings
	//
	define( "DD_EMAILPARAMS", "EMAILPARAMS" );

	define( "DD_EMAILPARAMS_GLOBAL", "GLOBAL" );
	define( "DD_EMAILPARAMS_USER", "USER" );
?>