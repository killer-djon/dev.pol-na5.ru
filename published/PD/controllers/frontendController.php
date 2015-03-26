<?php

	class frontendController extends ActionController 
	{
		private $theme =  '../themes/default/';		
		
		public function init()
		{	
			$this->defaultAction = 'albumList';
		}
		
		public function albumListAction()
		{
			$albumModel = new PDAlbum();
			$data = $albumModel->getAlbumListThumb(PDAlbum::STATE_PUBLIC);
			$list = $data['data'];
			
			foreach ( $list as &$row ) {
			    if ( file_exists( PDImage::getFilePathFrontThumb($row) ) ) {
				    $row['IMG_URL'] = $albumModel->getThumbFrontendUrl($row);
			    }
				else {
				    $row['IMG_URL'] = '';
				}
				
				$row['PF_NAME'] = htmlspecialchars($row['PF_NAME']);
				$row['PF_DATESTR'] = htmlspecialchars($row['PF_DATESTR']);
			}
			 
			$preproc = new PDSmarty(PDSmarty::MODE_FRONTEND);
			
			$powerBy = ( Wbs::getSystemObj()->isDisablePoweredBy() ) ? false : true;
			$preproc->assign('powerBy', $powerBy);

            $user_settings_model = new UserSettingsModel();
            $galery_name = $user_settings_model->get("", "PD", "GalleryName");
    		$preproc->assign('galery_name', $galery_name);
    		
			if ( !file_exists( PDApplication::getDataUserThemesPath('default/main.css') ) )
				$preproc->assign('css', Url::get('/PD/templates/themes/default/main.css') );
			else
				$preproc->assign('css', PDApplication::getPublishedUserThemesUrl('default/main.css') );
				
				
//				$dir = Wbs::getSystemObj()->getWebUrl();
//				$dir = ( $dir == '/' || empty($dir) ) ? '' : $dir;
//				$mainUrl = 'http://'.$_SERVER['HTTP_HOST']
//				                .$dir
//								.'/photos/';
//
//				if( Wbs::getSystemObj()->isModeRewrite() || Wbs::isHosted() ) {
//					$mainUrl .= 'album/';
//				}						
//				else {
//					$mainUrl .= 'index.php?album=';
//				}
				
				$mainUrl = PDApplication::getFrontendUrl('', array(
        		    'album/',
        		    'index.php?album='
        		));
				$preproc->assign('album_list_link', $mainUrl);
				
		    if ( Wbs::isHosted() ) {
				$metric = metric::getInstance();
		 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'VIEW', 'FRONTEND');
			}
    		
    		$powerBy = ( Wbs::getSystemObj()->isDisablePoweredBy() ) ? false : true;
    		$preproc->assign('powerBy', $powerBy);
			
			$preproc->assign('albumList', $list);	
			$preproc->assign('image_count', $imageCount);		
			$preproc->assign('content', $preproc->fetch('album-view1.html'));

			$preproc->display($this->theme.'index.html');
		}
		
		public function albumAction()
		{
			$album = $this->requestParams[1];
			
			$album = urldecode($album);
			$album = mysql_real_escape_string($album);
			
			$albumModel = new PDAlbum();
			$row = $albumModel->getIdByLink($album);
			
			if ( $row['PF_STATUS'] != PDAlbum::STATE_PRIVATE && !empty($row) ) {				
				$album = new PDAlbum($row['PF_ID']);
				if ( empty($album->PF_SETTING) ) {
					$frontend =  1;
					$foto_size =  512;
					$foto_thumb =  96;
					$limit = 10;
				}
				else {
					$setting = new SimpleXMLElement( $album->PF_SETTING );
					$frontend = ( empty( $setting->frontend ) ) ? 1 : $setting->frontend;
					$foto_size = ( empty( $setting->{'foto-size'} ) ) ? 512 : (int)$setting->{'foto-size'};
					$foto_thumb = ( empty( $setting->{'foto-thumb'} ) ) ? 96 : (int)$setting->{'foto-thumb'};
					$limit = ( empty( $setting->{'foto-count'} ) ) ? 10 : (int)$setting->{'foto-count'};
					$view_oreginal = ( empty( $setting->{'view-oreginal'} ) ) ? 0 : (int)$setting->{'view-oreginal'};
                    if ( $limit == 'all' ) $limit = 10000;
				}
				
				$offset = ( isset($this->requestParams[2]) && !empty($this->requestParams[2]) ) ? str_replace('page', '',  $this->requestParams[2]) : 1;
				
				$offset -= 1;
				
				$imageList = $album->getImageListAndComments(null,
															 $offset*$limit,
															 $limit);
				$preproc = new PDSmarty(PDSmarty::MODE_FRONTEND);

				$powerBy = ( Wbs::getSystemObj()->isDisablePoweredBy() ) ? false : true;
			    $preproc->assign('powerBy', $powerBy);
				
				$list = $imageList['data'];
				
				if ( $foto_thumb == 48 ) {
				    $foto_thumb = 96;
				    $is48 = true;
				}
				
//    			if( Wbs::getSystemObj()->isModeRewrite() || wbs::isHosted()) {
//                    $orig_url = Url::getServerUrl() . '/photos/fullsize/%s/%s';
//                }
//        		else {
//        		    $orig_url = Url::getServerUrl() . '/photos/fullsize.php?filename=%s&hash=%s';
//        		}
        		$orig_url = PDApplication::getFrontendUrl('', array(
        		    'fullsize/%s/%s',
        		    'index.php?filename=%s&hash=%s'
        		));
					
				foreach ( $list as &$row_ ) {
					
//				    $isGeneric = !$row_['PL_WIDTH'] || empty($row_['PL_WIDTH']) || $row_['PL_WIDTH'] == 0;
//				    
//				    if ($isGeneric)
//				        $row_ = PDImage::updateInfoImage($row_);
				    
				    if ( $frontend != 1 ) {
				        $foto_thumb_l = $this->getActualSize($row_, $foto_thumb);
					    
					    $foto_size = ($frontend == 3) ? SIZE_970 : $foto_size;

					    $row_['PL_DISKFILENAME'] = base64_decode( rawurldecode($row_['PL_DISKFILENAME']) );
					    
					    if ( file_exists( PDImage::getFilePath($row_, $foto_thumb_l) ) && file_exists( PDImage::getFilePath($row_, $foto_size) ) ) {
    						$row_['IMG_URL'] = $albumModel->getImageDataUrl($row_, $foto_thumb_l);
    						$row_['IMG_URL_1024'] = $albumModel->getImageDataUrl($row_, $this->getActualSize($row_, $foto_size));
					    }
					    else {
    						$row_['IMG_URL'] = PDImage::getUrl($row_, $foto_thumb_l, true);
    						$row_['IMG_URL_1024'] = PDImage::getUrl($row_, $this->getActualSize($row_, $foto_size), true);
    						$row_['IS_IMG_CREATE'] = 1;
					    }
						
						$row_['HASH'] = sprintf($orig_url, rawurlencode(base64_encode($row_['PL_DISKFILENAME'])), md5( $row_['PL_ID'] . $row_['PL_UPLOADDATETIME'] ));
				    }
				    else  {
				        $foto_size_l = $this->getActualSize($row_, $foto_size);

				        $row_['PL_DISKFILENAME'] = base64_decode( rawurldecode($row_['PL_DISKFILENAME']) );
				        if ( file_exists( PDImage::getFilePath($row_, $foto_size_l) )  ) {
						    $row_['IMG_URL'] = $albumModel->getImageDataUrl($row_, $foto_size_l);
				        }
				        else {
				            $row_['IMG_URL'] = PDImage::getUrl($row_, $foto_size_l, true);
						    $row_['IS_IMG_CREATE'] = 1;
				        }
						
						$row_['HASH'] = sprintf($orig_url, rawurlencode(base64_encode($row_['PL_DISKFILENAME'])), md5( $row_['PL_ID'] . $row_['PL_UPLOADDATETIME'] ));
				    }
				    if ( $frontend == 3 ) {
				        $row_['PL_DESC'] = strip_tags( $row_['PL_DESC'] );
				    }
				    
				}
				
				if ( $is48 ) { 
				    $foto_thumb = 48;
				}
			
    			if ( !file_exists( PDApplication::getDataUserThemesPath('default/main.css') ) )
    				$preproc->assign('css', Url::get('/PD/templates/themes/default/main.css') );
    			else
    				$preproc->assign('css', PDApplication::getPublishedUserThemesUrl('default/main.css') );
    				
									
//				$dir = Wbs::getSystemObj()->getWebUrl();
//				$dir = ( $dir == '/' || empty($dir) ) ? '' : $dir;
//				$mainUrl = 'http://'.$_SERVER['HTTP_HOST']
//				                .$dir
//								.'/photos/';
//
//				if( Wbs::getSystemObj()->isModeRewrite() || Wbs::isHosted() ) {
//					$mainUrl .= '';
//					$pageUrl = $mainUrl.'album/'.$row['PF_LINK'].'/page';
//				}						
//				else {
//					$mainUrl .= 'index.php';
//					$pageUrl = $mainUrl.'?album='.$row['PF_LINK'].'&page';				    
//				}

    			$mainUrl = PDApplication::getFrontendUrl('', array(
        		    '',
        		    'index.php'
        		));
 
				if( Wbs::getSystemObj()->isModeRewrite() || Wbs::isHosted() ) {
					$pageUrl = $mainUrl.'album/'.$row['PF_LINK'].'/page';
				}						
				else {
					$pageUrl = $mainUrl.'?album='.$row['PF_LINK'].'&page';				    
				}        		
				
				$imageModel = new PDImage();
				$count = $imageModel->count($row['PF_ID']); 

			    $pages = Pager::render(
                    $offset + 1,
                    3,
                    ceil($count/$limit),
                    $pageUrl
                );
                
				if ( Wbs::isHosted() ) {
					$metric = metric::getInstance();
			 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'VIEW', 'FRONTEND', $frontend );
				}
                
                $pages = preg_replace('~[/&]page1(?!\d)~i', '', $pages);
                
                
                $user_settings_model = new UserSettingsModel();
                $galery_name = $user_settings_model->get("", "PD", "GalleryName");
                
        		$preproc->assign('galery_name', $galery_name);

				$preproc->assign('imageList', $list);	

				$preproc->assign('pages', $pages);
				
				$preproc->assign('album', $row);
				
				$preproc->assign('view_oreginal', $view_oreginal);
				$preproc->assign('album_list_link', $mainUrl);
				$preproc->assign('album_name', htmlspecialchars($row['PF_NAME']));
				$preproc->assign('album_link', $row['PF_LINK']);
				$preproc->assign('album_status', $row['PF_STATUS']);
				$preproc->assign('foto_size', $foto_size);
				$preproc->assign('foto_thumb', $foto_thumb);
				$preproc->assign('frontend', $frontend);
				
				$preproc->assign('ref', $_SERVER['HTTP_REFERER']);
				
				$preproc->assign('content', $preproc->fetch('image-view'. $frontend .'.html'));

				$preproc->display($this->theme.'index.html');
			}
			else {
                header("HTTP/1.0 404 Not Found");
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
                print "404 Not found";
				die();
			}
		}
		
		private function getActualSize($row, $size) {
		    $w = max( $row['PL_WIDTH'] , $row['PL_HEIGHT'] );
		    
		    if ( $w > $size ) return $size;
		    
		    $sizes = array(144, 256, 512, SIZE_970);
		    foreach ($sizes as $v) {
		        if ( $w > $v) {
		            $return = $v;
		        }
		        if ( $v >= $size) break;
		    }
		    if (!$return) $return = 144;
		    return $return;
		}
		
		public function viewAction()
		{
		    $imageName = $this->requestParams[1];
		    
		    $imageName = base64_decode( rawurldecode( $imageName ) );
		    
		    $preproc = new PDSmarty(PDSmarty::MODE_FRONTEND);
		
//			if ( preg_match('~\.([0-9]+)\.~', $imageName, $matchs) ) {
//			    $size = $matchs[1];
//			    $imageName = str_replace(".{$size}", "", $imageName);
//			}
		    
		    $imageModel = new PDImage();
		    $image = $imageModel->getImageByName($imageName);
		    
		    $size = $this->requestParams[2];
		    $size = ( isset($size) ) ? $size : SIZE_970;
		    
		    if ( $image ) {
		    
    		    $albumModel = new PDAlbum();
    		    if ( file_exists(PDImage::getFilePath($image, $size)) )
    		    	$imageUrl = $albumModel->getImageDataUrl($image, $size);
    		    else
    		    	$imageUrl = PDImage::getUrl($image, $size, PDImage::MODE_ORIG);
    		    	
    		    $imageDesc = $image['PL_DESC'];
    		    $imageDesc_mini = htmlspecialchars(StringUtils::truncate( strip_tags($image['PL_DESC']), 50));
    		    
    		    $album = $albumModel->loadId($image['PF_ID']);

    		    $albumDesc = $album->PF_DESC;
    		    $albumDesc_mini = StringUtils::truncate($albumDesc, 50);
    
    		    $powerBy = ( Wbs::getSystemObj()->isDisablePoweredBy() ) ? false : true;
    			$preproc->assign('powerBy', $powerBy);
    			
    		    $preproc->assign('imageUrl',   $imageUrl);
    		    $preproc->assign('imageDesc', $imageDesc);
    		    $preproc->assign('imageDesc_mini', $imageDesc_mini);
    		    
    		    $preproc->assign('albumDesc', $albumDesc);
    		    $preproc->assign('albumDesc_mini', $albumDesc_mini);
    		    
                $user_settings_model = new UserSettingsModel();
                $galery_name = $user_settings_model->get("", "PD", "GalleryName");
                
        		$preproc->assign('galery_name', $galery_name);
    		    
				if ( Wbs::isHosted() ) {
					$metric = metric::getInstance();
			 		$metric->addAction(Wbs::getDbkeyObj()->getDbkey(), User::getId(), 'PD', 'DOWNLOAD-URL', 'FRONTEND');
				}
        		
        		
    			$preproc->display('image-one.html');
		    }
		    else {
                header("HTTP/1.0 404 Not Found");
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
                echo "404 page not found";
				die();		        
		    }
		    
		}
	}
	

?>