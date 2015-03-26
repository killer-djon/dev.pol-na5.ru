<?php

class STSourcesSaveController extends JsonController
{
	protected $source_id;
	public function exec()
	{
		$this->source_id = Env::Post('id', Env::TYPE_INT, 0);
		$info = Env::Post('info');
		$params = Env::Post('params');
		$source_model = new STSourceModel();
		$sources = $source_model->getAll();

	        
        // Create hosted email
        $hostname = Env::Server("HTTP_HOST");
        $email = $params['email'];
        if (isset($params['inner']) && $params['inner']) {
        	if (isset($params['email'])) {
        		$params['email'] = $params['email']."@".$hostname;
        	}
        	$params['receipt_email'] = $params['receipt_email']."@".$hostname;
        	$params['confirm_email'] = $params['confirm_email']."@".$hostname;
        }
        if (!$this->source_id && !isset($params['inner'])) {
        	$params['inner'] = 0;
        }
        if (!$this->source_id && isset($params['inner']) && $params['inner']) {           
			$symb = 'qwertyuiopasdfghjkzxcvbnmQWERTYUPASDFGHJKLZXCVBNM23456789'; 
			$count = strlen($symb)-1; 
			$password = '';
			for ($i = 0; $i < 7; $i++ ) { 
				$password .= $symb[rand(0, $count)];
			}

			$params['password'] = $password;  
			$params['login'] = $params['email'];
			$params['server'] = '';
			$params['protocol'] = 'pop3';
			$params['port'] = 110;
            
			$mail = array(
				'MMA_EMAIL' => $email,
				'MMA_DOMAIN' => Env::Server("HTTP_HOST"),
				'MMA_OWNER' => Wbs::getDbKey(),
				'MMA_QUOTA' => 100,
				'MMA_PASSWORD' => $password
			);
			
			$xml = simplexml_load_file(WBS_ROOT_PATH . "/kernel/wbs.xml");
			$url = (string)$xml->MAILDAEMONDB->attributes()->SERVER_NAME;
			$url .= "/";
			$url .= (string)$xml->MAILDAEMONDB->attributes()->PAGE_URL;
			$url .= '?action=add';
			foreach($mail as $key => $val ) {
				$url .= "&" . rawurlencode( $key ) . "=" . rawurlencode( $val );
	        }
	        $result = file_get_contents($url); 
	        if ($result != 'OK') {
	        	$this->errors = "Could not create email box.";
	        	return false;
	        }
        }
		
		if (count($sources) < 2 && isset($params['email'])){
		    $params['isdefault'] = 1;
		     User::setSetting('DEFAULT_EMAIL_rus', $params['email'], 'ST', '');
             User::setSetting('DEFAULT_EMAIL_eng', $params['email'], 'ST', '');
		} else {
			if (isset($params['isdefault']) && isset($params['email'])) {
	            User::setSetting('DEFAULT_EMAIL_'.$params['language'], $params['email'], 'ST', '');
			} elseif (!isset($params['isdefault'])) {
			    $params['isdefault'] = 0;
			    if ($params['email'] == User::getSetting('DEFAULT_EMAIL_'.$params['language'], 'ST', '')){
	                User::setSetting('DEFAULT_EMAIL_'.$params['language'], '', 'ST', '');
			    }
			}
		}
        
        if (empty($params['confirm'])) $params['confirm'] = 0;

        if (empty($params['receipt'])) $params['receipt'] = 0;
        if (!empty($params['signature']))
		    $params['signature'] = str_replace("\n","<br />", $params['signature']);
        if (!empty($params['receipt_body']))
            $params['receipt_body'] = str_replace("\n","<br />", $params['receipt_body']);
        if (!empty($params['confirm_body']))
            $params['confirm_body'] = str_replace("\n","<br />", $params['confirm_body']);

        if (!isset($params['ssl'])) $params['ssl'] = 0;            
		if ($this->source_id) {
			$source_model->save($this->source_id, $info);
			$source_model->setParams($this->source_id, $params);
	 	} else {
	 		$info['type'] = 'email';
			$info['id'] = $source_model->add($info, $params);
			$this->response = $info;

			$metric = metric::getInstance();
			$metric->addAction(Wbs::getDbKey(), User::getId(), 'ST', 'ADDEMAIL', 'ACCOUNT');
	 	}
		
	}
}