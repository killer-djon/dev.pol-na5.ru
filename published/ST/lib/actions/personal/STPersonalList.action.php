<?php
class STPersonalListAction extends Action
{
    
    protected $user_id;
    
    public function __construct($user_id)
    {
        parent::__construct();
        $this->user_id = $user_id;
    }
    
	public function prepare()
	{
        $limit = Env::Get('limit', Env::TYPE_STRING);
        if (!$limit) {
            $limit = User::getSetting('LIMIT');
        }
        if (!$limit) {
            $limit = 10000;
        }
        $filters = array();
        $page = Env::Get('p', Env::TYPE_INT, 1);
        $offset = Env::Get('offset', Env::TYPE_INT, 0);
        
        $sort = Env::Get('sort', Env::TYPE_STRING, "id");
        $order = Env::Get('order', Env::TYPE_STRING, 'desc');
        $order = $order == 'desc' ? "DESC" : "ASC";
        
        $state_model = new STStateModel();
        $states = $state_model->getAll();
        
        $contact_id = substr(Env::Get('key'), 6, -6);
        $filters['client_c_id'] = $contact_id;
                
        if (!$offset){
            $offset = $limit * ($page - 1);
        }
        
        $request_model = new STRequestModel();
        $requests = $request_model->getAll(empty($sort)?false:$sort." ".$order, $offset.", ".$limit, $filters, false, '*', false);
	    
        $state_model = new STStateModel();
        $states = $state_model->getAll();
        
        $result = array();
        foreach ($requests as $request) {
            
            $date = WbsDateTime::getTime(strtotime($request['datetime']), TimeZones::getTimeZone(2, 0), 'H:i');
            if ($states[$request['state_id']]['group'] == -101) {
                $state_id = 0;
            } else {
                $state_id = 1;
            }
            $currentEl = array(
                'id' => $request['id'],
                'datetime' => $date,
                'subject' => $request['subject'],
                'state_id' => $state_id,
                'justsent' => ((time() - strtotime($request['datetime']))<60 ? 1:0)
            );
            
            $result[] = $currentEl;
        }

        $this->view->assign('requests', $result);
        $this->view->assign('count', $request_model->countAll($filters, false, true));
        
        $url = Url::get("/");
        
        $this->view->assign('user_id', $this->user_id);
        $this->view->assign('url_published', $url);
	}
}