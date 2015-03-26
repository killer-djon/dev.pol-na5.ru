<?php

class STRequestsUnreadcountController extends JsonController
{
	public function exec()
	{
		$filters = array();
        
        $filters['assigned_c_id'] = User::getContactId();
        $filters['read'] = 0;
		
		$request_model = new STRequestModel();
		
		$this->response = array('count' => $request_model->countAll($filters));
	}
}
