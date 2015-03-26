<?php

class STRequestsCheckController extends JsonController
{
	protected $updated = false;
	public function exec()
	{
		session_write_close();
		$timestamp = time();
		$last_check_email = User::getSetting('LAST_CHECK_EMAIL', 'ST', '');
		$force = Env::Get('force', Env::TYPE_INT, 0);
		
		if (!$last_check_email) {
		    User::setSetting('LAST_CHECK_EMAIL', $timestamp, 'ST', '');
		} else {
		    if ($timestamp - $last_check_email > 60 || ($force == 1 && $timestamp - $last_check_email > 10)) {
	            $source_model = new STSourceModel();
	            $sources = $source_model->getAll();
		        foreach ($sources as $source_id => $info) {
		            try {
                        User::setSetting('LAST_CHECK_EMAIL', $timestamp, 'ST', '');
		                $this->checkEmail($source_id);
		            } catch (Exception $e) {
		                if ($e->getCode()) {
		                    $this->errors[] = _('Error occurred while checking').' '.$source_model->getParams($source_id,'email').': '.$e->getMessage();
                        } else {
		                	echo $e;
		                }
		            }
		        }
		    }
		}

	}
	
	public function checkCustomer($email)
	{
		if (!Wbs::getDbkeyObj()->appExists('SC')) {
			return 0;
		}
		
		$model = new DbModel();
				
		$sql = "SELECT * FROM SC_customers
				WHERE Email LIKE '%".$model->escape($email)."%'";
		
		$info = $model->query($sql)->fetch();
		
		if (!$info) return 0;
		
		$contact_id = 0;
		try {
			$sql = "SELECT * FROM CONTACT WHERE SC_ID = ".(int)$info['customerID'];
			$contact_id = $model->query($sql)->fetch('C_ID');
		} catch (Exception $e) {
			
		}
		
	    $data = array(
	    	'C_FIRSTNAME' => $info['first_name'],
	    	'C_LASTNAME' => $info['last_name'],
	    	'C_EMAILADDRESS' => $info['Email'],
	    	'C_CREATEDATETIME' => $info['reg_datetime'],
	    	'C_CREATEAPP_ID' => 'SC',
	    	'C_CREATEMETHOD' => 'CHECKOUT',
	    	'C_CREATECID' => 0
	    );
	    
	    if ($info['addressID']) {
    	    $sql = "SELECT settings_value FROM SC_settings WHERE settings_constant_name = 'CONF_DEFAULT_LANG'";
		    $lang_id = $model->query($sql)->fetchField('settings_value');
		    if ($lang_id) {
		        $sql = "SELECT iso2 FROM SC_language WHERE id = i:lang_id";
		        $lang = $model->prepare($sql)->query(array('lang_id' => $lang_id))->fetchField('iso2');
	            if (!$lang) {
	                $lang = 'en';
	            }
		    } else {
		        $lang = 'en';
		    }	    	
		    $sql = "SELECT SCA.address, SCA.city, SCA.zip, SCA.state, SCCN.country_name_".$lang." 
		    		FROM SC_customer_addresses SCA 
		    		LEFT JOIN SC_countries SCCN ON SCA.countryID = SCCN.countryID
		    		WHERE SCA.addressID = '".$model->escape($info['addressID'])."'";
		    $address = $model->query($sql)->fetch();

		    $fields = array(
		        'address' => 'C_HOMESTREET',
		        'city' => 'C_HOMECITY',
		    	'zip' => 'C_HOMEPOSTALCODE',
		    	'state' => 'C_HOMESTATE',
		    	'country_name_'.$lang => 'C_HOMECOUNTRY',
		    );	

			foreach ($fields as $from => $to) {
		        if (ContactType::getFieldId($to, true)) {
		            $data[$to] = $address[$from];
		        }
		    }		    
	    }
	    if ($contact_id) {
	    	Contact::save($contact_id, $data);
	    	return $contact_id;
	    }
	    $contact_id = Contact::add(1, $data);
	    if ($contact_id) {
		    try {
		        $model->exec("SELECT SC_ID FROM CONTACT WHERE 0");
		    } catch (Exception $e) {
		        $model->exec("ALTER TABLE CONTACT ADD SC_ID INT (11) NULL DEFAULT NULL");
		        $model->exec("ALTER TABLE CONTACT ADD UNIQUE `SC_ID` ( `SC_ID` )");
		    }
	    	
	    	$sql = "UPDATE CONTACT SET SC_ID = '".$model->escape($info['customerID'])."' 
	    			WHERE C_ID = '".(int)$contact_id."'";
	    	$model->exec($sql);
	    }
	    return $contact_id;
  	}
		
	public function checkEmail($source_id) {
		$source_model = new STSourceModel();
		$params = $source_model->getParams($source_id, array('protocol', 'server', 'port', 'login', 'password', 'ssl'));	
		$prefix = isset($params['ssl']) && $params['ssl'] ? 'ssl://' : ''; 
		$mail_reader = new MailReader($params['protocol'], $prefix.$params['server'], $params['port'], $params['login'], $params['password']);	

		$n = $mail_reader->count();

		for ($id = 1; $id <= $n; $id++) {
			$message = $mail_reader->get($id);
		
			if ($message && ($request_id = $this->saveRequest($source_id, $message))) {
			if (isset($_GET['debug'])) {
			    echo $id, $request_id; 
			}
			    if (!User::getSetting('HAS_REQUESTS', 'ST', '')){
			        User::setSetting('HAS_REQUESTS', 1, 'ST', '');
			    }
				$this->response[] = $request_id;
				$r = $mail_reader->delete($id);
				// Log
				if (Wbs::isHosted()) {
					$log = WBS_DIR."temp/log/support.log";
					$fh = @fopen($log, "a+");
					if ($fh) {
						fwrite($fh, "Request ".$request_id." created. Mail ".$id." deleted (".$r.").\n");
						fclose($fh);
					}
				}
			}
		}
		$mail_reader->close();
	}
	
	public function saveRequest($source_id, $message) 
	{
		
		$subject = $message['headers']['subject'];
		$from = $message['headers']['from'];
		// get contact_id of the author		
		$address = MailParser::address($from);
		if ($address) {
			$email = $address[0]['email'];
			$contact_id = Contact::getByEmail($email);
		} else {
			$contact_id = 0;
			$email = '';
		}
		
		if (!$contact_id && $address && Wbs::getDbkeyObj()->appExists('SC')) {
			$contact_id = $this->checkCustomer($email);
		}
		
		$source_model = new STSourceModel();
		$params = $source_model->getParams($source_id);
		
		// Ignore
		if (($address && $email == $params['email']) || preg_match('/mailer-daemon@/ui', $from)) {
			return -1;
		}
	
		$message_id = isset($message['headers']['message-id']) ? $message['headers']['message-id'] : null ;
		if ($message_id) {
			if (strpos($message_id, '@') === false) {
				$domain = preg_replace("!^.*?@!uis", '', $email);
				if (substr($message_id, -1) === '>') {
					$message_id = substr($message_id, 0, -1).'@'.$domain.'>';
				} else {
					$message_id .= '@'.$domain; 
				}
			}
			$request['message_id'] = $message_id;
		} 
		if (is_array($message['headers']['date'])) {
			$t = end($message['headers']['date']); 
		} else {
			$t = $message['headers']['date'];
		}
		$request['datetime'] = date("Y-m-d H:i:s", strtotime($t));		
		$request['text'] = $message['text'];
		
		
		
		if (isset($message['attachments']) && $message['attachments']) {
			$request['attachments'] = $message['attachments'];
		}
					
		$request_model = new STRequestModel();
		// if it is old request		
		if (preg_match("!\[ID:([0-9]+)\]$!ui", $subject, $match) && ($request_id = $match[1]) && $request_info = $request_model->get($request_id)) {
			// Add new contact
			if (!$contact_id) {
				if (isset($request_info['state_id']) && $request_info['state_id'] < 0) {
					return -2;
				}
				$contact_id = Contact::addByNameEmail($address[0]['name'], $address[0]['email'], 'EMAIL');
			}						
			$request['actor_c_id'] = $contact_id;
			// Get next state of the request
			if ($request_info['client_c_id'] == $contact_id) {
				$type = 'EMAIL-CLIENT';
			} else {
				$type = 'EMAIL-NOT-CLIENT';
			}
			$state_action_model = new STStateActionModel();
			$action = $state_action_model->getByStateType($request_info['state_id'], $type);
			
			//@todo: Send Email with the system classes
			if (!$action) {
				//mail($from, "Создайте новый запрос", "Создайте новый запрос!", "From: ".$params['email']);
				return -3;	
			}
			
			if (isset($message['headers']['to'])) {
				$request['to'] = $message['headers']['to'];
				if (isset($message['headers']['cc'])) {
					$request['to'] .= '|'.$message['headers']['cc'];	
				}
			}
			
			
			$request['action_id'] = $action['id'];
			if ($action['state_id']) {
				$request['state_id'] = $action['state_id'];
			} else {
				$request['state_id'] = $request_info['state_id'];
			}
						
			// Save message to the log
			$request_log_model = new STRequestLogModel();
			try {
				$log_id = $request_log_model->add($request_id, $request, $action['assigned_c_id'] > 0 ? $action['assigned_c_id'] : 0);
			} catch (MySQLException $e) {
				$error = $e->getMessage();
				if (stristr($error, 'duplicate')) {
					return -4;
				} else {
					return false;
				}
			}
			if ($log_id) {
				$request_model->set($request_id, $request['state_id'], $action['assigned_c_id']);
				if (isset($message['attachments']) && $message['attachments']) {
					$new_path = STRequest::getAttachmentsPath($request_id, $log_id);
				}
			} else {
				return false;
			}
		} else {						
			$request['subject'] = $subject;
			$request['source_type'] = 'email';
			$request['client_from'] = $from ? $from : '';
			$request['client_c_id'] = $contact_id;
			$request['source'] = $params['email'];
			$request['source_id'] = $source_id;
			

			if (!preg_match('//u', $request['text'])) {
			    return false;
			}

			if (isset($_GET['debug'])) {
			    print_r($request); 
			}
			if (!$contact_id && isset($params['confirm']) && $params['confirm']) {
				$request['state_id'] = 0;
				try {
					$request_id =  STRequest::add($request);
					if (isset($_GET['debug'])) {
					    var_dump($request_id); exit;
					}
					if (!$request_id) {
						return false;
					}
				} catch (MySQLException $e) {
    					if (isset($_GET['debug'])) {
    					    echo $e; 
					}
					$error = $e->getMessage();
					if (stristr($error, 'duplicate')) {
						return -4;
					} else {
						return false;
					}
				}
				$request['id'] = $request_id; 				
				// Set status verification
				$this->sendEmail($source_id, $request, $params, 'confirm');
			} else {
				if (!$contact_id) {
					$contact_id = Contact::addByNameEmail($address[0]['name'], $address[0]['email'], 'EMAIL');
					$lang = $params['language'];
					$model = new DbModel();
					// Set language
					$sql = "UPDATE CONTACT SET C_LANGUAGE = s:lang WHERE C_ID = i:id";
					$model->prepare($sql)->exec(array('lang' => $lang, 'id' => $contact_id));
					$request['client_c_id'] = $contact_id;
				}
				try {
					$request_id =  STRequest::add($request);
					if (!$request_id) {
						return false;
					}
				} catch (MySQLException $e) {
					$error = $e->getMessage();
					if (stristr($error, 'duplicate')) {
						return -4;
					} else {
						return false;
					}
				}
				$request['id'] = $request_id; 				
				// Execute auto accept of the request
				$action_model = new STActionModel();
				$action = $action_model->getByType('ACCEPT', 'system');
				if ($action['state_id']) {				
					$request_model->set($request_id, $action['state_id']);
				}				

				if (isset($params['receipt']) && $params['receipt']) {
					$this->sendEmail($source_id, $request, $params, 'receipt');
				}
			}
			if (isset($message['attachments']) && $message['attachments']) {
				$new_path = STRequest::getAttachmentsPath($request_id);
			}
		}
		// Save attachments			
		if (isset($message['attachments']) && $message['attachments']) {
			$disk_usage_model = new DiskUsageModel();
			foreach($message['attachments'] as $file) {
				$old_path = WBS_DIR."temp/mail_part";
				if (rename($old_path.'/'.$file['file'], $new_path.'/'.$file['file'])) {
					$disk_usage_model->add('$SYSTEM', 'ST', $file['size']);	
				}
			}
		}
		return $request_id;			
	}
		
	protected function sendEmail($source_id, $request, $params, $prefix)
	{
		
		$source_model = new STSourceModel();
		$source_info = $source_model->get($source_id);
		$from = isset($params[$prefix.'_email']) && $params[$prefix.'_email'] ? $params[$prefix.'_email'] : $params['email'];
		$from = $source_info['name'] .' <'.$from.">";
		
		$template = new STTemplate();
		$template->setRequest($request);
		if (isset($request['client_c_id']) && $request['client_c_id']) {
			$template->setContact(Contact::getInfo($request['client_c_id']));
		}
		$body = $template->get($params[$prefix.'_body']);
		
		$message = Mailer::composeMessage();
		$message->addTo($request['client_from']);
		$message->addSubject($template->get($params[$prefix.'_subject']));
		$message->addContent($body);
		$message->addFrom($from);
		$message->addAppID('-U');
		
		Mailer::send($message);
	}
}