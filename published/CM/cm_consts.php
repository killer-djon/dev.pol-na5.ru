<?php

	//
	// Contact Manager constants
	//

	// Page names
	//
	define( "PAGE_CM_SIGNUPFORM", 'signupform.php' );
	define( "PAGE_CM_CONFIRMSIGNUPFORM", 'confirm.php' );
	// Object types
	//
	define( 'CM_OT_FOLDERS', 'FOLDERS' );
	define( 'CM_OT_USERGROUPS', 'USERGROUPS' );
	define( 'CM_OT_LISTS', 'LISTS' );
	define( 'CM_OT_SEARCH_RESULT', 'SEARCH_RESULTS' );
        
        define( 'CM_CONTACT_LIMIT', 100 );

	// Display features
	//
	define( "CM_GRID_VIEW", 'GRID' );
	define( "CM_LIST_VIEW", 'LIST' );

	$cm_defaultColumnSet = array( CONTACT_NAMEFIELD, CONTACT_EMAILFIELD );
	$cm_listColumnSet =  array( CONTACT_NAMEFIELD,
								CONTACT_FIRSTNAMEFIELD,
								CONTACT_LASTNAMEFIELD,
								CONTACT_NICKNAMEFIELD,
								CONTACT_EMAILFIELD );

	define( "CM_DISPLAYCONTACT_MODE", 'DISPLAYCONTACT_MODE' );

	// Contact field type name
	//
	$cm_contactFieldTypeNames = array(
									CONTACT_FT_TEXT => 'sv_texttype_name',
									CONTACT_FT_URL => 'sv_urltype_name',
									CONTACT_FT_EMAIL => 'sv_emailtype_name',
									CONTACT_FT_MEMO => 'sv_memotype_name',
									CONTACT_FT_IMAGE => 'sv_imagetype_name',
									CONTACT_FT_DATE => 'sv_datetype_name',
									CONTACT_FT_NUMERIC => 'sv_numerictype_name',
									CONTACT_FT_MENU => 'sv_menutype_name',
								);

	// Contact field mySQL types
	//
	$cm_contactFieldMySQLType = array(
									CONTACT_FT_TEXT => 'TEXT',
									CONTACT_FT_URL => 'TEXT',
									CONTACT_FT_EMAIL => 'TEXT',
									CONTACT_FT_MEMO => 'TEXT',
									CONTACT_FT_MENU => 'TEXT',
									CONTACT_FT_DATE => 'DATE',
									CONTACT_FT_NUMERIC => 'FLOAT',
									CONTACT_FT_IMAGE => 'TEXT'
								);

	// Database field names prefix
	//
	define( 'CM_FIELD_PREFIX', 'C_X_' );

	// Field position constants
	//
	define( 'CM_PLACEINTHEBOTTOM', 'BOTTOM' );
	define( 'CM_SECTIONQUALIFIER', 'SECTION' );

	define( 'CM_FIELDSYMBOLS', "abcdefghijklmnopqrstuvwxyz0123456789 ,.-/" );

	// Folder view options
	//
	define( "CM_FLDVIEW_GLOBAL", 'global' );
	define( "CM_FLDVIEW_LOCAL", 'local' );

	// Image fields view options
	//
	define( "CM_IMAGESVIEW_THUMBNAILS", 'thumbnails' );
	define( "CM_IMAGESVIEW_LINKS", 'links' );

	define( "CM_LISTVIEW_NOIMG", 'NOIMG' );

	// Signup form constants
	//
	define( "CM_SIGNUPFIELD_NAME", 'NAME' );
	define( "CM_SIGNUPFIELD_REQUIRED", 'REQUIRED' );
	define( "CM_SIGNUPFIELD_WIDTH", 'WIDTH' );

	define( "CM_SIGNUP_HTML", 'SIGNUP_HTML' );
	define( "CM_SIGNUP_USER", 'SIGNUP_USER' );
	define( "CM_SIGNUP_DATE", 'SIGNUP_DATE' );

	define( "CM_SIGNUP_DATA", 'SIGNUP_DATA' );

	define( "CM_SUBSCRIBERUSENAME", "Subscriber" );
	
	define ("CM_CANTOOLS_RIGHTS", "CANTOOLS");
	define ("CM_CANREPORTS_RIGHTS", "CANREPORTS");

?>
