<?php
class STPersonalSaveAction extends Action
{
    protected $contact_id;
    public function __construct($contact_id)
    {
        parent::__construct();
        $this->contact_id = $contact_id;
    }
    
    public function prepare()
    {
        $request['subject'] = Env::Post('summary');
        $request['text'] = Env::Post('text');
        $request['read'] = 0;
        $request['source_type'] = 'cabinet';
        $email = Contact::getName($this->contact_id, Contact::FORMAT_NAME_EMAIL, false, false);
        $request['source'] = $email;
        $request['priority'] = 0;
        $request['datetime'] = date("Y-m-d H:i:s");   
        $request['assigned_c_id'] = 0;
        $action_model = new STActionModel();
        $action = $action_model->getByType('ACCEPT', 'system');
        if ($action['state_id']) {
            $request['state_id'] = $action['state_id'];     
        } else {
            $request['state_id'] = 0;
        }
        $request['client_c_id'] = $this->contact_id;
        $request['client_from'] = $email;
        
        $request_id = STRequest::add($request);
        
        
        $action_model = new STActionModel();
        $request_model = new STRequestModel();
        
        $files = $_FILES['files'];
        if ($files){
	        $attachments = array();
	        $files_length = sizeof($files['name']);
	        $errors = false;
	        $path = STRequest::getAttachmentsPath($request_id)."/";
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
	        if (!$errors) $request_model->save($request_id, array('attachments' => $attachments));
        }
        
        $action = $action_model->getByType('ACCEPT', 'system');
        if ($action['state_id']) {          
            $request_model->set($request_id, $action['state_id']);
        }
        $this->view->assign('request', $request_id);
        if ($request_id){
	        $server_url = Url::getServerUrl();
	        $request_uri = Env::Server('REQUEST_URI');
	        $this->view->assign('request_uri', $server_url.$request_uri);
            header("Location: ". str_replace('&a=save','',$server_url.$request_uri));
        }
    }
}