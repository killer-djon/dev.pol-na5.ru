<?
	class ImageGdLib {
		
		private $imgRes = null;
		private $info = null;
		
		public function __construct($path = null) {
			$this->openImage($path);
		}
		
		public function getImageWidth()
	    {
	        return $this->info[0];
	    }
		public function getImageHeight()
	    {
	        return $this->info[1];
	    }
	    

		public function openImage($path) {
			if (!extension_loaded("GD"))
				throw new RuntimeException ("Not include GD library");
				
			$this->info = getimagesize( $path );
			switch ($this->info[2]) {
		        case 1:
		            // Create recource from gif image
					$this->imgRes = imagecreatefromgif( $path );
		            break;
		        case 2:
		            // Create recource from jpg image
					$this->imgRes = imagecreatefromjpeg( $path );
					
		            break;
		        case 3:
		            // Create resource from png image
					$this->imgRes = imagecreatefrompng( $path );
		            break;
		        case 6:
		            // Create recource from bmp image imagecreatefromwbmp
					$this->imgRes = imagecreatefromwbmp( $path );
		            break;
		        default:
		            break;
			}
			
			return $this;
		}


		public function _resize($w, $h) {
			$destImg = imagecreatetruecolor( $w, $h );
				
			if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, 0, 0, 0, 0, $w, $h, $this->info[0], $this->info[1] );
			else
				imagecopyresized ( $destImg, $this->imgRes, 0, 0, 0, 0, $w, $h, $this->info[0], $this->info[1] );			
			
			$this->imgRes = $destImg;
			return $this;
		}

		public function resize($size) {
			
			// Shrink image
			$width = $this->info[0];
			$height = $this->info[1];

		    if ( $width > $height ) {
				if ( $width > $size ) {
					$ratio = $width/$height;
		
					$height = $size/$ratio;
					$width = $size;
				}
			} else {
				if ( $height > $size ) {
					$ratio = $width/$height;
		
					$width = $size*$ratio;
					$height = $size;
				}
			}	

			$this->_resize($width, $height);		
			return $this;
		}		
		

		public function rotateImage($angle) {			
			$this->imgRes = imagerotate($this->imgRes, $angle, 0);  
			return $this;
		}

		public function thumbnailImage($width, $height) {
			$size = ( !is_null($width) ) ? $width : $height;
			
			$width = $this->info[0];
			$height = $this->info[1];
			if ( $width > $height ) {
				if ( $width > $size ) {
					$ratio = $width/$height;
		
					$width = $size*$ratio;
					$height = $size;

					$x = ($width - $size)/2;
				}
			} else {
				if ( $height > $size ) {
					$ratio = $width/$height;
		
					$height = $size/$ratio;
					$width = $size;
					
					$x = ($height - $size)/2;
				}
			}
			
			$destImg = imagecreatetruecolor( $size, $size );
				
			if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, -$x, 0, 0, 0, $size+$x, $size, $this->info[0], $this->info[1] );
			else
				imagecopyresized ( $destImg, $this->imgRes, -$x, 0, 0, 0, $size+$x, $size, $this->info[0], $this->info[1] );			
			
			$this->imgRes = $destImg;
			return $this;
			
			
		}
		
		public function outputImage($quality = null) {
			// Set the content type header - in this case image/jpeg
			header('Content-type: '.$this->info['mime']);
			
			// Output the image
			if ($quality)
				imagejpeg($this->imgRes, null, $quality);
			else
				imagejpeg($this->imgRes);
			
			// Free up memory
			imagedestroy($this->imgRes);			
		}

		public function writeImage($path, $quality = null) {
			// Output the image
			if ($quality)
				imagejpeg($this->imgRes, $path, $quality);
			else
				imagejpeg($this->imgRes, $path);
		
			return $this;
		}
		
		public function destroy()
		{
			imagedestroy($this->imgRes);
		}
	}

?>