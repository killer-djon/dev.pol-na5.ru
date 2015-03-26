<?php

	define('IMAGICK_COMPRESSION', 85);
	define('GD_COMPRESSION', 80);

    define('SIZE_970', 970);
    define('SIZE_750', 750);
    define('SIZE_512', 512);
    define('SIZE_256', 256);
    define('SIZE_144', 144);
    define('SIZE_96', 96);
    
	include_once dirname(__FILE__).'/../../system/init.php';
	
	if (defined('PUBLIC_AUTHORIZE') && PUBLIC_AUTHORIZE) {
		Wbs::publicAuthorize();
	}
	else {
		Wbs::authorizeUser("PD");
	}
	
	define ("APP_PATH", '/published/PD');
	
	AutoLoad::add( "ServiceSynchr_Flickr", APP_PATH."/include/ServiceSynchr/Flickr.php" );
	AutoLoad::add( "PDSmarty", APP_PATH."/include/Utils/PDSmarty.php" );
	
	AutoLoad::add( "PDWidget", APP_PATH."/include/Model/PDWidget.model.php" );
	
	AutoLoad::add( "FrontController", APP_PATH."/include/Controller/FrontController.php" );
	AutoLoad::add( "ActionController", APP_PATH."/include/Controller/ActionController.php" );
	
	// include image models
	AutoLoad::add( "PDImageFSModel", APP_PATH."/include/ImageModel/PDImageFSModel.php" );
	AutoLoad::add( "PDImageException", APP_PATH."/include/ImageModel/PDImageException.php" );

	AutoLoad::add( "WBSImageUtilsGd", APP_PATH."/include/ImageModel/WBSImageUtilsGd.php" );
	AutoLoad::add( "WBSImageUtilsIm", APP_PATH."/include/ImageModel/WBSImageUtilsIm.php" );
	AutoLoad::add( "ImageThumber", APP_PATH."/include/ImageModel/ImageThumber.php" );
	AutoLoad::add( "PDWbsImage", APP_PATH."/include/ImageModel/PDWbsImage.php" );
	
	//Utils
	AutoLoad::add( "Pager", APP_PATH."/include/Utils/Pager.php" );
	
	// include models
	AutoLoad::add( "PDAlbum", APP_PATH."/include/Model/PDAlbum.php" );
	AutoLoad::add( "PDApplication", APP_PATH."/include/Model/PDApplication.php" );
	AutoLoad::add( "PDImage", APP_PATH."/include/Model/PDImage.php" );
	//error
	AutoLoad::add( "PDException", APP_PATH."/include/Model/PDException.php" );
	
	//error handler FirePHP
	AutoLoad::add( "FB", APP_PATH."/include/Lib/FirePHPCore/fb.php" );
	AutoLoad::add( "FirePHP", APP_PATH."/include/Lib/FirePHPCore/FirePHP.class.php" );
	
	AutoLoad::add( "ErrorManager", APP_PATH."/include/Error/ErrorManager.php" );
	
//	ErrorManager::create(array(
//		'firebug' => true
////    	'file'=>'./logs/php_errors.'.date('Y-m-d').'.log'
//	));

	$lang = Env::Get('lang', Env::TYPE_STRING, CurrentUser::getLanguage());
	$lang = (strlen($lang) > 2) ? substr($lang, 0, 2) : $lang;
	
	$domain = 'webasystPD'.Wbs::getDbkeyObj()->getVersion('PD');
	if (!file_exists(dirname(__FILE__).'/locale/'.$lang.'/LC_MESSAGES/'.$domain.'.mo')) {
		$domain = 'webasystPD';
	}
	
	GetText::load($lang, realpath(dirname(__FILE__))."/locale", $domain);
	
	try {
		$updater = new WbsUpdater("PD");
		$updater->check();
	}
	catch (Exception $e) {
		var_dump($e);
	}
	
?>