<?php

class designController extends ActionController 
{
	public function init() 
	{
		$this->defaultAction = 'editor';
		
		
		$preproc = new PDSmarty();
		$this->setView( $preproc );
		$this->view->assign('sessionId', session_id());
		
		$this->view->assign('head', $this->view->fetch('designeditor-include.html') );
		
		$right = new Rights(User::getId());
        $manage_collections = $right->get('PD', Rights::FUNCTIONS, 'manage_collections', Rights::MODE_ONE);
        $modify_design = $right->get('PD', Rights::FUNCTIONS, 'modify_design', Rights::MODE_ONE);
        
        $this->view->assign('manage_collections', $manage_collections);
        $this->view->assign('modify_design', $modify_design);
		
	}	
	
	public function editorAction()
	{
	    
        $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FUNCTIONS, 'MODIFY_DESIGN', Rights::MODE_ONE, Rights::RETURN_BOOL) ) {
	        
	        header("HTTP/1.0 403 Forbidden");
            header("HTTP/1.1 403 Forbidden");
            header("Status: 403 Forbidden");
            echo "403 Forbidden";
            exit;
	    }		    
	    
//        $dir = Wbs::getSystemObj()->getWebUrl();
//		$dir = ( $dir == '/' || empty($dir) ) ? '' : $dir;
//		$mainUrl = Url::getServerUrl()
//		                .$dir
//						.'/photos/';
						
		$mainUrl = PDApplication::getFrontendUrl('');
        $frontend_link = $mainUrl;		
//		if( Wbs::getSystemObj()->isModeRewrite() || wbs::isHosted()) 
//			$mainUrl .= 'album/';		
//		else
//			$mainUrl .= 'index.php?album=';				
			
		$mainUrl = PDApplication::getFrontendUrl('', array(
		    'album/',
		    'index.php?album='
		));
        $hostUrl = Url::get('/', true);			
        $this->view->assign('frontend_link', $frontend_link);		    
	    
//		$themePath = './source/templates/themes/default';
		
		if ( !file_exists( PDApplication::getDataUserThemesPath('default/main.css') ) )
			$csscode = file_get_contents('./templates/themes/default/main.css');
		else
			$csscode = file_get_contents(PDApplication::getDataUserThemesPath('default/main.css'));
		
        $user_settings_model = new UserSettingsModel();
        $galery_name = $user_settings_model->get("", "PD", "GalleryName");
		$this->view->assign('galery_name', $galery_name);
				
		$this->view->assign('csscode', $csscode);		
		
		$content = $this->view->fetch('designeditor.html');
		$this->view->assign('content', $content);
		
		$this->view->assign('menu', 'design');

		$this->view->display('main.html');
	}
	
	public function savedesignAction()
	{		
		$csscode = Env::Post('csscode', Env::TYPE_STRING);
		
		if ( !file_exists(PDApplication::getDataUserThemesPath('default')) ) {
			self::mkdir(PDApplication::getDataUserThemesPath('default'));
		}
		
		file_put_contents(PDApplication::getDataUserThemesPath('default/main.css'), $csscode);
		
		session_write_close();
		header( "Location: backend.php?controller=design" );
		exit;
	}
	
	static function mkdir($dir, $mode = 0777, &$errStr = '', $basedir = null )
     {
      //$currentDir=getcwd();
      if ( is_null($basedir) ){
       $basedir = WBS_DIR;
      }
      $mode &= 0777;
    
      $basedir = trim(preg_replace('@([/\\\\]+)@','/',$basedir.'/'));
      $dir = trim(preg_replace(array('@([/\\\\]+)@','@[/\\\\]$@'),array('/',''),$dir));
    
      if(strpos($dir,$basedir) === 0){
       $dir = substr( $dir,strlen( $basedir ) );
      }else{
       $basedir = '';
      }
    
      
      
      $path_parts = explode('/', $dir);
      $current_path = preg_replace('@[/\\\\]$@','',$basedir);
      $oldMask = @umask(0);
      foreach ($path_parts as $path_part) {
       if(strlen($path_part)){
        $current_path.=($current_path?'/':'').$path_part;
        if (file_exists($current_path)){
         if(!is_dir($current_path)){
          $errStr = sprintf( "Unable to create directory: %s is file", $current_path );
          break;
         }
        }else{
         if(!@mkdir($current_path, 0777)){
          $errStr = sprintf( "Unable to create directory %s", $current_path );
          break;
         }
          
        }
       }
      }
      @umask($oldMask);
      //chdir( $currentDir );
      return (strlen($errStr)>0)?false:true;
     }
	
	public function restoredesignAction()
	{
		$this->removeDir(PDApplication::getDataUserThemesPath('default'));
		print json_encode(array('status' => 'ok'));
	}
	
 	private function removeDir($path) 
    {
    	if ( !file_exists($path) )
    		return 1;
		$dir = opendir($path);
		while(($file = readdir($dir))) {
			if ( is_file ($path."/".$file)) {
				unlink ($path."/".$file);
			}
		}
		closedir ($dir);
		rmdir ($path);    		
    }
    
    public function saveGallaryNameAction()
    {
        $name = Env::Post('galleryName', Env::TYPE_STRING);

        
        $user_settings_model = new UserSettingsModel();
        $user_settings_model->set("", "PD", "GalleryName", htmlspecialchars($name));
    }
		
}

?>