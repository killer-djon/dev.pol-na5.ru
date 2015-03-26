<?php

class ajaxController extends ActionController 
{
    private $r_obj = null;
	
	public function init()
	{
		
		$right = new Rights(User::getId());
		$this->r_obj = array(
		    'create_album' => $right->get('PD', Rights::FOLDERS, 'ROOT', Rights::MODE_APP, Rights::RETURN_BOOL),
		    'manage_album' => $right->get('PD', Rights::FOLDERS, 'MANAGE_ALBUM', Rights::MODE_ONE, Rights::RETURN_BOOL),
			'manage_collections' => $right->get('PD', Rights::FUNCTIONS, 'MANAGE_COLLECTIONS', Rights::MODE_ONE, Rights::RETURN_BOOL),
			'modify_design' => $right->get('PD', Rights::FUNCTIONS, 'MODIFY_DESIGN', Rights::MODE_ONE, Rights::RETURN_BOOL)
		);
	}
	
	public function albumListAction()
	{
		$albumModel = new PDAlbum();		
		$albumList = $albumModel->getAlbumListThumb();
		
	    $right = new Rights( User::getId() );
			
		$manage_album = $right->get('PD', Rights::FOLDERS, 'MANAGE_ALBUM', Rights::MODE_APP, Rights::RETURN_BOOL);
		
		foreach ( $albumList['data'] as $key => $item ) {
		    if ( $v = $right->get('PD', Rights::FOLDERS, $item['PF_ID'], Rights::MODE_ONE, Rights::RETURN_INT) ) {
                $albumList['data'][$key]['RIGHT'] = $v;
		    }
		    else {
		        if ( !$manage_album )
		            unset($albumList['data'][$key]);
		    }
		}
		$albumList['data'] = array_values($albumList['data']);
		$albumList['total'] = count($albumList['data']);		
		$albumList['RIGHTS'] = $this->r_obj;
		
		print json_encode( $albumList  );
	}
	
	public function imageListAction()
	{
		$idAlbum = Env::Post('albumId', Env::TYPE_INT, 1);
		
		$right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $idAlbum, Rights::MODE_ONE, Rights::RETURN_BOOL) ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
				
		$album = new PDAlbum($idAlbum);
		
		$imageList = $album->getImageListAndComments(null,
													 Env::Post('offset', Env::TYPE_INT, 0),
													 Env::Post('limit', Env::TYPE_INT, 10));

        $right = new Rights(User::getId());
        
        $imageList['RIGHT'] = $right->get('PD', Rights::FOLDERS, $idAlbum, Rights::MODE_ONE, Rights::RETURN_INT);
        $imageList['RIGHTS'] = $this->r_obj;
		print json_encode($imageList);
	}
	
	public function albumListCompactAction()
	{
		$sql = new CSelectSqlQuery("PIXFOLDER");
		$sql->setSelectFields('PF_ID, PF_NAME');
		$sql->setOrderBy('PF_SORT');
		$data = Wdb::getData($sql);
		
	    $right = new Rights( User::getId() );
		
		foreach ( $data as $key => $item ) {
		    $v = $right->get('PD', Rights::FOLDERS, $item['PF_ID'], Rights::MODE_ONE, Rights::RETURN_INT);
		    if ( $v >= 3 ) {
                $data[$key]['RIGHT'] = $v;
		    }
		    else {
	            unset($data[$key]);
	            continue;
	        }
		    $data[$key]['PF_NAME_FULL'] = htmlspecialchars($data[$key]['PF_NAME']);
		    $data[$key]['PF_NAME'] = htmlspecialchars(StringUtils::truncate($data[$key]['PF_NAME'], 20)); 
		    
		}
		print json_encode(array(
			'status' => 'OK',
			'data' => $data
		));
	}
	
	public function photomoveAction(){
	    $albumId = Env::Post('albumId', Env::TYPE_INT);
	    $photoList = Env::Post('photolist', Env::TYPE_ARRAY_INT);
	    
	    $imageModel = new PDImage();
	    $photoInfoList = $imageModel->get($photoList);
	    
	    $fsmodel = new PDImageFSModel(PDApplication::getInstance());
	    foreach ($photoInfoList as $photo) {
	        $fsmodel->moveImage($photo['PL_DISKFILENAME'], $photo['PF_ID'], $albumId);
	    }
	    $imageModel->move($albumId, $photoList);
	    
	    print json_encode(array(
			'status' => 'OK'
		));
	}
	
	public function imageOneAction()
	{
		$imageId = WebQuery::getParam("imageId");
		$positionImage = WebQuery::getParam("positionImage");

		$albumModel = new PDAlbum();
		$imageModel = new PDImage();		
		
		$imgList = $imageModel->getImageAndPrevNext($imageId);
		
		$right = new Rights( User::getId() );		
		if ( !$right->get('PD', Rights::FOLDERS, $imgList['c']['PF_ID'], Rights::MODE_ONE, Rights::RETURN_BOOL) ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),
	        ));
	        return;
	    }
	    
	    $dec_size = array(256, 512, 750, 970);
	    $size = max($imgList['c']['PL_WIDTH'], $imgList['c']['PL_HEIGHT']);
	    $sizes = array();
	    foreach ( $dec_size as $_size ) {
    	    if ( $size > $_size ) {
    	        if ( file_exists(PDImage::getFilePath($imgList['c'], $_size)) ) {
        	        $img = new PDWbsImage(PDImage::getFilePath($imgList['c'], $_size));
        	        $sizes[$_size] = array( "w" => $img->getImageWidth(), "h" => $img->getImageHeight() );
    	        } 
    	    }
	    }
	    
	    $imgList['c']['IMG_URL_R'] = Url::getServerUrl().$albumModel->getImageDataUrl($imgList['c'], (isset($_COOKIE['sizeOneView']) ? $_COOKIE['sizeOneView'] : SIZE_970 ) );
	    
	    if (isset($imgList['c'])&&isset($imgList['c']['PL_DISKFILENAME']))
	    	$imgList['c']['PL_DISKFILENAME'] = rawurlencode(base64_encode($imgList['c']['PL_DISKFILENAME']));
	    if (isset($imgList['l'])&&isset($imgList['l']['PL_DISKFILENAME']))
	    	$imgList['l']['PL_DISKFILENAME'] = rawurlencode(base64_encode($imgList['l']['PL_DISKFILENAME']));
	    if (isset($imgList['r'])&&isset($imgList['r']['PL_DISKFILENAME']))
	    	$imgList['r']['PL_DISKFILENAME'] = rawurlencode(base64_encode($imgList['r']['PL_DISKFILENAME']));
	    
		$imgList['c']['IMG_URL'] = Url::getServerUrl().$albumModel->getImageDataUrl($imgList['c'], (isset($_COOKIE['sizeOneView']) ? $_COOKIE['sizeOneView'] : SIZE_970 ));
		$imgList['c']['PL_DESC'] = strip_tags($imgList['c']['PL_DESC']);
		$imgList['c']['PL_DESC_H'] = htmlspecialchars(strip_tags($imgList['c']['PL_DESC']));
		$imgList['c']['sizes'] = $sizes;
		if ( mb_strlen($imgList['c']['PL_DESC']) > 100 ) {
		    $imgList['c']['PL_DESC'] = mb_substr($imgList['c']['PL_DESC'], 0, 100) . '...';
		}
		
		$hash = md5( $imgList['c']['PL_ID'] . $imgList['c']['PL_UPLOADDATETIME'] );
		$name = $imgList['c']['PL_DISKFILENAME'];
		
		$imgList['c']['PL_UPLOADDATETIME'] = WbsDateTime::getTime($imgList['c']['UPLOADDATETIME']);
		$imgList['c']['PL_W'] = $imgList['c']['PL_WIDTH']; 
		$imgList['c']['PL_H'] = $imgList['c']['PL_HEIGHT'];
		
        if( Wbs::getSystemObj()->isModeRewrite() || wbs::isHosted()) {
             //$orig_url = Url::getServerUrl() . '/photos/fullsize/'. $name .'/'. $hash;
             $orig_url = PDApplication::getFrontendUrl('fullsize/'. $name .'/'. $hash);
        }
		else {
		    //$orig_url = Url::getServerUrl() . '/photos/fullsize.php?filename='. $name .'&hash='. $hash;
		    $orig_url = PDApplication::getFrontendUrl('index.php?filename='. $name .'&hash='. $hash);
		}			

		$imgList['c']['HASH'] = $orig_url;
		
		$imageCount = $imageModel->count($imgList['c']['PF_ID']);
		$sql = new CSelectSqlQuery('PIXFOLDER', 'PF');
		$sql->setSelectFields(array('PF_ID', 
									'PF_NAME', 
									'PF_DATESTR', 
									'PF_LINK',
		                            'UNIX_TIMESTAMP(PF_CREATEDATETIME) AS PF_CREATEDATETIME',
		                            'PF_STATUS',
		                            'PF_CREATEUSERNAME',
		                            'PF_THUMB',
									'PF_DESC',
		                            'C_ID'));
		$sql->addConditions('PF_ID', $imgList['c']['PF_ID']);
		$album = Wdb::getRow($sql);
		
		
		if ( is_numeric($album['C_ID']) && $album['C_ID'] != 0 ) {
			$name = Contact::getName($album['C_ID']);
	        if ( !empty($name) )
	    	    $album['PF_CREATEUSERNAME'] = $name;
		}
		
		$album['PF_CREATEDATETIME'] = WbsDateTime::getTime($album['PF_CREATEDATETIME']);
		
		$album['PF_LINK_MIN'] = StringUtils::minimize($album['PF_LINK']);
			
		$album['PF_LINK'] = urlencode($album['PF_LINK']);
		$album['PF_LINK_MIN'] = htmlspecialchars($album['PF_LINK_MIN']);
		
		
		$sql = new CSelectSqlQuery('PIXEXIF');
		$sql->setSelectFields(array('PE_FILESIZE',
									'UNIX_TIMESTAMP(PE_DATETIME) AS PE_DATETIME',
									'PE_MODEL',
									'PE_MAKE',
									'PE_EXPOSURETIME',
									'PE_FNUMBER',
									'PE_ISOSPEEDRATINGS',
									'PE_FOCALLENGTH'));		
		$sql->addConditions('PL_ID', $imgList['c']['PL_ID']);
		$exif = Wdb::getRow($sql);
		
		if( isset($exif['PE_MODEL']) ) 
			$exif['PE_MODEL'] = ( $exif['PE_MODEL'] ) ? base64_decode( $exif['PE_MODEL'] ) : '';
		if( isset($exif['PE_MAKE']) ) 
			$exif['PE_MAKE'] = ( $exif['PE_MAKE'] ) ? base64_decode( $exif['PE_MAKE'] ) : '';
		
		if ( isset($exif['PE_DATETIME']) && $exif['PE_DATETIME'] == 0 )
		    $exif['PE_DATETIME'] = '';
		else
		    $exif['PE_DATETIME'] = WbsDateTime::getTime($exif['PE_DATETIME']);

		if( isset($exif['PE_FOCALLENGTH']) )
			$exif['PE_FOCALLENGTH'] = ( $exif['PE_FOCALLENGTH'] == '0mm' ) ? '' :  $exif['PE_FOCALLENGTH'] ;
		if( isset($exif['PE_FNUMBER']) )
			$exif['PE_FNUMBER'] = ( $exif['PE_FNUMBER'] == 'F/1' ) ? '' :  $exif['PE_FNUMBER'] ;
 
//		$dir = Wbs::getSystemObj()->getWebUrl();
//		$dir = ( $dir == '/' || empty($dir) ) ? '' : $dir;
//		$urlToView = Url::getServerUrl()
//		                .$dir
//						.'/photos/';
//						
//		if( Wbs::getSystemObj()->isModeRewrite() || wbs::isHosted()) 
//			$urlToView .= 'view/';		
//		else
//			$urlToView .= 'index.php?view=';

		$urlToView = PDApplication::getFrontendUrl('', array(
		    'view/',
		    'index.php?view='
		));
		
        $right = new Rights(User::getId());
			
        if ( Wbs::isHosted() ) {
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'DOWNLOAD');
		}
        
        
		print json_encode(array(
			'status' => 'OK',
			'data' =>  $imgList,
			'album' => $album,
			'imageCount' => $imageCount,
		    'urlToView' => $urlToView,
			'exif' => $exif,
		    'RIGHT' => $right->get('PD', Rights::FOLDERS, $imgList['c']['PF_ID'], Rights::MODE_ONE, Rights::RETURN_INT),
		    'RIGHTS' => $this->r_obj 
		));
	}
	
	
	public function uploadImageAction() 
	{
	    @set_time_limit(0);
	    
		$albumId = Env::Post('albumId', Env::TYPE_INT, 1);
		$albumIdG = Env::Get('albumId', Env::TYPE_INT, 1);
		$albumId = ($albumId) ? $albumId : $albumIdG;
		
		$isAlbumThumb = Env::Post('isAlbumThumb', Env::TYPE_INT);
		
		$imagefs = new PDImageFSModel( PDApplication::getInstance() );
		try {		    
		    $imagePath = $imagefs->upload($albumId);		    
		}
		catch(RuntimeException $e) {
		    echo $e->getMessage() ;
		    die();
		}
		
		$imagePath = realpath($imagePath);		
		
		$sql = new CSelectSqlQuery('PIXLIST');
		$sql->setSelectFields('MAX(PL_SORT)');
		$sql->addConditions('PF_ID', $albumId);
		$maxSort = Wdb::getFirstField($sql);
		$maxSort = ( is_null($maxSort) ) ? 0 : $maxSort + 1 ;
		            
		$pathInfo = pathinfo($imagePath);
		$imageModel = new PDImage();
		
		$imageId = $imageModel->pre_add(array(
			'PF_ID' => $albumId,
			'PL_FILENAME' =>  $_FILES['Filedata']['name'],
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
		
		$imageParam = array(
			'PL_WIDTH' => $imageLib->getImageWidth(),
			'PL_HEIGHT' => $imageLib->getImageHeight(),
			'PL_DESC' => '',
			'PL_UPLOADUSERNAME' => User::getName(),
			'PL_MODIFYUSERNAME' => User::getName(),
			'PL_UPLOADDATETIME' => CDateTime::now()->toStr(),
			'PL_MODIFYDATETIME' => CDateTime::now()->toStr(),
		);
		
		if ( extension_loaded( "exif" )) {
		    try {
			    $exifInfo = $this->makeExif( $this->getExifInfo($imagePath) );
			    $exifParam = array(
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
				);
				$imageParam = array_merge($imageParam, $exifParam);
		    }
		    catch ( RuntimeException $e ) {
		        
		    }
		}
		$imageModel->add($imageId, $imageParam);
		$imageLib->destroy();
		$imagefs->createThumbToImage($imagePath);
		
		$albumModel = new PDAlbum();
		if ( !$albumModel->isAlbumThumb($albumId) ) {
			$imageModel_ = new PDImage();
			$image  = $imageModel_->getImage($imageId);
			$albumModel->setThumb($albumId, $imageId, rawurlencode(base64_encode($image['PL_DISKFILENAME'])) );
		}
		
		if ( $imagefs->hasImageThumb($imagePath) ) {
			$sql = new CUpdateSqlQuery("PIXLIST");
			$sql->addConditions("PL_ID", $imageId);
			$sql->addFields("PL_STATUSINT = 0");
			Wdb::runQuery($sql);
		}
		
		if ( Wbs::isHosted() ) {
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'UPLOAD-FLASH', 'ACCOUNT', filesize($imagePath));
		}
			
		header("HTTP/1.1 200 Ok");
		print "OK";
	}
	
	private function errorDisplay($error) {
	    header("HTTP/1.1 500 ".$error);
        exit();
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
	        $exifInfo['FocalLength'] = $a/$b.'mm';
	    
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


	public function createAlbumAction()
	{	    
	    $right = new Rights(User::getId());
	    if ( !$right->get('PD', Rights::FOLDERS, 'ROOT', Rights::MODE_ONE, Rights::RETURN_BOOL) ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
	    
		$albumName = Env::Post('albumName', Env::TYPE_STRING_TRIM, '');
		$albumModel = new PDAlbum();
		$albumId = $albumModel->add($albumName);
		
		if ( Wbs::isHosted() ) {
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'CREATE-ALBUM', 'ACCOUNT');
		}
		print $albumId;
	}
	
	public function setThumbAction()
	{
		@set_time_limit(0);
		$albumId = Env::Post('albumId', Env::TYPE_INT);
		$thumbId = Env::Post('thumbId', Env::TYPE_STRING);
		if(!$thumbId || !$albumId) return false;
		
		$albumModel = new PDAlbum();
		$albumModel->setThumb($albumId, $thumbId);
	}
	/**
   	 * @param string $imageFilePath
   	 * @return string - xml string
   	 */
   	private function getExifInfo($imageFilePath) {   	    
   		$exif_info = @exif_read_data($imageFilePath);
   		$info = array();
   		$info['Width'] = $exif_info['COMPUTED']['Width'];
   		$info['Height'] = $exif_info['COMPUTED']['Height'];
   		$info['DateTimeOriginal'] = $exif_info['DateTimeOriginal'];
   		$info['FileName'] = base64_encode($exif_info['FileName']);
   		$info['FileSize'] = $exif_info['FileSize'];
   		$info['Make'] = base64_encode($exif_info['Make']);
   		$info['Model'] = base64_encode($exif_info['Model']);
   		
   		$info['ExposureTime'] = $exif_info['ExposureTime'];
   		$info['FNumber'] = $exif_info['FNumber'];
   		$info['ISOSpeedRatings'] = $exif_info['ISOSpeedRatings'];
   		$info['FocalLength'] = $exif_info['FocalLength'];
   		
   		return $info;
   	}

   	public function cangeAlbumNameAction()
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
   		
   		$albumName = Env::Post('albumName', Env::TYPE_STRING);
   		if ( empty($albumName) ) {
   		    print json_encode(array(
       			'status' => 'ERR',
   		    	'response' => '',
   		        'error' => _("Album name is empty.")
       		));
       		return ;   			
   		}
   		
   		$albumModel = new PDAlbum();
   		$name = $albumModel->changeName($albumId, $albumName);
   		print json_encode(array(
   			'status' => 'OK',
   			'response' => stripslashes($name)
   		));
   	}
   	public function cangeAlbumDescAction()
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
		
   		$albumDesc = Env::Post('albumDesc', Env::TYPE_STRING);  

   		$albumModel = new PDAlbum();
   		$albumModel->changeDesc($albumId, $albumDesc);
   		print json_encode(array(
   			'status' => 'OK',
   			'response' => stripslashes($albumDesc)
   		));   		
   	}
   	
//   	public function run()
//   	{
//   		try {
//   			parent::run();
//   		}
//   		catch (RuntimeException $e) {
//   			print json_encode(array(
//   				'status' => 'ERR',
//   				'error' => $e->getMessage()
//   			));
//   		}
// 	  	catch (PDException  $e) {
// 
//		}   		
//   	}
	public function sortAlbumAction()
	{
	    $right = new Rights(User::getId());
	    if ( !$right->get('PD', Rights::FOLDERS, 'MANAGE_ALBUM', Rights::MODE_ONE, Rights::RETURN_BOOL) ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
	    
		$albumModel = new PDAlbum();
		$albumModel->sort();
		
		print json_encode(array(
   			'status' => 'OK'
   		));
	}
	public function sortImageAction()
	{
	    $albumId = WebQuery::getParam("albumId") ;
        $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $albumId, Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }		    
	    
		$imageModel = new PDImage();
		$imageModel->sort();
		
		print json_encode(array(
   			'status' => 'OK'
   		));
	}	
	private function isRightAlbum($id) {
	    $right = new Rights(User::getId());
	    if ( $right->get('PD', Rights::FOLDERS, $id, Rights::MODE_ONE, Rights::RETURN_INT) != 7 ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return false;	        
	    }
	    return true;
	}
	
	public function deleteAlbumAction()
	{
	    $albumId = Env::Post('albumId', Env::TYPE_STRING);
	        
	    if ( !$this->isRightAlbum($albumId) ) return;
		
		$albumModel = new PDAlbum();
		
		$right = new Rights(User::getId());
		$right->set('PD', Rights::FOLDERS, $albumId, 0);
		
		$size = $albumModel->sizeImagesByAlbum($albumId);
		$albumModel->remove($albumId);
		
		$dm = new DiskUsageModel();
		$dm->delete('$SYSTEM', 'PD', $size);
		
		
		print json_encode(array(
			'status' => 'OK'
		));		
	}
	
	public function deleteImageAction()
	{
		$imageId = Env::Post('imageId', Env::TYPE_INT);
		
		$imageModel = new PDImage();
		$size = $imageModel->remove($imageId);
		
		$dm = new DiskUsageModel();
		$dm->delete('$SYSTEM', 'PD', $size);
		
		print json_encode(array(
			'status' => $size
		));			
	}
	
	
	public function cropImageAction()
	{
	    @set_time_limit(0);
	    $param = WebQuery::getParams();
	    
        $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $param['albumId'], Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
		
		$param['width'] = ceil($param['width']);
		$param['height'] = ceil($param['height']);

    	$param['width']  = ( $param['width'] == 0 ) ? 1: $param['width'];
    	$param['height']  = ( $param['height'] == 0) ? 1: $param['height'];
    	
		$imagefs = new PDImageFSModel( PDApplication::getInstance() );
		$size = $imagefs->cropImage(
			$param['albumId'],
			base64_decode( rawurldecode( $param['imageFile'] ) ),
			$param['width'],
			$param['height'],
			$param['x'],
			$param['y']
		);

		$dm = new DiskUsageModel();
		$dm->delete('$SYSTEM', 'PD', abs($size['old']- $size['new']));
		
		$sql = new CUpdateSqlQuery("PIXLIST");
		$sql->addConditions("PL_ID", $param['imageId']);
		$sql->addFields("PL_ROTATE = PL_ROTATE + 1");
		$sql->addFields("PL_WIDTH  = ".$param['width']);
		$sql->addFields("PL_HEIGHT = ".$param['height']);
		$sql->addFields("PL_FILESIZE = ".$size['new']);
		Wdb::runQuery($sql);
		
   		if ( Wbs::isHosted() ) {
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'CROP', 'ACCOUNT');
		}
		print json_encode(array(
   			'status' => 'OK'
   		));
	}
	
	public function rotateImageAction()
	{
	    @set_time_limit(0);
		$param = WebQuery::getParams();
		
	    $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $param['albumId'], Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }
		
		$imagefs = new PDImageFSModel( PDApplication::getInstance() );
		$info = $imagefs->rotateImage(
			$param['albumId'],
			base64_decode(rawurldecode( $param['imageFile'] )),
			$param['rotate']
		);
		
		$sql = new CUpdateSqlQuery("PIXLIST");
		$sql->addConditions("PL_ID", $param['imageId']);
		$sql->addFields("PL_ROTATE = PL_ROTATE + 1");
		$sql->addFields("PL_WIDTH  = ".$info['width']);
		$sql->addFields("PL_HEIGHT = ".$info['height']);
		Wdb::runQuery($sql);
		
   		if ( Wbs::isHosted() ) {
   			
   			if ($param['rotate'] == 90)
   				$side = 'RIGHT';
   			else if ($param['rotate'] == -90)
   				$side = 'LEFT';
   			else 
   				$side = $param['rotate'];
   				
			$metric = metric::getInstance();
	 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'ROTATE', 'ACCOUNT', $side);
		}
		print json_encode(array(
   			'status' => 'OK'
   		));
	}
	
	public function changeThumbAlbumAction()
	{
	    @set_time_limit(0);
        $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, Env::Post('albumId', Env::TYPE_INT), Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        print json_encode(array(
	            'status' => 'ERR',
                'error' => _('Access denied.'),                
	        ));
	        return;
	    }		    
	    
	    $albumModel = new PDAlbum();
	    
	    $albumModel->setThumb(Env::Post('albumId', Env::TYPE_INT),
	                          Env::Post('thumbId', Env::TYPE_INT), 
	                          Env::Post('thumbPath', Env::TYPE_STRING) );
	    
	    print json_encode(array(
   			'status' => 'OK'
   		));
	}
	
	public function changeNewThumbAlbumAction() 
	{
	    $albumId = Env::Get('albumId', Env::TYPE_INT);
		
	    $right = new Rights( User::getId() );		
	    if ( !$right->get('PD', Rights::FOLDERS, $albumId, Rights::MODE_ONE, Rights::RETURN_OBJECT)->isWrite()  ) {
	        return;
	    }		    
	    
	    $albumModel = new PDAlbum($albumId);
	    $image = $albumModel->getFirstImage();

        $albumModel->setThumb($albumId,
	                          $image->PL_ID, 
	                          rawurlencode(base64_encode($image->PL_DISKFILENAME_real)) );
	
		print json_encode(array(
   			'status' => 'OK'
   		));
	}


}

?>
