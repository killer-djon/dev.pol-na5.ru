<?php

	//
	// Mail Master sockets functions
	//
	
	$EOL = "\r\n";

	function socketMailOpen($params)
	{
		global $mmStrings;
		global $log, $EOL;

		$prefix = $params['secure'] ? 'ssl://' : '';

		$fp = fsockopen($prefix.$params['server'], $params['port'], &$errno, &$errstr, 10);
		if($fp)
		{
			if($params['protocol'] == 'pop3')
			{
				//stream_set_timeout($fp, 5);
				$log = fgets($fp);
				$cmd = "USER {$params['user']}$EOL";
				fputs($fp, $cmd);
				$log .= $cmd . fgets($fp);
				$cmd = "PASS {$params['pass']}$EOL";
				fputs($fp, $cmd);
				$data = fgets($fp);
				$log .= $cmd . $data;
				if(stripos($data, '+OK') === 0)
					return $fp;
			}
			else
			{
				sleep(1);
				$log = fread($fp, 1024);
				$cmd = "O1 LOGIN {$params['user']} {$params['pass']}$EOL";
				fputs($fp, $cmd);
				$data = fread($fp, 1024);
				$log .= $cmd . $data;
				if(stripos($data, 'O1 OK') !== false)
					return $fp;
			}
			fclose($fp);

			$error = PEAR::raiseError( $mmStrings['msg_login_error'] );
			$error->userinfo = $log;
			return PEAR::raiseError( $error );
		}
		$error = PEAR::raiseError( sprintf($mmStrings['msg_connect_error'], $params['server']) );
		$error->userinfo = "$errno: $errstr";
		return PEAR::raiseError( $error );
	}

	function socketMailClose($fp, $params)
	{
		global $log, $EOL;

		if($params['protocol'] == 'pop3')
			$cmd = "QUIT$EOL";
		else
			$cmd = "C1 LOGOUT$EOL";

		fputs($fp, $cmd);
		$log .= $cmd . fread($fp, 1024);

		fclose($fp);
	}

	function socketMailHeaders($connect, $params, $server_uidl, $cache_uidl, $account)
	{
		global $ignoreDeletedMessages;

		$uidl_for_save = array_diff($server_uidl, $cache_uidl);
		if(!in_array($params['server'], $ignoreDeletedMessages))
			$uidl_for_delete = array_diff($cache_uidl, $server_uidl);
		else
			$uidl_for_delete = array();

		$ret = true;
		if($uidl_for_save)
		{
			if($params['protocol'] == 'pop3')
				$ret = socketPOP3headers($connect, $uidl_for_save, $account);
			else
				$ret = socketIMAPheaders($connect, $uidl_for_save, $account);
		}
		if(!$ret)
			return false;
		//
		// Delete messages absent on remote server from cache
		//
		$attDir = mm_getNoteAttachmentsDir( '', 0 );
		$imgDir = mm_getNoteAttachmentsDir( '', 1 );

		foreach($uidl_for_delete as $uid)
		{
			// delete all attachments from file system
			unlinkRecursive($attDir.$account.'~'.$uid);
			unlinkRecursive($imgDir.$account.'~'.$uid);

			// delete from DB
			$query = "DELETE FROM MMCACHE WHERE MMC_ACCOUNT='"
				.mysql_real_escape_string($account)."' AND MMC_UID='"
				.mysql_real_escape_string($uid)."' LIMIT 1";
			if(PEAR::isError($ret = execPreparedQuery($query, array())))
				return false;
		}
		return true;
	}

	function socketIMAPheaders($fp, $uidl_for_save, $account)
	{
		global $log, $EOL, $connectLimit;
		$start = microtime(true);
		$ret = true;

		$cmd = "H1 SELECT INBOX$EOL";
		fputs($fp, $cmd);
		$data = fread($fp, 1024);
		$log .= $cmd . $data;


		if(preg_match('/(\d+) EXISTS/i', $data, $match) && $match[1])
		{
			foreach($uidl_for_save as $num=>$uid)
			{
				$cmd = "H2 FETCH $num:$num (UID FLAGS RFC822.SIZE BODY[HEADER.FIELDS (date from subject content-type x-priority)])$EOL";
				fputs($fp, $cmd);

				$headers = '';
				$i = 0;
				while(($data = fgets($fp)) && (stripos($data, 'H2 OK') !== 0))
				{
					if($connectLimit && (microtime(true) - $start > $connectLimit))
						return false;

					$headers .= $data;
					if(trim($data) == ')')
					  $i++;
				}
				$log .= $data;

				$out = array();
				$out['MMC_UID'] = $uid;
				$out['MMC_ACCOUNT'] = $account;
				$out['MMC_FLAG'] = '';
				if(preg_match('/^\* \d+ FETCH \(UID (\d+) FLAGS \((.*)\) RFC822.SIZE (\d+)/i', $headers, $match) && $match[1])
					$out['MMC_SIZE'] = $match[3];

				parseHeaders($headers, &$out);
				// Save current header to cache (local DB)
				if(!saveHeaderToCache($out))
					$ret = false;
			}
		}
		return $ret;
	}

	function socketPOP3headers($fp, $uidl_for_save, $account)
	{
		global $log, $EOL, $connectLimit;
		$list = array();
		$start = microtime(true);
		$ret = true;

		$cmd = "STAT$EOL";
		fputs($fp, $cmd);
		$stat = fgets($fp);
		$log .= $cmd.$stat;

		if(preg_match('/^\+OK (\d+) (\d+)/i', $stat, $match))
		{
			$num = $match[1];

			// get messages size
			$cmd = "LIST$EOL";
			fputs($fp, $cmd);
			$data = fgets($fp);
			$log .= $cmd.$data;
			if(stripos($data, '+OK') === 0)
				while(($data = fgets($fp)) && (trim($data) != '.'))
					$list[] = $data;
					
			foreach($uidl_for_save as $num=>$uid)
			{
				$out = array();
				$headers = '';
				$cmd = "TOP $num 0$EOL";
				fputs($fp, $cmd);
				$data = fgets($fp);
				$log .= $cmd.$data;

				if(stripos($data, '+OK') !== 0)
					continue;
				while(($data = fgets($fp)) && (trim($data) != '.'))
				{
					if($connectLimit && (microtime(true) - $start > $connectLimit))
						return false;
					$headers .= $data;
				}

				if(preg_match('/^\d+ (\d+)/', $list[$num-1], $match))
					$out['MMC_SIZE'] = trim($match[1]);

				$out['MMC_UID'] = $uid;
				$out['MMC_ACCOUNT'] = $account;
				$out['MMC_FLAG'] = '';
				$out['MMC_HEADER'] = trim($headers);

				parseHeaders($headers, &$out);
				// Save current header to cache (local DB)
				if(!saveHeaderToCache($out))
					$ret = false;
			}
		}
		return $ret;
	}

	function socketMailUIDL($connect, $params)
	{
		if($params['protocol'] == 'pop3')
			return socketPOP3_UIDL($connect);
		else
			return socketIMAP_UIDL($connect);
	}

	function socketIMAP_UIDL($fp)
	{
		global $log, $EOL, $connectLimit;
		$uidl = array();
		$start = microtime(true);

		$cmd = "U1 EXAMINE INBOX$EOL";
		fputs($fp, $cmd);
		$data = fread($fp, 1024);
		$log .= $cmd . $data;

		if(preg_match('/(\d+)\s+EXISTS/i', $data, $match) && $match[1])
		{
			$msg_count = $match[1];

			$cmd = "U2 FETCH 1:$msg_count UID$EOL";
			fputs($fp, $cmd);

			$i = 0;
			while(($data = fgets($fp)) && (stripos($data, 'U2 OK') !== 0))
			{
				if(preg_match('/^\*\s+(\d+)\s+FETCH\s+\(UID\s+(.+)\)/i', $data, $match))
				{
					if($connectLimit && (microtime(true) - $start > $connectLimit))
						return 'error';
					$uidl[++$i] = $match[2];
				}
			}
			if($msg_count == count($uidl))
			{
				return $uidl;
			}
			return null;
		}
		return 'examine error';
	}

	function socketPOP3_UIDL($fp)
	{
		global $log, $EOL, $connectLimit;
		$uidl = array();
		$start = microtime(true);

		$cmd = "UIDL$EOL";
		fputs($fp, $cmd);
		$data = fgets($fp);
		$log .= $cmd.$data;

		if(stripos($data, '+OK') === 0)
		{
			$i = 0;
			while(($data = fgets($fp)) && (trim($data) != '.'))
			{
				if($connectLimit && (microtime(true) - $start > $connectLimit))
					return 'error';

				if(preg_match('/^(\d+)\s+(.+)/', $data, $match))
				{
					$uidl[$i + 1] = trim($match[2]);
					$i++;
				}
			}
			$cmd = "STAT$EOL";
			fputs($fp, $cmd);
			$data = fgets($fp);
			$log .= $cmd.$data;
			if(preg_match('/\+OK\s+(\d+)/i', $data, $match) && $match[1] == count($uidl))
			{
				return $uidl;
			}
			return null;
		}
		return 'uidl error';
	}

	function socketMailGetMsg($fp, $params)
	{
		global $log, $EOL;
//		$message = array();

		if($params['protocol'] == 'pop3')
		{
			$uidl = socketPOP3_UIDL($fp); // Get id by uid
			$ids = array_flip($uidl);
			$id = $ids[$params['uid']];

			if($id)
			{
				$cmd = "RETR $id$EOL";
				fputs($fp, $cmd);
				$data = fgets($fp);
				$log .= $cmd.$data;

				if(stripos($data, '+OK') === 0)
				{
//					$message = array();
//					mm_parse($fp, &$message, 0);
					$msg = '';

					$memory_limit = 10000000;
					if(preg_match('/^(\d+)/', ini_get('memory_limit'), $match))
						$memory_limit = ($match[1] - (memory_get_usage() / 1000000)) * 100000;
					$size = 0;
					while((rtrim($data = fgets($fp)) != '.') && ($size < $memory_limit))
					{
						$msg .= $data;
						$size += strlen($data);
					}
					return $msg;
				}
			}
		}
		else
		{
			$cmd = "B1 SELECT INBOX$EOL";
			fputs($fp, $cmd);
//			$data = fread($fp, 1024);
//			$log .= $cmd . $data;
			$log .= $cmd;
			while(($data = fgets($fp)) && (stripos($data, 'B1 OK') !== 0)) {
				$log .= $data;
			}

			$uidl = socketIMAP_UIDL($fp); // Get id by uid
			$ids = array_flip($uidl);
			$id = $ids[$params['uid']];

			if($id)
			{
				$cmd = "B2 FETCH $id:$id BODY[]$EOL";
				fputs($fp, $cmd);
				$data = fgets($fp);
				$log .= $cmd.$data;

				if(stripos($data, "* $id FETCH") === 0)
				{
					$message = array();
					while(($data = fgets($fp)) && (stripos($data, 'B2 OK') !== 0))
						$message[] = $data;

					$cmd = "B3 STORE $id:$id +FLAGS (\Seen)$EOL";
					fputs($fp, $cmd);
					$data = fread($fp, 1024);
					$log .= $cmd . $data;

					unset($message[count($message)-1]);
					return join('', $message);
				}
			}
		}
		return '';
	}

	function socketMailDelete($connect, $params, $ids)
	{
		if($params['protocol'] == 'pop3')
			return socketPOP3_delete($connect, $ids);
		else
			return socketIMAP_delete($connect, $ids);
	}

	function socketIMAP_delete($fp, $ids)
	{
		global $log, $EOL;
		$uidl = array();

		$cmd = "D1 SELECT INBOX$EOL";
		fputs($fp, $cmd);
		$data = fread($fp, 1024);
		$log .= $cmd . $data;

		if(preg_match('/(\d+) EXISTS/i', $data, $match) && $match[1])
		{
			foreach($ids as $id)
			{
				$cmd = "D2 STORE $id:$id +FLAGS (\Deleted)$EOL";
				fputs($fp, $cmd);
				$data = fread($fp, 1024);
				$log .= $cmd . $data; // preg_match('/D2 OK/i', $data)
			}
			$cmd = "D3 EXPUNGE$EOL";
			fputs($fp, $cmd);
			$data = fread($fp, 1024);
			$log .= $cmd . $data;
			if(stripos($data, 'D3 OK') === 0)
				return true;
		}
		return false;
	}

	function socketPOP3_delete($fp, $ids)
	{
		global $log, $EOL;

		foreach($ids as $id)
		{
			$cmd = "DELE $id$EOL";
			fputs($fp, $cmd);
			$data = fgets($fp);
			$log .= $cmd . $data; // preg_match('/^\+OK/i', $data)
		}
		return true;
	}

	//
	// Save current header to cache (local DB)
	//
	function saveHeaderToCache($out)
	{
		$query = "SELECT MMC_UID FROM MMCACHE WHERE MMC_ACCOUNT='!MMC_ACCOUNT!' AND MMC_DATETIME='!MMC_DATETIME!' AND MMC_FROM='!MMC_FROM!' AND MMC_SUBJECT='!MMC_SUBJECT!'";
		$MMC_UID = db_query_result($query, DB_FIRST, $out);

		if($MMC_UID && !PEAR::isError($MMC_UID)) {
			$query = "UPDATE MMCACHE SET MMC_UID='".mysql_real_escape_string($out['MMC_UID'])."' WHERE MMC_UID='"
				.mysql_real_escape_string($MMC_UID)."' AND  MMC_ACCOUNT='".mysql_real_escape_string($out['MMC_ACCOUNT'])."'";

			if(PEAR::isError($res = execPreparedQuery($query, array())))
				return false;

			// @TODO: rebuild MMC_ATTACHMENT field or make relative attachment path !!!

			$attDir = mm_getNoteAttachmentsDir( '', 0 );
			$imgDir = mm_getNoteAttachmentsDir( '', 1 );

			// rename attachments directories
			if($MMC_UID != $out['MMC_UID']) {
				if(is_dir($attDir.$out['MMC_ACCOUNT'].'~'.$MMC_UID)) {
					@rename($attDir.$out['MMC_ACCOUNT'].'~'.$MMC_UID, $attDir.$out['MMC_ACCOUNT'].'~'.$out['MMC_UID']);
				}
				if(is_dir($imgDir.$out['MMC_ACCOUNT'].'~'.$MMC_UID)) {
					@rename($imgDir.$out['MMC_ACCOUNT'].'~'.$MMC_UID, $imgDir.$out['MMC_ACCOUNT'].'~'.$out['MMC_UID']);
				}
			}

		} else {

			$query = "REPLACE INTO MMCACHE SET MMC_UID='"
				.mysql_real_escape_string($out['MMC_UID'])."', MMC_ACCOUNT='"
				.mysql_real_escape_string($out['MMC_ACCOUNT'])."', MMC_DATETIME='"
				.$out['MMC_DATETIME']."', MMC_FROM='"
				.mysql_real_escape_string($out['MMC_FROM'])."', MMC_SUBJECT='"
				.mysql_real_escape_string($out['MMC_SUBJECT'])."', MMC_SIZE='"
				.$out['MMC_SIZE']."', MMC_ATTACHMENT='"
				.$out['MMC_ATTACHMENT']."', MMC_FLAG='"
				.$out['MMC_FLAG']."', MMC_PRIORITY='"
				.$out['MMC_PRIORITY']."'";

			if(PEAR::isError($res = execPreparedQuery($query, array())))
				return false;
		}
		return true;
	}

?>