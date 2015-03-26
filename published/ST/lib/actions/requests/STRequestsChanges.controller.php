<?php

class STRequestsChangesController extends JsonController
{
    protected $request_id;
    protected $last_req_log_id;
    protected $last_req_id;
    protected $last_log_id;
    protected $timestamp;
    
    public function exec()
    {
        session_write_close();
        $filters = array();
        $log = array();
        
        $filters['assigned_c_id'] = User::getContactId();
        $filters['read'] = 0;
        $request_model = new STRequestModel();
        $count = $request_model->countAll($filters);
        
        $this->request_id = Env::Get('id', Env::TYPE_INT, 0);
        $this->last_req_log_id = Env::Get('lastreqlogid', Env::TYPE_INT, 0);
        $this->last_req_id = Env::Get('lastreqid', Env::TYPE_INT, 0);
        $this->last_log_id = Env::Get('lastlogid', Env::TYPE_INT, 0);
        // print_r(date('Y-m-d H:i:s', $this->timestamp));
        
        $request_log_model = new STRequestLogModel();
        if ($this->request_id>0){
	        $log = $request_log_model->getByRequest($this->request_id, false, $this->last_req_log_id, User::getContactId());
	        
	        foreach ($log as &$l) {
	            $l['contact'] = Contact::getName($l['actor_c_id']);
	            $l['datetime'] = WbsDateTime::getTime(strtotime($l['datetime']));
	        }
        }
        $updatedRequests = $request_log_model->getUpdatedRequests($this->last_log_id);
        $newRequests = $request_model->getAll(false, 1, array('after_id' => $this->last_req_id), false, "COUNT(*)", true);

        $this->response = array(
                'count' => $count,
                'last_req_id' => $request_model->getLastId(),
                'last_log_id' => $request_log_model->getLastId(),
                'log' => $log,
                'updated' => STRequest::prepareRequests($updatedRequests),
                'newRequests' => $newRequests
        );
    }
}