<?php

class flickrController extends ActionController 
{
	public function init() 
	{
		$this->defaultAction = 'auth';
		
				
		$preproc = new PDSmarty();
		$this->setView( $preproc );
		$this->view->assign('sessionId', session_id());
	}
	/**
	 * step 1
	 *
	 */
	public function authAction()
	{
	    $usm = new UserSettingsModel();
    	$key = $usm->get(User::getId(), 'PD', 'flickr_key');
    	if ( $key ) {
        	$keys = explode('|', $key);
    	    
    	}
    	
    	if ( !empty($keys[0]) && !empty($keys[1]) ) {
        	$flickr = new ServiceSynchr_Flickr($keys[0], $keys[1]);
            $this->view->assign("furl", $flickr->getAuthUrl()  );
    	}
    	
    	$this->view->assign("backurl", Url::get('/PD/backend.php?controller=flickr&action=authresponse', true));
    	
    	$this->view->assign("idSettingsView", empty($keys[0]) || empty($keys[1]));
    	
        $this->view->assign("fparam1", $keys[0]);
        $this->view->assign("fparam2", $keys[1]);
		$this->view->assign("step", 1);
		$this->view->assign("isParams", true);
		$this->view->display("flickrImport.html");	
		
		Env::setSession("album", Env::Get('album', Env::TYPE_STRING));
	}
	public function goFlickrAction() {
	    $fparam1 = Env::Get('fparam1', Env::TYPE_STRING);
	    $fparam2 = Env::Get('fparam2', Env::TYPE_STRING);
	    
	    $usm = new UserSettingsModel();
    	$usm->set(User::getId(), 'PD', 'flickr_key', $fparam1.'|'.$fparam2);
	    
	    $flickr = new ServiceSynchr_Flickr($fparam1, $fparam2);
        $url = $flickr->getAuthUrl();
        
        print $url;
    	exit();	
	}
	
   	public function saveApiFlickrAction() {
	    $fparam1 = Env::Get('fparam1', Env::TYPE_STRING);
	    $fparam2 = Env::Get('fparam2', Env::TYPE_STRING);
	    
	    User::setSetting('flickr_key', $fparam1.'|'.$fparam2, 'PD');
    	
    	$flickr = new ServiceSynchr_Flickr($fparam1, $fparam2);
        $url = $flickr->getAuthUrl();
        
        print $url;
   	}	
	
	/**
	 * step 2
	 *
	 */
	public function authresponseAction()
	{
		$usm = new UserSettingsModel();
    	list($fparam1, $fparam2) = explode('|', $usm->get(User::getId(), 'PD', 'flickr_key', $fparam1.'|'.$fparam2));
		
		$flickr = new ServiceSynchr_Flickr($fparam1, $fparam2);
				
		if ( !Env::Session('flickr_token') ) {			
			
			$auth = $flickr->getToken( WebQuery::getParam('frob') );
	
			$token = $auth['token']['_content'];
			$user_id = $auth['user']['nsid'];
			
			Env::setSession("flickr_token", $token);
			Env::setSession("flickr_user_id",$user_id);
		}
		else {
			$token = Env::Session('flickr_token');
			$user_id = Env::Session('flickr_user_id');
		}
		
		$flickr->setAuthToken($token);
		
		$photosets = $flickr->getListPhotosets($user_id);
		Env::setSession("photosets", $photosets);
		
		$this->view->assign("step", 2);
		$this->view->assign("photosets", $photosets);
		$this->view->display("flickrImport.html");		
	}
	
	/**
	 * step 3
	 *
	 */
	public function importImageAction()
	{
		$token = Env::Session('flickr_token');
		
		$usm = new UserSettingsModel();
    	list($fparam1, $fparam2) = explode('|', $usm->get(User::getId(), 'PD', 'flickr_key', $fparam1.'|'.$fparam2));
		
    	$flickr = new ServiceSynchr_Flickr($fparam1, $fparam2);
		$flickr->setAuthToken($token);

		$photosets_id = array_keys( $_POST );
		
		$photosets = Env::Session('photosets');
		
		$photos = array();

		foreach( $photosets_id as $id ) {
			try {
				$listFoto = $flickr->getPhotoListByPhotosetId($id);
			}
			catch( RuntimeException $e) {
				$photos[] = array(
					'error' => $e->getMessage()
				);
				continue;
			}
			
			foreach ( $listFoto['photo'] as $foto ) {
				
				$info = $flickr->getSizeById($foto['id']);
				$photos[] = array(
			 		'album' => $this->getSetnameById($id, $photosets),
			 		'photo_id' => $info['size'][count($info['size'])-1]['source'],
			 		'photo_name' => $foto['title']
			 	);
//				$photos[] = array(
//			 		'album' => $this->getSetnameById($id, $photosets),
//			 		'photo_id' => ServiceSynchr_Flickr::photoDateToUrl($foto),
//			 		'photo_name' => $foto['title']
//			 	);
			 }
		}
		
		$this->view->assign("step", 3);
		$this->view->assign("photosets", json_encode($photos) );
		$this->view->assign("album", Env::Session('album') );
		$this->view->display("flickrImport.html");		
	}
	
	/**
	 * step 4
	 *
	 */
	public function loadAction()
	{	
		$this->loadImage( $_REQUEST['photo_id'], $_REQUEST['photo_name'].'.jpg', $_REQUEST['album'] );
	}
	
	private function loadImage( $url, $filename, $album ) {
		$urlp =  parse_url($url);
		$host = $urlp['host'];
		/*
			GET http://www.site.ru/news.html HTTP/1.0\r\n
			Host: www.site.ru\r\n
			Referer: http://www.site.ru/index.html\r\n
			\r\n
	     */ 
	    $h   = array();
	    $h[] = 'GET ' . $url . ' HTTP/1.0';
	    $h[] = 'Host: ' . $host;
	    $h   = implode("\r\n",$h) . "\r\n\r\n";
	    $fp  = fsockopen( $host , 80);
	       if (!fwrite($fp,$h)) {
	        fclose($fp);
	        return false;
	    }
	    
	   	$fileout = fopen( realpath(AppPath::TEMP_PATH()).'/' . $filename, "w");
	   	$buf = "";
	    while(!feof($fp)) {
			$buf .= fgets($fp, 8192);
	   	}
	    fclose($fp);
	    $r = explode("\r\n\r\n", $buf);
	    
	    fwrite( $fileout,  $r[1] );
	    fclose($fileout);
	    unset($buf);
	    unset($r);

	    $this->addImg($album, realpath(AppPath::TEMP_PATH()).'/'.$filename);
	}
	
	
	private function addImg($albumId, $path, $isAlbumThumb = true)
	{
		
		$imagefs = new PDImageFSModel( PDApplication::getInstance() );
		$info = pathinfo($path);		
		$imagePath = $imagefs->uploadSimple($albumId, $path);
		
		$sql = new CSelectSqlQuery('PIXLIST');
		$sql->setSelectFields('MAX(PL_SORT)');
		$sql->addConditions('PF_ID', $albumId);
		$maxSort = Wdb::getFirstField($sql);
		$maxSort = ( is_null($maxSort) ) ? 0 : $maxSort + 1 ;
		
		$dm = new DiskUsageModel();
        if ( Limits::get('AA', 'SPACE') == 0 || Limits::get('AA', 'SPACE') * 1024 * 1024 > $dm->getAll() +filesize($imagePath)  ) {
			$dm->add('$SYSTEM', 'PD', filesize($imagePath) );
        }
        else {
        	return false;
        }
        
		if ( Wbs::isHosted() ) {
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'UPLOAD-FLICKR', 'ACCOUNT', filesize($imagePath));
		}
		
		if ( extension_loaded( "exif" ))
			$exifInfo = $this->getExifInfo($imagePath);
		
            $pathInfo = pathinfo($imagePath);
            $imageModel = new PDImage();
		$imageId = $imageModel->pre_add(array(
			'PF_ID' => $albumId,
			'PL_FILENAME' =>  $info['basename'],
			'PL_FILETYPE' => $pathInfo['extension'],
			'PL_FILESIZE' => filesize($imagePath),
			'PL_DISKFILENAME' => $pathInfo['basename'],
			'PL_SORT' => $maxSort,
			'PL_STATUSINT' => 1,
		    'C_ID' => User::getContactId()
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
		
			'PE_WIDTH' => $exifInfo['Width'],
			'PE_HEIGHT' => $exifInfo['Height'],
			'PE_DATETIME' => $exifInfo['DateTimeOriginal'],
			'PE_FILENAME' => $exifInfo['FileName'],
			'PE_FILESIZE' => $exifInfo['FileSize'],
			'PE_MAKE' => $exifInfo['Make'],
			'PE_MODEL' => $exifInfo['Model'],
			'PE_EXPOSURETIME' => $exifInfo['ExposureTime'],
			'PE_FNUMBER' => $exifInfo['FNumber'],
			'PE_ISOSPEEDRATINGS' => $exifInfo['ISOSpeedRatings'],
			'PE_FOCALLENGTH' => $exifInfo['FocalLength']
		
		));
		$imageLib->destroy();

		$imagefs->createThumbToImage($imagePath);
		
		if ( $imagefs->hasImageThumb($imagePath) ) {
			$sql = new CUpdateSqlQuery("PIXLIST");
			$sql->addConditions("PL_ID", $imageId);
			$sql->addFields("PL_STATUSINT = 0");
			Wdb::runQuery($sql);
		}
		
	}
	
	private function getSetnameById($id, $photosets) {
		foreach ( $photosets as $set) {
			if ( $set['id'] == $id )
				return $set['title']['_content'];
		}
		return false;
	}

   	private function getExifInfo($imageFilePath) {
   		$exif_info = exif_read_data($imageFilePath);
   		$info = array();
   		$info['Width'] = ($exif_info['COMPUTED']['Width']) ? $exif_info['COMPUTED']['Width'] : '';
   		$info['Height'] = ($exif_info['COMPUTED']['Height']) ? $exif_info['COMPUTED']['Height'] : '';
   		$info['DateTimeOriginal'] = ($exif_info['DateTimeOriginal']) ? $exif_info['DateTimeOriginal'] : '';
   		$info['FileName'] = ($exif_info['FileName']) ? base64_encode($exif_info['FileName']) : '';
   		$info['FileSize'] = ($exif_info['FileSize']) ? $exif_info['FileSize'] : '';
   		$info['Make'] = ($exif_info['Make']) ? base64_encode($exif_info['Make']) : '';
   		$info['Model'] = ($exif_info['Model']) ? base64_encode($exif_info['Model']) : "";
   		
   		$info['ExposureTime'] = ($exif_info['ExposureTime']) ? $exif_info['ExposureTime'] : '';
   		$info['FNumber'] = ($exif_info['FNumber']) ? $exif_info['FNumber'] : '';
   		$info['ISOSpeedRatings'] = ($exif_info['ISOSpeedRatings']) ? $exif_info['ISOSpeedRatings'] : '';
   		$info['FocalLength'] = ($exif_info['FocalLength']) ? $exif_info['FocalLength'] : '';
   		
   		return $info;
   	}	
   	

	
}

?>