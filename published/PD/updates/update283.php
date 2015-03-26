<?php

//echo "DBKEY: ".Wbs::getDbkeyObj()->getDbkey()."\n";

$model = new DbModel();

// Files widgets
$sql = "SELECT WP.* FROM WG_PARAM WP JOIN WG_WIDGET W ON WP.WG_ID = W.WG_ID 
        WHERE WP.WGP_NAME = 'FOLDERS' AND W.WT_ID = 'DDList'";
$q = $model->query($sql);
foreach ($q as $row) {
	$folder_id = $row['WGP_VALUE'];
    if (!$folder_id || substr($folder_id, -1) == '.') {
        continue;
    }
	
	$folders = $model->query("SELECT DF_ID FROM DOCFOLDER WHERE REPLACE( DF_ID, '.', '' ) = '".$model->escape($folder_id)."'")->fetchAll();
	// Fix if only one folder exists 
	if (count($folders) == 1) {
		//echo "Fixed: ".$folder_id." -> ".$folders[0]['DF_ID']."\n";
		$sql = "UPDATE WG_PARAM 
		        SET WGP_VALUE = s:folder_id WHERE WG_ID = i:widget_id AND WGP_NAME = 'FOLDERS'";
		$model->prepare($sql)->exec(array('widget_id' => $row['WG_ID'], 'folder_id' => $folders[0]['DF_ID']));		
	}
	// Report if exists two or more folders 
	elseif (count($folders) > 1) {
	   //echo "Not fixed: Widget ".$row['WG_ID'].", folder ".$folder_id."\n";	
	}
}

?>