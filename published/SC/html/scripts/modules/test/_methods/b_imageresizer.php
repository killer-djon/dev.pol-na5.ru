<?php

$smarty = &Core::getSmarty();

function getPicturesList(){
	$result = array();
	
	$path = new DirectoryIterator(DIR_PRODUCTS_PICTURES);

	foreach($path as $key => $item ){
		if( $item->isFile() && !$item->isDot() ){
			$result[] = array(
				'src' => $item->getFilename(),
				'path' => ( !file_exists(DIR_PRODUCTS_PICTURES."/_thumb/".$item->getFilename()) ? URL_PRODUCTS_PICTURES : URL_PRODUCTS_PICTURES."/_thumb" ),
				'_thumb' => ( !file_exists(DIR_PRODUCTS_PICTURES."/_thumb/".$item->getFilename()) ? false : true ),
				//URL_PRODUCTS_PICTURES//$item->getPath()
			);

		}
	}
	return $result;
}

function imgResize($input, $output_dir = "", $percent = 0.4){
	if( empty($input) )
		return false;
	
	$filename = DIR_PRODUCTS_PICTURES.DIRECTORY_SEPARATOR.$input;
	$dest_path = DIR_PRODUCTS_PICTURES.DIRECTORY_SEPARATOR."_thumb";
	
	if( !file_exists($dest_path) && !is_dir($dest_path) ){
		@mkdir($dest_path, 0775);
	}
	
	list($width, $height) = getimagesize($filename);
	$newwidth = ( ($width * $percent) >= 800 ? ($width * $percent)*$percent : ($width * $percent) );
	$newheight = ( ($height * $percent) >= 800 ? ($height * $percent)*$percent : ($height * $percent) );
	
	$thumb = imagecreatetruecolor($newwidth, $newheight);
	$source = imagecreatefromjpeg($filename);
	
	imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	if( imagejpeg($thumb, $dest_path.DIRECTORY_SEPARATOR.basename($filename)) ){
		return $dest_path.DIRECTORY_SEPARATOR.basename($filename);
	}
	
	return false;
}

if( isset($_POST['convert']) && isset($_POST['pic_select']) ){
	if( is_array($_POST['pic_select']) && count($_POST['pic_select']) ){
		$result = array();
		
		foreach( $_POST['pic_select'] as $item ){
			$result[] = imgResize($item);
		}
	}
	$smarty->assign("success", 1);
}
$pictures = getPicturesList();
$cnt = count($pictures);

$offset = (isset($_GET['show_all']) ? $cnt : 100);
$start = ( isset($_GET['start']) ? ( $_GET['start'] != 1 ? intval( $_GET['start']*$offset) : 0 ) : 0 );

$result = array();

for($i = $start; $i <= ($start+$offset); $i++){
	$result[] = $pictures[$i];
}

$smarty->assign("pictures", $result);
$smarty->assign("pages", ceil($cnt/$offset));

$smarty->assign('admin_sub_dpt', 'images_transform.html');

?>