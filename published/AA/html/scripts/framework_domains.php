<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );

	//
	// Authorization
	//
	
	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );
	
	$errorStr = null;
	$kernelStrings = $loc_str[$language];
	
	$framework_url = null;
	$main_xml_config = 'kernel/wbs.xml';
	if (!file_exists(WBS_DIR.$main_xml_config)) {
		$errorStr = 'File "'.$main_xml_config.'" doesn\'t exist';
	} elseif($xml = @file_get_contents(WBS_DIR.$main_xml_config)) {
		$sxml = new SimpleXMLElement($xml);
		if(sizeof(@(array)$sxml->FRAMEWORK->attributes()) != 0) {
			$fw_path = (string)$sxml->FRAMEWORK->attributes()->PATH . '/wa-config/db.php';
			$fw_path = realpath(WBS_DIR . '/'.$fw_path);
			$framework_url = (string)$sxml->FRAMEWORK->attributes()->URL;
		}
	}
	$fw_config = @include($fw_path);
	if (!empty($fw_config['default'])) {
		$fw_config = $fw_config['default'];
		$prevConnection = $wbs_database;
		$wbs_database = db_custom_connect($fw_config['database'], $fw_config['user'], $fw_config['password'], $fw_config['host'], null, 'utf8');
		if ( PEAR::isError($wbs_database)) {
			$errorStr = 'DB connect error: '.$res->getMessage();
		} else {
			$qr = "SELECT customer_contact_id FROM baza_wahost WHERE dbkey='!dbkey!'";
			$contact_id  = db_query_result($qr, DB_FIRST, array('dbkey'=>$DB_KEY));
			if ( PEAR::isError($contact_id ) ) {
				$errorStr = 'Error executing query: '.$contact_id ->getMessage();
			}
			if ( !$contact_id ) {
				$errorStr = 'Empty customer contact ID';
			}
			$hash = md5(mt_rand(1000000, 9000000));
			$hash = substr($hash, 0, 16).$contact_id.substr($hash, 16);
			$uri = '#/domains';
			
			$qr = "DELETE FROM baza_personalhash WHERE contact_id='!contact_id!'";
			$res = db_query($qr, array('contact_id'=>$contact_id));
			if ( PEAR::isError($res) ) {
				$errorStr = 'Error executing query: '.$res->getMessage();
			}
			$qr = "INSERT INTO baza_personalhash SET contact_id='!contact_id!', create_datetime=NOW(), hash='!hash!', uri='!uri!'";
			$res = db_query($qr, array('contact_id'=>$contact_id, 'hash'=>$hash, 'uri'=>$uri));
			if ( PEAR::isError($res) ) {
				$errorStr = 'Error executing query: '.$res->getMessage();
			}
		}
		$wbs_database = $prevConnection;

		if (!$errorStr) {
			header('Location: '.$framework_url.'/my/personalhash/'.$hash.'/');
			exit;
		}
	}
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );
	$preproc->assign( 'errorStr', $errorStr);
	$preproc->display( "framework_domains.htm" );

?>