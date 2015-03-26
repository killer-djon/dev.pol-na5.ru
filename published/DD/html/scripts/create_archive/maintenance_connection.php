<?php

//error_reporting(E_ALL);

$scriptPath = getcwd();

$WBSPath = $scriptPath."/../../../../../";

$rp = realpath($WBSPath);

define( "WBS_DIR", $rp."/" );

$activeFile = $_REQUEST['activeFile'];

$s_file = ini_get('session.save_path').'/sess_'.$activeFile;

if ($activeFile and file_exists($s_file)) 
	{
	$activity = WBS_DIR.'temp/'.$activeFile.'.tmp';

	if (!$handle = fopen($activity, 'w+')) 
		echo 'Bad#1';
	
	$content = time();
	
	if (fwrite($handle, $content) === FALSE) 
		echo 'Bad#2';
	
	fclose($handle);
	echo 'Ok';
	}
else 
	{
	echo 'Bad#3';
	}

	
?>