<?php 

@set_time_limit(0);

//ini_set('memory_limit', '128M');
//error_reporting(0);

function dd_preAddCallBack($p_event, &$p_header)
//
// Pre extract callback function
//
	{
	global $dd_addTmpName__;

	$p_header['stored_filename'] = $dd_addTmpName__;

	return 1;
	}

$json_file = $_SERVER['argv'][1];

if (!$json_string = file_get_contents($json_file)) 
	die("#ERROR_3");

$json_style = json_decode($json_string);

define("PCLZIP_TEMPORARY_DIR",$json_style->tmp_zip);

require_once realpath($json_style->for_pclzip);

$archive = new PclZip($json_style->archivePath);

$res = $archive->create(array());


// write process of archiving
//
function wr($count)
	{
	global $json_style;
	$filename = $json_style->activity."_up";

		if (!$handle = fopen($filename, "w")) {
		die("#ERROR_1");
	    }

		$count = intval($count);
		   if (fwrite($handle, $count) === FALSE) {
		die("#ERROR_2");
		   }

	fclose($handle);
	}

$count_a = 0;

wr(0);

foreach ($json_style->archiveFiles as $DL_ID ) 
	{

	$count_a ++;
	$last_modify = filemtime($json_style->activity);
	if ((time() - $last_modify) > $json_style->limit_time) // FIXME
		{
		die("#ERROR_4");
		}
	wr($count_a);
	
	$dd_addTmpName__ = $DL_ID->stored_filename; 
	$p_header['stored_filename'] = $DL_ID->stored_filename; 
	
	if (is_readable($DL_ID->filePath)) 
		{
		$archive->add( $DL_ID->filePath, $DL_ID->PCLZIP_OPT_REMOVE_PATH, $DL_ID->fileDir, $DL_ID->PCLZIP_CB_PRE_ADD, "dd_preAddCallBack" );//$DL_ID->dd_preAddCallBack );
		}
	}

echo "#YES";
?>