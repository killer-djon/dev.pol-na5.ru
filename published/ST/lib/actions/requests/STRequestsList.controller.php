<?php

class STRequestsListController extends JsonController
{
	public function exec()
	{
        session_write_close();
        $limit = Env::Get('limit', Env::TYPE_STRING);
        if (!$limit) {
            $limit = User::getSetting('LIMIT');
        }
		if (!$limit) {
			$limit = 10;
		}
		$filters = array();
		$page = Env::Get('p', Env::TYPE_INT, 1);
        $offset = Env::Get('offset', Env::TYPE_INT, 0);
        $sort = Env::Get('sort', Env::TYPE_STRING);
        $order = Env::Get('order', Env::TYPE_STRING);
        $search = Env::Get('search', Env::TYPE_STRING, false);
        $refreshed = Env::Get('refreshed', Env::TYPE_STRING, false);
        
        $order = $order == 'desc' ? "DESC" : "ASC";
        
        $state_id = Env::Get('stateid', Env::TYPE_STRING, false);
        $filter = Env::Get('filter', Env::TYPE_STRING, false);
        $single_row = Env::Get('singlerow', Env::TYPE_STRING, false);
        $last_id = Env::Get('lastid', Env::TYPE_STRING, false);
        $hiddenColumns = array();
        
        $state_model = new STStateModel();
        $states = $state_model->getAll();
        
        if ($state_id && $state_id != 'all') {
            $filters['state_id'] = $state_id;
        }
	
        if ($filter == 'open') {
            $states_group = $state_model->getByGroup("-101");
            foreach($states_group as $state){
                $filters['state_id'][] = $state['id'];
            }
        }
        
	    if ($filter == 'archive') {
            $states_group = $state_model->getByGroup("-102");
            foreach($states_group as $state){
                $filters['state_id'][] = $state['id'];
            }
        }
        
	    if ($filter == 'notassigned') {
            $filters['assigned_c_id'] = '';
        }
        
	    if (strpos($filter,'assigned')>-1 ) {
            $filters['assigned_c_id'] = intval(substr($filter, strpos($filter,'-')+1));
            if (!strpos($filter,'all')){
		        $states_group = $state_model->getByGroup("-101");
		        foreach($states_group as $state){
                    $filters['state_id'][] = $state['id'];
		        }
            }
        }
        
        if (strpos($filter,'state')>-1 ) {
            $filters['state_id'] = intval(substr($filter, strpos($filter,'-')+1));
        }
        
        if ($filter == 'trash') {
            $filters['state_id'] = -1;
            $hiddenColumns = array('state_id','assigned_c_id','new_window');
        }
        
        if ($filter == 'my') {
            $filters['assigned_c_id'] = User::getContactId();
        }
        
        if ($filter == 'unread') {
            $filters['assigned_c_id'] = User::getContactId();
            $filters['read'] = 0;
        }
        
	    if ($filter == 'verification') {
            $filters['state_id'] = 0;
            $hiddenColumns = array('state_id','assigned_c_id','new_window');
        }
    
		if (!$offset){
			$offset = $limit * ($page - 1);	
		}
		
		$request_model = new STRequestModel();
		if (!($last_id)){
			if (!$single_row) {
	            $requests = $request_model->getAll(empty($sort)?false:$sort." ".$order, $offset.", ".$limit, $filters, $search, '*', false, $refreshed);
			} else {
	            $requests = array($request_model->get($single_row));
			}
		} else {
	        $filters['after_id'] = $last_id;
            $requests = $request_model->getAll(empty($sort)?false:$sort." ".$order, $offset.", ".$limit, $filters, $search, '*');
            
        }
		
        $result = STRequest::prepareRequests($requests, $hiddenColumns);
        unset($filters['after_id']);
		$this->response = array('requests' => $result, 'count' => $request_model->countAll($filters, $search));
	}
}