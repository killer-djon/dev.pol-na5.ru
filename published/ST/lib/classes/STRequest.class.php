<?php

class STRequest 
{
	protected static $states;
	protected $id;
	protected $info;
	
	public function __construct($id, $info = false)
	{
		$this->id = $id ? $id : ($info && $info['id'] ? $info['id'] : false);
		if (!$this->id) {
			throw new Exception('Request not found.');
		}
		if ($info) {
			$this->info = $info;
		} else {
			$request_model = new STRequestModel();
			$this->info = $request_model->get($id);
		}
		if (!$this->info) {
			throw new Exception(_('Request not found.'));
		}
	}
	
	public static function add(&$data, $classes = array())
	{
		$requests_model = new STRequestModel();
		$request_class_model = new STRequestClassModel();
		
		$request_id = $requests_model->add($data);
		if (!$request_id) {
			return false;
		}
		
		$data['classes'] = $classes;
		if ($classes) {
			$request_class_model->add($request_id, $classes);
		}
		// Check rules for this request
		$rule_model = new STRuleModel();
		$rules = $rule_model->getAll();
		foreach ($rules as $rule) {
			$parser = new ExpressionParser($rule['condition']);
			$expression = $parser->parse();
			if ($v = $expression->getValue($data)) {
				// Run actions
				$rule_model->execActions($data, $rule['action']);
			}
		}
		
		// Saving data
		$requests_model->save($request_id, $data);
		if (isset($data['classes']) && $data['classes']) {
			$request_class_model->add($request_id, $data['classes']);
		}

		$metric = metric::getInstance();
		$metric->addAction(Wbs::getDbKey(), User::getId(), 'ST', 'GETREQUEST', strtoupper($data['source_type']));		

		return $request_id;
	}
	
	
	public function getConfirmURL()
	{
		$key = $this->getKey();
		$url = "/personal.php?key=".$key.(!Wbs::isHosted() ? "&DB_KEY=".base64_encode(Wbs::getDbKey()) : "");
		return Url::get($url, true);
	}
	
	public function getKey()
	{
		$hash = substr(md5($this->info['datetime']), 0, 6);
		$hash .= "r".$this->id;
		$hash .= substr(md5($this->info['client_from']), -6);
		return $hash;
	}
	
	public static function getStates()
	{
		if (!self::$states) {
			$state_model = new STStateModel();
			self::$states = $state_model->getAll();
		}
		return self::$states;
	}
	
	protected static function blockquote($str)
	{
		$str = preg_replace("!\r?\n\s?&gt;\s?([^\r\n]*)!ui", "\n$1", $str);
		return "\n<blockquote>".stripcslashes($str)."\n</blockquote>";
	}
	
	public static function formatHTML($text, $attachments = array(), $key = false, $html = false)
	{
		if (!$html && !preg_match("!(^|[^\[])<(br|div|span|font|p|b|i|s|table|body|a)[^>@]*>!uis", $text)) {
			$text = htmlspecialchars($text);
			$text = str_replace("  ", "&nbsp; ", $text);
			while (preg_match("!((\r?\n\s?&gt;[^\r\n]*)+)!uis", $text)) {
				$text = preg_replace("!((\r?\n\s?&gt;[^\r\n]*)+)!uise", "self::blockquote('$1')", $text);
			}
			$text = nl2br($text);
			$text = preg_replace("!([^\n\s]+)\s\[(http:[^\]]+)\]!uis", '<a href="$1">$2</a>', $text);
			$text = preg_replace("!([^\n\s]+)\s\[mailto:\\1\]!uis", '<a href="mailto:$1">$1</a>', $text);
	        $text = preg_replace("!([\s\n\r\t])(https?:[^\s\r\t\n<]+)/\.!is", '$1<a href="$2/">$2/</a>.', $text);                                    
            $text = preg_replace("!([\s\n\r\t])(https?:[^\s\r\t\n<]+)!is", '$1<a href="$2">$2</a>', $text); 
		} else {
			$text = self::stripTags($text, array('title', 'style', 'script', 'frameset', 'object', 'embed', 'iframe'));
			$text = self::stripTags($text, array('meta'), true, false);
			$text = preg_replace('@(<!DOCTYPE[^>]*>[\r\n\s\t]*)?<html[^>]*>(.*?)</html>@usi', '$2', $text);
			$text = preg_replace('!<head[^>]*>(.*?)</head>!usi', '$1', $text);
			$text = preg_replace('!<body([^>]*)>(.*?)</body>!usi', '<div$1>$2</div>', $text);
			while (preg_match("!((\r?\n\s?&gt;[^\r\n]*)+)!uis", $text)) {
				$text = preg_replace("!((\r?\n\s?&gt;[^\r\n]*)+)!uise", "self::blockquote('$1')", $text);
			}
			// For cutted mails
			$text = preg_replace('@(<!DOCTYPE[^>]*>[\r\n\s\t]*)?<html[^>]*>(.*?)<body@usi', '<div', $text);
			$text = preg_replace('@<[^>]+$@usi', '', $text);
			$text = preg_replace('!</blockquote>[\s\r\t\n]*<blockquote[^>]*>!uis', '', $text);
			if ($attachments && is_array($attachments)) {
				foreach ($attachments as $n => $file) {
					if (isset($file['content-id'])) {
						$url = (User::getId() ? '?' : 'personal.php?key='.Env::Get('key')."&") . "m=requests&".(User::getId() ? "act=attachment&id=".$key."&n=".$n : "attachment=".$key.$n);
						$text = str_replace("cid:" . $file['content-id'], $url, $text);
					}
				}
			}
			$text = preg_replace('!<base[^>]*>!is', '', $text);
			
			$text = preg_replace('!<img[^>]*cid:[^>]*>!is', '[image]', $text);
			$text = self::fixTags($text);
		}			
		return $text;
	}
	
	protected static function fixDiv($text)
	{
		$text = stripcslashes($text);
		if (strstr($text, '</div>') === false) {
			$text = str_replace('</td>', '</div></td>', $text);
		}
		return $text;
	}
	
	protected static function fixTags($text) 
	{
		$text = preg_replace('%(<td[^>]*><div[^>]*>.*?</td>)%uise', "self::fixDiv('$1')", $text);
		// Fix unclosed tags
	    $patt_open = "%((?<!</)(?<=<)[\s]*[^/!>\s]+(?=>|[\s]+[^>]*[^/]>)(?!/>))%is";
	    $patt_close = "%((?<=</)([^>]+)(?=>))%is";
	    $c_tags = $m_open = $m_close = array();
	    if (preg_match_all($patt_open, $text, $matches)) {
	        $m_open = $matches[1];
	        if ($m_open) {
	            preg_match_all($patt_close, $text, $matches2);
	            $m_close = $matches2[1];
	            if (count($m_open) > count($m_close)) {
	                $m_open = array_reverse($m_open);
	                foreach ($m_close as $tag) {
	                	if (isset($c_tags[$tag])) {
	                		$c_tags[$tag]++;
	                	} else {	
	                		$c_tags[$tag] = 1;
	                	}
	                }
	                $close_html = "";
	                foreach ($m_open as $k => $tag) {
	                	if ((!isset($c_tags[$tag]) || $c_tags[$tag]-- <= 0) && !in_array(strtolower($tag), array('br', 'img'))) {
	                		$close_html = $close_html.'</'.$tag.'>';
	                	}	
	                }
	                $text .= $close_html;
	            }
	        }
	    }
	    return $text;
	    // Fix unopen tags @todo: improve this code
	    if (preg_match_all($patt_close, $text, $matches, PREG_OFFSET_CAPTURE)) {
	    	foreach ($matches[1] as $match) {
	    		$tag = $match[0];
	    		$open = preg_match_all('%(<[\s]*'.$tag.'[^>]*>)%is', self::subString($text, 0, $match[1]), $m);
	    		$closed = preg_match_all('%(</'.$tag.'>)%is', self::subString($text, 0, $match[1]), $m);
	    		if ($open - $closed <= 0) {
	    			$n = self::strLength($tag) + 3;
	    			$text = self::subString($text, 0, $match[1] - 2).str_repeat(' ', $n).self::subString($text, $match[1] - 2 + $n);
	    		}
	    	}
	    }
	    
	    return $text;
	}
	
	protected static function subString($str, $begin, $end = null)
	{
		$result = "";
		if (!$end) {
			$end = self::strLength($str);
		}
		for ($i = $begin; $i < $end; $i++) {
			$result .= $str[$i];
		}
		return $result;
	}
	
	protected static function strLength($str)
	{
		$i = 0;
		while (isset($str[$i])) {
			$i++;
		}
		return $i;
	}
	
	protected static function stripTags($text, $tags, $with_content = true, $closed = true) 
	{
		if ($with_content) {
			$text = preg_replace('!<('.implode('|', $tags).')[^>]*>.*?</\\1>!usi', '', $text);
			if ($closed) {
				$text = preg_replace('!<('.implode('|', $tags).')[^>]*>.*!usi', '', $text);
			} else {
				$text = preg_replace('!<('.implode('|', $tags).')[^>]*>!usi', '', $text);
			}
		} else {
			$text = preg_replace('!<('.implode('|', $tags).')[^>]*>(.*?)</\\1>!usi', '$2', $text);
		}
		return $text;
	}
		
	public function getInfo()
	{
		$request = $this->info;
		$request['assigned'] = $request['assigned_c_id'] ? Contact::getName($request['assigned_c_id']) : "";
		
		if (!$request['subject']) {
			$request['subject'] = '<'._('no subject').'>';
		}

		$request['text'] = self::formatHTML($request['text'], $request['attachments'], "r".$request['id'], Wbs::isHosted() && $request['id'] > 106000 && $request['source_type'] == 'email');
		
		// Clear badly html if it possible
		$address = MailParser::address($request['client_from']);
		if ($address) {
			$request['from_name'] = $address[0]['name'];
			$request['from_email'] = $address[0]['email'];
		}
				
		return $request;
	} 
	
	public static function getDatetimeBySeconds($fullseconds)
	{
		if($fullseconds < 60) {
			return sprintf(_('%ds'), $fullseconds);
		} elseif($fullseconds < 60 * 60) {
			return sprintf(_('%dm'), round(($fullseconds) / 60));
		} else {
			$minutes = round(($fullseconds / 60) % 60);
			$hours = round(($fullseconds / (60*60)) % 24);
			$days = round(($fullseconds / (60*60*24)) % 31);
			$months = round(($fullseconds / (60*60*24*31)) % 12);
			$years = round(($fullseconds / (60*60*24*31*12)));

			if($fullseconds < 60 * 60 * 24) {
				return  sprintf(_('%dh %dm'), $hours, $minutes );
			} elseif($fullseconds < 60 * 60 * 24 * 7)  {
				return sprintf(_('%dd %dh'), $days, $hours);
			} elseif($fullseconds < 60 * 60 * 24 * 31) {
				return sprintf(_('%dd'), $days);
			} elseif($fullseconds < 60 * 60 * 24 * 365) {
				return sprintf(_('%dm %dd'), $months, $days);
			} else {
				$yearDays = round(($fullseconds / (60*60*24)) % 365);
				return sprintf(_('%dy %dd'), $years, $yearDays);
			}
		}
	}
	
	/*private function RgbToHsv ($R, $G, $B)  // RGB Values:Number 0-255
	{                                       // HSV Results:Number 0-1
	   $HSL = array();
	
	   $var_R = ($R / 255);
	   $var_G = ($G / 255);
	   $var_B = ($B / 255);
	
	   $var_Min = min($var_R, $var_G, $var_B);
	   $var_Max = max($var_R, $var_G, $var_B);
	   $del_Max = $var_Max - $var_Min;
	
	   $V = $var_Max;
	
	   if ($del_Max == 0)
	   {
	      $H = 0;
	      $S = 0;
	   }
	   else
	   {
	      $S = $del_Max / $var_Max;
	
	      $del_R = ( ( ( $max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
	      $del_G = ( ( ( $max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
	      $del_B = ( ( ( $max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
	
	      if      ($var_R == $var_Max) $H = $del_B - $del_G;
	      else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
	      else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;
	
	      if (H<0) $H++;
	      if (H>1) $H--;
	   }
	
	   $HSL['H'] = $H;
	   $HSL['S'] = $S;
	   $HSL['V'] = $V;
	
	   return $HSL;
	}
	
	private function HsvToRgb ($H, $S, $V)  // HSV Values:Number 0-1
	{                                       // RGB Results:Number 0-255
	    $RGB = array();
	
	    if($S == 0)
	    {
	        $R = $G = $B = $V * 255;
	    }
	    else
	    {
	        $var_H = $H * 6;
	        $var_i = floor( $var_H );
	        $var_1 = $V * ( 1 - $S );
	        $var_2 = $V * ( 1 - $S * ( $var_H - $var_i ) );
	        $var_3 = $V * ( 1 - $S * (1 - ( $var_H - $var_i ) ) );
	
	        if       ($var_i == 0) { $var_R = $V     ; $var_G = $var_3  ; $var_B = $var_1 ; }
	        else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $V      ; $var_B = $var_1 ; }
	        else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $V      ; $var_B = $var_3 ; }
	        else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $V     ; }
	        else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $V     ; }
	        else                   { $var_R = $V     ; $var_G = $var_1  ; $var_B = $var_2 ; }
	
	        $R = $var_R * 255;
	        $G = $var_G * 255;
	        $B = $var_B * 255;
	    }
	
	    $RGB['R'] = $R;
	    $RGB['G'] = $G;
	    $RGB['B'] = $B;
	
	    return $RGB;
	}
	
    private function RGBToHex($RGB) 
    {
       $hex = array(
          hexdec($RGB['R']),
          hexdec($RGB['G']),
          hexdec($RGB['B'])
       );
       foreach ($hex as &$val){
	       if ($val < 9) { $val = '0' + $val;}
       }
       return $hex;
    }
    
    private function HSBToHex ($hsb) {
        return RGBToHex(HSBToRGB($hsb));
    }*/

	public static function prepareLogs($log, $request_info)
	{
      	$action_model = new STActionModel();
        $all_actions = $action_model->getAll();
        
        $states_model = new STStateModel();
        $states = $states_model->getAll();      
          
        $order = User::getSetting('LOG_ORDER', 'ST');
		$previousTime = strtotime($request_info['datetime']);
		$contact_types = array();
        if ($order == 'DESC'){
            $log = array_reverse($log);
        }
		foreach ($log as &$l) {
		    $actor_info = Contact::getInfo($l['actor_c_id']);
		    if (!isset($contact_types[$actor_info['CT_ID']])) {
				$contact_types[$actor_info['CT_ID']] = new ContactType($actor_info['CT_ID']);
		    }
			$field = $contact_types[$actor_info['CT_ID']]->getPhotoField(true, true);
		    if ($field && !empty($actor_info[$field])) {
		    	$l['upic'] = $actor_info[$field].'&size=96';	
		    }
			$l['contact'] = Contact::getName($l['actor_c_id']);
            $l['countdown'] = self::getDatetimeBySeconds(strtotime($l['datetime']) - $previousTime);
            $previousTime = strtotime($l['datetime']);
            $l['db_datetime'] = $l['datetime'];
			$l['datetime'] = WbsDateTime::getTime(strtotime($l['datetime']));
			$l['has_border'] = false;
			if (in_array($all_actions[$l['action_id']]['type'], array('REPLY', 'CLIENT-REOPEN', 'EMAIL-CLIENT', 'RESTORE', ''))){
                $l['has_border'] = true;
			}
			
			if (substr($l['attachments'], 0 ,1) != '/') {
				$l['attachments'] = unserialize($l['attachments']);
				if (empty($l['attachments'])) $l['attachments'] = array();
				foreach ($l['attachments'] as &$row) {
					if (isset($row['content-id'])) {
						$row['content_id'] = $row['content-id'];
					} 
				}
				
			} else {
				$l['attachments'] = array();
			}
			if (isset($all_actions[$l['action_id']]) && ($all_actions[$l['action_id']]['type'] == 'COMMENT' || $all_actions[$l['action_id']]['type'] == 'FORWARD-ASSIGN')) {
				$l['text'] = STRequest::formatHTML($l['text'], $l['attachments'], 'l'.$l['id'], true);
			} else {
				$l['text'] = STRequest::formatHTML($l['text'], $l['attachments'], 'l'.$l['id']);
			}
			$recipients = explode('|', $l['to']);
			$l['to'] = $recipients[0];
            if (!empty($recipients[1])) $l['cc'] = $recipients[1];
            if (!empty($recipients[2])) $l['bcc'] = $recipients[2];
            if (@$states[$all_actions[$l['action_id']]['state_id']]['properties']) {
				$color = $states[$all_actions[$l['action_id']]['state_id']]['properties']->css;
				$color = substr($color,strpos($color,'color:')+6);
	            if (strpos($color,';')>0) $color = substr($color,0,strpos($color,';'));
				$l['color'] = $color;
		        $color = str_replace("rgb","rgba",$color);
		        $color = str_replace(")",",0.15)",$color);
				$l['light_color'] = $color;
            }
		}
	    if ($order == 'DESC'){
            $log = array_reverse($log);
        }
		return $log;
	}

    public static function prepareRequests($requests, $hiddenColumns = array())
    {
        $result = array();
        $unreadCssClass = ' ui-grid-row-unread ';
        
        foreach ($requests as $request) {
            $date = strtotime($request['datetime']);
            if (date('Y-m-d', $date) == date('Y-m-d', time())) {
                $date = WbsDateTime::getTime(strtotime($request['datetime']));
                $date = explode(' ', $date);
                $date = $date[1];
            } else {
                $date = WbsDateTime::getTime(strtotime($request['datetime']));
            }
            if (empty($request['client_c_id'])){
                $from = $request['client_from'];
            } else {
                $from = Contact::getName($request['client_c_id']);
            }
            $currentEl = array(
                'id' => $request['id'],
            //  'source_id' => $request['source_id'],
                'datetime' => $date,
                'client_from' => htmlspecialchars($from),
                'subject' => htmlspecialchars($request['subject'], ENT_QUOTES),
                'state_id' => $request['state_id'],
                'assigned_c_id' => $request['assigned_c_id'] ? Contact::getName($request['assigned_c_id']) : "",
                'read' => $request['read'],
                'new_window' => "<a href='javascript:void(0)' rel='?m=requests&act=info&id=".$request['id']."'><span class='ui-icon ui-icon-newwin'></span></a>"
            );
            $result[] = $currentEl;
        }
        foreach($result as &$resArr){
            foreach($resArr as $key => &$resEl){
               $resEl = array('options' => array(
                   'cssClass' => 'ui-grid-column-'.$key.((empty($resArr['read']) && $resArr['assigned_c_id']==(Contact::getName(User::getContactId())))?$unreadCssClass:"")
               ),
               'data' => $resEl
               );
                if (in_array($key, $hiddenColumns))
	                $resEl['options']['cssClass'] .= " ui-hidden";
               if ($key == 'datetime') $resEl['options']['attrs']['nowrap'] = 'nowrap';
               if ($key == 'id') $resEl['options']['attrs']['rel'] = $resEl['data'];
               
               //if ($key == 'datetime') $resEl['options']['before'] = "<span class='ui-icon ui-icon-clock'></span>";
               //if ($key == 'state_id') $resEl['options']['before'] = "<span class='ui-icon ui-icon-mail-open'></span>";
            }
            unset($resArr['read']);
            $resArr = array('data' => $resArr);
        }
        
        return $result;
    } 
	
    public static function getAttachmentsPath($request_id, $log_id = false, $create = true)
	{
		$request_id = str_pad($request_id, 4, '0', STR_PAD_LEFT);
		$path = Wbs::getDbkeyObj()->files()->getAppAttachmentsDir('ST');
		if ($create && !file_exists($path)) mkdir($path, 0775, true);
		$path .= "/requests";
		if ($create && !file_exists($path)) mkdir($path, 0775);
		$path .= "/".substr($request_id, -2);
		if ($create && !file_exists($path)) mkdir($path, 0775);
		$path .= "/".substr($request_id, -4, 2);
		if ($create && !file_exists($path)) mkdir($path, 0775);
		$path .= "/".(int)$request_id;
		if ($create && !file_exists($path)) mkdir($path, 0775);
		if ($log_id) {
			$path .= "/" . $log_id;
			if ($create && !file_exists($path)) mkdir($path, 0775);
		}
		return $path;
	}
	
	public static function removeAttachments($request_id, $log_id = false)
	{
		$path = self::getAttachmentsPath($request_id, $log_id, false);
		if (file_exists($path)) {
		      $current_dir = opendir($path);
		      while (false !== $fname = readdir($current_dir)) {
		        if ($fname == '.' || $fname == '..') continue;
		        unlink($path."/".$fname);
		      }
		      closedir($current_dir);
		      rmdir($path);
		}
	}
	
	public static function removeRequest($request_id)
	{
		$request_model = new STRequestModel();
		$request_log_model = new STRequestLogModel();
		$disk_usage_model = new DiskUsageModel();
		$attachments = $request_log_model->getWithAttachments($request_id);
		foreach ($attachments as $log_id => $attach) {
			$attach = unserialize($attach);
			foreach  ($attach as $file) {
				$disk_usage_model->add('$SYSTEM', 'ST', -$file['size']);
			}
			self::removeAttachments($request_id, $log_id);
		}
		$request_info = $request_model->get($request_id);
		if ($request_info['attachments']) {
			foreach ($request_info['attachments'] as $file) {
				$disk_usage_model->add('$SYSTEM', 'ST', -$file['size']);
			}
		}
		self::removeAttachments($request_id);
		$request_model->delete($request_id);
	}
	
	public static function moveAttachments($path, $request_id, $log_id = false)
	{
		$attachments = array();
		$errors = false;
		$dir = Wbs::getDbkeyObj()->files()->getAppAttachmentsDir('ST')."/attachments".$path;
		$new_path = self::getAttachmentsPath($request_id, $log_id)."/";
	    if (is_dir($dir)) {
	      $current_dir = opendir($dir);
	      while (false !== $fname = readdir($current_dir)) {
	        if ($fname == '.' || $fname == '..') continue;
	        if (!is_dir($dir.$fname)) {
	        	$attachments[] = array(
	        		'name' => $fname,
	        		'type' => MailDecoder::getMimeType($fname),
	        		'disposition' => 'attachment',
	        		'file' => $fname,
	        		'size' => filesize($dir.$fname)
	        	); 
	        	if (!rename($dir.$fname, $new_path.$fname)) {
	        		$errors = true;
	        	}
	        }
	      }
	      closedir($current_dir);
	    }
	    if (!$errors) {
	    	if ($log_id) {
				$log_model = new STRequestLogModel();
				$log_model->saveAttachments($log_id, $attachments);	    			    		
	    	} else {
	    		$request_model = new STRequestModel();
	    		$request_model->save($request_id, array('attachments' => $attachments));
	    	}
	    }	
	    return $attachments;	
	}
    
}
