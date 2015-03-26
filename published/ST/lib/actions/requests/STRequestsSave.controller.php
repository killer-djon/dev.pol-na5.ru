<?php
class STRequestsSaveController extends JsonController
{
	protected $request_id;
	protected $request_info = array();
	protected $action_id;
	
	public function exec()
	{
		$this->request_id = Env::Post('id');
		$order = Env::Post('order', Env::TYPE_STRING, '');
	    if (!empty($order)) {
	        if ($order != 'ASC') $order = 'DESC';
	        User::setSetting('LOG_ORDER', $order, 'ST');
            $this->response['success'] = true;
            return false;
        }
		$this->action_id = Env::Post('action_id');
		
		$request_model = new STRequestModel();
		$this->request_info = $request_model->get($this->request_id);
		
		$state_action_model = new STStateActionModel();
		$actions = $state_action_model->getByState($this->request_info['state_id'], 'user');
		
		if (!isset($actions[$this->action_id])) {
			$this->errors = _('Action can not be accomplished because another user has changed this request status.');
			return false;
		}
		
		$action_model = new STActionModel();
		$action = $action_model->get($this->action_id);
		
        $states_model = new STStateModel();
        $states = $states_model->getAll();
		
		if (!$action['state_id']) {
			// State of the request does not change.
			$action['state_id'] = $this->request_info['state_id'];
		}
		
        if ($this->request_info['state_id'] == -1 && !in_array($action['type'], array('RESTORE','REMOVE'))) {
            $this->response['success'] = false;
            return true;
        }
		
		if (($action['assigned_c_id'] == -1) && !$this->request_info['assigned_c_id']) {
			$assigned = User::getContactId();	
		} elseif ($action['assigned_c_id'] > 0) {
			$assigned = $action['assigned_c_id'];
		} elseif ($action['assigned_c_id'] == -3) {
			$assigned = -3; 
		} else {
			$assigned = $this->request_info['assigned_c_id'];
		}

		$this->response['type'] = $action['type'];
		if ($action['type'] == 'REMOVE') {
			STRequest::removeRequest($this->request_id);
			return;			
		}
        
		if ($action['log_name']) {
			$info = array();

			$info['actor_c_id'] = User::getContactId();
			$info['state_id'] = $action['state_id'];
			$info['action_id'] = $this->action_id;
			$info['assigned_c_id'] = $assigned > 0 ? $assigned : 0;
			$request_log_model = new STRequestLogModel();
            $source_model = new STSourceModel();
            
            $attachments = array();
		    if ($_FILES){
                $files = $_FILES['files'];
                $files_length = sizeof($files['name']);
                $errors = false;
                for ($key=0; $key<$files_length; $key++){
                    if ($files["error"][$key] == UPLOAD_ERR_OK) {
                        $attachments[] = array(
                            "name" => $files['name'][$key],
                            "type" => $files['type'][$key],
                            "size" => $files['size'][$key],
                            "path" => $files["tmp_name"][$key]
                        );
                        //$tmp_name = $files["tmp_name"][$key];
                    }
                }
            }
            
			// Do action
			switch ($action['type']) {
				case 'RESTORE':
					$action['state_id'] = $request_log_model->getPreviousState($this->request_id);
					// for new requests
					if (!$action['state_id']){
						if ($this->request_info['client_c_id']>0) {
						    $action = $action_model->getByType('ACCEPT', 'system');
							//$info['state_id'] = $action['state_id'] = $source_model->getParams($this->request_info['source'], 'state');
						} else {
	                        $action['state_id'] = 0;
	                    }
					}
                    $info['state_id'] = $action['state_id'];
					$success = $request_log_model->add($this->request_id, $info);
					break;
				case 'REPLY':
				case 'FORWARD':
				case 'FORWARD-ASSIGN':
					$info['text'] = Env::Post('text');
					if ($action['type'] == 'FORWARD-ASSIGN') {
						$prefix = 'Fwd: ';
						$info['text'] = str_replace(array("  ", "\r\n", "\n"), array("&nbsp;&nbsp;", "<br />", "<br />"), htmlspecialchars($info['text']));
					} elseif ($action['type'] == 'FORWARD') {
						$prefix = 'Fwd: ';
					}  elseif ($action['type'] == 'REPLY') {
						$prefix = 'Re: ';
					} else {
						$prefix = '';
					}
					$subject = $prefix.$this->getSubject();
					$from = $this->getFrom();
                    
					$params = array(
						'cc' => Env::Post('cc'),
						'bcc' => Env::Post('bcc'),
						'from' => $from, 
						'reply-to' => $from
					);
					
					$send_mail = true;
					//var_dump(Env::Post('assigned')); 
					if (Env::Post('assigned')) {
						$to = Contact::getName(Env::Post('assigned'), Contact::FORMAT_NAME_EMAIL, false, false);
						if ($action['assigned_c_id'] == -2 || $action['type'] == 'FORWARD-ASSIGN') {
							$assigned = $info['assigned_c_id'] = Env::Post('assigned');
						}	
					} else {
                    	$to = Env::Post('to');
					}
					if ($action['type'] == 'FORWARD' && $action['assigned_c_id'] > 0) {
						$assigned_to = Contact::getName($assigned, Contact::FORMAT_NAME_EMAIL, false, false);
						$user_id = Contact::getInfo($assigned, false, 'U_ID');
						
						$right = new Rights($user_id, Rights::USER);
                        if ($right->get('ST', 'MESSAGES', 'ASSIGNED', Rights::MODE_ONE, Rights::RETURN_INT)){
                            //$text = User::getSetting('assign_template', 'ST', '');
                            //if (!$text) {
                            $text = '{TEXT}<br /><br />
<blockquote type="CITE" style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
'. _('Original request') .': <a href="{REQUEST_URL}">{REQUEST_ID}</a>
<br /><br />
{REQUEST_TEXT}
</blockquote>';
                            //}
		                    $template = new STTemplate();
		                    $template->setContact(Contact::getInfo($assigned));
		                    $template->setRequest($this->request_info);
		                    $template->setParams(array('TEXT' => $info['text']));

		                    $text = $template->get($text);
                            $this->sendEmail($assigned_to, $subject, $text, $params, $attachments);
						}
					}
					
					$message = $info['text'];
					if ($action['type'] == 'FORWARD-ASSIGN') {
						$action_model = new STActionModel();
						$t = $action_model->getParams($action['id'], 'template');
						if ($t) {
							if (strstr($t, '{TEXT}') === false) {
								$t = '{TEXT}<br />'.$t;
							}
							$template = new STTemplate();
							$template->setRequest($this->request_info);
							$template->setContact(Contact::getInfo(Env::Post('assigned')));
							$t = preg_replace('!\[`(.*?)`\]!uise', '_("$1")', $t);
							$template->setParams(array('TEXT' => $info['text']));
							$message = $template->get($t);
						}
					}
					
					if ($send_mail) {
						$this->sendEmail($to, $subject, $message, $params, $attachments);
					}
					$info['to'] = $to.'|'.$params['cc'].'|'.$params['bcc'];
					$success = $request_log_model->add($this->request_id, $info);						
	
					break;
                case 'ASSIGN':
                    $assigned = Env::Post('assigned', Env::TYPE_INT, 0);    
                    $subject = $this->getSubject();
                    $info['text'] = Env::Post('text');
                    
                    $info['text'] = str_replace(array("  ", "\r\n", "\n"), array("&nbsp;&nbsp;", "<br />", "<br />"), $info['text']);
                    $user_id = Contact::getInfo($assigned, false, 'U_ID');
                    $text = '{TEXT}<br /><br />
<blockquote type="CITE" style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex">
'. _('Original request') .': <a href="{REQUEST_URL}">{REQUEST_ID}</a>
<br /><br />
{REQUEST_TEXT}
</blockquote>';
                 	$to = Contact::getName($assigned, Contact::FORMAT_NAME_EMAIL, false, false);
                 	$rights = new Rights($user_id);   
			        if ($rights->get('ST','MESSAGES','ASSIGNED')) {
	                    $template = new STTemplate();
	                    $template->setContact(Contact::getInfo($assigned));
	                    $template->setRequest($this->request_info);
	                    $template->setParams(array('TEXT' => $info['text']));
			        	
			     
                    	$from = $this->getFrom();
	                    $params = array(
							'from' => $from, 
							'reply-to' => $from                    
	                    );
			        	
                        $this->response['mail'] = $this->sendEmail($to, $subject, $template->get($text), $params);
                    }
                    
                    $info['assigned_c_id'] = $assigned;
                    $info['to'] = $to;
                    
                    $success = $request_log_model->add($this->request_id, $info);
                    break;
				case 'COMMENT':
					$info['text'] = Env::Post('text');				
					if (isset($action['properties']['editor']) && $action['properties']['editor'] == 'html') {
						// @todo: clear html
					} else {
						$info['text'] = str_replace(array("  ", "\r\n", "\n"), array("&nbsp; ", "<br />", "<br />"), htmlspecialchars($info['text']));
					}
					
					if (Env::Post('cc')) {
						$from = $this->getFrom();
						$to = Env::Post('cc');
						$info['to'] = '|'.$to;
						$params = array(
							'to' => $to,
							'from' => $from, 
							'reply-to' => $from						
						);
						$action_model = new STActionModel();
						$t = $action_model->getParams($action['id'], 'template');
						if ($t) {
							if (strstr($t, '{TEXT}') === false) {
								$t = '{TEXT}<br />'.$t;
							}
							$template = new STTemplate();
							$template->setRequest($this->request_info);
							//$template->setContact(Contact::getInfo(Env::Post('assigned')));
							$t = preg_replace('!\[`(.*?)`\]!uise', '_("$1")', $t);
							$template->setParams(array('TEXT' => $info['text']));
							$message = $template->get($t);
							$this->sendEmail($to, $this->getSubject(), $message, $params);
						}						
					}
					$success = $request_log_model->add($this->request_id, $info);
					break;
				default:
					$success = $request_log_model->add($this->request_id, $info);
			}
		}
        if ($success){
            if ($_FILES){
                $files = $_FILES['files'];
	            $attachments = array();
	            $files_length = sizeof($files['name']);
	            $errors = false;
	            $path = STRequest::getAttachmentsPath($this->request_id, $success)."/";
	            for ($key=0; $key<$files_length; $key++){
		            if ($files["error"][$key] == UPLOAD_ERR_OK) {
			            $file_id = uniqid();
			            $attachments[] = array(
			                "name" => $files['name'][$key],
			                "type" => $files['type'][$key],
			                "file"  => $file_id,
			                "size" => $files['size'][$key]
			            );
			            $tmp_name = $files["tmp_name"][$key];
			            if (!move_uploaded_file($tmp_name, $path.$file_id)){
			               $errors = true;
			            }
		            }
	            }
	            if (!$errors) $request_log_model->saveAttachments($success, $attachments);
            }
            $success = $request_model->set($this->request_id, $action['state_id'], $assigned);
            if (($info['actor_c_id'] != $info['assigned_c_id']) && ($states[$action['state_id']]['group'] == -101)) {
                $request_model->setRead($this->request_id, 0);
            }
        }
		$this->response['success'] = $success;
        
        $info['log_name'] = $action['log_name'];
        $info['datetime'] = WbsDateTime::getTime(time());
        $info['contact'] = Contact::getName($info['actor_c_id']);
        //$this->response['info'] = $info;
	}
	
	protected function getSubject()
	{
		$subject = $this->request_info['subject'];
		$subject .= ' [ID:'.$this->request_id.']';
		
		return $subject;
	}

    protected function getFrom() {
        $source_model = new STSourceModel();
        $sources = $source_model->getAll();
        $from = Company::getName().' <'.Wbs::getSystemObj()->getEmail().'>';
        if (sizeof($sources) == 1){
            $source = array_shift($sources);
            $from_name = $source['name'];
            $from_email = $source_model->getParams($source['id'], 'email');
            $from = $from_name .' <'.$from_email.'>';
        } else {
            $source_emails = $source_model->getEmails();
            $source = false;
            if (
               $this->request_info['source_type'] == "email"
               &&
               isset($source_emails[$this->request_info['source']])
            ){
                $from = $source_emails[$this->request_info['source']];               
            } elseif ($this->request_info['source_type'] == "form"){
                try {
                    $widget = new SupportForm($this->request_info['source']);        
                    $source_id = $widget->getParam('SOURCEID');
                    $source = $source_model->getById($source_id);
                    if ($source) {
                    	$from = $source_model->getParams($source_id, 'email');
                    	$from = $source['name'].' <'.$from.'>';
                    }
                } catch (Exception $e) {
                }
            } else {
                $lang = Contact::getInfo($this->request_info['client_c_id'], false, 'C_LANGUAGE');
                if (!$lang) {
                    $lang = 'eng';
                }
                if (User::getSetting('DEFAULT_EMAIL_'.$lang, 'ST', '')) {
                    $from = User::getSetting('DEFAULT_EMAIL_'.$lang, 'ST', '');
                }
                $source = $source_model->getById($source_model->getByEmail($from));
                if ($source) {
                    $from = $source['name'] .' <'.$from.'>';
                }
            }
        }
        return $from;
    }
    
	protected function getReplyTo()
	{
		$source_model = new STSourceModel();
		$source_info = $source_model->get($this->request_info['source_id']);
		return $source_info['name'] .' <'.$source_model->getParams($this->request_info['source_id'], 'email').">";
	}
	
	public function sendEmail($to, $subject, $content, $params = false, $attachments = false)
	{
		$message = Mailer::composeMessage();
		if (isset($params['from'])) {
			$from = $params['from'];
		} else {
			if (User::getInfo(false, 'C_EMAILADDRESS')) {
				$from = Contact::getName(User::getContactId(), Contact::FORMAT_NAME_EMAIL, false, false);			
			} else {
				$from = Company::getName();
				if ($from) {
					$from .= " <".Wbs::getSystemObj()->getEmail().">";
				} else {
					$from = Wbs::getSystemObj()->getEmail();
				}
			}
		}
		$message->addFrom($from);
		if (isset($params['reply-to'])) {
			$message->addReplyTo($params['reply-to']);
		}		
		$message->addTo($to);
		if (isset($params['cc'])) {
			$message->addCc($params['cc']); 
		}
		if (isset($params['bcc'])) {
			$message->addBcc($params['bcc']); 
		}		
		$message->addSubject($subject);
		$message->addContent($content);
		if (!empty($attachments)) $message->addAttachments($attachments);
		$message->addAppId('-U');
		
		return Mailer::send($message);		
	}
}

?>
