<?php
/**
 * The Mail mm_mimeDecode class is used to decode mail/mime messages
 *
 * This class will parse a raw mime email and return the structure.
 * Returned structure is similar to that returned by imap_fetchstructure().
 */
class mm_mimeDecode
{
	/**
	 * The raw email to decode
	 *
	 * @var	string
	 * @access private
	 */
	var $_input;

	/**
	 * The header part of the input
	 *
	 * @var	string
	 * @access private
	 */
	var $_header;

	/**
	 * The body part of the input
	 *
	 * @var	string
	 * @access private
	 */
	var $_body;

	/**
	 * If an error occurs, this is used to store the message
	 *
	 * @var	string
	 * @access private
	 */
	var $_error;

	/**
	 * Constructor.
	 *
	 * Sets up the object, initialise the variables, and splits and
	 * stores the header and body of the input.
	 *
	 * @param string The input to decode
	 * @access public
	 */
	function mm_mimeDecode($input)
	{
		list($header, $body) = $this->_splitBodyHeader($input);

		$this->_input  = $input;
		$this->_header = $header;
		$this->_body   = $body;
	}

	/**
	 * Begins the decoding process. If called statically
	 * it will create an object and call the decode() method of it.
	 *
	 * @return object Decoded results
	 * @access public
	 */
	function decode()
	{
		return $this->_decode($this->_header, $this->_body);
	}

	/**
	 * Performs the decoding. Decodes the body string passed to it
	 * If it finds certain content-types it will call itself in a
	 * recursive fashion
	 *
	 * @param string Header section
	 * @param string Body section
	 * @return object Results of decoding process
	 * @access private
	 */
	function _decode($headers, $body, $default_ctype = 'text/plain')
	{
		$return = new stdClass;
		$return->headers = array();
		$headers = $this->_parseHeaders($headers);

		foreach($headers as $value) {
			if (isset($return->headers[strtolower($value['name'])]) AND !is_array($return->headers[strtolower($value['name'])])) {
				$return->headers[strtolower($value['name'])]   = array($return->headers[strtolower($value['name'])]);
				$return->headers[strtolower($value['name'])][] = $value['value'];

			} elseif (isset($return->headers[strtolower($value['name'])])) {
				$return->headers[strtolower($value['name'])][] = $value['value'];

			} else {
				$return->headers[strtolower($value['name'])] = $value['value'];
			}
		}

		reset($headers);
		foreach($headers as $key=>$value) {
//		while (list($key, $value) = each($headers)) {
			$headers[$key]['name'] = strtolower($headers[$key]['name']);
			switch ($headers[$key]['name']) {

				case 'content-type':
					$content_type = $this->_parseHeaderValue($headers[$key]['value']);

					if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
						$return->ctype_primary   = $regs[1];
						$return->ctype_secondary = $regs[2];
					}

					if (isset($content_type['other'])) {
						foreach($content_type['other'] as $p_name=>$p_value) {
//						while (list($p_name, $p_value) = each($content_type['other'])) {
							$return->ctype_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-disposition':
					$content_disposition = $this->_parseHeaderValue($headers[$key]['value']);
					$return->disposition   = $content_disposition['value'];
					if (isset($content_disposition['other'])) {
						foreach($content_disposition['other'] as $p_name=>$p_value) {
//						while (list($p_name, $p_value) = each($content_disposition['other'])) {
							$return->d_parameters[$p_name] = $p_value;
						}
					}
					break;

				case 'content-transfer-encoding':
					$content_transfer_encoding = $this->_parseHeaderValue($headers[$key]['value']);
					break;
			}
		}

		if (isset($content_type)) {
			switch (strtolower($content_type['value'])) {
				case 'text/plain':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$return->body = $this->_decodeBody($body, $encoding);
					break;

				case 'text/html':
					$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
					$return->body = $this->_decodeBody($body, $encoding);
					break;

				case 'multipart/parallel':
				case 'multipart/appledouble': // Appledouble mail
				case 'multipart/report': // RFC1892
				case 'multipart/signed': // PGP
				case 'multipart/digest':
				case 'multipart/alternative':
				case 'multipart/related':
				case 'multipart/mixed':

					if(!isset($content_type['other']['boundary'])){
						$this->_error = 'No boundary found for ' . $content_type['value'] . ' part';
						return false;
					}

					$default_ctype = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';

					$parts = $this->_boundarySplit($body, $content_type['other']['boundary']);

					for($i = 0; $i < count($parts); $i++) {
						list($part_header, $part_body) = $this->_splitBodyHeader($parts[$i]);
						$part = $this->_decode($part_header, $part_body, $default_ctype);
//						if($part === false)
//							throw new RuntimeException($this->_error);
						$return->parts[] = $part;
					}
					break;

				case 'message/rfc822':
					$obj = &new mm_mimeDecode($body);
					$return->parts[] = $obj->decode();
					unset($obj);
					break;

				default:
					if(!isset($content_transfer_encoding['value']))
						$content_transfer_encoding['value'] = '7bit';
					$return->body = $this->_decodeBody($body, $content_transfer_encoding['value']);
					break;
			}

		} else {
			$ctype = explode('/', $default_ctype);
			$return->ctype_primary   = $ctype[0];
			$return->ctype_secondary = $ctype[1];
			$return->body = $this->_decodeBody($body);
		}

		return $return;
	}

	/**
	 * Given the output of the above function, this will return an
	 * array of references to the parts, indexed by mime number.
	 *
	 * @param  object $structure   The structure to go through
	 * @param  string $mime_number Internal use only.
	 * @return array			   Mime numbers
	 */
	function &getMimeNumbers(&$structure, $no_refs = false, $mime_number = '', $prepend = '')
	{
		$return = array();
		if (!empty($structure->parts)) {
			if ($mime_number != '') {
				$structure->mime_id = $prepend . $mime_number;
				$return[$prepend . $mime_number] = &$structure;
			}
			for ($i = 0; $i < count($structure->parts); $i++) {

			
				if (!empty($structure->headers['content-type']) AND substr(strtolower($structure->headers['content-type']), 0, 8) == 'message/') {
					$prepend	  = $prepend . $mime_number . '.';
					$_mime_number = '';
				} else {
					$_mime_number = ($mime_number == '' ? $i + 1 : sprintf('%s.%s', $mime_number, $i + 1));
				}

				$arr = &mm_mimeDecode::getMimeNumbers($structure->parts[$i], $no_refs, $_mime_number, $prepend);
				foreach ($arr as $key => $val) {
					$no_refs ? $return[$key] = '' : $return[$key] = &$arr[$key];
				}
			}
		} else {
			if ($mime_number == '') {
				$mime_number = '1';
			}
			$structure->mime_id = $prepend . $mime_number;
			$no_refs ? $return[$prepend . $mime_number] = '' : $return[$prepend . $mime_number] = &$structure;
		}
		
		return $return;
	}

	/**
	 * Given a string containing a header and body
	 * section, this function will split them (at the first
	 * blank line) and return them.
	 *
	 * @param string Input to split apart
	 * @return array Contains header and body section
	 * @access private
	 */
	function _splitBodyHeader($input)
	{
		if(preg_match("/^(.+?)\r?\n\r?\n(.*)/s", ltrim($input), $match)) {
			return array($match[1], $match[2]);
		}
/*
		str_replace("\r", '', $input);
		$input = explode("\n", ltrim($input), 2);
		if(isset($input[1])) {
			return $input;
		}
*/
		$this->_error = 'Could not split header and body';
		return false;
	}

	/**
	 * Parse headers given in $input and return
	 * as assoc array.
	 *
	 * @param string Headers to parse
	 * @return array Contains parsed headers
	 * @access private
	 */
	function _parseHeaders($input)
	{

		if ($input !== '') {
			// Unfold the input
			$input   = preg_replace("/\r?\n/", "\r\n", $input);
			$input   = preg_replace("/\r\n(\t| )+/", ' ', $input);
			$headers = explode("\r\n", trim($input));

			foreach ($headers as $value) {
				$hdr_name = substr($value, 0, $pos = strpos($value, ':'));
				$hdr_value = substr($value, $pos+1);
				if($hdr_value[0] == ' ')
					$hdr_value = substr($hdr_value, 1);

				$return[] = array(
								  'name'  => $hdr_name,
								  'value' => $hdr_value
								 );
			}
		} else {
			$return = array();
		}

		return $return;
	}

	/**
	 * Function to parse a header value,
	 * extract first part, and any secondary
	 * parts (after ;) This function is not as
	 * robust as it could be. Eg. header comments
	 * in the wrong place will probably break it.
	 *
	 * @param string Header value to parse
	 * @return array Contains parsed result
	 * @access private
	 */
	function _parseHeaderValue($input)
	{

		if (($pos = strpos($input, ';')) !== false) {

			$return['value'] = trim(substr($input, 0, $pos));
			$input = trim(substr($input, $pos+1));

			if (strlen($input) > 0) {

				// This splits on a semi-colon, if there's no preceeding backslash
				// Now works with quoted values; had to glue the \; breaks in PHP
				// the regex is already bordering on incomprehensible
				$splitRegex = '/([^;\'"]*[\'"]([^\'"]*([^\'"]*)*)[\'"][^;\'"]*|([^;]+))(;|$)/';
				preg_match_all($splitRegex, $input, $matches);
				$parameters = array();
				for ($i=0; $i<count($matches[0]); $i++) {
					$param = $matches[0][$i];
					while (substr($param, -2) == '\;') {
						$param .= $matches[0][++$i];
					}
					$parameters[] = $param;
				}

				for ($i = 0; $i < count($parameters); $i++) {
					$param_name  = trim(substr($parameters[$i], 0, $pos = strpos($parameters[$i], '=')), "'\";\t\\ ");
					$param_value = trim(str_replace('\;', ';', substr($parameters[$i], $pos + 1)), "'\";\t\\ ");
					if ($param_value[0] == '"') {
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
	 * This function splits the input based
	 * on the given boundary
	 *
	 * @param string Input to parse
	 * @return array Contains array of resulting mime parts
	 * @access private
	 */
	function _boundarySplit($input, $boundary)
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
	 * Given a header, this function will decode it
	 * according to RFC2047. Probably not *exactly*
	 * conformant, but it does pass all the given
	 * examples (in RFC2047).
	 *
	 * @param string Input header value to decode
	 * @return string Decoded header value
	 * @access private
	 */
	function _decodeHeader($input)
	{
		// Remove white space between encoded-words
		$input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

		// For each encoded-word...
		while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {

			$encoded  = $matches[1];
			$charset  = $matches[2];
			$encoding = $matches[3];
			$text	 = $matches[4];

			switch (strtolower($encoding)) {
				case 'b':
					$text = base64_decode($text);
					break;

				case 'q':
					$text = str_replace('_', ' ', $text);
					preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
					foreach($matches[1] as $value)
						$text = str_replace('='.$value, chr(hexdec($value)), $text);
					break;
			}

			$input = str_replace($encoded, $text, $input);
		}

		return $input;
	}

	/**
	 * Given a body string and an encoding type,
	 * this function will decode and return it.
	 *
	 * @param  string Input body to decode
	 * @param  string Encoding type to use.
	 * @return string Decoded body
	 * @access private
	 */
	function _decodeBody($input, $encoding = '7bit')
	{
		switch (strtolower($encoding)) {
			case '7bit':
				return $input;
				break;

			case 'quoted-printable':
				return $this->_quotedPrintableDecode($input);
				break;

			case 'base64':
				return base64_decode($input);
				break;

			default:
				return $input;
		}
	}

	/**
	 * Given a quoted-printable string, this
	 * function will decode and return it.
	 *
	 * @param  string Input body to decode
	 * @return string Decoded body
	 * @access private
	 */
	function _quotedPrintableDecode($input)
	{
		// Remove soft line breaks
		$input = preg_replace("/=\r?\n/", '', $input);

		// Replace encoded characters
		$input = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $input);

		return $input;
	}

	/**
	 * Checks the input for uuencoded files and returns
	 * an array of them. Can be called statically, eg:
	 *
	 * $files =& mm_mimeDecode::uudecode($some_text);
	 *
	 * It will check for the begin 666 ... end syntax
	 * however and won't just blindly decode whatever you
	 * pass it.
	 *
	 * @param  string Input body to look for attahcments in
	 * @return array  Decoded bodies, filenames and permissions
	 * @access public
	 * @author Unknown
	 */
	function &uudecode($input)
	{
		// Find all uuencoded sections
		preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);

		for ($j = 0; $j < count($matches[3]); $j++) {

			$str	  = $matches[3][$j];
			$filename = $matches[2][$j];
			$fileperm = $matches[1][$j];

			$file = '';
			$str = preg_split("/\r?\n/", trim($str));
			$strlen = count($str);

			for ($i = 0; $i < $strlen; $i++) {
				$pos = 1;
				$d = 0;
				$len=(int)(((ord(substr($str[$i],0,1)) -32) - ' ') & 077);

				while (($d + 3 <= $len) AND ($pos + 4 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
					$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
					$c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
					$c3 = (ord(substr($str[$i],$pos+3,1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

					$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

					$file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077));

					$pos += 4;
					$d += 3;
				}

				if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
					$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
					$c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

					$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

					$pos += 3;
					$d += 2;
				}

				if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i]))) {
					$c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
					$c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
					$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

				}
			}
			$files[] = array('filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file);
		}

		return $files;
	}

}
