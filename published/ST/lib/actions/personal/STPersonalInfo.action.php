<?php
class STPersonalInfoAction extends Action
{
    protected $request_id;
    protected $action_id;
    protected $user_id;
    protected $backlink;

    protected $request;
    protected $request_info;

    public function __construct($user_id)
    {
        parent::__construct();
        $this->user_id = $user_id;
        $this->action_id = Env::Get('action', Env::TYPE_STRING, false);
        $this->request_id = Env::Get('id', Env::TYPE_INT, 0);
        
        $this->backlink = str_replace('&sb=y','',Url::Get('/personal.php').'?key='.Env::Get('key').'&DK_KEY='.Env::Get('DK_KEY').'&iframe='.Env::Get('iframe').'&t=requests');
        if ($this->action_id) {
            $this->save();
        }
        $this->request = new STRequest($this->request_id);    
    }

    private function save(){
        $request_model = new STRequestModel();
        $this->request_info = $request_model->get($this->request_id);

        $action_model = new STActionModel();
        $action = $action_model->get($this->action_id);

        $states_model = new STStateModel();
        $states = $states_model->getAll();
        
        if (!$action['state_id']) {
            // State of the request does not change.
            $action['state_id'] = $this->request_info['state_id'];
        }

        if ($action['assigned_c_id'] > 0) {
            $assigned = $action['assigned_c_id'];
        } elseif ($action['assigned_c_id'] == -3) {
        	$assigned = -3;
        } else {
            $assigned = $this->request_info['assigned_c_id'];
        }
        
        if ($action['log_name']) {
            $info = array();

            $info['actor_c_id'] = $this->user_id;
            $info['state_id'] = $action['state_id'];
            $info['action_id'] = $this->action_id;
            $info['assigned_c_id'] = $assigned > 0 ? $assigned : 0;
            $request_log_model = new STRequestLogModel();

            switch ($action['type']) {
                case 'CLIENT-REOPEN':
                    $info['text'] = Env::Post('reopen');
                    $success = $request_log_model->add($this->request_id, $info);
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
                    break;
                default:
                    $success = $request_log_model->add($this->request_id, $info);
            }
        }

        if ($success){
            $success = $request_model->set($this->request_id, $action['state_id'], $assigned);
            if ($states[$action['state_id']]['group'] == -101) {
                $request_model->setRead($this->request_id, 0);
            }
            if ($success && $action['type']=='CLIENT-REOPEN'){
                header("Location: ". $this->backlink."&sb=y#id=".$this->request_id);
            }
        }
    }

    public function prepare()
    {
        $request_model = new STRequestModel();
        $this->request_info = $this->request->getInfo();
        
        if (!empty($this->request_info['client_c_id'])){
            $this->request_info['from_name'] = Contact::getName($this->request_info['client_c_id']);
        }
         
        $states_model = new STStateModel();
        $states = $states_model->getAll();
        $this->view->assign('states', $states);
          
        $action_model = new STActionModel();
        $st_action_model = new STStateActionModel();
        $all_actions = $action_model->getAll();
        $actions = $st_action_model->getByState($this->request_info['state_id'], 'client');
        
        $this->view->assign('actions', $actions);
        $this->view->assign('all_actions', $all_actions);
        
        if (isset($states[2]['properties']->css)){        
	        $color = $states[2]['properties']->css;
	        $color = substr($color,strpos($color,'color:')+6);
	        if (strpos($color,';')>0) $color = substr($color,0,strpos($color,';'));
	        $color = str_replace("rgb","rgba",$color);
	        $color = str_replace(")",",0.15)",$color);
        } else {
            $color = "rgb(255,255,255)";
        }
        $this->view->assign('request_color', $color);
             
        if (isset($states[$this->request_info['state_id']]['properties']->css)){
	        $color = $states[$this->request_info['state_id']]['properties']->css;
	        $color = substr($color,strpos($color,'color:')+6);
	        if (strpos($color,';')>0) $color = substr($color,0,strpos($color,';'));
        } else {
            $color = "rgb(0,0,0)";
        }
        $this->view->assign('current_state_color', $color);
        
        if (isset($states[$this->request_info['state_id']])){
           $this->request_info['state'] =  $states[$this->request_info['state_id']]['name'];
        } else {
           $this->request_info['state'] = _s('Unknown');
        }
        
        $client_actions= array();
        foreach ($all_actions as $action){
            if ($action['group']=="client" || $action['type']=="REPLY" || $action['type']=="EMAIL-CLIENT")
            $client_actions[] = "'".$action['id']."'";
        }
        $client_actions = implode(',',$client_actions);
        
        $request_log_model = new STRequestLogModel();
        $log = $request_log_model->getByRequest($this->request_id,false,false,false,$client_actions);
        
        $log = STRequest::prepareLogs($log, $this->request_info);
        foreach ($log as &$l) {
            $l['text'] = str_replace("-- ", "", $l['text']);
            $l['datetime'] = WbsDateTime::getTime(strtotime($l['datetime']), TimeZones::getTimeZone(2, 0), 'H:i');
            if ($all_actions[$l['action_id']]['group']!="client" && $all_actions[$l['action_id']]['type'] != 'EMAIL-CLIENT') {
                $l['account'] = Company::getName();
            }
        }
        $this->view->assign('log', $log);

        $this->request_info['datetime'] = WbsDateTime::getTime(strtotime($this->request_info['datetime']), TimeZones::getTimeZone(2, 0), 'H:i');

        $this->view->assign('request', $this->request_info);

        $this->view->assign('backlink', $this->backlink); 
    }
}