<?php

class STRequestsNewController extends JsonController
{
    public function exec()
    {
    	$action_model = new STActionModel();
    	$action = $action_model->getByType('ACCEPT', 'system');
        $data = array(
	        'datetime' => date('Y-m-d H:i:s', time()),
            'source_type' => 'user',
        	'source' => User::getContactId(),
	        'state_id' => isset($action['state_id']) ? $action['state_id'] : 0,
	        'priority' => 0,
	        'client_from' => Env::Post('client_from',Env::TYPE_STRING), 
            'client_c_id' => Env::Post('client_c_id',Env::TYPE_INT), 
	        'assigned_c_id' => Env::Post('assigned_c_id',Env::TYPE_INT),
            'read' => 0,
	        'subject' => Env::Post('subject',Env::TYPE_STRING),
	        'text' => Env::Post('text',Env::TYPE_STRING)
        )
        ;
        $classes = Env::Post('classes');
        $this->response = STRequest::add($data, $classes);
   	}
}