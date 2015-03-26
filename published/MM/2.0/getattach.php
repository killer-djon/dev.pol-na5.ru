<?php

	include_once '_screen.init.php';

	$mid = WebQuery::getParam('mid');
	$file = base64_decode(WebQuery::getParam('file'));

	$ctype = get_mime($file);
	$path = Wbs::getSystemObj()->files()->getDataPath().'/'.Wbs::getDbkeyObj()->getDbkey().'/attachments/mm/attachments/'.$mid.'/'.$file;

	if (preg_match("/msie/i",$_SERVER['HTTP_USER_AGENT'])) {
		if (preg_match("/[а-я]/ui", $file)) {
		 $filename = iconv("UTF-8", "Windows-1251", $file);
		} else {
		 $filename = rawurlencode($file);
		}
	} else {
		 $filename = $file;
	}
	
	@ob_end_clean();

	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Type: '.$ctype);
	header('Content-Disposition: attachment; filename="'.$filename.'"');

	print file_get_contents($path);

	function get_mime($filename)
	{
		preg_match('/\.(.*?)$/', $filename, $m);
		switch(strtolower($m[1]))
		{
			case 'jpg': case 'jpeg': case 'jpe': return 'image/jpg';
			case 'png': case 'gif': case 'bmp': case 'tiff' : return 'image/'.strtolower($m[1]);

			case 'doc': case 'docx': return 'application/msword';
			case 'xls': case 'xlt': case 'xlm': case 'xld': case 'xla': case 'xlc': case 'xlw': case 'xll': return 'application/vnd.ms-excel';
			case 'ppt': case 'pps': return 'application/vnd.ms-powerpoint';
			case 'rtf': return 'application/rtf';
			case 'txt': return 'text/plain';
			case 'pdf': return 'application/pdf';
			case 'html': case 'htm': case 'php': return 'text/html';
			case 'zip': return 'application/zip';
			case 'tar': return 'application/x-tar';
			case 'js': return 'application/x-javascript';
			case 'json': return 'application/json';
			case 'css': return 'text/css';
			case 'xml': return 'application/xml';
			case 'mpeg': case 'mpg': case 'mpe': return 'video/mpeg';
			case 'mp3': return 'audio/mpeg3';
			case 'wav': return 'audio/wav';
			case 'aiff': case 'aif': return 'audio/aiff';
			case 'avi': return 'video/msvideo';
			case 'wmv': return 'video/x-ms-wmv';
			case 'mov': return 'video/quicktime';
			case 'zip': return 'application/zip';
			case 'tar': return 'application/x-tar';
			case 'swf': return 'application/x-shockwave-flash';

			default: return 'application/octet-stream';
		}
	}

?>