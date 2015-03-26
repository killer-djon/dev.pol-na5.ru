<?php
//
// Mail Master parsers functions
//

function decodeHeaderLine($str, $charset=false)
{
	if(preg_match("/=\?(.+)\?(B|Q)\?(.+)\?=?(.*)/i", $str, $matches))
		$str = iconv_mime_decode($str, 0, 'UTF-8');
	elseif($charset)
		$str = iconv($charset, 'UTF-8', $str);

	return trim($str);
}

function splitBodyHeader($input)
{
	if(preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $match))
		return array($match[1], $match[2]);
	return false;
}

function parseHeaders($input, $list=false)
{
	$input   = preg_replace("/\r?\n/", "\r\n", $input);
	$input   = preg_replace("/\r\n(\t| )+/", ' ', $input);
	$headers = explode("\r\n", trim($input));

	foreach($headers as $value)
	{
		$hdr_name = strtolower(trim(substr($value, 0, $pos = strpos($value, ':'))));
		$hdr_value = trim(substr($value, $pos+1));
		$hdr[$hdr_name] = empty($hdr[$hdr_name]) ? $hdr_value : $hdr[$hdr_name].'; '.$hdr_value;
	}

	if($list != false)
	{
		$list['MMC_ATTACHMENT'] = 0;		
		if(preg_match('/^\s*"?([a-z\/]+)"?/i', $hdr['content-type'], $match))
		{
			if(strtolower($match[1]) == 'multipart/mixed')
				$list['MMC_ATTACHMENT'] = 1;
		}
		$charset = false;
		if(preg_match('/charset\s*=\s*"?([a-z0-9-]+)"?/i', $hdr['content-type'], $match))
			$charset = $match[1];

		$list['MMC_FROM'] = decodeHeaderLine($hdr['from'], $charset);
		$list['MMC_SUBJECT'] = decodeHeaderLine($hdr['subject'], $charset);
		if($hdr['date'] && preg_match('/^([^\(]+)/', $hdr['date'], $match))
			// strtotime() fails with UT timezone
			$match[1]=preg_replace('/UT$/i', 'UTC', trim($match[1]));
			$list['MMC_DATETIME'] = date('Y-m-d H:i:s', strtotime($match[1]));

		$list['MMC_PRIORITY'] = 3;
		if(isset($hdr['x-priority']) && preg_match('/^\d/', $hdr['x-priority'], $match))
			$list['MMC_PRIORITY'] = $match[0];
	}
	return $hdr;
}

function parseBody($obj, $account, $uid, $num=0, $clear=false)
{
	static $body; // output array for recurse
	if($clear) $body = array();
	static $msg_flag; // save part type for all subparts
	static $charset; // save found anywhere

	if(!empty($obj->parts))
	{
		for($i=0; $i<count($obj->parts); $i++) // see all elements
		{
			$type = strtolower($obj->parts[$i]->ctype_primary);

			if($type == 'multipart')
				parseBody($obj->parts[$i], $account, $uid);
			elseif($type == 'message') // ctype_secondary=>rfc822
			{
				$msg_flag = true; // text will be saved in this part (in $body['msg_text'])
				parseBody($obj->parts[$i], $account, $uid, $num++);
			}
			elseif($type == 'text' &&
				(!isset($obj->parts[$i]->disposition) ||
				strtolower($obj->parts[$i]->disposition) == 'inline')) // attach may be text!
			{
				if(!$msg_flag) // save if parent is not attach
					$text = $obj->parts[$i];
				else // save last text (in [$num])
				{
					$body['msg_text'][$num] = $obj->parts[$i]->body;
					$body['msg_type'][$num] = strtolower($obj->parts[$i]->ctype_secondary);
					$body['msg_charset'][$num] = strtolower($obj->parts[$i]->ctype_parameters['charset']);
				}
			}
			elseif(($type == 'image') || // is this an image?
				(isset($obj->parts[$i]->disposition) &&
				($obj->parts[$i]->disposition == 'attachment')) || // is this an attach?
				($type == 'application')) // it's an attachment too
			{ // save attaches etc.
				if(!empty($obj->parts[$i]->d_parameters['filename']))
					$curName = decodeHeaderLine($obj->parts[$i]->d_parameters['filename']);
				elseif(!empty($obj->parts[$i]->ctype_parameters['name']))
					$curName = decodeHeaderLine($obj->parts[$i]->ctype_parameters['name']);
				else
					$curName = "attachment_$num";

				$curFile = array();
				$curFile['name'] = $curName;
				$curFile['type_p'] = $obj->parts[$i]->ctype_primary;
				$curFile['type_s'] = $obj->parts[$i]->ctype_secondary;

				if(isset($obj->parts[$i]->headers['content-id']))
				$cid = isset($obj->parts[$i]->headers['content-id']) ? $obj->parts[$i]->headers['content-id'] : false;
				$disposition = isset($obj->parts[$i]->disposition) ? $obj->parts[$i]->disposition : false;
				if($cid && $disposition != 'attachment')
				{
					$curFile['cid'] = $cid; // for img only
					$destPath = mm_getNoteAttachmentsDir($account.'~'.$uid, 1);
				}
				else
				{
					$curFile['cid'] = false;
					$destPath = mm_getNoteAttachmentsDir($account.'~'.$uid, 0);
				}

				if(!file_exists($destPath))
				{
					$errStr = null;
					forceDirPath($destPath, $errStr);
				}
				@file_put_contents($destPath.'/'.$curName, $obj->parts[$i]->body);

				$curFile['size'] = @filesize($destPath.'/'.$curName);

				$body['attached_files'][] = $curFile;

				$obj->parts[$i]->body = '';
			}
		}
	}
	if(empty($text) && empty($body)) // no text in this and last parts
		$text = $obj; // find it in the end of current part

	if(!empty($text->body) && (empty($body['text']) || $body['type'] != 'html')) // return only last text (html has prioritry)
	{
		$body['text'] = $text->body; // rewrite on exist
		$body['type'] = strtolower($text->ctype_secondary);
		if(!empty($text->ctype_parameters['charset']))
			$charset = strtolower($text->ctype_parameters['charset']);
	
		if(!empty($charset) && (strtoupper($charset) != 'UTF-8'))
			$body['text'] = iconv($charset, 'UTF-8', $body['text']);
		$body['charset'] = strtolower($charset);
	}
	return $body;
}
/*
//
// Returns 1 attached file if part number maches
//
function parseAttach($obj, $part, $clear=false)
{
	static $out; // result will be stiped here somewhere in the tree, and returnas here
	static $cur_part;
	static $charset;
	if($clear)
	{
		$out = '';
		$cur_part = 0;
		$charset = false;
	}
	for($i=0; $i<count($obj->parts); $i++) // see all elements
	{
		$type = $obj->parts[$i]->ctype_primary;
		if(!empty($obj->parts[$i]->ctype_parameters['charset']))
			$charset = $obj->parts[$i]->ctype_parameters['charset'];

		if(($type == 'multipart') || ($type == 'message'))
			parseAttach($obj->parts[$i], $part);
		elseif(($type == 'image') || // is this an image?
					(isset($obj->parts[$i]->disposition) &&
					($obj->parts[$i]->disposition == 'attachment'))) // is this an attach?
		{ // return image or attach + name + type
			if($part == $cur_part)
			{
				if(!empty($obj->parts[$i]->ctype_parameters['name']))
					$fname = $obj->parts[$i]->ctype_parameters['name'];
				elseif(!empty($obj->parts[$i]->d_parameters['filename']))
					$fname = $obj->parts[$i]->d_parameters['filename'];
				else
				  $fname = 'attachment';
				$out = array($obj->parts[$i]->body,
					decodeHeaderLine($fname, $charset),
					$obj->parts[$i]->ctype_primary,
					$obj->parts[$i]->ctype_secondary);
			}
			$cur_part++;
		}
	}
	return $out;
}
*/
function format_msgbody($msg)
{
	$m = cut_scripts($msg['text']);

	$m = preg_replace('/(<[^>]+?)\son.*?=\s*".*?"/is', '$1', $m);
	$m = preg_replace("/(<[^>]+?)\son.*?=\s*'.*?'/is", '$1', $m);
	$m = preg_replace("/javascript\s*:/i", '$1', $m);
	$m = preg_replace('/(href\s*=\s*"?)\s*&[^>"]+/is', '$1', $m);

	if($msg['type'] == 'plain')
		return format_plain($m);

//	$m = parse_pre($m, true);

	$m = str_ireplace('<a ', '<a target="_blank" ', $m);

	return "\n\n".$m."\n\n";
}

function format_plain($str)
{
	$str = str_replace("\n ", "\n&nbsp;", trim($str));
	$str = preg_replace("/\r?\n/", '<br>', $str);
	$str = "\n\n<div style = \"font-family: monospace\">\n"
		.str_replace('	', ' &nbsp;', $str)."\n</div>\n\n";
	if(!preg_match('/<a[^>]+href\s*=[^>]+>/i', $str)) {
		$str = preg_replace("/(?:http(s?):\/\/(www\.)|http(s?):\/\/|(www\.))([a-z0-9_\.\-]{2,}\.[a-z]{2,4}[a-z0-9_\.\-\/\?&=@:%]*)/i",
			"<a href=\"http$1$3://$2$4$5\" target=\"_blank\" style=\"color:blue\">$0</a>", $str);
		$str = preg_replace("/ftp:\/\/[a-z0-9_\.\-]{2,}\.[a-z]{2,4}[a-z0-9_\.\-\/\?&=@:%]*/i",
			"<a href=\"$0\" target=\"_blank\" style=\"color:blue\">$0</a>", $str);
		$str = preg_replace("/[a-zA-Z0-9]+[\.\-_]?[a-zA-Z0-9]+@([a-z0-9]+[\.|\-]?[a-z0-9]+){1,4}\.[a-z]{2,4}/",
			"<a href=\"mailto:$0\" style=\"color:blue\">$0</a>", $str);
	}
	$str = parse_pre($str, true);

	return $str;
}

function parse_pre($str, $is_html) // change <pre> tags for <div>'s
{
	if($is_html) $s = preg_replace("/<pre[^>]*>/i",'|¤|', $str, 1); // html => find 1-st <pre>
	else $s = preg_replace("/<\/pre[^>]*>/i",'|¤|', $str, 1); // plain => find 1-st </pre>

	$arr=explode('|¤|',$s);

	if(isset($arr[1])) // found <pre> || </pre>
		return parse_pre($arr[0], $is_html).parse_pre($arr[1], !$is_html);
	else // there is not <pre> in this block
		if(!$is_html)
			$str = format_plain($str); // change
	return $str;
}

//
// Formats and implode message text with it's attacments etc.
//
function prepare_body($body, $id, $account, $uid)
{
	global $DB_KEY;
	$att = array();

	$prefix = ($_SERVER['SERVER_PORT'] == 43 ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
	$uri = $prefix.(onWebasystServer() ? '' : '/published');

	if(!empty($body['attached_files'])) // there are some attaches in this message
	{
		for($i=0; $i<count($body['attached_files']); $i++) // what is it?
		{
			if(!empty($body['attached_files'][$i]['cid'])) // this is a inline picture
			{ // src="cid:xxxx" -> src="image.php?...&rand"
				$cid = preg_replace("/\s*<(.+?)>\s*/", "\$1", $body['attached_files'][$i]['cid']);
				$pattern = '/cid:'.preg_quote($cid,"/").'/i'; // src="cid:xx$xx" -> xx\\$xx
				$path = prepareURLStr("$uri/common/html/scripts/getimage.php", array(
					'user'=>base64_encode($DB_KEY),
					'msg'=>$account.'~'.$uid,
					'file'=>base64_encode($body['attached_files'][$i]['name'])
				));
				$body['text'] = preg_replace($pattern, $path, $body['text']);
			}
			else
			{ // this is attached file
				$path = prepareURLStr( '../../2.0/getattach.php', array(
					'mid'=>$account.'~'.$uid,
					'file'=>urlencode(base64_encode($body['attached_files'][$i]['name']))
				));
				$att[$i] = '<a href="'.$path.'">'.cut_scripts($body['attached_files'][$i]['name'])
					.'</a> ('.formatFileSizeStr($body['attached_files'][$i]['size']).')';
			}
		}
	}
	$text = format_msgbody($body); // & cut_scripts

	if(!empty($body['msg_text'])) // there ara attached forwards here (see Yamail)
		for($i=0; $i<count($body['msg_text']); $i++) // add it
		{
			$t = iconv($body['msg_charset'][$i], 'UTF-8', $body['msg_text'][$i]);
			$t = format_msgbody($t, $body['msg_type'][$i]);
			$text .= "\n<hr size=1 noshade>\n$t";
		}

	if(!empty($att))
		$body['att_str'] = implode(' &nbsp;', $att);

	$body['text'] = $text;
}

function formatMsgLead($str, $len=80)
{
	$str = preg_replace('/<title>(.*?)<\/title>/is', ' ', $str);
	$str = preg_replace('/<style(.*?)>(.*?)<\/style>/is', ' ', $str);
	$str = preg_replace('/<(br|p)[^>]*>/i', ' ', $str); // \n alrready changed to <br>
	$str = preg_replace('/[ \r\n\t\f]+/', ' ', $str);
	$str = strip_tags($str);
	$str = trim(cut_scripts($str));

	if($len)
		return chop_str($str, $len);
	else
		return $str;
}

function chop_str($str, $num)
{
	if(mb_strlen($str, 'utf-8') > $num)
		$str = mb_substr($str, 0, $num, 'utf-8').'...';
	return $str;
}

function utf8_urldecode($str)
{
	$str = preg_replace('/%u([0-9a-f]{3,4})/i', "&#x\\1;", urldecode($str));
	return html_entity_decode($str, null, 'UTF-8');
}

function mm_parse($connect, $message, $part, $default_ctype='text/plain', $boundary=false)
{
	$hdr = '';

	while(rtrim($data = fgets($connect)) != '.')
	{
		if(!isset($message['headers'][$part])) {

			$hdr .= $data;
		
			if(preg_match('/\r?\n\r?\n$/', $hdr)) {
				$message['original_header'][$part] = trim($hdr);
				$headers = parseHeaders($hdr);

				reset($headers);
				foreach($headers as $key=>$value) {

					switch($key) {

						case 'content-type':
							$content_type = parseHeaderValue($value);
//							$message['headers'][$part]['content-type']['value'] = $content_type['value'];
							if(preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
								$message['headers'][$part]['content-type']['ctype_primary']   = $regs[1];
								$message['headers'][$part]['content-type']['ctype_secondary'] = $regs[2];
							}
							if(isset($content_type['other'])) {
								foreach($content_type['other'] as $p_name=>$p_value) {
									$message['headers'][$part]['content-type']['ctype_parameters'][$p_name] = $p_value;
								}
							}
							break;

						case 'content-disposition':
							$content_disposition = parseHeaderValue($value);
							$message['headers'][$part]['content-disposition']['disposition'] = $content_disposition['value'];
							if(isset($content_disposition['other'])) {
								foreach($content_disposition['other'] as $p_name=>$p_value) {
									$message['headers'][$part]['content-disposition']['d_parameters'][$p_name] = $p_value;
								}
							}
							break;

						case 'content-transfer-encoding':
							$message['headers'][$part]['content_transfer_encoding'] = parseHeaderValue($value);
							break;

						default:
							$message['headers'][$part][$key] = $value;
					}
				}
			}
		}
		else {
			if(isset($message['headers'][$part]['content-type'])) {

				$content_type = strtolower(
					$message['headers'][$part]['content-type']['ctype_primary'].'/'.
					$message['headers'][$part]['content-type']['ctype_secondary']);

				$encoding = isset($message['headers'][$part]['content_transfer_encoding']['value']) ?
					$message['headers'][$part]['content_transfer_encoding']['value'] : '7bit';

				switch($content_type) {

					case 'text/plain':
						if($boundary && !strpos($data, $boundary)) {
							$message['body'][$part] .= decodeBody($data, $encoding);
							break;
						} else {
							return;
						}

					case 'text/html':
						if($boundary && !strpos($data, $boundary)) {
							$message['body'][$part] .= decodeBody($data, $encoding);
							break;
						} else {
							return;
						}

					case 'multipart/parallel':
					case 'multipart/appledouble':
					case 'multipart/report': // RFC1892
					case 'multipart/signed': // PGP
					case 'multipart/digest':
					case 'multipart/alternative':
					case 'multipart/related':
					case 'multipart/mixed':
						if(!isset($message['headers'][$part]['content-type']['ctype_parameters']['boundary'])){
							$message[$part]['error'] = "No boundary found for part $part";
							return false;
						}
						$boundary = $message['headers'][$part]['content-type']['ctype_parameters']['boundary'];
						$default_ctype = ($content_type === 'multipart/digest') ? 'message/rfc822' : 'text/plain';
						if(strpos($data, $boundary)) {
							mm_parse($connect, $message, ++$part, $default_ctype, $boundary);
							break;
						}
/*
// STOP
						$parts = boundarySplit($body, $message['headers'][$part]['content-type']['ctype_parameters']['boundary']);
						for($i = 0; $i < count($parts); $i++) {
							mm_parse($connect, $message, ++$part, $default_ctype);
						}
*/
						break;

					case 'message/rfc822':
						mm_parse($connect, $message, ++$part);
						break;

					default:
						if(!isset($message['headers'][$part]['content_transfer_encoding']['value']))
							$message['headers'][$part]['content_transfer_encoding']['value'] = '7bit';
						$message['body'][$part] = decodeBody($body, $content_transfer_encoding['value']);
						break;
				}
			} else {
				$message['body'][$part] .= decodeBody($data);
			}
		}
	}
	return;
}

/**
* Function to parse a header value, extract first part, and any secondary parts (after ;)
*
* @param string Header value to parse
* @return array Contains parsed result
*/
function parseHeaderValue($input)
{
	if(($pos = strpos($input, ';')) !== false) {

		$return['value'] = trim(substr($input, 0, $pos));
		$input = trim(substr($input, $pos+1));

		if(strlen($input) > 0) {

			// This splits on a semi-colon, if there's no preceeding backslash
			// Now works with quoted values; had to glue the \; breaks in PHP
			// the regex is already bordering on incomprehensible
			$splitRegex = '/([^;\'"]*[\'"]([^\'"]*([^\'"]*)*)[\'"][^;\'"]*|([^;]+))(;|$)/';
			preg_match_all($splitRegex, $input, $matches);
			$parameters = array();
			for($i=0; $i<count($matches[0]); $i++) {
				$param = $matches[0][$i];
				while (substr($param, -2) == '\;') {
					$param .= $matches[0][++$i];
				}
				$parameters[] = $param;
			}

			for($i = 0; $i < count($parameters); $i++) {
				$param_name  = trim(substr($parameters[$i], 0, $pos = strpos($parameters[$i], '=')), "'\";\t\\ ");
				$param_value = trim(str_replace('\;', ';', substr($parameters[$i], $pos + 1)), "'\";\t\\ ");
				if($param_value[0] == '"') {
					$param_value = substr($param_value, 1, -1);
				}
				$return['other'][$param_name] = $param_value;
				$return['other'][strtolower($param_name)] = $param_value;
			}
		}
	} else {
		$return['value'] = trim($input);
	}
	return $return;
}

/**
 * This function splits the input based on the given boundary
 *
 * @param string Input to parse
 * @return array Contains array of resulting mime parts
 */
function boundarySplit($input, $boundary)
{
	$parts = array();

	$bs_possible = substr($boundary, 2, -2);
	$bs_check = '\"' . $bs_possible . '\"';

	if ($boundary == $bs_check) {
		$boundary = $bs_possible;
	}

	$tmp = explode('--' . $boundary, $input);

	for ($i = 1; $i < count($tmp) - 1; $i++) {
		$parts[] = $tmp[$i];
	}

	return $parts;
}

/**
 * Given a body string and an encoding type, this function will decode and return it.
 *
 * @param  string Input body to decode
 * @param  string Encoding type to use.
 * @return string Decoded body
 */
function decodeBody($input, $encoding = '7bit')
{
	switch(strtolower($encoding)) {
		case 'quoted-printable':
			return quotedPrintableDecode(rtrim($input));

		case 'base64':
			return base64_decode(rtrim($input));
	}
	return $input; // case '7bit' and other
}

/**
 * Given a quoted-printable string, this function will decode and return it.
 *
 * @param  string Input body to decode
 * @return string Decoded body
 */
function quotedPrintableDecode($input)
{
	// Remove soft line breaks
	$input = preg_replace("/=\r?\n/", '', $input);

	// Replace encoded characters
	$input = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $input);

	return $input;
}

?>