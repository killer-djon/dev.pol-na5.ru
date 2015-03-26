<?php

class albumController extends ActionController 
{
	public function init() 
	{
		$this->defaultAction = 'albums';
		
		
		$preproc = new PDSmarty();
		$this->setView( $preproc );
		$this->view->assign('sessionId', session_id());
		
		$this->view->assign('head', $this->view->fetch('album-include.html') );

        $right = new Rights(User::getId());
        $manage_collections = $right->get('PD', Rights::FUNCTIONS, 'manage_collections', Rights::MODE_ONE);
        $modify_design = $right->get('PD', Rights::FUNCTIONS, 'modify_design', Rights::MODE_ONE);
        
        $this->view->assign('manage_collections', $manage_collections);
        $this->view->assign('modify_design', $modify_design);
        $this->view->assign('memory_limit', ini_get('memory_limit'));
	}	
		
	public function albumsAction()
	{
//		$dir = Wbs::getSystemObj()->getWebUrl();
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
		$this->view->assign('mainUrl', $mainUrl);
		$this->view->assign('hostUrl', $hostUrl);
		
		$content = $this->view->fetch('albums.html');
		$this->view->assign('content', $content);
		
		$this->view->assign('menu', 'albums');

		$this->view->display('main.html');
	}
	
	
	
	public function albumSettingAction()
	{
//		$dir = Wbs::getSystemObj()->getWebUrl();
//		$dir = ( $dir == '/' || empty($dir) ) ? '' : $dir;
//		$mainUrl = 'http://'.$_SERVER['HTTP_HOST']
//		                .$dir
//						.'/photos/';
//		
//		if( Wbs::getSystemObj()->isModeRewrite() || Wbs::isHosted()) 
//			$mainUrl .= 'album/';		
//		else
//			$mainUrl .= 'index.php?album=';
	    $mainUrl = PDApplication::getFrontendUrl('', array(
		    'album/',
		    'index.php?album='
		));
		
	    $this->view->assign('mainUrl', $mainUrl);

		
		$albumId = Env::Get('albumId', Env::TYPE_STRING, 1);
		
		$sql = new CSelectSqlQuery('PIXFOLDER');
		$sql->addConditions('PF_ID', $albumId);
		$row = Wdb::getRow($sql);
		
		$link = (empty($row['PF_LINK'])) ? StringUtils::translit(htmlspecialchars($row['PF_NAME'])) : htmlspecialchars($row['PF_LINK']);
		
		$link = mb_strtolower($link);
		
		$row['PF_NAME'] = htmlspecialchars( StringUtils::truncate($row['PF_NAME'], 20));
		
		try {
			$setting = new SimpleXMLElement($row['PF_SETTING']);
		}
		catch (Exception $e) {}
		
		if ( empty($setting->frontend) )
			$setting->frontend = 1;
		
		$this->view->assign('frontend', $setting->frontend);
		$this->view->assign('foto_size', $setting->{'foto-size'});
		$this->view->assign('foto_thumb', $setting->{'foto-thumb'});
		$this->view->assign('foto_count', $setting->{'foto-count'});
		$this->view->assign('view_oreginal', $setting->{'view-oreginal'});
				
		$this->view->assign('album', $row);
		$this->view->assign('link', $link);
		
		$this->view->display('album-settings.html');
	}	
	public function albumSettingSaveAction()
	{
		$albumId = (int)Env::Get('albumId', Env::TYPE_INT);
		
	    $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $albumId, Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
		
		$albumLink =  mb_strtolower(Env::Post('album-link', Env::TYPE_STRING_TRIM, ''));
		$albumLink = str_replace('/', '', $albumLink); 
		if ( empty( $albumLink ) ) {
			$sql = new CSelectSqlQuery('PIXFOLDER');
			$sql->setSelectFields('PF_NAME');
			$sql->addConditions('PF_ID', $albumId);
			$name = Wdb::getFirstField($sql);
			
			if ( empty($name) )   $name = 'album';
			
			$link = StringUtils::translit($name); 
		}
		else {
			$albumLink = StringUtils::translit($albumLink); 
		    $link = preg_replace('~[^a-z0-9\_\-]~i', '', $albumLink);
		}
		
		if ( Env::Post('foto1' , Env::TYPE_INT) == 1 ) {
			$frontendType = 1;
			$setting = '<setting><frontend>1</frontend><foto-size>'
				.Env::Post('foto-size1', Env::TYPE_STRING)
				.'</foto-size><foto-count>'
				.Env::Post('foto-count1', Env::TYPE_STRING)
				.'</foto-count><view-oreginal>'
				.Env::Post('view-oreginal', Env::TYPE_STRING)
				.'</view-oreginal></setting>';
		}
		else if ( Env::Post('foto2' , Env::TYPE_INT) == 1 ) {
			$frontendType = 2;
			$setting = '<setting><frontend>2</frontend><foto-size>'
				.Env::Post('foto-size2', Env::TYPE_STRING)
				.'</foto-size><foto-thumb>'
				.Env::Post('foto-thumb2', Env::TYPE_STRING)
				.'</foto-thumb><foto-count>'
				.Env::Post('foto-count2', Env::TYPE_STRING)
				.'</foto-count><view-oreginal>'
				.Env::Post('view-oreginal', Env::TYPE_STRING)
				.'</view-oreginal></setting>';
		}
		else if ( Env::Post('foto3' , Env::TYPE_INT) == 1 ) {
			$frontendType = 3;
			$setting = '<setting><frontend>3</frontend><foto-thumb>'
				.Env::Post('foto-thumb3', Env::TYPE_STRING)
				.'</foto-thumb><foto-count>'
				.Env::Post('foto-count3', Env::TYPE_STRING)
				.'</foto-count><view-oreginal>'
				.Env::Post('view-oreginal', Env::TYPE_STRING)
				.'</view-oreginal></setting>';
		}
		
		$link = mysql_real_escape_string($link);
		
	    $isUnical = false;
        while (!$isUnical) {
            $sql = "SELECT * FROM PIXFOLDER WHERE PF_LINK = '{$link}' AND NOT (PF_ID = {$albumId})";
            $row = Wdb::getRow($sql);
            
            if ( $row ) {
                $link = $link.'-1';
            }
            else
                $isUnical = true;
            
        }
		
        if (Env::Post('album-published', Env::TYPE_INT, 2) == 2) {
        	$link = '';
        	$setting = '';
        }
        
		$sql = new CUpdateSqlQuery("PIXFOLDER");
		$sql->addFields( " PF_STATUS = ".Env::Post('album-published', Env::TYPE_INT, 2).", PF_LINK = '". $link ."', PF_SETTING='". addslashes($setting) ."'" );
		$sql->addConditions('PF_ID', Env::Get('albumId', Env::TYPE_INT, 1));
		Wdb::runQuery($sql);
		
		if ( Wbs::isHosted() ) {
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'CHANGE-PUBLISHED', 'ACCOUNT', $frontendType);
		}
		
	}
	
	
	public function uploadFormAction()
	{
	    @set_time_limit(0);
	    
		$albumId = Env::Post('albumId', Env::TYPE_INT, 1);
		$albumIdG = Env::Get('albumId', Env::TYPE_INT, 1);
		$albumId = ($albumId) ? $albumId : $albumIdG;
		
		if ( Env::Post('type', Env::TYPE_STRING) == 'file' ) {
		    
    		$isAlbumThumb = Env::Post('isAlbumThumb', Env::TYPE_INT);
    		
    		$imagefs = new PDImageFSModel( PDApplication::getInstance() );
    		$imagePaths = $imagefs->uploadAll($albumId);
    		if ( count($imagePaths) > 0 ) {
        		foreach ( $imagePaths as $key => $imagePath ) {
        		
            		$imagePath = realpath($imagePath);		
            		
            		$sql = new CSelectSqlQuery('PIXLIST');
            		$sql->setSelectFields('MAX(PL_SORT)');
            		$sql->addConditions('PF_ID', $albumId);
            		$maxSort = Wdb::getFirstField($sql);
            		$maxSort = ( is_null($maxSort) ) ? 0 : $maxSort + 1 ;
            		
            		if ( extension_loaded( "exif" )) {
            			$exifInfo = $this->makeExif( $this->getExifInfo($imagePath) );
            		}
            			
            		$pathInfo = pathinfo($imagePath);
            		$imageModel = new PDImage();
					$imageId = $imageModel->pre_add(array(
						'PF_ID' => $albumId,
						'PL_FILENAME' => $_FILES['Filedata']['name'][$key],
						'PL_FILETYPE' => $pathInfo['extension'],
						'PL_FILESIZE' => filesize($imagePath),
						'PL_DISKFILENAME' => $pathInfo['basename'],
						'PL_STATUSINT' => 1,
						'PL_SORT' => $maxSort,
					    'C_ID' => User::getContactId(),
					
						'PL_WIDTH' => '0',
						'PL_HEIGHT' => '0',
						'PL_DESC' => ''
					));
					$imageLib = new PDWbsImage($imagePath);
					$imageModel->add($imageId, array(
						'PL_WIDTH' => $imageLib->getImageWidth(),
						'PL_HEIGHT' => $imageLib->getImageHeight(),
						'PL_DESC' => '',
						'PL_UPLOADUSERNAME' => User::getName(),
						'PL_MODIFYUSERNAME' => User::getName(),
						'PL_UPLOADDATETIME' => CDateTime::now()->toStr(),
						'PL_MODIFYDATETIME' => CDateTime::now()->toStr(),
					
						'PE_WIDTH' => (!empty($exifInfo['Width'])) ? $exifInfo['Width'] : 0,
						'PE_HEIGHT' => (!empty($exifInfo['Height'])) ? $exifInfo['Height'] : 0,
						'PE_DATETIME' => (!empty($exifInfo['DateTimeOriginal'])) ? $exifInfo['DateTimeOriginal'] : CDateTime::now()->toStr(),
						'PE_FILENAME' => $exifInfo['FileName'],
						'PE_FILESIZE' => (!empty($exifInfo['FileSize'])) ? $exifInfo['FileSize'] : 0,
						'PE_MAKE' => $exifInfo['Make'],
						'PE_MODEL' => $exifInfo['Model'],
						'PE_EXPOSURETIME' => $exifInfo['ExposureTime'],
						'PE_FNUMBER' => $exifInfo['FNumber'],
						'PE_ISOSPEEDRATINGS' => $exifInfo['ISOSpeedRatings'],
						'PE_FOCALLENGTH' => $exifInfo['FocalLength']
					
					));
            		$imageLib->destroy();
            		
	        		$albumModel = new PDAlbum();
					if ( !$albumModel->isAlbumThumb($albumId) ) {
						$imageModel = new PDImage();
						$image  = $imageModel->getImage($imageId);
						$albumModel->setThumb($albumId, $imageId, rawurlencode(base64_encode($image['PL_DISKFILENAME'])) );
					}
					$imagefs->createThumbToImage($imagePath);
					
	        		if ( $imagefs->hasImageThumb($imagePath) ) {
						$sql = new CUpdateSqlQuery("PIXLIST");
						$sql->addConditions("PL_ID", $imageId);
						$sql->addFields("PL_STATUSINT = 0");
						Wdb::runQuery($sql);
					}
					
	        		if ( Wbs::isHosted() ) {
						$metric = metric::getInstance();
				 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'UPLOAD-STD', 'ACCOUNT', filesize($imagePath));
					}
					
        		}
        		
    		}
    		$this->view->assign('fileOneLoaded', '1');
    		
    		$countUpload = 0;
    		foreach($_FILES['Filedata']['error'] as $val) {
    		    if ( $val == 0 )
    		        $countUpload += 1;
    		}
    		
    		if ( count($imagePaths) == $countUpload ) {
    		    $this->view->assign('fileOneMessage', _('All files successfully uploaded.'));
    		}
    		else {
    		    $this->view->assign('fileOneMessage', _('Not all files were uploaded and added to your photo library.<br />Supported image formats are JPG, GIF, PNG.'));
    		    $this->view->assign('fileOneError', '1');
    		}
    		
		}
		$albumModel = new PDAlbum();
		$albums = $albumModel->getAlbumList();
		
		$dm = new DiskUsageModel();
        $usage = $dm->getAll();        
        $limit = Limits::get('AA', 'SPACE')*1024*1024 - $usage;
        
        if (Limits::get('AA', 'SPACE') != 0)
            $limit = ( $limit < 0 ) ? 0 : $limit;
        else
            $limit = -100;
        
        $this->view->assign('usage', $usage);
        $this->view->assign('limit', $limit);
        
        $this->view->assign('memory_limit', ini_get('memory_limit'));
        
        $this->view->assign('max_file_size', ini_get('upload_max_filesize') );
		
		$this->view->assign('albumId', Env::Get('albumId', Env::TYPE_INT));
		$album = $albumModel->getById(Env::Get('albumId', Env::TYPE_INT));
		
		$this->view->assign('albumName', htmlspecialchars( StringUtils::truncate($album['PF_NAME'], 20)));
		$this->view->display('upload_form.html');
	}
	/**
   	 * @param string $imageFilePath
   	 * @return string - xml string
   	 */
   	private function getExifInfo($imageFilePath) {   	    
   		$exif_info = @exif_read_data($imageFilePath);
   		$info = array();
   		if (!$exif_info) {
   			return $info;
   		}
   		if (isset($exif_info['COMPUTED'])) {
   			$info['Width'] = $exif_info['COMPUTED']['Width'];
   			$info['Height'] = $exif_info['COMPUTED']['Height'];
   		}
   		if (isset($exif_info['DateTimeOriginal']))
   			$info['DateTimeOriginal'] = $exif_info['DateTimeOriginal'];
   		if (isset($exif_info['FileName']))
   			$info['FileName'] = base64_encode($exif_info['FileName']);
   		if (isset($exif_info['FileSize']))
   			$info['FileSize'] = $exif_info['FileSize'];
   		if (isset($exif_info['Make']))
   			$info['Make'] = base64_encode($exif_info['Make']);
   		if (isset($exif_info['Model']))
   			$info['Model'] = base64_encode($exif_info['Model']);
   		
   		if (isset($exif_info['ExposureTime']))
   			$info['ExposureTime'] = $exif_info['ExposureTime'];
   		if (isset($exif_info['FNumber']))
   			$info['FNumber'] = $exif_info['FNumber'];
   		if (isset($exif_info['ISOSpeedRatings']))
   			$info['ISOSpeedRatings'] = $exif_info['ISOSpeedRatings'];
   		if (isset($exif_info['FocalLength']))
   			$info['FocalLength'] = $exif_info['FocalLength'];
   		
   		return $info;
   	}	
	
	private function makeExif(&$exifInfo) 
	{
	    list($a, $b) = explode('/', $exifInfo['ExposureTime']);
	    if ( $a > 1 ) {
	        if ( ceil($b/$a) > 0 ) {
	        	if ($b/$a > 1) {
	        		$exifInfo['ExposureTime'] = '1/'.ceil($b/$a);
	        	}
	        	else {
	        		$exifInfo['ExposureTime'] = round($a/$b , 1);
	        	}
			}
	    }
	    	    
	    list($a, $b) = explode('/', $exifInfo['FocalLength']);
	    if ( $b != 0) 
	        $exifInfo['FocalLength'] = ($a/$b).'mm';
	    
	    $exifInfo['FNumber'] = $this->exif_get_fstop($exifInfo);
	    
	    return $exifInfo;
	}

	private function exif_get_float($value) {
      $pos = mb_strpos($value, '/');
      if ($pos === false) return (float) $value;
      $a = (float) mb_substr($value, 0, $pos);
      $b = (float) mb_substr($value, $pos+1);
      return ($b == 0) ? ($a) : ($a / $b);
    }

	private function exif_get_fstop($exif) {
      $apex  = $this->exif_get_float($exif['FNumber']);
      $fstop = pow(2, $apex/2);
      if ($fstop == 0) return false;
      return 'F/' . round($fstop,1);
    } 	
	
	public function createCollectionAction()
	{
	    $albumId = Env::Get('albumId', Env::TYPE_INT);
	    
	    $albumModel = new PDAlbum();
	    $albumModel->loadId( $albumId );
	    
//	    $collectionDesc = preg_replace('~[^a-z0-9\_]~i', '', StringUtils::translit($albumModel->PF_NAME, '_'));
	    $collectionDesc = htmlspecialchars($albumModel->PF_NAME);
	    
	    $collectionLink = mb_strtolower(StringUtils::translit($albumModel->PF_NAME, '_'));
	    
	    if ($collectionLink == 'album')
	    	$collectionLink = 'myalbum';
	    
	    $imageList = Env::Get('imageList', Env::TYPE_STRING);
	    $imageList = mb_substr($imageList, 0, mb_strlen($imageList)-1);
	    
	    $this->view->assign('albumId', $albumId);
	    $this->view->assign('collectionLink', $collectionLink);
	    $this->view->assign('collectionDesc', $collectionDesc);
	    $this->view->assign('imageList', $imageList);
	    $this->view->display('createCollection.html');
	}
	
	public function createCollectionSaveAction()
	{
	    $albumId = Env::Post('albumId', Env::TYPE_INT);
	    
	    $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $albumId, Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
	    
	    $imageList = Env::Post('imageList', Env::TYPE_STRING);
	    $collectionName = Env::Post('collectionName', Env::TYPE_STRING);
	    $collectionPhoto = Env::Post('collectionPhoto', Env::TYPE_STRING);
	    
	    $type = Env::Post('type', Env::TYPE_STRING);
	    $widget_type = Env::Post('widget_type', Env::TYPE_STRING);
	    
	    if ( $collectionPhoto == 'select')
	        $list = explode(',', $imageList);
        else {
            $imageModel = new PDImage();
            $list = $imageModel->getImageByAlbum($albumId, true);
        }
	    
	    $albumModel = new PDAlbum();
	    $albumModel->loadId( $albumId );
	    $link = mb_strtolower(StringUtils::translit($collectionName, '_'));
		$link = preg_replace('~[^a-z0-9\_]~i', '', $link);
	    $link = str_replace('-', '_', $link);
	    if ( empty($link) )  $link = 'myalbum';
	    
	    if ($link == 'album') $link = 'myalbum';
	    
	    $isUnical = false;
        while (!$isUnical) {
            $sql = "SELECT * FROM WG_WIDGET WHERE WG_FPRINT = '{$link}' ";
            $row = Wdb::getRow($sql);
            
            if ( $row ) {
                $link = $link.'_1';
            }
            else
                $isUnical = true;            
        }
	    
	    if ( $type == 'links' ) $type = PDWidget::TYPE_LINK;
	    else if ( $type == 'widget' ) $type = PDWidget::TYPE_WIDGET; 
	    
	    $widg = new PDWidget();
	    	    
		$collectionName = htmlspecialchars($collectionName);
	    print $widg->addWidget($list, 0, $link, $collectionName, $type, $widget_type);
	}
	
	public function collectionAction()
	{	    	    
	    $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FUNCTIONS, 'MANAGE_COLLECTIONS', Rights::MODE_ONE, Rights::RETURN_BOOL) ) {
	        
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
        $hostUrl = Url::get('/', true);			
        
        $mainUrl = PDApplication::getFrontendUrl('', array(
		    'album/',
		    'index.php?album='
		));
        
        $this->view->assign('frontend_link', $frontend_link);	    
	    
        $this->view->assign('menu', 'collection');
		
		$wid = new PDWidget();
		$collections = $wid->getAll();
		
		if ( count($collections) == 0 ) {
		    $content = $this->view->fetch('collection-empty.html');
    		$this->view->assign('content', $content);
    		
    		$this->view->display('main.html');
    	    return;
		}
		
		foreach( $collections as &$row ) {
		    $row['WG_ID_BASE64'] = base64_encode($row['WG_ID']);
		    if ( $row['WST_ID'] != 'Album' && $row['WST_ID'] != 'Gallery' ) {
		        $row['isLink'] = 1;
		    }
		    $desc = $row['WG_DESC'];
		    if ( trim($desc) == '' )
		    	$row['WG_DESC'] = '?';
		}
		
		$this->view->assign('collections', $collections);
		
//		$this->view->assign('head', $this->view->fetch('collection-include.html'));
		
		$id =Env::Get('id', Env::TYPE_INT);
		
		if ( !$id ) {
    		$usm = new UserSettingsModel();
    	    $id = $usm->get(User::getId(), 'PD', 'last_collection');
    	    $id = ($id) ? $id : '';
		}
		else {
		    $usm = new UserSettingsModel();
	        $usm->set(User::getId(), 'PD', 'last_collection', $id);
		}
		
    	$this->view->assign('startId', base64_encode($id) );
		
		$this->view->assign('bodyUrl', Url::getServerUrl().Url::get('/PD/'));
		
		$content = $this->view->fetch('collection.html');
		$this->view->assign('content', $content);
		
		$this->view->display('main.html');	    
	}
	
	private function makeCollection($collectionId)
	{
	    $wid = new PDWidget();
	    $coll = $wid->getById($collectionId);
	    $url = PDApplication::getFrontendUrl(''.$coll['widget']['WG_FPRINT']);
	    $url = '../../WG/show.php?q='. base64_encode( Wbs::getDbkeyObj()->getDbkey() ) .'-'.$coll['widget']['WG_FPRINT'];
	    $colectionPreviewLink = $url . '&mode=preview';
	    
	    $this->view->assign('coll', $coll['widget']);
	    $this->view->assign('url', $url);
	    $this->view->assign('colectionPreviewLink', $colectionPreviewLink);
	    $this->view->assign('countPhoto', count( explode(',', ($coll['param']['FILES'])) ) );
	    $this->view->assign('viewMode', count( explode(',', ($coll['param']['VIEW_MODE'])) ) );
	}
	
	public function collectionDataAction()
	{
	    $collectionId = Env::Get('collectionId', Env::TYPE_INT);	    
        $this->makeCollection($collectionId);
	    
	    $this->view->display('collection-data.html');
	}
	
	
	
	public function collectionSettingsAction()
	{
	    $wid = new PDWidget();
	    $col = $wid->getById( Env::Get('collectionId', Env::TYPE_STRING) );
	    $col = $col['widget'];
	    $colectionLink = '../../WG/show.php?q='. base64_encode( Wbs::getDbkeyObj()->getDbkey() ) .'-'.$col['WG_FPRINT'];
	    $colectionPreviewLink = $colectionLink . '&mode=preview';
	    
	    $this->view->assign('colectionLink', $colectionLink);
	    $this->view->assign('colectionPreviewLink', $colectionPreviewLink);
	    $this->view->display('collection-settings.html');
	}
	
	public function collectionSelectAction()
	{
	    $id = Env::Get('id', Env::TYPE_BASE64);
	    
	    $usm = new UserSettingsModel();
	    $usm->set(User::getId(), 'PD', 'last_collection', $id);
	}
	
	public function changePhotoDescAction()
	{
	    $albumId = Env::Get('albumId', Env::TYPE_INT);
	    
	    $albumModel = new PDAlbum($albumId);	    
	    $imageList = $albumModel->getImageListDesc($albumId);

	    $this->view->assign('albumId', $albumId);
	    $this->view->assign('albumName', htmlspecialchars(StringUtils::truncate($albumModel->PF_NAME), 20 ));
        $this->view->assign('imageList', $imageList);
	    $this->view->display('changePhotoDesc.html');
	}
	
	public function changePhotoDescSaveAction()
	{
	    $list = array();	    
	    foreach ( $_POST as $key => $val ) {
	        $list[] = '('.intval($key).', \''.mysql_real_escape_string($val).'\')';	        
	    }
	    	    $str = implode(',', $list);
	    	    
	    $sql = 'INSERT INTO PIXLIST
					(PL_ID, PL_DESC)
				VALUES '.$str.'
				ON DUPLICATE KEY UPDATE PL_DESC = VALUES(PL_DESC)';
	    Wdb::runQuery($sql);
	}
	
	
	public function changeDescElementAction()
	{	 
	    $type = Env::Get('type', Env::TYPE_STRING);
	    if ( $type == 'album' ) {
	        $albumId = Env::Get('albumId', Env::TYPE_STRING);
	        
	        $albumModel = new PDAlbum($albumId);
	        $desc = $albumModel->PF_DESC;
	        
	        $imageModel = new PDImage();
	        $image = $imageModel->getImage($albumModel->PF_THUMB);
	        
	        if ( $albumModel->getCount($albumId) > 0 )
	        	$imgUrl = PDImage::getUrl($image, 96);
	        else
	            $imgUrl = '';
	        
        	
	        $datestr = $albumModel->PF_DATESTR;
	        
	       	$this->view->assign('albumId', $albumId);
	    }
	    else {
	        $imageId = Env::Get('imageId', Env::TYPE_STRING);
	        $albumModel = new PDAlbum();
	        
	        $imageModel = new PDImage();
	        $image = $imageModel->getImage($imageId);
	        
	        $desc = $image['PL_DESC'];
	        
        	$imgUrl = PDImage::getUrl($image, 96);
        	$this->view->assign('imageId', $imageId);
	    }
	    $this->view->assign('datestr', htmlspecialchars($datestr));
	    $this->view->assign('type', $type);
	    $this->view->assign('imgUrl', $imgUrl);
	    $this->view->assign('desc', htmlspecialchars($desc));
	    $this->view->display('changeDescElement.html');	    
	}
	public function changeDescElementSaveAction()
	{
	    $type = Env::Get('type', Env::TYPE_STRING);
	    if ( $type == 'album' ) {
	        
    	    $albumId = Env::Get('albumId', Env::TYPE_STRING);
    	    $right = new Rights( User::getId() );	    		
    	    if ( !$right->get('PD', Rights::FOLDERS, $albumId, Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
    	        print json_encode(array(
    	            'status' => 'ERR',
                    'error' => _('Access denied.'),                
    	        ));
    	        return;
    	    }
	        
	        $albumId = Env::Get('albumId', Env::TYPE_STRING);
	        $desc = Env::Post('elementDesc', Env::TYPE_STRING);
	        $datestr = Env::Post('datestr', Env::TYPE_STRING);
	        
	        $albumModel = new PDAlbum();
	        $albumModel->changeDesc($albumId, $desc, $datestr);
	    }
	    else {
	        $imageId = Env::Get('imageId', Env::TYPE_STRING);
	        $image = new PDImage();
	        $row = $image->getImage($imageId);
	        $albumId = $row['PF_ID'];
	        
    	    $right = new Rights( User::getId() );	    		
    	    if ( !$right->get('PD', Rights::FOLDERS, $albumId, Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
    	        print json_encode(array(
    	            'status' => 'ERR',
                    'error' => _('Access denied.'),                
    	        ));
    	        return;
    	    }
	        
	        $desc = Env::Post('elementDesc', Env::TYPE_STRING);
	        
	        $imageModel = new PDImage();
	        
	        $imageModel->changeDesc($imageId, $desc);
	        
	    }
	}
	
	public function collectionOptionDialogAction()
	{
	    $this->view->display('collectionOptionDialog.html');
	}
	
	public function linkToImageAction()
	{
	    $imagesId = explode(',', Env::Get('images', Env::TYPE_STRING) );
	    $size = Env::Post('size', Env::TYPE_INT, Env::Cookie('sizeOneView', Env::TYPE_STRING));
	    
	    if (  $imagesId[0] != '' ) {
    	    $imageModel = new PDImage();
    	    
    	    $imagesData = $imageModel->getImages($imagesId);
    
    	    $urlToView = PDApplication::getFrontendUrl('', array(
    		    'view/',
    		    'index.php?view='
		    ));
    	    
    	    $links = array();
    	    $linksHTML = array();
    	    $linksBBcode = array();
    	    
    	    if ($imagesData)
    	    foreach ( $imagesData as $imageRow ) {
    	        if ( mb_strlen($imageRow['PL_DESC']) > 0 ) {
    	            $alt = 'alt="' . htmlspecialchars(htmlspecialchars(strip_tags($imageRow['PL_DESC']))) . '"';
    	            $title = 'title="' . htmlspecialchars(htmlspecialchars(strip_tags($imageRow['PL_DESC']))) . '"';
    	        }
    	        else {
    	            $alt = '';
    	            $title = '';
    	        }
    	        
    //	        $links[] = Url::getServerUrl().$imageModel->getFileUrl($imageRow, $size);
                $links[] = $urlToView.rawurlencode(base64_encode($imageRow['PL_DISKFILENAME'])).'/'.$size;
    	        $linksHTML[] = '<img src="'.Url::getServerUrl().$imageModel->getFileUrl($imageRow, $size).'" '.$alt.' '.$title.'/>';
    	        $linksBBcode[] = '[IMG]'.Url::getServerUrl().$imageModel->getFileUrl($imageRow, $size).'[/IMG]';	        
    	    }
    	    $this->view->assign('linkList', implode("\n", $links));
    	    $this->view->assign('linksHTML', implode("\n", $linksHTML));
    	    $this->view->assign('linksBBcode', implode("\n", $linksBBcode));
	    }
	    
	    $this->view->display('imageLinks.html');	    
	}
	
	public function slideShowAction()
	{
	    $albumId = Env::Get('albumId', Env::TYPE_INT);
	    
	    $imageModel = new PDImage();
	    
        $fileList = $imageModel->getImageByAlbum($albumId);
        
        
        $imagelist = array();
        foreach($fileList as $k => $v) {
            $imagelist[] = array(
                'url' => PDImage::getUrl($v, SIZE_970)."&mode=orig",
                'w' => $v['PL_WIDTH'],
            	'h' => $v['PL_HEIGHT'],
                'desc' => htmlspecialchars(strip_tags($v['PL_DESC']))
            );
        }
        
//        var_dump($imagelist);
        
        $this->view->assign('imagelist', $imagelist);
	    
	    $this->view->display('slideshow.html');	   
	}
	
	public function settingsAction() {
	    
	    $groupsModel = new GroupsModel();
	    $groups = $groupsModel->getAll();

	    $this->view->assign('groups', $groups);
	    
	    $user_settings_model = new UserSettingsModel();
	    $imagelib = $user_settings_model->get("", "PD", "ImageLib");
	    if (Wbs::isHosted())
	    	$imagelib = ($imagelib) ? $imagelib : 'imagick';
	    else
	    	$imagelib = ($imagelib) ? $imagelib : 'gd';
	    $sharpen = $user_settings_model->get("", "PD", "SharpenUsaged");
	    $this->view->assign('imagelib', $imagelib);
	    $this->view->assign('isNotImagick', !PDImageFSModel::isImagick_() );
	    $this->view->assign('isSharpen', $sharpen == '1');
	    
	    $isAccessUG = User::hasAccess('UG');
	    		      		   
	    $frontend_link = PDApplication::getFrontendUrl('');
	    
	    $right = json_decode($user_settings_model->get("", "PD", "NowRightAlbum"), true);
        $this->view->assign('right', $right);
        
        $this->view->assign('frontend_link', $frontend_link);	
		$this->view->assign('isAccessUG', $isAccessUG);
		
	    $content = $this->view->fetch('settings.html');
		$this->view->assign('content', $content);
		
		$this->view->assign('menu', 'settings');
		$this->view->display('main.html');
	}

	public function settingsSaveAction() {
	    $imagelib = Env::Post('imagelib', Env::TYPE_STRING, '');
	    $sharpen = Env::Post('sharpen', Env::TYPE_INT, '0');
	    $right = ( is_array($_POST['right']) ? $_POST['right'] : array()) ;
	    
	    $groupsModel = new GroupsModel();
	    $groups = $groupsModel->getAll(true);
	    
	    foreach ($groups as $groupId => $groupName) {
	        if ( !array_key_exists($groupId, $right) ) {
	            $right[$groupId] = 0;
	        }
	    }
	    
	    $user_settings_model = new UserSettingsModel();	    
	    if ($imagelib == 'imagick') {
	        $user_settings_model->set("", "PD", "ImageLib", 'imagick');
	    }
	    elseif ($imagelib == 'gd') {
	        $user_settings_model->set("", "PD", "ImageLib", 'gd');
	    }	    
	    if ( $sharpen == 1 ) {    	    
            $user_settings_model->set("", "PD", "SharpenUsaged", $sharpen);
	    }
	    else {
	        $user_settings_model->set("", "PD", "SharpenUsaged", 0);
	    }
	    
	    if ( count($right) > 0 ) {
	        $user_settings_model->set("", "PD", "NowRightAlbum", json_encode($right));
	    }
	    
	    $this->view->assign('saved', 'saved');
	    $this->settingsAction();
	}
}

?>