<?php

class STPersonalSaveController extends JsonController
{
	
	public function exec()
	{
        $request['subject'] = Env::Post('summary');
        $request['text'] = Env::Post('text');
        $request['read'] = 0;
        $request['source_type'] = 'cabinet';
        $email = User::getEmail(true);
        $request['source'] = $email;
        $request['priority'] = 0;
        $request['datetime'] = date("Y-m-d H:i:s");   
        $request['assigned_c_id'] = 0;
        $request['state_id'] = 2;
        $request['client_c_id'] = User::getContactId();
        $request['client_from'] = $email;

        $request_id = STRequest::add($request);
        
        $action_model = new STActionModel();
        $request_model = new STRequestModel();
        $action = $action_model->getByType('ACCEPT', 'system');
        if ($action['state_id']) {          
            $request_model->set($request_id, $action['state_id']);
        }
		
	}
}

?>