<?php

	//
	// Address Book application constants
	//

	define( "MAIN_XML_CONFIG", "kernel/wbs.xml" );

	// Page names
	//
	define( "PAGE_MM_ADDMODFOLDER", "addmodfolder.php" );
	define( "PAGE_MM_MAILMASTER", "mailmaster.php" );
	define( "PAGE_MM_ADDMODMESSAGE", "addmodmessage.php" );
	define( "PAGE_MM_VIEW", "view.php" );
	define( "PAGE_MM_MESSAGE", "addmodmessage.php" );
	define( "PAGE_MM_GETMSGFILE", "getmsgfile.php" );
	define( "PAGE_MM_GETMSGIMAGE", "getmsgimage.php" );
	define( "PAGE_MM_GETSENTIMG", "getsentimg.php" );
	define( "PAGE_MM_COPYMOVE", "copymove.php" );
	define( "PAGE_MM_MANAGER", "quicknotesmanager.php" );
	define( "PAGE_MM_USERRIGHTS", "userrights.php" );
	define( "PAGE_MM_PRINT", "print.php" );
	define( "PAGE_MM_ACCESSRIGHTS", 'accessrightsinfo.php' );
	define( "PAGE_MM_TPLLIST", "quicknotestemplates.php" );
	define( "PAGE_MM_ADDMODTPL", "addmodtpl.php" );

	define( "PAGE_MM_ADDIMAGES", "addimages.php" );

	define( "PAGE_MM_SAVEAS", "saveas.php" );

	define( "PAGE_MM_SEND", "sendmessage.php" );
	define( "PAGE_MM_TESTSEND", "testsend.php" );

	define( "PAGE_MM_FROMTPL", "fromtpl.php" );

	define( "PAGE_MM_SERVICE", "service.php" );
	define( "PAGE_MM_ADDMODSENDER", "addmodsender.php" );

	define( "PAGE_MM_ACCOUNTS", "accounts.php" );
	define( "PAGE_MM_ADDMODACCOUNT", "addmodaccount.php" );
	define( "PAGE_MM_SELECTMODACCOUNT", "selectmodaccount.php" );

	// Records per page
	//
	define( "MM_RECORDS_PER_PAGE", 20 );

	// Attachments directory
	//
	define( "MM_ATTACHMENTS_DIR", sprintf( "%s/mm/attachments", WBS_ATTACHMENTS_DIR ) );
	define( "MM_IMAGES_DIR", sprintf( "%s/mm/images", WBS_ATTACHMENTS_DIR ) );

	//
	// Subscribe emails count for sending
	// email _as_soon_as_possible_
	//

	define( "MM_ADJOURN", 50);

	//
	// Note view options
	//

	define( "MM_COLUMN_PRIORITY", "MMM_PRIORITY" );
	define( "MM_COLUMN_ATTACHEDFILES", "ATTACHEDFILES" );
	define( "MM_COLUMN_SUBJECT", "MMM_SUBJECT" );
	define( "MM_COLUMN_STATUS", "MMM_STATUS" );
	define( "MM_COLUMN_FROM", "MMM_FROM" );
	define( "MM_COLUMN_TO", "MMM_TO" );
	define( "MM_COLUMN_SIZE", "MMM_SIZE" );
	define( "MM_COLUMN_DATE", "MMM_DATETIME" );

	$mm_columns = array(
		MM_COLUMN_PRIORITY,
		MM_COLUMN_ATTACHEDFILES,
		MM_COLUMN_SUBJECT,
		MM_COLUMN_STATUS,
		MM_COLUMN_FROM,
		MM_COLUMN_TO,
		MM_COLUMN_SIZE,
		MM_COLUMN_DATE
	);

	$mm_columnNames = array(
		MM_COLUMN_PRIORITY => 'app_priority_field',
		MM_COLUMN_ATTACHEDFILES => 'app_attachment_field',
		MM_COLUMN_SUBJECT => 'app_subject_field',
		MM_COLUMN_STATUS => 'app_status_field',
		MM_COLUMN_FROM => 'app_from_field',
		MM_COLUMN_TO => 'app_to_field',
		MM_COLUMN_SIZE => 'app_size_field',
		MM_COLUMN_DATE => 'app_date_field'
	);

	define( "MM_STATUS_DRAFT", 0 );
	define( "MM_STATUS_PENDING", 1 );
	define( "MM_STATUS_SENDING", 2 );
	define( "MM_STATUS_SENT", 3 );
	define( "MM_STATUS_RECEIVED", 4 );
	define( "MM_STATUS_TEMPLATE", 100 );
	define( "MM_STATUS_ERROR", 99 );

	$mm_statusNames = array(
		MM_STATUS_DRAFT => 'app_draft_status',
		MM_STATUS_PENDING => 'app_pending_status',
		MM_STATUS_SENDING => 'app_sending_status',
		MM_STATUS_SENT => 'app_sent_status',
		MM_STATUS_RECEIVED => 'app_received_status',
		MM_STATUS_TEMPLATE => 'app_template_status',
		MM_STATUS_ERROR => 'app_error_status'
	);

	$mm_statusStyle = array(
		MM_STATUS_DRAFT => 'color: #006400',
		MM_STATUS_PENDING => 'color: #006400; font-style: italic',
		MM_STATUS_SENDING => 'color: #006400; font-weight: bold; font-style: italic',
		MM_STATUS_SENT => 'color: #a52a2a',
		MM_STATUS_RECEIVED => 'color: #000000',
		MM_STATUS_TEMPLATE => 'color: #2C3EE6',
		MM_STATUS_ERROR => 'color: #FF0000; font-weight: bold',
		98 => 'color: #FF0000; font-style: italic'
	);

	define( "MM_TYPE_TEXT", 0 );
	define( "MM_TYPE_HTML", 1 );

	define( "MM_PRIORITY_LOW", 0 );
	define( "MM_PRIORITY_NORMAL", 1 );
	define( "MM_PRIORITY_HIGH", 2 );

	$mm_priorityNames = array(
							MM_PRIORITY_LOW => 'app_low_priority',
							MM_PRIORITY_NORMAL => 'app_normal_priority',
							MM_PRIORITY_HIGH => 'app_high_priority'
						);

	$mm_message_data_schema = array(

		"MMM_ID" => array(
									"TYPE" => "numeric",
									"DEFAULT" => 0
								),

		"MMF_ID" => array(
									"TYPE" => "text",
									"REQ"=>true
								),

		"MMM_FROM" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>100,
									"REQ"=>true
								),

		"MMM_TO" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"REQ"=>true
								),

		"MMM_CONTENT" => array(
									"TYPE" => "text",
									"DEFAULT" => ""
								),

		"MMM_SUBJECT" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250
								),

		"MMM_STATUS" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>MM_STATUS_DRAFT, "NAME"=>"app_draft_status" ),
													array( "VALUE"=>MM_STATUS_PENDING, "NAME"=>"app_pending_status" ),
													array( "VALUE"=>MM_STATUS_SENT, "NAME"=>"app_sent_status" ),
													array( "VALUE"=>MM_STATUS_TEMPLATE, "NAME"=>"app_template_status" ),
													array( "VALUE"=>MM_STATUS_ERROR, "NAME"=>"app_error_status" )
											),
									"DEFAULT" => MM_STATUS_DRAFT
								),

		"MMM_PRIORITY" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>MM_PRIORITY_LOW, "NAME"=>"app_low_priority" ),
													array( "VALUE"=>MM_PRIORITY_NORMAL, "NAME"=>"app_normal_priority" ),
													array( "VALUE"=>MM_PRIORITY_HIGH, "NAME"=>"app_high_priority" )
											),
									"DEFAULT" => MM_TYPE_HTML
								),

		"MMM_DATETIME" => array(
									"TYPE" => "text",
									"DEFAULT" => null
								),

		"MMM_USERID" => array(
									"TYPE" => "text",
									"LEN" => 50,
									"DEFAULT" => null
								),

		"MMM_ATTACHMENT" => array(
									"TYPE" => "text",
									"DEFAULT" => null
								),

		"MMM_IMAGES" => array(
									"TYPE" => "text",
									"DEFAULT" => null
								)

	);

	$mm_msgTypes = array( MM_TYPE_TEXT=>"app_text_type", MM_TYPE_HTML=>"app_html_type" );

	$mm_sender_data_schema = array(

		"MMS_ID" => array(
									"TYPE" => "numeric",
									"DEFAULT" => 0
								),


		"MMS_FROM" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),

		"MMS_EMAIL" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMS_REPLYTO" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMS_RETURNPATH" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),

		"MMS_LANGUAGE" => array(
									"TYPE" => "text",
									"DEFAULT" => LANG_ENG,
									"LEN"=>20,
									"REQ"=>true
								),

		"MMS_ENCODING" => array(
									"TYPE" => "text",
									"DEFAULT" => "iso-8859-1",
									"LEN"=>20
								)
	);

	$mm_account_data_schema = array(

		"MMA_ID" => array(
									"TYPE" => "numeric",
									"DEFAULT" => 0
								),
		"MMA_NAME" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMA_EMAIL" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMA_DOMAIN" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMA_SERVER" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMA_LOGIN" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>true
								),
		"MMA_PASSWORD" => array(
									"TYPE" => "text",
									"DEFAULT" => "",
									"LEN"=>250,
									"REQ"=>false
								),
		"MMA_PORT" => array(
									"TYPE" => "numeric",
									"DEFAULT" => 110
								),
		"MMA_PROTOCOL" => array(
									"TYPE" => "select",
									"OPTIONS" => array(
													array( "VALUE"=>"POP3", "NAME"=>"POP3" ),
													array( "VALUE"=>"IMAP", "NAME"=>"IMAP" ),
											),
									"DEFAULT" => "POP3"
								),
		"MMA_SECURE" => array(
									"TYPE" => "checkbox",
									"DEFAULT" => ""
								),
		"MMA_INTERNAL" => array(
									"TYPE" => "text",
									"DEFAULT" => ""
								),
		"MMA_ACCESS" => array(
									"TYPE" => "numeric",
									"DEFAULT" => 0
								)
	);


	define( "MM_ATTACHMENTS", 0 );
	define( "MM_IMAGES", 1 );

	$contactSectionsNames = array(
		VS_CONTACT => array( 'NAME'=>'amn_vars_contact_name', 'COMMENT'=>'amn_vars_contact_comment' ),
		VS_CURRENT_USER => array( 'NAME'=> 'amn_vars_my_contact_name', 'COMMENT'=> 'amn_vars_my_contact_comment' ),
		VS_COMPANY => array( 'NAME'=>'amn_vars_company_name', 'COMMENT'=> 'amn_vars_company_comment' )
	);

	$mm_EditorEnabledStatuses = array( MM_STATUS_DRAFT, MM_STATUS_TEMPLATE );

	define('MM_OPT_RECIPIENTS_LIMIT', 'RECIPIENTS_LIMIT' );
	define('MM_OPT_DAILY_RECIPIENTS_LIMIT', 'DAILY_RECIPIENTS_LIMIT' );
	define('MM_OPT_DISABLE_EDITING_FOOTER', 'DISABLE_EDITING_FOOTER' );
	define('MM_OPT_DISABLE_UNSUBSCRIBE_FOOTER', 'DISABLE_UNSUBSCRIBE_FOOTER' );
	define('MM_OPT_SENDNOW_RECIPIENTS_LIMIT', 'SENDNOW_RECIPIENTS_LIMIT' );
	define('MM_OPT_ATTACHMENT_SIZE_LIMIT', 'ATTACHMENT_SIZE_LIMIT' );

	define('MM_VAR_UNSIBSCRIBE_URL', '{MANAGE_YOUR_SUBSCRIPTION_URL}');

	define('MM_GRID_VIEW', 'MM_GRID_VIEW');
	define('MM_LIST_VIEW', 'MM_LIST_VIEW');

	define('MM_ERROR_ACCOUNT_EXISTS', 1);

	define('SEND_NOW_MAX_EMAILS', 5);

	$xml = file_get_contents( WBS_DIR . MAIN_XML_CONFIG );
	$sxml = new SimpleXMLElement($xml);
	if(isset($sxml->POP_IMAP_SERVER) && sizeof( (array)$sxml->POP_IMAP_SERVER->attributes() ) != 0 )
		$host = (string)$sxml->POP_IMAP_SERVER->attributes()->host;
	else
		$host = '';
	define('MAIL_SERVER', $host);

	define('CONTACTS_PER_PAGE', 100);
	define('MESSAGES_PER_PAGE', 30);
	define('ERROR_STRING', '<font color="red"><b>%s</b></font>');

//	define('MM_RECEIVE_SIZE_LIMIT', 2000000);

	$ignoreDeletedMessages = array('pop.gmail.com'); // Example: 'pop.gmail.com', 'imap.gmail.com'

?>