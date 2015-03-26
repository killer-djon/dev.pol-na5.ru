<?php 

    define("WBS_APP_PATH", realpath(dirname(__FILE__)."/../"));
       	
	// Init system
	include_once(WBS_APP_PATH."/../../system/init.php");
	
	// Include UG autoload
    include_once(WBS_APP_PATH."/config/autoload.php");
	
    $app_id = 'ST';
    
	// Authorize
	Wbs::authorizeUser($app_id);
  
		
	// Localization (Gettext)
    $lang = Env::Get('lang', Env::TYPE_STRING, false);
    if (!$lang) {
    	$lang = User::getLang();
    }
    $lang = substr($lang, 0, 2);
    if (file_exists(WBS_APP_PATH."/locale/".$lang."/LC_MESSAGES/webasyst".$app_id.".mo")) {
    	GetText::load($lang, WBS_APP_PATH."/locale", 'webasyst'.$app_id);
    } else {
    	GetText::load($lang, WBS_APP_PATH."/locale", 'webasyst');
    }
         
    $smarty = new WbsSmarty(WBS_APP_PATH."/templates", $app_id, $lang);		
	// Set smarty
	Registry::set("smarty", $smarty);
	
	$updater = new WbsUpdater("ST");
	$updater->check();	
	
	
?>