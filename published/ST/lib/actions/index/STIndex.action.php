<?php

class STIndexAction extends Action
{
	
	public function getSettings()
	{
		$settings = array();
		// Hide button new request
		if (!User::getSetting('manual', 'ST', '')) {
			$settings['hiddenElements'] = array('new-request');
		}
		return $settings;
	}
	
	public function getData()
	{
		$data = array();
		
		$state_model = new STStateModel();
		$data['states'] = $state_model->getAll();
		
        $action_model = new STActionModel();
        $data['actions'] = $action_model->getAll();
		return $data;
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
		
		$this->view->assign('data', $this->getData(), View::TYPE_JSON);
		$this->view->assign('settings', $this->getSettings(), View::TYPE_JSON);
        $this->view->assign('user_id', User::getInfo(User::getId(),'C_ID'), View::TYPE_JSON);
        $this->view->assign('user_is_admin', User::hasAccess('ST','FUNCTIONS','ADMIN'), View::TYPE_JSON);
        
        $filters = array('assigned_c_id' => User::getContactId(), 'read' => 0);
        $request_model = new STRequestModel();
        $request_log_model = new STRequestLogModel();
        $this->view->assign('unread_count', $request_model->countAll($filters), View::TYPE_JSON);
        $this->view->assign('last_req_id', $request_model->getLastId(), View::TYPE_JSON);
        $this->view->assign('last_log_id', $request_log_model->getLastId(), View::TYPE_JSON);
        
        $requests_cnt = $request_model->countAll();
        $show_limit_msg = false;
        $limit = Limits::get('ST');
        if ($limit) {
        $this->view->assign('use_limit', 1);
        }
        if ($limit > 0 && $requests_cnt > $limit){
            $limit_msg_size = 25; 
            $k = $requests_cnt / $limit;
            if ($k > 2){
                $limit_msg_size = 50; 
            }
            if ($k > 3){
                $limit_msg_size = 75; 
            }
            if ($k > 4){
                $limit_msg_size = 100; 
            }
            $this->view->assign('limit_msg_size', $limit_msg_size);
            $show_limit_msg = true;
	        if (Wbs::isHosted() && User::hasAccess('AA')) {
	            $limit_msg_link = '<br /><a style="color:white;text-decoration:underline;" target="_top" href="'.Url::get('/index.php').'?url='.Url::get('/AA/html/scripts/change_plan.php').'">'._s("Upgrade account").'</a>';
	        } else {
	            $limit_msg_link = '<br />'._s("Please refer to your account administrator.");
	        }
            $this->view->assign('limit_msg_link', $limit_msg_link);
            $this->view->assign('requests_cnt', $requests_cnt);
            $this->view->assign('limit', $limit);
        }
        $this->view->assign('show_limit_msg', $show_limit_msg);
        if (!Wbs::isHosted()) {
        	$this->view->assign('has_requests', true);
        } else {
        	$this->view->assign('has_requests', ($requests_cnt > 0 || User::getSetting('HAS_REQUESTS', 'ST', '')));
        }
        
        $source_model = new STSourceModel();
        
        $has_sources = $source_model->getAll();
        if (!$has_sources) {
            $widgets_model = new WidgetsModel();
	        $has_sources = $widgets_model->getByType('ST');
        }
        if (!$has_sources){
            $this->view->assign('has_sources', false);
        } else {
            $this->view->assign('has_sources', true);
        }
        
        $default_email = $source_model->getEmailByParam('isdefault',1,1);
        if (!empty($default_email)) {
            $default_email = array_shift($default_email);
        }
        $this->view->assign('default_email', $default_email);
        
        $user_rights_model = new UserRightsModel();
        $rights = $user_rights_model->getUsers('ST', 'SCREENS', 'RL');
        
        $users_model = new UsersModel();
        $names = $users_model->getNames('U_ID');
        $users = array();
        foreach ($rights as $user_id => $r) {
            if ($r[0] > 0) {
                $users[$names[$user_id]['C_ID']] = $names[$user_id]['C_FULLNAME']; 
            //    $users[$names[$user_id]['C_ID']]['id'] = $names[$user_id]['C_ID']; 
            }
        }
        $users = $this->asorti($users);
        $users_sorted = array();
	    foreach ($users as $user_id => $user) {
	        $name = $users[$user_id];
            $users_sorted[] = array('name' => $name, 'id' => $user_id);
        }
        $this->view->assign('users', $users_sorted, View::TYPE_JSON);   
	}
} 

?>