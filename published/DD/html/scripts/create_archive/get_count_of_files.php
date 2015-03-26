<?php

//error_reporting(E_ALL);

$scriptPath = getcwd();

$WBSPath = $scriptPath."/../../../../../";

$rp = realpath($WBSPath);

define( "WBS_DIR", $rp."/" );

$activeFile =$_REQUEST['activeFile'];
 
 
$s_file = ini_get('session.save_path').'/sess_'.substr($activeFile,0,-3);

if ($activeFile and file_exists($s_file)) 
	{
	$activity = WBS_DIR.'temp/'.substr($activeFile,0,-3).'.tmp_up';
	
	if (!$handle = @fopen($activity, 'r')) 
		die('1');
	
		
	$contents = fread($handle, filesize($activity));

	
	fclose($handle);
	echo $contents;
	}
else 
	{
	die('Error#2');
	}

	
?>