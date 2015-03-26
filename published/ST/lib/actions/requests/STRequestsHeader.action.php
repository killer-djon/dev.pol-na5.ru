<?php
class STRequestsHeaderAction extends Action 
{
	protected $request_id;
	protected $request_info;
	protected $full = false;
	
	public function __construct($full = false)
	{
		$this->full = $full;
		parent::__construct();
		$this->request_id = Env::Get('id', Env::TYPE_INT, 0);
		$request_model = new STRequestModel();
		$this->request_info = $request_model->get($this->request_id);
	}
	
	private function asorti($arr) { 
	   $arr2 = $arr; 
	   foreach($arr2 as $key => $val) { 
	      $arr2[$key] = strtolower($val); 
	   } 
	  
	   asort($arr2); 
	   foreach($arr2 as $key => $val) { 
	      $arr2[$key] = $arr[$key]; 
	   } 
	
	   return $arr2; 
	} 
	
	public function prepare()
	{
        
        $this->request_info['client_from'] = htmlspecialchars_decode($this->request_info['client_from']);
        
		$this->view->assign('request', $this->request_info);
		
		$this->view->assign('full', $this->full);
		
		$state_action_model = new STStateActionModel();
		$actions = $state_action_model->getByState($this->request_info['state_id'], 'user');
		
		$action_model = new STActionModel();
		
		$template = new STTemplate();
		$template->setRequest($this->request_info);
		
        $source_model = new STSourceModel();
        $source = false;
        
        $email = User::getSetting('DEFAULT_EMAIL_rus', 'ST', '');
        if (!$email) $email = User::getEmail();
        $sources = $source_model->getAll();
	    if (sizeof($sources) == 1){
            $source = array_shift($sources);
        } else {
			if ($this->request_info['source_type'] == 'email' && $this->request_info['source']) {
				$source_id = $source_model->getByEmail($this->request_info['source']);
				$source = $source_model->getById($source_id);
			} elseif ($this->request_info['source_type'] == "form"){
	            try {
	               $widget = new SupportForm($this->request_info['source']);        
	               $source_id = $widget->getParam('SOURCEID');
	               $source = $source_model->getById($source_id);
	            } catch (Exception $e) {
	            }
	        } elseif ($this->request_info['source_type'] == "cabinet"){
	            $contact_info = Contact::getInfo($this->request_info['client_c_id']);
	            if (empty($contact_info['C_LANGUAGE'])) {
	               $contact_info['C_LANGUAGE'] = 'eng';
	            }
	            $email = User::getSetting('DEFAULT_EMAIL_'.$contact_info['C_LANGUAGE'], 'ST', '');
	            $source = $source_model->getByEmail($email);
	        }
        }
		if (!$source) {
            $source = $source_model->getByEmail($source_model->getByEmail($email));
		} else {
            $email = $source['name']." <".$source_model->getParams($source['id'], 'email').">";
		}
		
        $this->view->assign('email', $email);
        
        $signature = $source_model->getParams($source['id'], 'signature');
		
        $signature = preg_replace( "`(!\")((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?`i", "<a href=\"http\\3://\\5\\6\\8\\9\" title=\"\\0\">\\5\\6</a>", $signature);
        
		$signature = str_replace("\n", "<br />", $signature);

        $signature = "<div class='wa-st-signature'>".$signature."</div>";
        
		$action_params = array();
		$other_actions = 0;
		foreach ($actions as $i => $a) {
			if ($a['type'] == 'FORWARD' || $a['type'] == 'REPLY') {
				$action_params[$i] = $action_model->getParams($a['id']);
				if (isset($action_params[$i]['template'])) {
					$contact_info = $a['type'] == 'REPLY' && $this->request_info['client_c_id'] ?  Contact::getInfo($this->request_info['client_c_id']) : array();
					$template->setContact($contact_info);
                    $template->setRequest($this->request_info);
					$t = $action_params[$i]['template'];
					$t = preg_replace('!\[`(.*?)`\]!uise', '_("$1")', $t);
					if ($a['type'] == 'REPLY') {
						 $t .= "<br />\n-- \n<br /><div class='wa-st-signature-wrap'>".$signature."</div>";
						//$t = "<br /><div style='-moz-user-select: none; -khtml-user-select:none;' unselectable='on' contenteditable='false'>"."-- \n<br />".$signature."</div>";
					}
				    if ($a['type'] == 'FORWARD') {
                         $t = mb_substr($t,0,mb_strpos($t,'--'))."<div class='wa-st-signature'>".mb_substr($t,mb_strpos($t,'--')-2)."</div>";
                    }
					$action_params[$i]['template'] = $template->get($t);
				} 
			}
			if ($a['sorting'] > 0) {
				$other_actions++;
			}
		}
		
		$this->view->assign('other_actions', $other_actions);
		
		$this->view->assign('actions', $actions);
		$this->view->assign('actions_json', $actions, View::TYPE_JSON);
		$this->view->assign('action_params', $action_params);

		// Get emails for from
		$emails = $source_model->getEmails();
		$user_email = User::getEmail();
		if ($user_email) {
			$emails[$user_email] = User::getEmail(true);
		} 
		$this->view->assign('emails', $emails);
		
		/*
		if (isset($request_info['source_type']) && $request_info['source_type'] == 'email') {
			$email = $request_info['source'];
		} else {
			$email = User::getSetting('email', 'ST', '');
		}
		$this->view->assign('email', $email);
		*/
        
		// Get All Users which have access to the application
        $user_rights_model = new UserRightsModel();
        $rights = $user_rights_model->getUsers('ST', 'SCREENS', 'RL');
        
        $users_model = new UsersModel();
        $names = $users_model->getNames('U_ID');
        $users = array();
        foreach ($rights as $user_id => $r) {
        	if ($r[0] > 0) {
                /*$right = new Rights($user_id, Rights::USER);
        	    if ($right->get('ST', 'MESSAGES', 'ASSIGNED', Rights::MODE_ONE, Rights::RETURN_INT)){
        		  $users[$names[$user_id]['C_ID']] = '"'.$names[$user_id]['C_FULLNAME'].'"'.($names[$user_id]['C_EMAILADDRESS'] ? ' &lt;'.$names[$user_id]['C_EMAILADDRESS'].'&gt;' : '');
        	    }*/
        	    $users[$names[$user_id]['C_ID']] = $names[$user_id]['C_FULLNAME'];
        	}
        }
        
        $users = $this->asorti($users);
		$this->view->assign('users', $users);		
        $this->view->assign('current_user', User::getInfo(User::getId()));
             
	}
}