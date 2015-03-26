<?php

	//
	// Document Depot constants
	//
    Error_Reporting(E_ALL & ~E_NOTICE);
	// Page names
	//
	define( "PAGE_PD_CATALOG", 'catalog.php' );
	define( "PAGE_PD_DIRECTORYBUILDER", 'directorybuilder.php' );
	define( "PAGE_PD_USERRIGHTS", 'userrights.php' );
	define( "PAGE_PD_ADDMODFOLDER", 'addmodfolder.php' );
	define( "PAGE_PD_FOLDER", 'folder.php' );
	define( "PAGE_PD_ADDFILE", 'addfile.php' );
	define( "PAGE_PD_GETFILE", 'getfolderfile.php' );
	define( "PAGE_PD_GETHISTORYFILE", 'gethistoryfile.php' );
	define( "PAGE_PD_RESTORE", 'restore.php' );
	define( "PAGE_PD_COPYMOVE", 'copymove.php' );
	define( "PAGE_PD_FILEPROPERTIES", 'fileproperties.php' );
	define( "PAGE_PD_MODIFYFILE", 'modifyfile.php' );
	define( "PAGE_PD_VIEW", 'view.php' );
	define( "PAGE_PD_RECYCLED", 'service.php' );
	define( "PAGE_PD_ACCESSRIGHTS", 'accessrightsinfo.php' );
	define( "PAGE_PD_UPLOADARCHIVE", 'uploadarchive.php' );
	define( "PAGE_PD_UPLOADFILE", 'uploadfile.php' );
	define( "PAGE_PD_CREATEARCHIVE", 'createarchive.php' );
	define( "PAGE_PD_GETARCHIVE", 'getarchive.php' );
	define( "PAGE_PD_SENDEMAIL", 'sendemail.php' );
	define( "PAGE_PD_FILEDESC", 'filedesc.php' );
	define( "PAGE_PD_CHECKOUT", 'checkout.php' );
	define( "PAGE_PD_PROPAGATEACCESSRIGHTS", 'propagaterights.php' );
	define( "PAGE_PD_REPORTS", 'reports.php' );
	define( "PAGE_PD_REP_SPACEBYUSERS", 'rep_spacebyusers.php' );
	define( "PAGE_PD_REP_FILETYPESSTATS", 'rep_filetypesstats.php' );
	define( "PAGE_PD_REP_RECENTUPLOADS", 'rep_recentuploads.php' );
	define( "PAGE_PD_REP_FOLDERSSUMMARY", 'rep_folderssummary.php' );
	define( "PAGE_PD_REP_FREQUPLFILES", 'rep_frequpdfiles.php' );
	define( "PAGE_PD_SETVEROVERRIDEPARAMS", 'setvoparams.php' );
	define( "PAGE_PD_SETEMAILPARAMS", 'setemailparams.php' );
	define( "PAGE_PD_SHARE_ALBUM", 'sharealbum.php');
	define( "PAGE_PD_VIEW_SHARED_ALBUM", 'view_shared_album.php');
	define( "PAGE_PD_GETIMAGEFILE", 'getimagefile.php' );
	define( "PAGE_PD_GETFILETHUMB", 'getfilethumb.php' );
	define( "PAGE_PD_VIEWIMAGEDETAILS", 'viewimagedetails.php' );
    
    // Photo depot HTTP path
    define( "PD_HTTP_PATH", "http://".$_SERVER["SERVER_NAME"].substr( $_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-strlen(basename($_SERVER['SCRIPT_NAME'])) ));
    define( "PD_ROOT_HTTP_PATH", "http://".$_SERVER["SERVER_NAME"]."/published/" );
	// Notifications constants
	//
	define( "PD_MAIL_NOTIFICATION", "OnFolderUpdate" );

	// Directories
	//
	if (defined("WBS_ATTACHMENTS_DIR")) {
	    define( "PD_FILES_DIR", sprintf( WBS_ATTACHMENTS_DIR."/pd/files" ) );
	    define( "PD_HISTORY_DIR", sprintf( WBS_ATTACHMENTS_DIR."/pd/history" ) );
    }

	//
	// Document operations
	//

	define( "PD_DELETEDOC", 10 );
	define( "PD_RESTOREDOC", 11 );
	define( "PD_ADDDOC", 12 );

	define( "PD_DELETEFOLDER", 13 );
	define( "PD_ADDFOLDER", 14 );

	//
	// File view options
	//

	define( "PD_GRID_VIEW", 0 );
	define( "PD_LIST_VIEW", 1 );
	define( "PD_THUMBLIST_VIEW", 2 );
	define( "PD_THUMBTILE_VIEW", 3 );

	define( "PD_FLDVIEW_GLOBAL", 'global' );
	define( "PD_FLDVIEW_LOCAL", 'local' );

	define( "PD_COLUMN_DESC", "PL_DESC" );
	define( "PD_COLUMN_FILETYPE", "PL_FILETYPE" );
	define( "PD_COLUMN_FILESIZE", "PL_FILESIZE" );
	define( "PD_COLUMN_UPLOADDATE", "PL_UPLOADDATETIME" );
	define( "PD_COLUMN_UPLOADUSER", "PL_UPLOADUSERNAME" );
	define( "PD_COLUMN_MODIFYDATETIME", "PL_MODIFYDATETIME" );
	define( "PD_COLUMN_MODIFYUSERNAME", "PL_MODIFYUSERNAME" );
	define( "PD_COLUMN_DELETED", "PL_DELETEDATETIME" );

	$pd_columns = array(
							PD_COLUMN_DESC,
							PD_COLUMN_FILETYPE,
							PD_COLUMN_FILESIZE,
							PD_COLUMN_UPLOADDATE,
							PD_COLUMN_UPLOADUSER,
							PD_COLUMN_MODIFYDATETIME,
							PD_COLUMN_MODIFYUSERNAME
						);

	$pd_listModeColumns = array(
							PD_COLUMN_DESC,
							PD_COLUMN_FILESIZE,
							PD_COLUMN_UPLOADDATE,
							PD_COLUMN_UPLOADUSER
						);

	$pd_columnNames = array(
								PD_COLUMN_DESC => 'app_desc_field',
								PD_COLUMN_FILETYPE => 'app_type_field',
								PD_COLUMN_FILESIZE => 'app_size_field',
								PD_COLUMN_UPLOADDATE => 'app_uploaddate_field',
								PD_COLUMN_UPLOADUSER => 'app_owner_field',
								PD_COLUMN_MODIFYDATETIME => 'app_modifieddate_field',
								PD_COLUMN_MODIFYUSERNAME => 'app_modifiedby_field'
							);

	// File upload Errors
	//
	define ( "PD_FILE_UPLOAD_ERR_OK", 0 );
	define ( "PD_FILE_UPLOAD_ERR_INI_SIZE", 1 );
	define ( "PD_FILE_UPLOAD_ERR_FORM_SIZE", 2);
	define ( "PD_FILE_UPLOAD_ERR_PARTIAL", 3 );
	define ( "PD_FILE_UPLOAD_ERR_NO_FILE", 4 );
	define ( "PD_FILE_UPLOAD_ERR_NO_TMP_DIR", 6 );
	define ( "PD_FILE_UPLOAD_EXC_LIMIT", 7 );
	define ( "PD_FILE_UPLOAD_EXC_DB_SIZE_LIMIT", 71 );
	define ( "PD_CANT_COPY_INTO_SAME_ALBUM", 8 );


	$pd_uploadErrors = array(
							PD_FILE_UPLOAD_ERR_OK => 'add_screen_upload_err_success',
							PD_FILE_UPLOAD_ERR_INI_SIZE => 'add_screen_upload_err_maxfile_exceed',
							PD_FILE_UPLOAD_ERR_FORM_SIZE => 'add_screen_upload_err_maxfilehtml_exceed',
							PD_FILE_UPLOAD_ERR_PARTIAL => 'add_screen_upload_err_part',
							PD_FILE_UPLOAD_ERR_NO_FILE => 'add_screen_upload_err_nofile',
							PD_FILE_UPLOAD_ERR_NO_TMP_DIR => 'add_screen_upload_err_tempmissed'
						);

	//
	// Thumbnails
	//

	define("PD_THUMBNAILSTATE", "THUMBNAILSTATE");
	define("PD_THUMBENABLED", "ENABLED");
	define("PD_THUMBDISABLED", "DISABLED");

	define("PD_DEFAULT_THUMB_SIZE", 96);
	define("PD_ULTRA_SMALL_THUMB_SIZE", 30);
	define("PD_144_SIZE", 144);
	define("PD_SMALL_THUMB_SIZE", 256);
	define("PD_MEDIUM_THUMB_SIZE", 512);
	define("PD_LARGE_THUMB_SIZE", 1024);

	// Version control
	//

	define( "PD_VERSIONCONTROLSTATE", "VERSIONCONTROLSTATE" );
	define( "PD_VCENABLED", "ENABLED" );
	define( "PD_VCDISABLED", "DISABLED" );

	define( "PD_CHECK_IN", "IN" );
	define( "PD_CHECK_OUT", "OUT" );

	define( "PD_SKIP_FILES", "skip" );
	define( "PD_REPLACE_FILES", "replace" );

	$pd_knownImageFormats = array('jpg', 'gif', 'png');

	// Archives
	//
	define( "PD_CREATEARCHIVE_FILES", 0 );
	define( "PD_CREATEARCHIVE_FOLDER", 1 );
	define( "PD_CREATEARCHIVE_ENTIRE", 2 );

	// Email settings
	//
	define( "PD_EMAILPARAMS", "EMAILPARAMS" );
	define( "PD_EMAILPARAMS_GLOBAL", "GLOBAL" );
	define( "PD_EMAILPARAMS_USER", "USER" );
	
	define("PD_RESIZE_OPTIONS_SMALL_WIDTH", 640);
	define("PD_RESIZE_OPTIONS_SMALL_HEIGHT", 480);
	
	define("PD_RESIZE_OPTIONS_MEDIUM_WIDTH", 800);
	define("PD_RESIZE_OPTIONS_MEDIUM_HEIGHT", 600);
	
	define("PD_RESIZE_OPTIONS_LARGE_WIDTH", 1024);
	define("PD_RESIZE_OPTIONS_LARGE_HEIGHT", 768);
	
	// Applet uploader settings
	define( "PD_APPLET_UPLOAD_DESTINATION", PD_HTTP_PATH.PAGE_PD_UPLOADFILE );
    define( "PD_APPLET_FINISH_DESTINATION", PD_HTTP_PATH.PAGE_PD_ADDFILE);
    
    // Action names
    define( "PD_ACTION_SHARE", "share" );
	
	// View shared album
	define( "PD_SHARED_ALBUM_BASE_LINK", PD_HTTP_PATH.PAGE_PD_VIEW_SHARED_ALBUM );
	define( "PD_SHARED_TEMPLATES_ROOT", "../../data/templates/share_album/");
	define( "PD_SHARED_DEFAULT_TEMPLATES_DIR", PD_SHARED_TEMPLATES_ROOT."default/");
	
	// Recycle bin
	define("PD_ENABLE_RECYCLE_BIN", false);
	
	// Other
	define("PD_WITH_SUBDIRS", false);
	define("PD_DEFAULT_OUTPUT_IMAGES_FORMAT", "JPG");
	
	define("PD_SLIDESHOW_DEFAULT_THUMBS_COUNT", 7);
	define("PD_SLIDESHOW_CONST_PREF", "f4r");
?>