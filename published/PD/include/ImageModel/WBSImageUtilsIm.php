<?php

	/**
	 * Класс для работы с изображениями. Использует ImageMagick
	 * @author Solyankin Kirill
	 * @link http://www.bitweaver.org/doc/magickwand/index.html
	 */
	class WBSImageUtilsIm {
		private $image;
		private $path;
		
		public function __construct($path = null) {
			if ( IsMagickWand( $path ) ) {
				$this->image = $path;
			}
			else if( is_string( $path ) ) {
				$this->image = NewMagickWand();
				if ($path)
					$this->openImage($path);
				$this->path = $path;
			}			
		}		
		
		/**
		 * @param string $path
		 * @return WBSImageUtilsIm
		 */
		public function openImage($path) {
			MagickReadImage($this->image, $path);
			return $this;
		}
		
		/**
		 * @param Number $size
		 * @return WBSImageUtilsIm
		 */
		public function resize($size) {
			
			$image = CloneMagickWand($this->image);
			
			// Shrink image
			$width = MagickGetImageWidth( $image);
			$height = MagickGetImageHeight( $image);
			
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

			MagickResizeImage( $image, $width, $height, MW_LanczosFilter, 1.0 );
			// применяем UnsharpMask чтобы сделать картинку чётче (особенно актуально для маленьких превьюх)
			MagickUnsharpMaskImage( $image, 1.5, 1.0, 1.5, 0.02 );
					
			return new WBSImageUtilsIm($image);
		}
		
		/**
		 * @param float $angle
		 * @return WBSImageUtilsIm
		 */
		public function rotate($angle) {		
			$image = CloneMagickWand($this->image);
				
			MagickRotateImage( $image, NewPixelWand("#FFFFFF"), $angle );
			return new WBSImageUtilsIm($image);
		}
		public function crop($w, $h, $x, $y) {
			$image = CloneMagickWand($this->image);
			
			MagickCropImage( $image, $w, $h, $x, $y); 
			return new WBSImageUtilsIm($image);
		}
		
		/**
		 * @param int $size
		 * @return WBSImageUtilsIm
		 */
		public function cropBox($size) {
			$image = CloneMagickWand($this->image);
			
			$width = MagickGetImageWidth( $image );
			$height = MagickGetImageHeight( $image );
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
			
			MagickResizeImage( $image, $width, $height, MW_LanczosFilter, 1.0 );
			
			//imagecopyresampled ( $destImg, $this->imgRes, -$x, 0, 0, 0, $size+$x, $size, $this->info[0], $this->info[1] );
			MagickCropImage( $image, $size, $size, $x, 0); 
			return new WBSImageUtilsIm($image);
		}
		
		public function outputImage() {
			header("Content-Type: image/jpeg");
			
			MagickEchoImageBlob($this->image);
			MagickRemoveImage($this->image);
		}
		/**
		 * @param string $path
		 * @return WBSImageUtilsIm
		 */
		public function save($path = null) {
			$path_  = ( $path ) ? $path : $this->path;
			MagickWriteImage($this->image, $path_);
			
			return $this;
		}
		
	}
?>